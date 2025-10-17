<?php

namespace App\Controllers;

use App\Libraries\TwilioService;

class Verification extends BaseController {
    public function index() {
        $maxWaitTime = 5; // Sekunden
        $waited = 0;

        // session()->set('uuid', '640e8804-e219-43d8-b529-faf247c606b3');

        // UUID aus GET-Parameter oder Session holen
        $uuid = $this->request->getGet('uuid') ?? session()->get('uuid');

        // Falls UUID aus GET kommt, in Session speichern
        if ($uuid && !session()->get('uuid')) {
            session()->set('uuid', $uuid);
        }

        while (!$uuid) {
            $uuid = session()->get('uuid');
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

            $redirectUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'];
            log_message('info', '[VERIFICATION REDIRECT] Kein Datensatz gefunden → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl)->with('error', lang('Verification.noOfferFound'));
        }

        // form_fields ist JSON, decode es:
        $fields = json_decode($row->form_fields, true);
        $phone = $fields['phone'] ?? '';
        $email = $fields['email'] ?? '';

        $phone = $this->normalizePhone($phone);

        $isMobile = is_mobile_number($phone);

        $method = $isMobile ? 'sms' : 'call';

        // NEUE LOGIK: Prüfe ob Telefonnummer bereits verifiziert wurde
        $verifiedPhoneModel = new \App\Models\VerifiedPhoneModel();
        $validityHours = $this->siteConfig->phoneVerificationValidityHours ?? 24;
        $isAlreadyVerified = $verifiedPhoneModel->isPhoneVerified($phone, null, $validityHours);

        if ($isAlreadyVerified) {
            log_message('info', "Telefonnummer $phone bereits verifiziert (innerhalb {$validityHours}h). Überspringe Verifizierung für UUID $uuid");

            // Markiere Offerte als verifiziert
            $builder->where('uuid', $uuid)->update([
                'verified' => 1,
                'verify_type' => 'auto_verified' // kennzeichnet automatische Verifizierung
            ]);

            // Sende E-Mails direkt (wie nach manueller Verifizierung)
            $this->handlePostVerification($uuid, $row);

            // Weiterleitung zur Erfolgsseite
            $nextUrl = session('next_url') ?? $this->siteConfig->thankYouUrl['de'];
            log_message('info', '[VERIFICATION REDIRECT] Auto-Verifizierung erfolgreich → Erfolgsseite mit next_url: ' . $nextUrl);
            return view('verification_success', [
                'siteConfig' => $this->siteConfig,
                'next_url' => $nextUrl,
                'auto_verified' => true // Flag für View
            ]);
        }

        // In Session schreiben
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        // Weiterleitung zu send(), um direkt Code zu verschicken
        $locale = getCurrentLocale(); // 'de', 'fr', 'en', ...

        if ($locale === 'de') {
            // Deutsch ohne Prefix
            log_message('info', '[VERIFICATION REDIRECT] Weiterleitung zu /verification/send (Locale: de)');
            return redirect()->to('/verification/send');
        } else {
            // Andere Sprachen mit Prefix
            log_message('info', "[VERIFICATION REDIRECT] Weiterleitung zu /{$locale}/verification/send (Locale: {$locale})");
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

        log_message('info', "SEND: Phone from session: " . ($phone ?? 'NULL') . ", Method: " . ($method ?? 'NULL'));

        if (!$phone) {
            log_message('error', 'SEND: Telefonnummer fehlt in Session!');
            $locale = getCurrentLocale();
            $prefix = ($locale === 'de') ? '' : '/' . $locale;
            $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
            log_message('error', '[VERIFICATION REDIRECT] Telefonnummer fehlt → Weiterleitung zu: ' . $nextUrl);
            return redirect()->to($nextUrl)->with('error', lang('Verification.phoneMissing'));
        }

        $phone = $this->normalizePhone($phone);

        // Prüfe, ob Mobilnummer
        $isMobile = is_mobile_number($phone);
        log_message('info', "DEBUG: Phone {$phone} -> isMobile: " . ($isMobile ? 'YES' : 'NO') . " -> method from session: {$method}");

        // Wenn kein Mobile, dann nur Anruf zulassen
        if (!$isMobile && $method !== 'call') {
            log_message('error', "SEND: Festnetz erkannt aber Methode ist {$method}");
            $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
            log_message('error', '[VERIFICATION REDIRECT] Festnetz aber falsche Methode → Weiterleitung zu: ' . $nextUrl);
            return redirect()->to($nextUrl)->with('error', lang('Verification.fixedLineOnlyCall'));
        }

        if (!$method) {
            log_message('error', 'SEND: Methode fehlt in Session!');
            $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
            log_message('error', '[VERIFICATION REDIRECT] Methode fehlt → Weiterleitung zu: ' . $nextUrl);
            return redirect()->to($nextUrl)->with('error', lang('Verification.chooseMethod'));
        }

        //$method = 'call';

        $verificationCode = rand(1000, 9999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        log_message('info', "Verifizierungscode $verificationCode via $method an $phone");

        $twilio = new TwilioService();

        // Hilfsfunktion für korrekte locale-URL
        $locale = getCurrentLocale();
        $prefix = ($locale === 'de') ? '' : '/' . $locale;

        if ($method === 'sms') {
            // SMS über Infobip versenden
            $infobip = new \App\Libraries\InfobipService();
            $message = lang('Verification.smsVerificationCode', [
                'sitename' => $this->siteConfig->name,
                'code' => $verificationCode
            ]);
            $infobipResponseArray = $infobip->sendSms($phone, $message);

            session()->set('sms_sent_status', $infobipResponseArray['status']);
            session()->set('sms_message_id', $infobipResponseArray['messageId']);

            if ($infobipResponseArray['success']) {
                log_message('info', "SMS-Code an $phone über Infobip gesendet.");
                $redirectUrl = $prefix . '/verification/confirm';
                log_message('info', '[VERIFICATION REDIRECT] SMS erfolgreich gesendet → Weiterleitung zu: ' . $redirectUrl);
                return redirect()->to($redirectUrl);
            } else {
                log_message('error', "Infobip SMS Fehler an $phone: " . ($infobipResponseArray['error'] ?? 'Unknown error'));
                $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
                log_message('error', '[VERIFICATION REDIRECT] SMS Fehler → Weiterleitung zu: ' . $nextUrl);
                return redirect()->to($nextUrl)->with('error', lang('Verification.errorSendingCode'));
            }
        } elseif ($method === 'call') {
            //$phone = '+436505711660';
            $message = lang('Verification.callVerificationCode', [
                'sitename' => $this->siteConfig->name,
            ]);
            $success = $twilio->sendCallCode($phone, $message, $verificationCode);

            if ($success) {
                log_message('info', "Anruf-Code an $phone gestartet.");
                $redirectUrl = $prefix . '/verification/confirm';
                log_message('info', '[VERIFICATION REDIRECT] Anruf erfolgreich gestartet → Weiterleitung zu: ' . $redirectUrl);
                return redirect()->to($redirectUrl);
            } else {
                log_message('error', "Twilio Call Fehler an $phone.");
                $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
                log_message('error', '[VERIFICATION REDIRECT] Anruf Fehler → Weiterleitung zu: ' . $nextUrl);
                return redirect()->to($nextUrl)->with('error', lang('Verification.errorSendingCode'));
            }
        }

        $nextUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'] ?? '/';
        log_message('error', '[VERIFICATION REDIRECT] Keine Methode matched → Weiterleitung zu: ' . $nextUrl);
        return redirect()->to($nextUrl)->with('error', lang('Verification.errorSendingCode'));
    }

    public function confirm() {
        $verificationCode = session('verification_code');
        if (!$verificationCode || $verificationCode == '') {
            log_message('info', 'Verifizierung Confirm verificationCode fehlt.');
            $redirectUrl = session()->get('next_url') ?? $this->siteConfig->thankYouUrl['de'];
            log_message('info', '[VERIFICATION REDIRECT] Confirm: Code fehlt → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl);
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

    public function verifyGet() {
        // GET-Request auf /verification/verify abfangen
        // Dies passiert wenn User F5 drückt oder zurück navigiert
        $locale = getCurrentLocale();
        $prefix = ($locale === 'de') ? '' : '/' . $locale;

        log_message('info', '[VERIFICATION REDIRECT] GET auf /verification/verify → Weiterleitung zu /verification/confirm');

        // Zurück zur Confirm-Seite
        return redirect()->to($prefix . '/verification/confirm')
            ->with('info', lang('Verification.pleaseUseForm'));
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

            // WICHTIG: Methode NEU berechnen basierend auf neuer Nummer
            $isMobile = is_mobile_number($normalizedPhone);
            $method = $isMobile ? 'sms' : 'call';

            log_message('info', "Telefonnummer geändert zu $normalizedPhone - isMobile: " . ($isMobile ? 'YES' : 'NO') . " - Methode: $method");

            // Nummer und Methode in Session speichern
            session()->set('phone', $normalizedPhone);
            session()->set('verify_method', $method);

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

                if ($infobipResponseArray['success']) {
                    log_message('info', "SMS-Code an $normalizedPhone über Infobip gesendet (Fallback).");
                    $redirectUrl = $prefix . '/verification/confirm';
                    log_message('info', '[VERIFICATION REDIRECT] Telefon geändert: SMS erfolgreich → Weiterleitung zu: ' . $redirectUrl);
                    return redirect()->to($redirectUrl);
                } else {
                    log_message('error', "Infobip SMS Fehler an $normalizedPhone: " . ($infobipResponseArray['error'] ?? 'Unknown error'));
                    $redirectUrl = $prefix . '/verification/confirm';
                    log_message('error', '[VERIFICATION REDIRECT] Telefon geändert: SMS Fehler → Weiterleitung zu: ' . $redirectUrl);
                    return redirect()->to($redirectUrl)->with('error', lang('Verification.errorSendingCode'));
                }
            }

            if ($method === 'call') {
                $message = lang('Verification.callVerificationCode', [
                    'sitename' => $this->siteConfig->name,
                ]);
                $success = $twilio->sendCallCode($normalizedPhone, $message, $verificationCode);
                if ($success) {
                    $redirectUrl = $prefix . '/verification/confirm';
                    log_message('info', '[VERIFICATION REDIRECT] Telefon geändert: Anruf erfolgreich → Weiterleitung zu: ' . $redirectUrl);
                    return redirect()->to($redirectUrl);
                }
            }

            $redirectUrl = $prefix . '/verification/confirm';
            log_message('error', '[VERIFICATION REDIRECT] Telefon geändert: Fehler → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl)->with('error', lang('Verification.errorSendingCode'));
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

                // NEUE LOGIK: Telefonnummer in verified_phones speichern
                $phone = session()->get('phone');
                $verifyMethod = session()->get('verify_method');

                if ($phone) {
                    $offerModel = new \App\Models\OfferModel();
                    $offerData = $offerModel->where('uuid', $uuid)->first();
                    $email = null;
                    $platform = null;

                    if ($offerData) {
                        $fields = json_decode($offerData['form_fields'], true);
                        $email = $fields['email'] ?? null;
                        $platform = $offerData['platform'] ?? null;
                    }

                    $verifiedPhoneModel = new \App\Models\VerifiedPhoneModel();
                    $verifiedPhoneModel->addVerifiedPhone($phone, $email, $verifyMethod, $platform);

                    log_message('info', "Telefonnummer $phone als verifiziert gespeichert (Methode: $verifyMethod)");

                    // NEUE LOGIK: Alle vorherigen unverifizierte Anfragen mit derselben Telefonnummer verifizieren
                    // UND: Sende eine einzige E-Mail für alle Offerten der Gruppe
                    $offerModel = new \App\Models\OfferModel();
                    $currentOffer = $offerModel->where('uuid', $uuid)->first();

                    $this->verifyAndNotifyGroupedOffers($phone, $currentOffer);
                }

                log_message('info', 'Verifizierung abgeschlossen: E-Mail(s) gesendet.');

                $nextUrl = session('next_url') ?? $this->siteConfig->thankYouUrl['de'];
                log_message('info', '[VERIFICATION REDIRECT] Code korrekt: Verifizierung erfolgreich → Erfolgsseite mit next_url: ' . $nextUrl);
                return view('verification_success', [
                    'siteConfig' => $this->siteConfig,
                    'next_url' => $nextUrl
                ]);
            }

            // Falscher Code
            log_message('info', 'Verifizierung Confirm: Falscher Code. Bitte erneut versuchen.');
            log_message('info', '[VERIFICATION REDIRECT] Falscher Code → redirect()->back()');
            return redirect()->back()->with('error', lang('Verification.wrongCode'));
        }


        // --- FALL 3: Ungültiger Request ---
        log_message('error', '[VERIFICATION REDIRECT] Ungültiger Request → redirect()->back()');
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
            $redirectUrl = $prefix . '/';
            log_message('error', '[VERIFICATION REDIRECT] E-Mail-Link: Ungültiger Link (ID/Token fehlt) → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl)->with('error', lang('Verification.invalidVerificationLink'));
        }

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer || $offer['verification_token'] !== $token) {
            $redirectUrl = $prefix . '/';
            log_message('error', '[VERIFICATION REDIRECT] E-Mail-Link: Ungültiger/alter Token → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl)->with('error', lang('Verification.invalidOrOldVerificationLink'));
        }

        if ((int)$offer['verified'] === 1) {
            $redirectUrl = $prefix . '/';
            log_message('info', '[VERIFICATION REDIRECT] E-Mail-Link: Bereits verifiziert → Weiterleitung zu: ' . $redirectUrl);
            return redirect()->to($redirectUrl)->with('message', lang('Verification.alreadyVerified'));
        }

        $fields = json_decode($offer['form_fields'], true);
        $phone = $fields['phone'] ?? '';
        $phone = $this->normalizePhone($phone);

        $isMobile = is_mobile_number($phone);
        $method = $isMobile ? 'sms' : 'call';

        session()->set('uuid', $offer['uuid']);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);
        session()->set('next_url', $this->siteConfig->thankYouUrl['de']); // fix immer danke seite

        $redirectUrl = $prefix . '/verification/send';
        log_message('info', '[VERIFICATION REDIRECT] E-Mail-Link: Gültig, Session gesetzt → Weiterleitung zu: ' . $redirectUrl);
        return redirect()->to($redirectUrl);
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


    /**
     * Hilfsmethode für Post-Verifizierungs-Aktionen
     * Wird aufgerufen wenn Telefonnummer bereits verifiziert war
     */
    private function handlePostVerification(string $uuid, object $row): void {
        $offerModel = new \App\Models\OfferModel();
        $offerData = $offerModel->where('uuid', $uuid)->first();

        if ($offerData) {
            $type = $offerData['type'] ?? 'unknown';

            // Stelle sicher, dass Preis berechnet ist
            if (empty($offerData['price']) || $offerData['price'] <= 0) {
                $updater = new \App\Libraries\OfferPriceUpdater();
                $updater->updateOfferAndNotify($offerData);

                // frisch aus DB holen (mit Preis)
                $offerData = $offerModel->find($offerData['id']);

                // Prüfe nochmals ob Preis jetzt gesetzt ist
                if (empty($offerData['price']) || $offerData['price'] <= 0) {
                    log_message('error', 'Offer ID ' . $offerData['id'] . ' konnte nicht verifiziert werden: Preis ist 0');
                    return;
                }
            }

            // Dann Offer an Offertensteller und Admins senden
            $this->sendOfferNotificationEmail(
                json_decode($offerData['form_fields'], true) ?? [],
                $type,
                $uuid,
                $offerData['verify_type'] ?? null
            );

            // E-Mail an passende Firmen
            $notifier = new \App\Libraries\OfferNotificationSender();
            $notifier->notifyMatchingUsers($offerData);

            log_message('info', 'Auto-Verifizierung abgeschlossen: E-Mails gesendet für UUID ' . $uuid);
        }
    }

    /**
     * Sendet eine gruppierte E-Mail für mehrere Offerten
     * @param array $offers Array von Offerten
     * @return void
     */
    protected function sendGroupedOfferNotificationEmail(array $offers): void {
        if (empty($offers)) {
            return;
        }

        // Prüfe ob für alle Offerten bereits confirmation_sent_at gesetzt ist
        $allAlreadySent = true;
        foreach ($offers as $offer) {
            if (empty($offer['confirmation_sent_at'])) {
                $allAlreadySent = false;
                break;
            }
        }

        if ($allAlreadySent) {
            log_message('info', 'Gruppierte E-Mail wurde bereits versendet für Offerten IDs: ' . implode(', ', array_column($offers, 'id')));
            return;
        }

        helper('text');

        // Verwende die erste Offerte für allgemeine Daten
        $firstOffer = $offers[0];
        $formFields = json_decode($firstOffer['form_fields'], true);

        // Sprache aus Offer-Daten setzen
        $language = $formFields['lang'] ?? 'de';
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
        $userEmail = $formFields['email'] ?? null;

        if (!$userEmail) {
            log_message('error', 'Gruppierte E-Mail: E-Mail-Adresse fehlt');
            return;
        }

        // Bereite Offerten-Daten auf
        $offersData = [];
        foreach ($offers as $offer) {
            $offerFields = json_decode($offer['form_fields'], true);
            $type = $offer['type'] ?? 'unknown';

            // Technische Felder rausfiltern
            $filteredFields = array_filter($offerFields, function ($key) {
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

            $offersData[] = [
                'uuid' => $offer['uuid'],
                'type' => $type,
                'verifyType' => $offer['verify_type'] ?? null,
                'filteredFields' => $filteredFields,
                'data' => $offerFields,
            ];
        }

        $emailData = [
            'offers' => $offersData,
            'isMultiple' => count($offersData) > 1,
            'data' => $formFields, // Für allgemeine Daten (Name, etc.)
        ];

        // HTML-Ansicht generieren (neue View für gruppierte Offerten)
        $message = view('emails/grouped_offer_notification', $emailData);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => count($offersData) > 1 ? lang('Email.offer_added_requests_title') : lang('Email.offer_added_request_title'),
            'content' => $message,
            'siteConfig' => $this->siteConfig,
        ])->render('emails/layout');

        // Maildienst starten
        $email = \Config\Services::email();
        $email->setFrom($this->siteConfig->email, $this->siteConfig->name);
        $email->setTo($userEmail);
        $email->setBCC($bccString);
        $email->setSubject(
            count($offersData) > 1
                ? lang('Email.offer_added_multiple_subject')
                : lang('Email.offer_added_email_subject')
        );
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Gruppierte Mail senden fehlgeschlagen: ' . print_r($email->printDebugger(['headers']), true));
        } else {
            log_message('info', "Gruppierte E-Mail gesendet an $userEmail für " . count($offersData) . " Offerten");

            // Setze confirmation_sent_at für alle Offerten
            $db = \Config\Database::connect();
            $builder = $db->table('offers');
            $offerIds = array_column($offers, 'id');
            $builder->whereIn('id', $offerIds)->update([
                'confirmation_sent_at' => date('Y-m-d H:i:s')
            ]);
            log_message('info', 'confirmation_sent_at gesetzt für Offerten IDs: ' . implode(', ', $offerIds));
        }
    }

    protected function sendOfferNotificationEmail(array $data, string $formName, string $uuid, ?string $verifyType = null): void {
        helper('text'); // für esc()

        // Prüfe ob Bestätigungsmail bereits versendet wurde
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->where('uuid', $uuid)->first();

        if ($offer && !empty($offer['confirmation_sent_at'])) {
            log_message('info', "Bestätigungsmail wurde bereits versendet für Angebot ID {$offer['id']} (UUID: $uuid)");
            return;
        }

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
            'siteConfig' => $this->siteConfig,
        ])->render('emails/layout');

        // Maildienst starten
        $email = \Config\Services::email();

        $email->setFrom($this->siteConfig->email, $this->siteConfig->name);
        $email->setTo($userEmail);            // Kunde als To
        $email->setBCC($bccString);         // Admins als BCC
        $email->setSubject(lang('Email.offer_added_email_subject'));
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if (!$email->send()) {
            log_message('error', 'Mail senden fehlgeschlagen: ' . print_r($email->printDebugger(['headers']), true));
        } else {
            // Setze confirmation_sent_at
            if ($offer && $offer['id']) {
                $db = \Config\Database::connect();
                $builder = $db->table('offers');
                $builder->where('id', $offer['id'])->update([
                    'confirmation_sent_at' => date('Y-m-d H:i:s')
                ]);
                log_message('info', "confirmation_sent_at gesetzt für Angebot ID {$offer['id']} (UUID: $uuid)");
            }
        }

    }

    /**
     * Verifiziert alle unverifizierte Anfragen mit derselben Telefonnummer
     * und sendet gruppierte E-Mails (eine E-Mail pro group_id)
     *
     * @param string $phone Normalisierte Telefonnummer
     * @param array $currentOffer Die gerade verifizierte Offerte
     * @return void
     */
    private function verifyAndNotifyGroupedOffers(string $phone, array $currentOffer): void {
        $db = \Config\Database::connect();
        $offerModel = new \App\Models\OfferModel();

        // Finde alle unverifizierte Offerten mit derselben Telefonnummer
        $unverifiedOffers = $offerModel->where('verified', 0)->findAll();

        $offersToVerify = [];
        $verifyMethod = session()->get('verify_method') ?? 'sms';

        // Sammle alle Offerten mit der gleichen Telefonnummer
        foreach ($unverifiedOffers as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $offerPhone = $formFields['phone'] ?? '';
            $normalizedOfferPhone = $this->normalizePhone($offerPhone);

            if ($normalizedOfferPhone === $phone) {
                $offersToVerify[] = $offer;
            }
        }

        // Füge die aktuelle Offerte hinzu (falls nicht schon in der Liste)
        $currentOfferInList = false;
        foreach ($offersToVerify as $offer) {
            if ($offer['id'] === $currentOffer['id']) {
                $currentOfferInList = true;
                break;
            }
        }
        if (!$currentOfferInList) {
            $offersToVerify[] = $currentOffer;
        }

        // Gruppiere nach group_id
        $groupedOffers = [];
        foreach ($offersToVerify as $offer) {
            $groupKey = $offer['group_id'] ?? 'individual_' . $offer['id'];
            if (!isset($groupedOffers[$groupKey])) {
                $groupedOffers[$groupKey] = [];
            }
            $groupedOffers[$groupKey][] = $offer;
        }

        // Für jede Gruppe: Verifiziere alle Offerten und sende eine E-Mail
        foreach ($groupedOffers as $groupKey => $offers) {
            $verifiedOffers = [];

            foreach ($offers as $offer) {
                // Verifiziere die Offerte in der DB
                $builder = $db->table('offers');
                $builder->where('id', $offer['id'])->update([
                    'verified' => 1,
                    'verify_type' => ($offer['id'] === $currentOffer['id']) ? $verifyMethod : 'auto_verified_same_phone'
                ]);

                // Stelle sicher, dass Preis berechnet ist
                if (empty($offer['price']) || $offer['price'] <= 0) {
                    $updater = new \App\Libraries\OfferPriceUpdater();
                    $updater->updateOfferAndNotify($offer);
                    // Frisch aus DB holen (mit Preis)
                    $offer = $offerModel->find($offer['id']);
                }

                // Nur hinzufügen wenn Preis > 0
                if (!empty($offer['price']) && $offer['price'] > 0) {
                    $verifiedOffers[] = $offer;
                    log_message('info', "Offerte ID {$offer['id']} (UUID: {$offer['uuid']}) verifiziert (Gruppe: $groupKey)");
                } else {
                    log_message('error', "Offerte ID {$offer['id']} übersprungen - Preis ist 0");
                }
            }

            if (empty($verifiedOffers)) {
                log_message('warning', "Keine gültigen Offerten in Gruppe $groupKey zum Versenden");
                continue;
            }

            // Sende eine einzige E-Mail für alle Offerten dieser Gruppe
            if (count($verifiedOffers) === 1) {
                // Einzelne Offerte - normale E-Mail
                $offer = $verifiedOffers[0];
                $this->sendOfferNotificationEmail(
                    json_decode($offer['form_fields'], true) ?? [],
                    $offer['type'] ?? 'unknown',
                    $offer['uuid'],
                    $offer['verify_type'] ?? null
                );

                // E-Mail an passende Firmen
                $notifier = new \App\Libraries\OfferNotificationSender();
                $notifier->notifyMatchingUsers($offer);

                log_message('info', "E-Mail gesendet für Offerte ID {$offer['id']}");
            } else {
                // Mehrere Offerten - gruppierte E-Mail
                $this->sendGroupedOfferNotificationEmail($verifiedOffers);

                // E-Mail an passende Firmen für jede Offerte
                $notifier = new \App\Libraries\OfferNotificationSender();
                foreach ($verifiedOffers as $offer) {
                    $notifier->notifyMatchingUsers($offer);
                }

                $offerIds = array_column($verifiedOffers, 'id');
                log_message('info', "Gruppierte E-Mail gesendet für Offerten IDs: " . implode(', ', $offerIds));
            }
        }

        log_message('info', "Insgesamt " . count($offersToVerify) . " Anfragen mit Telefonnummer $phone verifiziert und E-Mails gesendet");
    }

}
