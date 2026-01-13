<?php

namespace App\Controllers;

use App\Libraries\CategoryManager;
use App\Libraries\InfobipService;
use App\Libraries\TwilioService;
use App\Models\ProjectModel;

class Request extends BaseController
{
    public function start()
    {
        // Formulare aus CategoryManager laden
        $categoryManager = new CategoryManager();
        $locale = $this->request->getGet('lang') ?? service('request')->getLocale();
        $forms = $categoryManager->getAllForms($locale);

        // Projekte aus DB laden
        $projectModel = new ProjectModel();
        $projects = $projectModel->getActiveProjectsWithNames($locale);

        // Initial ausgewähltes Formular (aus URL-Parameter)
        $initial = $this->request->getGet('initial');

        // Kategorie-Farbe für initial-Formular ermitteln
        $initialCategoryColor = null;
        if ($initial) {
            $initialForm = $categoryManager->getFormById($initial, $locale);
            if ($initialForm) {
                $initialCategoryColor = $initialForm['category_color'] ?? null;
            }
        }

        // SiteConfig für Logo und Header
        $siteConfig = siteconfig();

        return view('request/start', [
            'forms' => $forms,
            'projects' => $projects,
            'initial' => $initial,
            'initialCategoryColor' => $initialCategoryColor,
            'siteConfig' => $siteConfig,
            'lang' => $locale,
            'categoryManager' => $categoryManager,
        ]);
    }

    public function submit()
    {
        // Ausgewählte Formulare und Projekte
        $selectedForms = $this->request->getPost('forms') ?? [];
        $selectedProjects = $this->request->getPost('projects') ?? [];
        $initialForm = $this->request->getPost('initial');

        // Validierung
        if (empty($selectedForms) && empty($selectedProjects)) {
            return redirect()->back()->withInput()->with('error', 'Bitte wähle mindestens ein Formular oder ein Projekt aus.');
        }

        // Session erstellen
        $sessionId = bin2hex(random_bytes(16));

        // Formular-Links zusammenstellen (initial-Formular wird priorisiert)
        $locale = service('request')->getLocale();
        $formLinks = $this->getFormLinks($selectedForms, $selectedProjects, $locale, $initialForm);

        if (empty($formLinks)) {
            return redirect()->back()->withInput()->with('error', 'Für die ausgewählten Formulare/Projekte sind keine Links hinterlegt.');
        }

        // Daten in Session speichern
        $sessionData = [
            'id' => $sessionId,
            'forms' => $selectedForms,
            'projects' => $selectedProjects,
            'form_links' => $formLinks,
            'current_index' => 0,
            'total_forms' => count($formLinks),
            'completed_forms' => [],
            'created_at' => time(),
        ];

        session()->set('request_' . $sessionId, $sessionData);

        // Zum ersten Formular weiterleiten
        return $this->redirectToForm($sessionId, 0);
    }

    /**
     * Wird aufgerufen nachdem ein WordPress-Formular abgeschlossen wurde
     */
    public function next()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Aktuelles Formular als erledigt markieren
        $currentIndex = $sessionData['current_index'];
        $sessionData['completed_forms'][] = $currentIndex;
        $sessionData['current_index'] = $currentIndex + 1;

        session()->set('request_' . $sessionId, $sessionData);

        // Prüfen ob noch Formulare übrig sind
        if ($sessionData['current_index'] < $sessionData['total_forms']) {
            // Zum nächsten Formular weiterleiten
            return $this->redirectToForm($sessionId, $sessionData['current_index']);
        }

        // Alle Formulare erledigt → Zur Finalisierung
        return redirect()->to('/request/finalize?session=' . $sessionId);
    }

    /**
     * Finalisierung: Termin, Auftraggeber, Kontaktdaten, Verifikation
     */
    public function finalize()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Schritt aus URL oder Default
        $step = $this->request->getGet('step') ?? 'termin';

        // SiteConfig für Logo und Header
        $siteConfig = siteconfig();

        // Farbe des letzten Formulars ermitteln (für Header und Buttons)
        $lastFormColor = null;
        $formLinks = $sessionData['form_links'] ?? [];
        if (!empty($formLinks)) {
            $lastForm = end($formLinks);
            $lastFormColor = $lastForm['category_color'] ?? null;
        }

        // Edit-URLs für Zurück-Button generieren (nur im ersten Schritt)
        $editUrls = [];
        if ($step === 'termin') {
            $editUrls = $this->generateEditUrlsForSession($sessionId, $formLinks);
        }

        return view('request/finalize', [
            'sessionId' => $sessionId,
            'sessionData' => $sessionData,
            'step' => $step,
            'siteConfig' => $siteConfig,
            'lastFormColor' => $lastFormColor,
            'editUrls' => $editUrls,
        ]);
    }

    /**
     * Generiere Edit-URLs für alle Offers einer Session
     */
    protected function generateEditUrlsForSession(string $sessionId, array $formLinks): array
    {
        $offerModel = new \App\Models\OfferModel();
        $editTokenModel = new \App\Models\EditTokenModel();

        // Alle Offers dieser Session finden
        $offers = $offerModel->where('request_session_id', $sessionId)->findAll();

        if (empty($offers)) {
            return [];
        }

        $editUrls = [];

        foreach ($offers as $offer) {
            // Versuche die passende Form-URL zu finden
            $formUrl = null;
            $formName = null;

            // Details parsen um form_link zu finden
            $details = json_decode($offer['details'] ?? '{}', true);
            if (!empty($details['form_link'])) {
                $formUrl = $details['form_link'];
            }

            // Fallback: erste passende Form-URL aus formLinks nehmen
            if (!$formUrl && !empty($formLinks)) {
                foreach ($formLinks as $link) {
                    // Prüfe ob der Typ/Kategorie passt
                    if (!empty($link['category_key']) && $link['category_key'] === $offer['type']) {
                        $formUrl = $link['url'];
                        $formName = $link['name'];
                        break;
                    }
                }
                // Wenn nichts passt, nimm die letzte URL
                if (!$formUrl) {
                    $lastLink = end($formLinks);
                    $formUrl = $lastLink['url'];
                    $formName = $lastLink['name'];
                }
            }

            if ($formUrl) {
                $editUrl = $editTokenModel->generateEditUrl((int)$offer['id'], $formUrl, 'user');
                $editUrls[] = [
                    'offer_id' => $offer['id'],
                    'type' => $offer['type'],
                    'name' => $formName ?? $offer['type'],
                    'edit_url' => $editUrl,
                ];
            }
        }

        return $editUrls;
    }

    /**
     * Finalisierung speichern
     */
    public function saveFinalize()
    {
        $sessionId = $this->request->getPost('session');
        $step = $this->request->getPost('step');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Daten speichern je nach Schritt
        switch ($step) {
            case 'termin':
                $sessionData['termin'] = [
                    'datum' => $this->request->getPost('datum'),
                    'zeit' => $this->request->getPost('zeit_flexibel'),
                ];
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/finalize?session=' . $sessionId . '&step=auftraggeber');

            case 'auftraggeber':
                $sessionData['auftraggeber'] = [
                    'typ' => $this->request->getPost('auftraggeber_typ'),
                    'firma' => $this->request->getPost('firma'),
                ];
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/finalize?session=' . $sessionId . '&step=kontakt');

            case 'kontakt':
                $telefon = $this->request->getPost('telefon_full') ?: $this->request->getPost('telefon');
                $sessionData['kontakt'] = [
                    'vorname' => $this->request->getPost('vorname'),
                    'nachname' => $this->request->getPost('nachname'),
                    'email' => $this->request->getPost('email'),
                    'telefon' => $telefon,
                    'strasse' => $this->request->getPost('strasse'),
                    'hausnummer' => $this->request->getPost('hausnummer'),
                    'plz' => $this->request->getPost('plz'),
                    'ort' => $this->request->getPost('ort'),
                    'erreichbar' => $this->request->getPost('erreichbar'),
                ];
                session()->set('request_' . $sessionId, $sessionData);

                // SMS Verifikationscode senden
                return $this->sendVerificationCode($sessionId, $sessionData);

            case 'verify':
                $inputCode = $this->request->getPost('code');
                $storedCode = $sessionData['verification_code'] ?? null;

                if (!$storedCode || $inputCode != $storedCode) {
                    return redirect()->to('/request/finalize?session=' . $sessionId . '&step=verify')
                        ->with('error', 'Der eingegebene Code ist falsch. Bitte versuche es erneut.');
                }

                // Verifikation erfolgreich
                $sessionData['verified'] = true;
                $sessionData['verified_at'] = date('Y-m-d H:i:s');
                session()->set('request_' . $sessionId, $sessionData);

                // SMS/Anruf-Historie als verifiziert markieren
                $this->markVerificationHistoryAsVerified($sessionId, $storedCode);

                return redirect()->to('/request/complete?session=' . $sessionId);
        }

        return redirect()->to('/request/finalize?session=' . $sessionId);
    }

    /**
     * SMS Verifikationscode senden
     */
    protected function sendVerificationCode(string $sessionId, array $sessionData, bool $isResend = false)
    {
        $phone = $sessionData['kontakt']['telefon'] ?? '';

        if (!$phone) {
            return redirect()->to('/request/finalize?session=' . $sessionId . '&step=kontakt')
                ->with('error', 'Telefonnummer fehlt.');
        }

        // Nummer normalisieren
        $phone = $this->normalizePhone($phone);

        // Prüfen ob Mobilnummer
        $isMobile = is_mobile_number($phone);
        $method = $isMobile ? 'sms' : 'call';

        // Code generieren (4-stellig)
        $code = rand(1000, 9999);
        $sessionData['verification_code'] = $code;
        $sessionData['verification_method'] = $method;
        $sessionData['verification_phone'] = $phone;
        $sessionData['verification_sent_at'] = date('Y-m-d H:i:s');
        session()->set('request_' . $sessionId, $sessionData);

        $siteName = siteconfig()->name ?? 'Offerten';
        $success = false;
        $messageId = null;

        try {
            if ($method === 'sms') {
                // SMS über Infobip
                $infobip = new InfobipService();
                $message = "Dein Bestätigungscode für {$siteName}: {$code}";
                $result = $infobip->sendSms($phone, $message);
                $success = $result['success'] ?? false;
                $messageId = $result['message_id'] ?? null;

                if (!$success) {
                    log_message('error', 'SMS Versand fehlgeschlagen: ' . ($result['error'] ?? 'Unknown'));
                } else {
                    log_message('info', "Verification SMS sent to {$phone}, code: {$code}");
                }
            } else {
                // Anruf über Twilio
                $twilio = new TwilioService();
                $message = lang('Verification.callVerificationCode', ['sitename' => $siteName]);
                $success = $twilio->sendCallCode($phone, $message, $code);

                if ($success) {
                    log_message('info', "Verification call initiated to {$phone}, code: {$code}");
                } else {
                    log_message('error', "Verification call failed to {$phone}");
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Verification Exception: ' . $e->getMessage());
        }

        // SMS/Anruf-Historie für alle verknüpften Offers speichern
        $this->logVerificationHistory($sessionId, $phone, $code, $method, $success, $messageId);

        if (!$success) {
            return redirect()->to('/request/finalize?session=' . $sessionId . '&step=verify')
                ->with('warning', $method === 'sms'
                    ? 'SMS konnte nicht gesendet werden. Bitte prüfe die Telefonnummer.'
                    : 'Anruf konnte nicht gestartet werden. Bitte prüfe die Telefonnummer.');
        }

        // Bei Resend Erfolgsmeldung anzeigen
        if ($isResend) {
            return redirect()->to('/request/finalize?session=' . $sessionId . '&step=verify')
                ->with('success', $method === 'sms' ? 'Ein neuer Code wurde per SMS gesendet.' : 'Ein neuer Anruf wurde gestartet.');
        }

        return redirect()->to('/request/finalize?session=' . $sessionId . '&step=verify');
    }

    /**
     * Speichert SMS/Anruf-Historie für alle verknüpften Offers
     */
    protected function logVerificationHistory(string $sessionId, string $phone, int $code, string $method, bool $success, ?string $messageId = null): void
    {
        $offerModel = new \App\Models\OfferModel();
        $historyModel = new \App\Models\SmsVerificationHistoryModel();

        // Alle Offers mit dieser request_session_id finden
        $offers = $offerModel->where('request_session_id', $sessionId)->findAll();

        if (empty($offers)) {
            log_message('warning', "[Request::logVerificationHistory] Keine Offers gefunden für request_session_id: {$sessionId}");
            return;
        }

        $status = $method === 'sms'
            ? ($success ? 'sent' : 'failed')
            : ($success ? 'call_initiated' : 'call_failed');

        foreach ($offers as $offer) {
            $historyModel->insert([
                'offer_id' => $offer['id'],
                'uuid' => $offer['uuid'] ?? null,
                'phone' => $phone,
                'verification_code' => $code,
                'method' => $method,
                'status' => $status,
                'message_id' => $messageId,
                'platform' => $offer['platform'] ?? null,
                'verified' => 0,
            ]);
        }

        log_message('info', "[Request::logVerificationHistory] Historie für " . count($offers) . " Offers gespeichert (method: {$method}, status: {$status})");
    }

    /**
     * Markiert SMS/Anruf-Historie als verifiziert
     */
    protected function markVerificationHistoryAsVerified(string $sessionId, int $code): void
    {
        $offerModel = new \App\Models\OfferModel();
        $historyModel = new \App\Models\SmsVerificationHistoryModel();

        // Alle Offers mit dieser request_session_id finden
        $offers = $offerModel->where('request_session_id', $sessionId)->findAll();

        if (empty($offers)) {
            return;
        }

        $offerIds = array_column($offers, 'id');

        // Historie-Einträge mit diesem Code als verifiziert markieren
        $db = \Config\Database::connect();
        $db->table('sms_verification_history')
            ->whereIn('offer_id', $offerIds)
            ->where('verification_code', $code)
            ->update([
                'verified' => 1,
                'verified_at' => date('Y-m-d H:i:s'),
            ]);

        log_message('info', "[Request::markVerificationHistoryAsVerified] Historie für " . count($offers) . " Offers als verifiziert markiert");
    }

    /**
     * Telefonnummer normalisieren
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone); // Nur Zahlen
        if (str_starts_with($phone, '0')) {
            $phone = '+41' . substr($phone, 1); // 0781234512 → +41781234512
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

    /**
     * Verifikationscode erneut senden
     */
    public function resendCode()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData || empty($sessionData['kontakt'])) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        return $this->sendVerificationCode($sessionId, $sessionData, true);
    }

    /**
     * Anfrage abgeschlossen - Weiterleitung zur Danke-Seite
     */
    public function complete()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start');
        }

        // Sprache aus Session oder Fallback
        $lang = $sessionData['lang'] ?? service('request')->getLocale() ?? 'de';

        // Alle Offers mit dieser request_session_id aktualisieren
        $this->updateLinkedOffers($sessionId, $sessionData);

        // Danke-Seite URL aus SiteConfig holen
        $siteConfig = siteconfig();
        $thankYouUrls = $siteConfig->thankYouUrl ?? [];

        // URL für aktuelle Sprache oder Fallback auf 'de'
        $redirectUrl = $thankYouUrls[$lang] ?? $thankYouUrls['de'] ?? $siteConfig->frontendUrl ?? '/';

        // Session aufräumen
        session()->remove('request_' . $sessionId);

        return redirect()->to($redirectUrl);
    }

    /**
     * Aktualisiert alle Offers die zu dieser Request-Session gehören
     * mit Kontaktdaten, Termin und Auftraggeber-Infos
     */
    protected function updateLinkedOffers(string $sessionId, array $sessionData): void
    {
        $offerModel = new \App\Models\OfferModel();

        // Alle Offers mit dieser request_session_id finden
        $offers = $offerModel->where('request_session_id', $sessionId)->findAll();

        if (empty($offers)) {
            log_message('warning', "[Request::complete] Keine Offers gefunden für request_session_id: {$sessionId}");
            return;
        }

        log_message('info', "[Request::complete] " . count($offers) . " Offers gefunden für request_session_id: {$sessionId}");

        // Kontaktdaten aus Session
        $kontakt = $sessionData['kontakt'] ?? [];
        $termin = $sessionData['termin'] ?? [];
        $auftraggeber = $sessionData['auftraggeber'] ?? [];
        $verifyMethod = $sessionData['verification_method'] ?? 'sms';

        // Telefonnummer normalisieren
        $phone = $kontakt['telefon'] ?? '';
        $phone = $this->normalizePhone($phone);

        // Daten für DB-Update vorbereiten
        $updateData = [
            'firstname' => $kontakt['vorname'] ?? null,
            'lastname' => $kontakt['nachname'] ?? null,
            'email' => $kontakt['email'] ?? null,
            'phone' => $phone,
            'city' => $kontakt['ort'] ?? null,
            'zip' => $kontakt['plz'] ?? null,
            'customer_type' => ($auftraggeber['typ'] ?? 'privat') === 'firma' ? 'firma' : 'privat',
            'company' => $auftraggeber['firma'] ?? null,
            'work_start_date' => $termin['datum'] ?? null,
            'verified' => 1,
            'verify_type' => $verifyMethod,
        ];

        // Alle Offers aktualisieren
        foreach ($offers as $offer) {
            // Bestehende form_fields aktualisieren
            $formFields = json_decode($offer['form_fields'], true) ?? [];

            // Kontaktdaten in form_fields einfügen
            $formFields['vorname'] = $kontakt['vorname'] ?? $formFields['vorname'] ?? null;
            $formFields['names'] = $kontakt['vorname'] ?? $formFields['names'] ?? null;
            $formFields['nachname'] = $kontakt['nachname'] ?? $formFields['nachname'] ?? null;
            $formFields['email'] = $kontakt['email'] ?? $formFields['email'] ?? null;
            $formFields['phone'] = $phone ?: ($formFields['phone'] ?? null);
            $formFields['erreichbar'] = $kontakt['erreichbar'] ?? $formFields['erreichbar'] ?? null;

            // Adresse
            $formFields['address_line_1'] = ($kontakt['strasse'] ?? '') . ' ' . ($kontakt['hausnummer'] ?? '');
            $formFields['zip'] = $kontakt['plz'] ?? $formFields['zip'] ?? null;
            $formFields['city'] = $kontakt['ort'] ?? $formFields['city'] ?? null;

            // Auftraggeber
            $formFields['auftraggeber_typ'] = $auftraggeber['typ'] ?? null;
            $formFields['firma'] = $auftraggeber['firma'] ?? null;
            $formFields['firmenname'] = $auftraggeber['firma'] ?? null;

            // Termin
            $formFields['datetime_1'] = $termin['datum'] ?? null;
            $formFields['zeit_flexibel'] = $termin['zeit'] ?? null;

            // Update Data für diese Offer
            $offerUpdateData = array_merge($updateData, [
                'form_fields' => json_encode($formFields, JSON_UNESCAPED_UNICODE),
            ]);

            $offerModel->update($offer['id'], $offerUpdateData);

            log_message('info', "[Request::complete] Offer ID {$offer['id']} aktualisiert mit Kontaktdaten");
        }

        // Telefonnummer als verifiziert speichern
        if ($phone && ($kontakt['email'] ?? null)) {
            $verifiedPhoneModel = new \App\Models\VerifiedPhoneModel();
            $platform = $offers[0]['platform'] ?? null;
            $verifiedPhoneModel->addVerifiedPhone($phone, $kontakt['email'], $verifyMethod, $platform);
            log_message('info', "[Request::complete] Telefonnummer {$phone} als verifiziert gespeichert");
        }

        // Preise berechnen falls nötig
        $priceUpdater = new \App\Libraries\OfferPriceUpdater();
        foreach ($offers as $offer) {
            // Frisch laden nach Update
            $updatedOffer = $offerModel->find($offer['id']);
            if (empty($updatedOffer['price']) || $updatedOffer['price'] <= 0) {
                $priceUpdater->updateOfferAndNotify($updatedOffer);
            }
        }

        // E-Mails senden
        $this->sendVerificationEmails($offers, $sessionData);
    }

    /**
     * Sendet E-Mails nach erfolgreicher Verifikation
     */
    protected function sendVerificationEmails(array $offers, array $sessionData): void
    {
        $offerModel = new \App\Models\OfferModel();

        // Offers neu laden mit aktualisierten Daten
        $updatedOffers = [];
        foreach ($offers as $offer) {
            $updatedOffer = $offerModel->find($offer['id']);
            if ($updatedOffer && !empty($updatedOffer['price']) && $updatedOffer['price'] > 0) {
                $updatedOffers[] = $updatedOffer;
            } else {
                log_message('warning', "[Request::sendVerificationEmails] Offer ID {$offer['id']} übersprungen - Preis ist 0");
            }
        }

        if (empty($updatedOffers)) {
            log_message('warning', "[Request::sendVerificationEmails] Keine gültigen Offers zum E-Mail-Versand");
            return;
        }

        helper(['text', 'email_template']);

        // Erste Offer für allgemeine Daten
        $firstOffer = $updatedOffers[0];
        $platform = $firstOffer['platform'] ?? null;

        if (empty($platform)) {
            log_message('error', "[Request::sendVerificationEmails] Platform fehlt");
            return;
        }

        $platformSiteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

        if (count($updatedOffers) === 1) {
            // Einzelne Offer - normale E-Mail mit Template
            $offer = $updatedOffers[0];
            $formFields = json_decode($offer['form_fields'], true) ?? [];

            $templateSent = sendOfferNotificationWithTemplate($offer, $formFields, $offer['type'] ?? 'unknown');

            if ($templateSent) {
                log_message('info', "[Request::sendVerificationEmails] E-Mail mit Template gesendet für Offer ID {$offer['id']}");
            } else {
                log_message('warning', "[Request::sendVerificationEmails] Template-Email fehlgeschlagen für Offer ID {$offer['id']}");
            }

            // Firmen benachrichtigen
            $notifier = new \App\Libraries\OfferNotificationSender();
            $sentCount = $notifier->notifyMatchingUsers($offer);
            log_message('info', "[Request::sendVerificationEmails] {$sentCount} Firmen benachrichtigt für Offer ID {$offer['id']}");

        } else {
            // Mehrere Offers - gruppierte E-Mail
            $this->sendGroupedOfferEmail($updatedOffers, $sessionData, $platformSiteConfig);
        }

        // confirmation_sent_at setzen
        $db = \Config\Database::connect();
        $offerIds = array_column($updatedOffers, 'id');
        $db->table('offers')
            ->whereIn('id', $offerIds)
            ->update(['confirmation_sent_at' => date('Y-m-d H:i:s')]);

        log_message('info', "[Request::sendVerificationEmails] confirmation_sent_at gesetzt für Offer IDs: " . implode(', ', $offerIds));
    }

    /**
     * Sendet gruppierte E-Mail für mehrere Offers
     */
    protected function sendGroupedOfferEmail(array $offers, array $sessionData, $platformSiteConfig): void
    {
        $kontakt = $sessionData['kontakt'] ?? [];
        $userEmail = $kontakt['email'] ?? null;

        if (!$userEmail) {
            log_message('error', "[Request::sendGroupedOfferEmail] E-Mail-Adresse fehlt");
            return;
        }

        // Sprache setzen
        $language = $sessionData['lang'] ?? 'de';
        service('language')->setLocale($language);

        // Technische Felder filtern
        $formFieldOptions = config('FormFieldOptions');
        $excludedFieldsAlways = $formFieldOptions->excludedFieldsAlways ?? [];

        $offersData = [];
        foreach ($offers as $offer) {
            $offerFields = json_decode($offer['form_fields'], true) ?? [];

            $filteredFields = array_filter($offerFields, function ($key) use ($excludedFieldsAlways) {
                $normalizedKey = strtolower(trim($key));
                foreach ($excludedFieldsAlways as $excludedField) {
                    if (strtolower($excludedField) === $normalizedKey) {
                        return false;
                    }
                }
                if (preg_match('/fluentform.*nonce/i', $key)) return false;
                if ($normalizedKey === 'names') return false;
                return true;
            }, ARRAY_FILTER_USE_KEY);

            $offersData[] = [
                'uuid' => $offer['uuid'],
                'type' => $offer['type'] ?? 'unknown',
                'verifyType' => $offer['verify_type'] ?? null,
                'filteredFields' => $filteredFields,
                'data' => $offerFields,
            ];
        }

        $firstOfferFields = json_decode($offers[0]['form_fields'], true) ?? [];

        $emailData = [
            'offers' => $offersData,
            'isMultiple' => count($offersData) > 1,
            'data' => $firstOfferFields,
        ];

        $message = view('emails/grouped_offer_notification', $emailData);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => lang('Email.offer_added_requests_title'),
            'content' => $message,
            'siteConfig' => siteconfig(),
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setFrom($platformSiteConfig->email, $platformSiteConfig->name);
        $email->setTo($userEmail);
        $email->setBCC($platformSiteConfig->email);
        $email->setSubject(lang('Email.offer_added_multiple_subject'));
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', '[Request::sendGroupedOfferEmail] Mail senden fehlgeschlagen');
        } else {
            log_message('info', "[Request::sendGroupedOfferEmail] Gruppierte E-Mail gesendet an {$userEmail} für " . count($offersData) . " Offers");

            // Firmen für alle Offers benachrichtigen
            $notifier = new \App\Libraries\OfferNotificationSender();
            foreach ($offers as $offer) {
                $sentCount = $notifier->notifyMatchingUsers($offer);
                log_message('info', "[Request::sendGroupedOfferEmail] {$sentCount} Firmen benachrichtigt für Offer ID {$offer['id']}");
            }
        }
    }

    /**
     * Leitet zum WordPress-Formular weiter mit allen nötigen Parametern
     */
    protected function redirectToForm(string $sessionId, int $index): \CodeIgniter\HTTP\RedirectResponse
    {
        $sessionData = session()->get('request_' . $sessionId);
        $formLink = $sessionData['form_links'][$index];

        $url = $formLink['url'];
        $separator = strpos($url, '?') !== false ? '&' : '?';

        // Parameter für WordPress
        $params = http_build_query([
            'session' => $sessionId,
            'index' => $index,
            'total' => $sessionData['total_forms'],
        ]);

        return redirect()->to($url . $separator . $params);
    }

    /**
     * Formular-Links für ausgewählte Formulare/Projekte zusammenstellen
     * Das initial-Formular wird immer an den Anfang gestellt
     */
    protected function getFormLinks(array $formIds, array $projects, string $locale = 'de', ?string $initialFormId = null): array
    {
        $categoryManager = new CategoryManager();
        $projectModel = new ProjectModel();

        $links = [];
        $initialLink = null;
        $addedUrls = []; // Um Duplikate zu vermeiden

        // Formular-Links (direkt ausgewählt)
        foreach ($formIds as $formId) {
            $form = $categoryManager->getFormById($formId, $locale);
            if ($form && !empty($form['form_link'])) {
                if (!in_array($form['form_link'], $addedUrls)) {
                    $linkData = [
                        'type' => 'form',
                        'form_id' => $formId,
                        'name' => $form['name'],
                        'category_key' => $form['category_key'],
                        'category_color' => $form['category_color'] ?? '#6c757d',
                        'url' => $form['form_link'],
                    ];

                    // Initial-Formular separat speichern
                    if ($initialFormId && $formId === $initialFormId) {
                        $initialLink = $linkData;
                    } else {
                        $links[] = $linkData;
                    }
                    $addedUrls[] = $form['form_link'];
                }
            }
        }

        // Projekt-Links (über zugewiesenes Formular)
        foreach ($projects as $projectSlug) {
            $project = $projectModel->findBySlug($projectSlug);
            if ($project && !empty($project['form_id'])) {
                $form = $categoryManager->getFormById($project['form_id'], $locale);
                if ($form && !empty($form['form_link'])) {
                    if (!in_array($form['form_link'], $addedUrls)) {
                        $links[] = [
                            'type' => 'project',
                            'key' => $projectSlug,
                            'name' => $project['name_de'],
                            'form_id' => $project['form_id'],
                            'category_color' => $form['category_color'] ?? '#6c757d',
                            'url' => $form['form_link'],
                        ];
                        $addedUrls[] = $form['form_link'];
                    }
                }
            }
        }

        // Initial-Formular an den Anfang stellen
        if ($initialLink) {
            array_unshift($links, $initialLink);
        }

        return $links;
    }

    /**
     * Debug: Session-Daten anzeigen
     */
    public function debug($sessionId = null)
    {
        if (!$sessionId) {
            return 'No session ID';
        }

        $data = session()->get('request_' . $sessionId);

        if (!$data) {
            return 'Session not found';
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
