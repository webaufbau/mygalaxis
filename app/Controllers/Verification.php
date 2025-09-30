<?php

namespace App\Controllers;

use App\Libraries\TwilioService;

class Verification extends BaseController {
    public function index() {
        $maxWaitTime = 5; // Sekunden
        $waited = 0;

        // session()->set('uuid', '640e8804-e219-43d8-b529-faf247c606b3');

        $uuid = session()->get('uuid');

        while (!$uuid = session()->get('uuid')) {
            sleep(1); // 1 Sekunde warten
            $waited++;

            if ($waited >= $maxWaitTime) {
                log_message('info', 'Verifikation kann nicht gemacht werden uuid fehlt nach 5 Sekunden' . print_r($_SESSION, true));

                return redirect()->to(session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de']); // Fehlerseite oder Hinweis
            }
        }

        $db = \Config\Database::connect();
        $builder = $db->table('offers');

        $maxWaitTime = 18; // Maximal x Sekunden warten
        $waited = 0;
        $sleepInterval = 1; // Sekunde

        $row = null;

        while ($waited < $maxWaitTime) {
            $row = $builder->where('uuid', $uuid)->orderBy('created_at', 'DESC')->get()->getRow();

            if ($row) {
                break;
            }

            sleep($sleepInterval);
            $waited += $sleepInterval;
        }

        if (!$row) {
            log_message('info', 'Verifikation kann nicht gemacht werden kein Datensatz mit der UUID ' . $uuid . ': ' . print_r($_SESSION, true));
            log_message('info', 'Abfrage: ' . $builder->db()->getLastQuery());

            return redirect()->to(session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'])->with('error', lang('Verification.noOfferFound'));
        }

        // form_fields ist JSON, decode es:
        $fields = json_decode($row->form_fields, true);
        $phone = $fields['phone'] ?? '';

        $phone = $this->normalizePhone($phone);

        $isMobile = is_mobile_number($phone);

        $method = $isMobile ? 'sms' : 'call';

        // In Session schreiben
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        // Weiterleitung zu send(), um direkt Code zu verschicken
        $locale = getCurrentLocale(); // 'de', 'fr', 'en', ...

        if ($locale === 'de') {
            // Deutsch ohne Prefix
            return redirect()->to('/verification/send');
        } else {
            // Andere Sprachen mit Prefix
            return redirect()->to("/{$locale}/verification/send");
        }
    }

    public function processing() {
        $uuid = $this->request->getGet('uuid') ?? session()->get('uuid');

        log_message('info', 'Verifizierung processing: Warte auf Datensatz');
        return view('processing_request', [
            'siteConfig' => $this->siteConfig,
            'uuid' => $uuid,
        ]);
    }

    public function checkSession() {
        $uuid = $this->request->getGet('uuid') ?? session()->get('uuid');

        if (!$uuid) {
            log_message('info', 'Verifizierung checkSession: waiting');
            return $this->response->setJSON(['status' => 'waiting']);
        }

        // Datenbank prüfen
        $db = \Config\Database::connect();
        $row = $db->table('offers')
            ->where('uuid', $uuid)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRow();

        if ($row) {
            log_message('info', 'Verifizierung checkSession: ok: ' . $uuid);
            return $this->response->setJSON(['status' => 'ok']);
        }

        log_message('info', 'Verifizierung checkSession: waiting: ' . $uuid);
        return $this->response->setJSON(['status' => 'waiting']);
    }

    public function send() {
        $request = service('request');

        $phone = session()->get('phone');
        $method = session()->get('verify_method');

        if (!$phone) {
            log_message('info', 'Verifizierung gesendet: Verifizierung Telefonnummer fehlt.');
            return redirect()->back()->with('error', lang('Verification.phoneMissing'));
        }

        $phone = $this->normalizePhone($phone);

        // Prüfe, ob Mobilnummer
        $isMobile = is_mobile_number($phone);

        // Wenn kein Mobile, dann nur Anruf zulassen
        if (!$isMobile && $method !== 'call') {
            return redirect()->back()->with('error', lang('Verification.fixedLineOnlyCall'));
        }

        if (!$method) {
            return redirect()->back()->with('error', lang('Verification.chooseMethod'));
        }

        //$method = 'call';

        $verificationCode = rand(1000, 9999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        log_message('info', "Verifizierungscode $verificationCode via $method an $phone");

        $twilio = new TwilioService();
        $success = false;

        // Hilfsfunktion für korrekte locale-URL
        $locale = getCurrentLocale();
        $prefix = ($locale === 'de') ? '' : '/' . $locale;

        if ($method === 'sms') {
            $success = false; // $twilio->sendSms($phone, "Ihr Verifizierungscode lautet: $verificationCode");

            if ($success) {
                log_message('info', "SMS-Code an $phone gesendet.");
            } else {
                log_message('error', "Twilio SMS Fehler an $phone.");

                // Fallback: Infobip versuchen
                $infobip = new \App\Libraries\InfobipService();
                $message = lang('Verification.smsVerificationCode', [
                    'sitename' => $this->siteConfig->name,
                    'code' => $verificationCode
                ]);
                $infobipResponseArray = $infobip->sendSms($phone, $message);

                session()->set('sms_sent_status', $infobipResponseArray['status']);
                session()->set('sms_message_id', $infobipResponseArray['messageId']);

                if ($infobipResponseArray) {
                    log_message('info', "SMS-Code an $phone über Infobip gesendet (Fallback).");
                    return redirect()->to($prefix . '/verification/confirm');
                } else {
                    log_message('error', "Infobip SMS Fehler an $phone.");
                }
            }
        } elseif ($method === 'call') {
            //$phone = '+436505711660';
            $message = lang('Verification.callVerificationCode', [
                'sitename' => $this->siteConfig->name,
            ]);
            $success = $twilio->sendCallCode($phone, $message, $verificationCode);

            if ($success) {
                log_message('info', "Anruf-Code an $phone gestartet.");
            } else {
                log_message('error', "Twilio Call Fehler an $phone.");
            }
        }

        if ($success) {
            return redirect()->to($prefix . '/verification/confirm');
        }

        return redirect()->to($prefix . '/verification')->with('error', lang('Verification.errorSendingCode'));
    }

    public function confirm() {
        $verificationCode = session('verification_code');
        if (!$verificationCode || $verificationCode == '') {
            log_message('info', 'Verifizierung Confirm verificationCode fehlt.');
            return redirect()->to(session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de']);
        }

        $smsStatus = session('sms_sent_status'); // z.B. "DELIVERED_TO_HANDSET", "INVALID_DESTINATION_ADDRESS"

        return view('verification_confirm', [
            'siteConfig' => $this->siteConfig,
            'verification_code' => $verificationCode,
            'sms_status' => $smsStatus,
            'phone' => session('phone'),
            'method' => session('verify_method'),
        ]);

    }

    public function verify() {
        $request = service('request');

        // Aktuelle Sprache ermitteln (Locale Helper muss verfügbar sein)
        $locale = getCurrentLocale();
        $prefix = ($locale === 'de') ? '' : '/' . $locale;

        $uuid = session()->get('uuid');
        $newPhone = $request->getPost('phone');
        $enteredCode = $request->getPost('code');
        $sessionCode = session()->get('verification_code');
        $submitbutton = $request->getPost('submitbutton'); // "changephone" oder "submitcode"

        // --- FALL 1: Benutzer will Telefonnummer ändern ---
        if ($submitbutton === 'changephone') {
            // Falls keine Telefonnummer eingegeben wurde
            if (empty($newPhone)) {
                return redirect()->back()->with('error', 'Bitte geben Sie eine Telefonnummer ein.');
            }

            // Falls gleiche Telefonnummer wie vorher
            if ($newPhone === session()->get('phone')) {
                return redirect()->back()->with('error', 'Die Telefonnummer wurde nicht geändert.');
            }

            // Neue Telefonnummer verarbeiten
            $normalizedPhone = $this->normalizePhone($newPhone);
            $method = session()->get('verify_method') ?? 'sms';

            // Nummer in Session speichern
            session()->set('phone', $normalizedPhone);

            // Neuen Code erzeugen
            $verificationCode = rand(1000, 9999);
            session()->set('verification_code', $verificationCode);

            // In Datenbank aktualisieren
            $db = \Config\Database::connect();
            $builder = $db->table('offers');
            $builder->where('uuid', $uuid)->update(['phone' => $normalizedPhone]);

            // Code versenden
            $twilio = new TwilioService();
            $success = false;

            if ($method === 'sms') {
                // Twilio deaktiviert, direkt Fallback
                $infobip = new \App\Libraries\InfobipService();
                $message = lang('Verification.smsVerificationCode', [
                    'sitename' => $this->siteConfig->name,
                    'code' => $verificationCode
                ]);
                $infobipResponseArray = $infobip->sendSms($normalizedPhone, $message);

                log_message('info', "SMS-Code an $normalizedPhone über Infobip gesendet: " . print_r($infobipResponseArray, true));
                session()->set('sms_sent_status', $infobipResponseArray['status']);
                session()->set('sms_message_id', $infobipResponseArray['messageId']);

                if ($infobipResponseArray) {
                    log_message('info', "SMS-Code an $normalizedPhone über Infobip gesendet (Fallback).");
                    return redirect()->to($prefix . '/verification/confirm');
                } else {
                    log_message('error', "Infobip SMS Fehler an $normalizedPhone.");
                }

                return redirect()->to($prefix . '/verification/confirm');
            }

            if ($method === 'call') {
                $message = lang('Verification.callVerificationCode', [
                    'sitename' => $this->siteConfig->name,
                ]);
                $success = $twilio->sendCallCode($normalizedPhone, $message, $verificationCode);
                if ($success) {
                    return redirect()->to($prefix . '/verification/confirm');
                }
            }

            return redirect()->to($prefix . '/verification')->with('error', lang('Verification.errorSendingCode'));
        }

        // --- FALL 2: Benutzer gibt Bestätigungscode ein ---
        if ($submitbutton === 'submitcode') {
            if ($enteredCode == $sessionCode) {
                $db = \Config\Database::connect();
                $builder = $db->table('offers');
                $builder->where('uuid', $uuid)->update([
                    'verified' => 1,
                    'verify_type' => session()->get('verify_method')
                ]);

                session()->remove('verification_code');


                // ---- NEU: E-Mail erst jetzt senden ----
                $offerModel = new \App\Models\OfferModel();
                $offerData = $offerModel->where('uuid', $uuid)->first();
                if ($offerData) {
                    $type = $offerData['type'] ?? 'unknown';
                    $this->sendOfferNotificationEmail(
                        json_decode($offerData['form_fields'], true) ?? [],
                        $type,
                        $uuid,
                        $offerData['verify_type'] ?? null
                    );
                }

                if ($offerData) {
                    $updater = new \App\Libraries\OfferPriceUpdater();
                    $updater->updateOfferAndNotify($offerData);
                }

                log_message('info', 'Verifizierung abgeschlossen: E-Mail gesendet.');


                log_message('info', 'Verifizierung abgeschlossen: gehe weiter zur URL: ' . (session('next_url') ?? $this->siteConfig->thankYouUrl['de']));
                return view('verification_success', [
                    'siteConfig' => $this->siteConfig,
                    'next_url' => session('next_url') ?? $this->siteConfig->thankYouUrl['de']
                ]);
            }

            // Falscher Code
            log_message('info', 'Verifizierung Confirm: Falscher Code. Bitte erneut versuchen.');
            return redirect()->back()->with('error', lang('Verification.wrongCode'));
        }


        // --- FALL 3: Ungültiger Request ---
        return redirect()->back()->with('error', lang('Verification.invalidRequest'));

    }


    public function checkSmsStatus() {
        $messageId = session('sms_message_id');
        if (!$messageId) {
            return $this->response->setJSON([
                'status' => 'NO_MESSAGE_ID',
                'message' => 'Bitte Nummer prüfen.'
            ]);
        }

        $infobip = new \App\Libraries\InfobipService();
        $status = $infobip->checkDeliveryStatus($messageId);

        return $this->response->setJSON($status);
    }

    // sending with mail after inserted and not verified:
    public function verifyOffer($offerId = null, $token = null) {
        $locale = getCurrentLocale();
        $prefix = ($locale === 'de') ? '' : '/' . $locale;

        if (!$offerId || !$token) {
            return redirect()->to($prefix . '/')->with('error', lang('Verification.invalidVerificationLink'));
        }

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer || $offer['verification_token'] !== $token) {
            return redirect()->to($prefix . '/')->with('error', lang('Verification.invalidOrOldVerificationLink'));
        }

        if ((int)$offer['verified'] === 1) {
            return redirect()->to($prefix . '/')->with('message', lang('Verification.alreadyVerified'));
        }

        $fields = json_decode($offer['form_fields'], true);
        $phone = $fields['phone'] ?? '';
        $phone = $this->normalizePhone($phone);

        $isMobile = is_mobile_number($phone);
        $method = $isMobile ? 'sms' : 'phone';

        session()->set('uuid', $offer['uuid']);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);
        session()->set('next_url', $this->siteConfig->thankYouUrl['de']); // fix immer danke seite

        return redirect()->to($prefix . '/verification/send');
    }

    private function normalizePhone(string $phone): string {
        $phone = preg_replace('/\D+/', '', $phone); // Nur Zahlen
        if (str_starts_with($phone, '0')) {
            $phone = '+41' . substr($phone, 1); // 0781234512 → +41781234512
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }


    protected function sendOfferNotificationEmail(array $data, string $formName, string $uuid, ?string $verifyType = null): void {
        helper('text'); // für esc()

        // Sprache aus Offer-Daten setzen
        $language = $data['lang'] ?? 'de'; // Fallback: Deutsch
        log_message('debug', 'language aus offerte/fallback: ' . $language);
        $request = service('request');
        if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
            service('language')->setLocale($language);
        } else {
            $request->setLocale($language);
        }

        $languageService = service('language');
        $languageService->setLocale($language);


        // Admins
        $adminEmails = [$this->siteConfig->email];
        $bccString = implode(',', $adminEmails);

        // Formularverfasser
        $userEmail = $data['email'] ?? null;

        $formular_page = null;
        if (isset($data['_wp_http_referer'])) {
            $formular_page = $data['_wp_http_referer'];
            $formular_page_exploder = explode('?', $formular_page);
            $formular_page = $formular_page_exploder[0];
            $formular_page = str_replace('-', ' ', $formular_page);
            $formular_page = str_replace('/', ' ', $formular_page);
            $formular_page = ucwords($formular_page);
            $formular_page = trim($formular_page);
        }

        // Technische Felder rausfiltern
        $filteredFields = array_filter($data, function ($key) {
            $excludeKeys = ['__submission', '__fluent_form_embded_post_id', '_wp_http_referer', 'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method'];
            if (in_array($key, $excludeKeys)) return false;
            if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) return false;
            return true;
        }, ARRAY_FILTER_USE_KEY);

        // Tracking-Felder entfernen
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer'];
        $filteredFields = array_filter($filteredFields, function ($key) use ($utmKeys) {
            return !in_array($key, $utmKeys);
        }, ARRAY_FILTER_USE_KEY);

        // Maildaten für View
        $emailData = [
            'formName' => $formName,
            'formular_page' => $formular_page,
            'uuid' => $uuid,
            'verifyType' => $verifyType,
            'filteredFields' => $filteredFields,
            'data' => $data,
        ];

        // HTML-Ansicht generieren
        $message = view('emails/offer_notification', $emailData);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => 'Ihre Anfrage',
            'content' => $message,
        ])->render('emails/layout');

        // Maildienst starten
        $email = \Config\Services::email();

        $email->setFrom($this->siteConfig->email, $this->siteConfig->name);
        $email->setTo($userEmail);            // Kunde als To
        $email->setBCC($bccString);         // Admins als BCC
        $email->setSubject(lang('Email.offer_added_email_subject'));
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Mail senden fehlgeschlagen: ' . print_r($email->printDebugger(['headers']), true));
        }

    }

}
