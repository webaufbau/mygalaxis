<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Entities\User;
use App\Libraries\ZipcodeService;

class OfferNotificationSender
{
    protected UserModel $userModel;
    protected ZipcodeService $zipcodeService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->zipcodeService = new ZipcodeService();
    }

    /**
     * E-Mail an alle passenden Firmen senden
     * @param bool $skipManualReviewCheck Wenn true, wird die manualOfferReviewEnabled-Prüfung übersprungen (für Admin-Freigabe)
     * @return int Anzahl versendeter E-Mails
     */
    public function notifyMatchingUsers(array $offer, bool $skipManualReviewCheck = false): int
    {
        // KRITISCHER SECURITY-CHECK: Nur verifizierte Offerten dürfen E-Mails auslösen
        if (empty($offer['verified']) || $offer['verified'] != 1) {
            log_message('warning', "Offerte #{$offer['id']} ist nicht verifiziert - keine Firmen-Benachrichtigungen gesendet");
            return 0;
        }

        // MANUAL REVIEW CHECK: Wenn manualOfferReviewEnabled aktiv ist, keine automatischen Firmen-Benachrichtigungen
        // (außer wenn explizit durch Admin freigegeben via $skipManualReviewCheck)
        if (!$skipManualReviewCheck) {
            $platform = $offer['platform'] ?? null;
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);
            if (!empty($siteConfig->manualOfferReviewEnabled)) {
                log_message('info', "Offerte #{$offer['id']} - manuelle Prüfung aktiviert, keine automatische Firmen-Benachrichtigung");
                return 0;
            }
        }

        $users = $this->userModel->findAll();
        $today = date('Y-m-d');
        $sentCount = 0;
        $notifiedCompanyIds = []; // Sammle IDs der benachrichtigten Firmen

        // Prüfe ob es eine Testanfrage ist
        $isTestOffer = !empty($offer['is_test']);

        foreach ($users as $user) {
            if (!$user->inGroup('user')) continue;

            // Prüfe ob User vom Admin blockiert wurde
            if ($user->is_blocked) {
                continue;
            }

            // TESTANFRAGE-LOGIK:
            // - Testfirmen erhalten ALLE Anfragen (Test + Normal)
            // - Normale Firmen erhalten NUR normale Anfragen (keine Testanfragen)
            $isTestCompany = !empty($user->is_test);
            if ($isTestOffer && !$isTestCompany) {
                // Testanfrage aber keine Testfirma -> überspringen
                continue;
            }

            // Check if user has disabled email notifications
            if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
                continue;
            }

            // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
            if ($this->isUserBlockedToday($user->id, $today)) {
                continue;
            }

            if ($this->doesOfferMatchUser($offer, $user)) {
                $this->sendOfferEmail($user, $offer);
                $notifiedCompanyIds[] = $user->id;
                $sentCount++;
            }
        }

        // Setze companies_notified_at wenn mindestens eine E-Mail gesendet wurde
        // ODER wenn keine passenden Firmen gefunden wurden (dann trotzdem als "verarbeitet" markieren)
        $db = \Config\Database::connect();
        $db->table('offers')->where('id', $offer['id'])->update([
            'companies_notified_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', "Firmen-Benachrichtigung für Offer ID {$offer['id']}: {$sentCount} E-Mails versendet");

        // Logge Firmen-Benachrichtigung in Email-Log (gruppiert)
        if ($sentCount > 0) {
            $emailLogModel = new \App\Models\OfferEmailLogModel();
            $priceFormatted = number_format($offer['discounted_price'] ?? $offer['price'], 0, '.', '\'');
            $typeMapping = [
                'move' => 'Umzug',
                'cleaning' => 'Reinigung',
                'move_cleaning' => 'Umzug + Reinigung',
                'painting' => 'Maler/Gipser',
                'plumbing' => 'Sanitär',
                'electrician' => 'Elektriker',
                'heating' => 'Heizung',
                'gardening' => 'Garten',
            ];
            $typeName = $typeMapping[$offer['type']] ?? ucfirst($offer['type']);
            $subject = "Neue Anfrage Preis Fr. {$priceFormatted}.– für {$typeName} ID {$offer['id']} - {$offer['zip']} {$offer['city']}";

            $emailLogModel->logEmail(
                offerId: $offer['id'],
                emailType: 'company_notification',
                recipientEmail: "{$sentCount} Firmen",
                recipientType: 'company',
                companyId: null,
                subject: $subject,
                status: 'sent',
                errorMessage: null,
                notifiedCompanyIds: $notifiedCompanyIds
            );
        }

        return $sentCount;
    }

    /**
     * E-Mail an spezifische Firmen senden (für manuelle Auswahl durch Admin)
     * @param array $offer Die Offerte
     * @param array $userIds Array von User-IDs der auszuwählenden Firmen
     * @param bool $skipManualReviewCheck Wenn true, wird die manualOfferReviewEnabled-Prüfung übersprungen
     * @return int Anzahl versendeter E-Mails
     */
    public function notifySpecificUsers(array $offer, array $userIds, bool $skipManualReviewCheck = false): int
    {
        // KRITISCHER SECURITY-CHECK: Nur verifizierte Offerten dürfen E-Mails auslösen
        if (empty($offer['verified']) || $offer['verified'] != 1) {
            log_message('warning', "Offerte #{$offer['id']} ist nicht verifiziert - keine Firmen-Benachrichtigungen gesendet");
            return 0;
        }

        if (empty($userIds)) {
            log_message('info', "Keine Firmen-IDs für Offerte #{$offer['id']} übergeben");
            return 0;
        }

        $today = date('Y-m-d');
        $sentCount = 0;

        // Prüfe ob es eine Testanfrage ist
        $isTestOffer = !empty($offer['is_test']);

        foreach ($userIds as $userId) {
            $user = $this->userModel->find($userId);

            if (!$user) {
                log_message('warning', "Firma ID {$userId} nicht gefunden");
                continue;
            }

            if (!$user->inGroup('user')) {
                log_message('warning', "User ID {$userId} ist keine Firma");
                continue;
            }

            // Prüfe ob User vom Admin blockiert wurde
            if ($user->is_blocked) {
                log_message('info', "Firma ID {$userId} ist blockiert - übersprungen");
                continue;
            }

            // TESTANFRAGE-LOGIK (nur bei manueller Auswahl als Warnung loggen, nicht blockieren)
            $isTestCompany = !empty($user->is_test);
            if ($isTestOffer && !$isTestCompany) {
                log_message('warning', "Testanfrage #{$offer['id']} wird an Nicht-Testfirma {$userId} gesendet (manuelle Auswahl)");
            }
            if (!$isTestOffer && $isTestCompany) {
                log_message('warning', "Normale Anfrage #{$offer['id']} wird an Testfirma {$userId} gesendet (manuelle Auswahl)");
            }

            // Check if user has disabled email notifications
            if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
                log_message('info', "Firma ID {$userId} hat E-Mail-Benachrichtigungen deaktiviert");
                continue;
            }

            // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
            if ($this->isUserBlockedToday($user->id, $today)) {
                log_message('info', "Firma ID {$userId} ist heute blockiert (Agenda)");
                continue;
            }

            // Bei manueller Auswahl: Sende ohne Matching-Prüfung
            $this->sendOfferEmail($user, $offer);
            $sentCount++;
        }

        // Setze companies_notified_at
        $db = \Config\Database::connect();
        $db->table('offers')->where('id', $offer['id'])->update([
            'companies_notified_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', "Firmen-Benachrichtigung für Offer ID {$offer['id']} an spezifische Firmen: {$sentCount} von " . count($userIds) . " E-Mails versendet");

        return $sentCount;
    }

    /**
     * Prüft ob ein User heute blockiert ist (Agenda-Eintrag)
     */
    protected function isUserBlockedToday(int $userId, string $today): bool
    {
        $blockedModel = model(\App\Models\BlockedDayModel::class);
        return $blockedModel
            ->where('user_id', $userId)
            ->where('date', $today)
            ->countAllResults() > 0;
    }

    protected function doesOfferMatchUser(array $offer, User $user): bool
    {
        // PLATFORM-CHECK ENTFERNT: Firmen erhalten Anfragen von allen Plattformen
        // (vorher: nur Anfragen von gleicher Platform wie Firma)

        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        // Verwende Platform der Offerte für siteConfig
        $offerPlatform = $offer['platform'] ?? null;
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offerPlatform);
        $siteCountry = $siteConfig->siteCountry ?? null;

        $relevantZips = $this->zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) return false;
        if (!empty($categories) && !in_array($offer['type'], $categories)) return false;
        if (!empty($languages) && !in_array($offer['language'], $languages)) return false;

        return true;
    }

    /**
     * Extrahiert alle Felder als separate Variablen für E-Mail-Templates
     * @param array $offer Vollständige Offertendaten
     * @return array Assoziatives Array mit extrahierten Feldern
     */
    protected function extractFieldsForTemplate(array $offer): array
    {
        $fields = [];
        $db = \Config\Database::connect();

        // Grundlegende Felder aus offers-Tabelle
        $fields['city'] = $offer['city'] ?? null;
        $fields['zip'] = $offer['zip'] ?? null;
        $fields['country'] = $offer['country'] ?? null;

        // Dekodiere form_fields falls noch nicht geschehen
        $formData = $offer['data'] ?? [];
        if (is_string($formData)) {
            $formData = json_decode($formData, true) ?? [];
        }

        // Extrahiere Adress-Objekte und mache einzelne Felder verfügbar
        // Normale Adresse
        if (isset($formData['address']) && is_array($formData['address'])) {
            $fields['address_street'] = $formData['address']['address_line_1'] ?? null;
            $fields['address_number'] = $formData['address']['address_line_2'] ?? null;
            $fields['address_zip'] = $formData['address']['zip'] ?? null;
            $fields['address_city'] = $formData['address']['city'] ?? null;
        }

        // Auszug-Adresse (Umzug)
        if (isset($formData['auszug_adresse']) && is_array($formData['auszug_adresse'])) {
            $fields['auszug_street'] = $formData['auszug_adresse']['address_line_1'] ?? null;
            $fields['auszug_number'] = $formData['auszug_adresse']['address_line_2'] ?? null;
            $fields['auszug_zip'] = $formData['auszug_adresse']['zip'] ?? null;
            $fields['auszug_city'] = $formData['auszug_adresse']['city'] ?? null;
        }

        // Einzug-Adresse (Umzug)
        if (isset($formData['einzug_adresse']) && is_array($formData['einzug_adresse'])) {
            $fields['einzug_street'] = $formData['einzug_adresse']['address_line_1'] ?? null;
            $fields['einzug_number'] = $formData['einzug_adresse']['address_line_2'] ?? null;
            $fields['einzug_zip'] = $formData['einzug_adresse']['zip'] ?? null;
            $fields['einzug_city'] = $formData['einzug_adresse']['city'] ?? null;
        }

        // Auszug-Adresse Firma (Umzug Firma)
        if (isset($formData['auszug_adresse_firma']) && is_array($formData['auszug_adresse_firma'])) {
            $fields['auszug_street'] = $formData['auszug_adresse_firma']['address_line_1'] ?? null;
            $fields['auszug_number'] = $formData['auszug_adresse_firma']['address_line_2'] ?? null;
            $fields['auszug_zip'] = $formData['auszug_adresse_firma']['zip'] ?? null;
            $fields['auszug_city'] = $formData['auszug_adresse_firma']['city'] ?? null;
        }

        // Einzug-Adresse Firma (Umzug Firma)
        if (isset($formData['einzug_adresse_firma']) && is_array($formData['einzug_adresse_firma'])) {
            $fields['einzug_street'] = $formData['einzug_adresse_firma']['address_line_1'] ?? null;
            $fields['einzug_number'] = $formData['einzug_adresse_firma']['address_line_2'] ?? null;
            $fields['einzug_zip'] = $formData['einzug_adresse_firma']['zip'] ?? null;
            $fields['einzug_city'] = $formData['einzug_adresse_firma']['city'] ?? null;
        }

        // Lade Umzug-spezifische Felder aus offers_move Tabelle
        if ($offer['type'] === 'move') {
            $moveData = $db->table('offers_move')
                ->where('offer_id', $offer['id'])
                ->get()
                ->getRowArray();

            if ($moveData) {
                $fields['from_city'] = $moveData['from_city'] ?? null;
                $fields['to_city'] = $moveData['to_city'] ?? null;
                $fields['from_object_type'] = $moveData['from_object_type'] ?? null;
                $fields['to_object_type'] = $moveData['to_object_type'] ?? null;
                $fields['from_room_count'] = $moveData['from_room_count'] ?? null;
                $fields['to_room_count'] = $moveData['to_room_count'] ?? null;
                $fields['move_date'] = $moveData['move_date'] ?? null;
                $fields['customer_type'] = $moveData['customer_type'] ?? null;
            }
        }

        // Lade Umzug-Reinigung-Kombi-spezifische Felder aus offers_move_cleaning Tabelle
        if ($offer['type'] === 'move_cleaning') {
            $moveCleaningData = $db->table('offers_move_cleaning')
                ->where('offer_id', $offer['id'])
                ->get()
                ->getRowArray();

            if ($moveCleaningData) {
                $fields['from_city'] = $moveCleaningData['from_city'] ?? null;
                $fields['to_city'] = $moveCleaningData['to_city'] ?? null;
                $fields['address_city'] = $moveCleaningData['address_city'] ?? null;
                $fields['from_object_type'] = $moveCleaningData['from_object_type'] ?? null;
                $fields['to_object_type'] = $moveCleaningData['to_object_type'] ?? null;
                $fields['from_room_count'] = $moveCleaningData['from_room_count'] ?? null;
                $fields['to_room_count'] = $moveCleaningData['to_room_count'] ?? null;
                $fields['cleaning_type'] = $moveCleaningData['cleaning_type'] ?? null;
                $fields['move_date'] = $moveCleaningData['move_date'] ?? null;
                $fields['customer_type'] = $moveCleaningData['customer_type'] ?? null;
            }
        }

        // Weitere wichtige Felder aus form_fields
        $simpleFields = [
            'vorname', 'nachname', 'email', 'phone', 'telefon', 'mobile', 'handy',
            'company', 'firma', 'datetime_1', 'work_start_date', 'move_date',
            'erreichbar', 'details_hinweise', 'sonstige_hinweise'
        ];

        foreach ($simpleFields as $fieldName) {
            if (isset($formData[$fieldName]) && !is_array($formData[$fieldName])) {
                $fields[$fieldName] = $formData[$fieldName];
            }
        }

        return $fields;
    }

    protected function sendOfferEmail(User $user, array $offer): void
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        // Lade vollständige Offertendaten inkl. data-Feld
        $offerModel = new \App\Models\OfferModel();
        $fullOffer = $offerModel->find($offer['id']);

        if (!$fullOffer) {
            log_message('error', 'Offerte ID ' . $offer['id'] . ' nicht gefunden für E-Mail-Versand');
            return;
        }

        // Dekodiere data-Feld falls JSON, oder verwende form_fields als Fallback
        if (isset($fullOffer['data']) && is_string($fullOffer['data'])) {
            $fullOffer['data'] = json_decode($fullOffer['data'], true) ?? [];
        } elseif (!isset($fullOffer['data']) || empty($fullOffer['data'])) {
            // Fallback: Verwende form_fields wenn data nicht existiert
            if (isset($fullOffer['form_fields']) && is_string($fullOffer['form_fields'])) {
                $fullOffer['data'] = json_decode($fullOffer['form_fields'], true) ?? [];
            } else {
                $fullOffer['data'] = [];
            }
        }

        // Prüfe ob User diese Offerte bereits gekauft hat
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchase = $purchaseModel
            ->where('offer_id', $offer['id'])
            ->where('user_id', $user->id)
            ->first();

        $alreadyPurchased = !empty($purchase);

        // Entferne sensible Adressdaten (Straße, Hausnummer) wenn noch nicht gekauft
        if (!$alreadyPurchased && !empty($fullOffer['data'])) {
            foreach ($fullOffer['data'] as $key => $value) {
                if (preg_match('/adresse|address/i', $key) && is_array($value)) {
                    // Entferne address_line_1 und address_line_2, aber behalte zip und city
                    if (isset($fullOffer['data'][$key]['address_line_1'])) {
                        unset($fullOffer['data'][$key]['address_line_1']);
                    }
                    if (isset($fullOffer['data'][$key]['address_line_2'])) {
                        unset($fullOffer['data'][$key]['address_line_2']);
                    }
                }
            }
        }

        // Versuche zuerst Datenbank-Template zu laden
        $language = $user->language ?? 'de';
        $offerType = $fullOffer['type'] ?? 'default';

        // Detect subtype from offer data
        $subtype = $offerModel->detectSubtype($fullOffer['data']);

        $templateModel = new \App\Models\EmailTemplateModel();

        // Versuche spezielles Firmen-Benachrichtigungs-Template zu finden
        // WICHTIG: NUR nach "company_notification" Templates suchen, NICHT auf default/customer templates zurückfallen!

        // 1. Suche nach "company_notification_[type]" z.B. "company_notification_cleaning"
        $companyNotificationType = 'company_notification_' . $offerType;
        $template = $templateModel
            ->where('offer_type', $companyNotificationType)
            ->where('language', $language)
            ->where('is_active', 1)
            ->first();

        if (!$template) {
            // 2. Fallback: Suche nach allgemeinem "company_notification" Template (ohne Subtype)
            $template = $templateModel
                ->where('offer_type', 'company_notification')
                ->where('subtype IS NULL')
                ->where('language', $language)
                ->where('is_active', 1)
                ->first();
        }

        if ($template) {
            // Verwende Datenbank-Template
            log_message('info', "Verwende Firmen-Benachrichtigungs-Template ID {$template['id']} für Typ: {$offerType}, Sprache: {$language}");
            $this->sendEmailWithDatabaseTemplate($user, $fullOffer, $template, $siteConfig, $alreadyPurchased);
        } else {
            // Fallback zu hardcoded View-Template
            log_message('info', "Kein Firmen-Benachrichtigungs-Template gefunden für Typ: {$offerType}, Sprache: {$language}, verwende Fallback View-Template");
            $this->sendEmailWithViewTemplate($user, $fullOffer, $siteConfig, $alreadyPurchased);
        }
    }

    /**
     * Sendet E-Mail mit Datenbank-Template
     */
    protected function sendEmailWithDatabaseTemplate(User $user, array $fullOffer, array $template, $siteConfig, bool $alreadyPurchased): void
    {
        helper('text');

        // Bereite Daten vor - merge offer data mit zusätzlichen Variablen
        $data = $fullOffer['data'] ?? [];
        $data['offer_id'] = $fullOffer['id'];
        $data['offer_title'] = $fullOffer['title'];
        $data['offer_type'] = $fullOffer['type'];
        $data['offer_city'] = $fullOffer['city'];
        $data['offer_zip'] = $fullOffer['zip'];
        $data['offer_price'] = $fullOffer['price'];
        $data['offer_discounted_price'] = $fullOffer['discounted_price'] ?? null;
        $data['offer_currency'] = $fullOffer['currency'] ?? 'CHF';
        $data['company_name'] = $user->company_name;
        $data['contact_person'] = $user->contact_person;
        $data['already_purchased'] = $alreadyPurchased;

        // URL zum Kauf der Offerte
        $data['offer_buy_url'] = rtrim($siteConfig->backendUrl, '/') . '/offers/buy/' . $fullOffer['id'];

        // Plattform-Domain aus der Offerte extrahieren (für Hinweis in E-Mails an Firmen)
        $offerPlatformDomain = '';
        if (!empty($fullOffer['platform'])) {
            $offerPlatformDomain = str_replace('my_', '', $fullOffer['platform']);
            $offerPlatformDomain = str_replace('_', '.', $offerPlatformDomain);
            $offerPlatformDomain = ucfirst($offerPlatformDomain);
        }
        $data['offer_platform_domain'] = $offerPlatformDomain;

        $excludedFields = [
            'terms_n_condition', 'terms_and_conditions', 'terms',
            'type', 'lang', 'language', 'csrf_test_name', 'submit', 'form_token',
            '__submission', '__fluent_form_embded_post_id', '_wp_http_referer',
            'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer',
            'skip_kontakt', 'skip_reinigung_umzug',
        ];

        // Wenn nicht gekauft, verstecke auch Kontaktdaten
        if (!$alreadyPurchased) {
            $excludedFields = array_merge($excludedFields, [
                'vorname', 'nachname', 'email', 'phone', 'telefon', 'tel',
                'e-mail', 'e_mail', 'mail', 'mobile', 'handy',
                'strasse', 'street', 'address', 'adresse', 'hausnummer'
            ]);
        }

        // Parse Template
        $parser = new \App\Services\EmailTemplateParser($user->platform);
        $parsedSubject = $parser->parse($template['subject'], $data, $excludedFields);

        // Parse field_display_template if available
        $fieldDisplayHtml = '';
        if (!empty($template['field_display_template'])) {
            $fieldDisplayHtml = $parser->parse($template['field_display_template'], $data, $excludedFields);
        } else {
            // Fallback: use show_all if no field_display_template
            $fieldDisplayHtml = $parser->parse('[show_all]', $data, $excludedFields);
        }

        // Replace {{FIELD_DISPLAY}} in body_template
        $bodyTemplate = str_replace('{{FIELD_DISPLAY}}', $fieldDisplayHtml, $template['body_template']);

        // Parse the complete body with all shortcodes
        $parsedBody = $parser->parse($bodyTemplate, $data, $excludedFields);

        // Füge Plattform-Hinweis am Anfang ein (nach Greeting, falls vorhanden)
        if (!empty($offerPlatformDomain)) {
            $platformNotice = '<div style="background-color: #e7f3ff; border: 1px solid #b8daff; border-radius: 5px; padding: 12px 15px; margin: 15px 0;">'
                . '<p style="margin: 0; font-size: 14px; color: #004085;">'
                . '<strong>Hinweis:</strong> Diese Anfrage stammt von <strong>' . esc($offerPlatformDomain) . '</strong>. '
                . 'Bei der Kommunikation mit dem Kunden erwähnen Sie bitte die Plattform "' . esc($offerPlatformDomain) . '".'
                . '</p></div>';

            // Versuche nach dem ersten </p> oder <h2> einzufügen, sonst am Anfang
            if (preg_match('/(<\/p>|<\/h2>)/i', $parsedBody, $matches, PREG_OFFSET_MATCH)) {
                $insertPos = $matches[0][1] + strlen($matches[0][0]);
                $parsedBody = substr($parsedBody, 0, $insertPos) . $platformNotice . substr($parsedBody, $insertPos);
            } else {
                $parsedBody = $platformNotice . $parsedBody;
            }
        }

        // Translate field values if template language is not German
        helper('email_translation');
        if ($template['language'] !== 'de') {
            $parsedBody = translate_email_field_values($parsedBody, $template['language']);
            $parsedSubject = translate_email_field_values($parsedSubject, $template['language']);
        }

        // Wrap in email layout
        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => $parsedSubject,
            'content' => $parsedBody,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        helper('email_template');
        $email = \Config\Services::email();
        $email->setTo($siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail());
        $email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
        $email->setSubject($parsedSubject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $user->getEmail() . ': ' . print_r($email->printDebugger(), true));
        } else {
            log_message('info', "Firmen-Benachrichtigung mit Datenbank-Template ID {$template['id']} gesendet an {$user->getEmail()} für Offerte #{$fullOffer['id']}");
        }
    }

    /**
     * Sendet E-Mail mit hardcoded View-Template (Fallback)
     */
    protected function sendEmailWithViewTemplate(User $user, array $fullOffer, $siteConfig, bool $alreadyPurchased): void
    {
        // Extrahiere separate Felder für E-Mail-Template
        $extractedFields = $this->extractFieldsForTemplate($fullOffer);

        // Versuche field_display_template aus Datenbank zu laden
        $customFieldDisplay = $this->getFieldDisplayFromDatabase($fullOffer, $user, $alreadyPurchased);

        // Extrahiere Domain aus User-Platform (NICHT Offer-Platform!)
        // So sieht die E-Mail aus als käme die Anfrage von der Plattform der Firma
        $domain = '';
        if (!empty($user->platform)) {
            $domain = str_replace('my_', '', $user->platform);
            $domain = str_replace('_', '.', $domain);
        } else {
            // Fallback: extrahiere aus frontendUrl der User-SiteConfig
            $url = $siteConfig->frontendUrl ?? base_url();
            $domain = preg_replace('#^https?://([^/]+).*$#', '$1', $url);
            $parts = explode('.', $domain);
            if (count($parts) >= 2) {
                $domain = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
            }
        }
        // Capitalize first letter
        $domain = ucfirst($domain);

        // Typ mit spezifischen Formulierungen für E-Mail-Betreffs
        $type = $this->getOfferTypeForSubject($fullOffer['type']);

        // Preis formatieren (entweder discounted_price oder regulärer price)
        $price = !empty($fullOffer['discounted_price']) ? $fullOffer['discounted_price'] : $fullOffer['price'];
        $priceFormatted = number_format($price, 0, '.', '\'');

        $subject = "Neue Anfrage Preis Fr. {$priceFormatted}.– für {$type} ID {$fullOffer['id']} - {$fullOffer['zip']} {$fullOffer['city']}";

        // Extrahiere Plattform-Domain aus der Offerte (NICHT User-Platform)
        $offerPlatformDomain = '';
        if (!empty($fullOffer['platform'])) {
            $offerPlatformDomain = str_replace('my_', '', $fullOffer['platform']);
            $offerPlatformDomain = str_replace('_', '.', $offerPlatformDomain);
            $offerPlatformDomain = ucfirst($offerPlatformDomain);
        }

        $message = view('emails/offer_new_detailed', [
            'firma' => $user,
            'offer' => $fullOffer,
            'siteConfig' => $siteConfig,
            'alreadyPurchased' => $alreadyPurchased,
            'fields' => $extractedFields,
            'customFieldDisplay' => $customFieldDisplay,
            'offerPlatformDomain' => $offerPlatformDomain, // Plattform-Domain der Offerte
        ]);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => 'Neue passende Offerten',
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        helper('email_template');
        $email = \Config\Services::email();
        $email->setTo($siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail());
        $email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $user->getEmail() . ': ' . print_r($email->printDebugger(), true));
        }
    }

    /**
     * Gibt den korrekten Typ-Namen für E-Mail-Betreffs zurück
     * Spezielle Formulierungen je nach Branche für "Neue Anfrage" E-Mails
     */
    protected function getOfferTypeForSubject(string $offerType): string
    {
        // Spezielle Formulierungen für Betreffs
        $typeMapping = [
            'move'              => 'Umzug',
            'cleaning'          => 'Reinigung',
            'move_cleaning'     => 'Umzug + Reinigung',
            'painting'          => 'Maler/Gipser',
            'painter'           => 'Maler/Gipser',
            'gardening'         => 'Garten Arbeiten',
            'gardener'          => 'Garten Arbeiten',
            'electrician'       => 'Elektriker Arbeiten',
            'plumbing'          => 'Sanitär Arbeiten',
            'heating'           => 'Heizung Arbeiten',
            'tiling'            => 'Platten Arbeiten',
            'flooring'          => 'Boden Arbeiten',
            'furniture_assembly'=> 'Möbelaufbau',
            'other'             => 'Sonstiges',
        ];

        return $typeMapping[$offerType] ?? ucfirst(str_replace('_', ' ', $offerType));
    }

    /**
     * Lädt field_display_template aus Datenbank für Kunden-Bestätigungs-Email
     * (wird für Firmen-Benachrichtigungen wiederverwendet)
     */
    protected function getFieldDisplayFromDatabase(array $fullOffer, User $user, bool $alreadyPurchased): ?string
    {
        $language = $user->language ?? 'de';
        $offerType = $fullOffer['type'] ?? 'default';

        $offerModel = new \App\Models\OfferModel();
        $subtype = $offerModel->detectSubtype($fullOffer['data']);

        // Lade das Kunden-Bestätigungs-Template (cleaning, gardening, etc.)
        $templateModel = new \App\Models\EmailTemplateModel();
        $template = $templateModel->getTemplateForOffer($offerType, $language, $subtype);

        if (!$template || empty($template['field_display_template'])) {
            return null; // Kein Template gefunden oder kein field_display_template vorhanden
        }

        // Bereite Daten für Parser vor
        $data = $fullOffer['data'] ?? [];
        $data['offer_id'] = $fullOffer['id'];
        $data['offer_title'] = $fullOffer['title'];
        $data['offer_type'] = $fullOffer['type'];
        $data['offer_city'] = $fullOffer['city'];
        $data['offer_zip'] = $fullOffer['zip'];
        $data['contact_person'] = $user->contact_person;
        $data['company_name'] = $user->company_name;

        $excludedFields = [
            'terms_n_condition', 'terms_and_conditions', 'terms',
            'type', 'lang', 'language', 'csrf_test_name', 'submit', 'form_token',
            '__submission', '__fluent_form_embded_post_id', '_wp_http_referer',
            'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer',
            'skip_kontakt', 'skip_reinigung_umzug',
        ];

        // Wenn nicht gekauft, verstecke auch Kontaktdaten
        if (!$alreadyPurchased) {
            $excludedFields = array_merge($excludedFields, [
                'vorname', 'nachname', 'email', 'phone', 'telefon', 'tel',
                'e-mail', 'e_mail', 'mail', 'mobile', 'handy',
                'strasse', 'street', 'address', 'adresse', 'hausnummer'
            ]);
        }

        // Parse field_display_template
        $parser = new \App\Services\EmailTemplateParser($user->platform);
        $parsedFieldDisplay = $parser->parse($template['field_display_template'], $data, $excludedFields);

        log_message('info', "Verwende field_display_template aus Template ID {$template['id']} für Firmen-Benachrichtigung");

        return $parsedFieldDisplay;
    }
}
