<?php

use App\Models\EmailTemplateModel;
use App\Services\EmailTemplateParser;

if (!function_exists('getEmailFromName')) {
    /**
     * Generate email sender name from site config
     * Uses sitename + domain_extension (e.g. "Offertenschweiz.ch")
     *
     * @param object $siteConfig Site configuration object
     * @return string Sender name
     */
    function getEmailFromName($siteConfig): string
    {
        $domain = '';

        // Debug Logging
        $debugEnabled = false; // Setze auf true zum Debuggen
        if ($debugEnabled) {
            log_message('debug', 'getEmailFromName() called');
            log_message('debug', 'siteConfig->name: ' . var_export($siteConfig->name ?? 'NULL', true));
            log_message('debug', 'siteConfig->email: ' . var_export($siteConfig->email ?? 'NULL', true));
            log_message('debug', 'siteConfig->domain_extension: ' . var_export($siteConfig->domain_extension ?? 'NULL', true));
        }

        // Versuche name zu bekommen
        // WICHTIG: Verwende nicht empty() mit Magic Getters!
        $name = $siteConfig->name ?? '';
        if ($name !== '' && $name !== null) {
            // Use first part of name (e.g. "Offertenschweiz AG" -> "Offertenschweiz")
            $domain = explode(' ', $name)[0];
        }

        // Fallback: Wenn name leer ist, verwende email domain
        $email = $siteConfig->email ?? '';
        if (($domain === '' || $domain === null) && $email !== '' && $email !== null) {
            // Extrahiere Domain aus E-Mail (z.B. info@offertenschweiz.ch -> offertenschweiz)
            $emailParts = explode('@', $email);
            if (count($emailParts) === 2) {
                $domainParts = explode('.', $emailParts[1]);
                if (count($domainParts) >= 2) {
                    $domain = $domainParts[0];
                }
            }
        }

        $domainExtension = $siteConfig->domain_extension ?? '.ch';
        $result = ucfirst($domain) . $domainExtension;

        if ($debugEnabled) {
            log_message('debug', 'getEmailFromName() result: ' . $result);
        }

        return $result;
    }
}

if (!function_exists('sendOfferNotificationWithTemplate')) {
    /**
     * Send offer notification email using database template
     *
     * @param array $offer Full offer data from database
     * @param array $data Form field data
     * @param string|null $formName Form name
     * @return bool Success status
     */
    function sendOfferNotificationWithTemplate(array $offer, array $data, ?string $formName = null): bool
    {
        helper('text');

        // Check if confirmation email was already sent
        if (!empty($offer['confirmation_sent_at'])) {
            log_message('info', "Bestätigungsmail wurde bereits versendet für Angebot ID {$offer['id']} (UUID: {$offer['uuid']})");
            return false;
        }

        // Get language and offer type
        $language = $data['lang'] ?? $offer['language'] ?? 'de';
        $offerType = $offer['type'] ?? 'default';

        // Set locale
        $languageService = service('language');
        $languageService->setLocale($language);

        $request = service('request');
        if (!($request instanceof \CodeIgniter\HTTP\CLIRequest)) {
            $request->setLocale($language);
        }

        // Detect subtype from form fields
        $offerModel = new \App\Models\OfferModel();
        $subtype = $offerModel->detectSubtype($data);

        // Load template from database
        $templateModel = new EmailTemplateModel();
        $template = $templateModel->getTemplateForOffer($offerType, $language, $subtype);

        if (!$template) {
            $subtypeInfo = $subtype ? ", Subtype: {$subtype}" : '';
            log_message('error', "Kein E-Mail Template gefunden für Offer Type: {$offerType}{$subtypeInfo}, Language: {$language}");
            // Fallback to old method if template not found
            return false;
        }

        $subtypeInfo = $subtype ? ", Subtype: {$subtype}" : '';
        $templateSubtype = $template['subtype'] ?? 'NULL (gilt für alle)';
        log_message('info', "Verwende E-Mail Template ID {$template['id']} (Template Subtype: {$templateSubtype}) für Offer Type: {$offerType}{$subtypeInfo}, Language: {$language}");

        // Load platform-specific config
        $platform = $offer['platform'] ?? null;
        if (empty($platform)) {
            log_message('error', "E-Mail kann nicht gesendet werden: Platform fehlt für Angebot ID {$offer['id']} (UUID: {$offer['uuid']})");
            return false;
        }

        log_message('debug', "=== EMAIL SUBJECT DEBUGGING für Angebot ID {$offer['id']} (UUID: {$offer['uuid']}) ===");
        log_message('debug', "Platform aus Offer-Datensatz: " . json_encode($platform));

        $platformSiteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

        log_message('debug', "Platform Site Config Name: " . json_encode($platformSiteConfig->name ?? 'NULL'));
        log_message('debug', "Platform Site Config URL: " . json_encode($platformSiteConfig->url ?? 'NULL'));

        // Prepare data for template parser
        $excludedFields = [
            'terms_n_condition',
            'terms_and_conditions',
            'terms',
            'type',
            'lang',
            'language',
            'csrf_test_name',
            'submit',
            'form_token',
            '__submission',
            '__fluent_form_embded_post_id',
            '_wp_http_referer',
            'form_name',
            'uuid',
            'service_url',
            'uuid_value',
            'verified_method',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'referrer',
            'skip_kontakt',
            'skip_reinigung_umzug',
        ];

        // Parse template with platform
        log_message('debug', "Template Subject (roh): " . json_encode($template['subject']));

        $parser = new EmailTemplateParser($platform);
        $parsedSubject = $parser->parse($template['subject'], $data, $excludedFields);

        log_message('debug', "Parsed Subject (geparst): " . json_encode($parsedSubject));
        log_message('debug', "=== END EMAIL SUBJECT DEBUGGING ===");

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

        // Translate field values if template language is not German
        helper('email_translation');
        if ($template['language'] !== 'de') {
            $parsedBody = translate_email_field_values($parsedBody, $template['language']);
            $parsedSubject = translate_email_field_values($parsedSubject, $template['language']);
        }

        // Wrap in email layout
        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'      => $parsedSubject,
            'content'    => $parsedBody,
            'siteConfig' => $platformSiteConfig,
        ])->render('emails/layout');

        // Get recipient email
        $userEmail = $data['email'] ?? null;

        if (!$userEmail) {
            log_message('error', "Keine E-Mail-Adresse für Angebot ID {$offer['id']} gefunden");
            return false;
        }

        // Prepare BCC for admins
        $adminEmails = [$platformSiteConfig->email];
        $bccString = implode(',', $adminEmails);

        // Send email
        $email = \Config\Services::email();
        $email->setFrom($platformSiteConfig->email, getEmailFromName($platformSiteConfig));
        $email->setTo($userEmail);
        $email->setBCC($bccString);
        $email->setSubject($parsedSubject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        // Set correct timezone for email header
        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Mail senden fehlgeschlagen: ' . print_r($email->printDebugger(['headers']), true));
            return false;
        }

        // Mark confirmation as sent
        $db = \Config\Database::connect();
        $builder = $db->table('offers');
        $builder->where('id', $offer['id'])->update([
            'confirmation_sent_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', "Bestätigungsmail mit Template ID {$template['id']} versendet für Angebot ID {$offer['id']} (UUID: {$offer['uuid']})");

        // Benachrichtige passende Firmen über die neue Offerte
        $notifier = new \App\Libraries\OfferNotificationSender();
        $sentCount = $notifier->notifyMatchingUsers($offer);
        log_message('info', "Firmen-Benachrichtigung versendet: {$sentCount} Firma(n) benachrichtigt für Angebot ID {$offer['id']}");

        return true;
    }
}

if (!function_exists('sendGroupedOfferNotificationWithTemplate')) {
    /**
     * Send grouped offer notification email using database template
     *
     * @param array $offers Array of offers to group
     * @param string $userEmail Recipient email
     * @param string $platform Platform identifier
     * @return bool Success status
     */
    function sendGroupedOfferNotificationWithTemplate(array $offers, string $userEmail, string $platform): bool
    {
        if (empty($offers)) {
            return false;
        }

        helper('text');

        // Get language from first offer
        $firstOffer = $offers[0];
        $formFields = json_decode($firstOffer['form_fields'], true) ?? [];
        $language = $formFields['lang'] ?? $firstOffer['language'] ?? 'de';

        // Set locale
        $languageService = service('language');
        $languageService->setLocale($language);

        $request = service('request');
        if (!($request instanceof \CodeIgniter\HTTP\CLIRequest)) {
            $request->setLocale($language);
        }

        // Load platform-specific config
        $platformSiteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

        // For grouped offers, we'll use a special "grouped" template or the default one
        $templateModel = new EmailTemplateModel();
        $template = $templateModel->getTemplateForOffer('grouped', $language);

        if (!$template) {
            // Fallback to default template
            $template = $templateModel->getTemplateForOffer('default', $language);
        }

        if (!$template) {
            log_message('error', "Kein E-Mail Template für gruppierte Offers gefunden (Language: {$language})");
            return false;
        }

        $excludedFields = [
            'terms_n_condition', 'terms_and_conditions', 'terms',
            'type', 'lang', 'language', 'csrf_test_name', 'submit', 'form_token',
            '__submission', '__fluent_form_embded_post_id', '_wp_http_referer',
            'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer',
            'vorname', 'nachname', 'names', 'email', 'phone',
            'skip_kontakt', 'skip_reinigung_umzug',
        ];

        // Parse template with grouped data
        $parser = new EmailTemplateParser($platform);

        // For grouped emails, we use the common data from first offer
        $parsedSubject = $parser->parse($template['subject'], $formFields, $excludedFields);

        // For body, we need to handle multiple offers
        // This is more complex - for now we'll use the existing grouped_offer_notification view
        // TODO: Enhance parser to support grouped offer loops

        $offersData = [];
        foreach ($offers as $offer) {
            $offerFields = json_decode($offer['form_fields'], true) ?? [];
            $type = $offer['type'] ?? 'other';

            $filteredFields = array_filter($offerFields, function ($key) use ($excludedFields) {
                $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
                return !in_array($normalizedKey, $excludedFields);
            }, ARRAY_FILTER_USE_KEY);

            $offersData[] = [
                'uuid' => $offer['uuid'],
                'type' => $type,
                'verifyType' => $offer['verify_type'] ?? null,
                'filteredFields' => $filteredFields,
                'data' => $offerFields,
            ];
        }

        // Use the grouped offer notification view for now
        // In the future, this could be enhanced to use template system
        $message = view('emails/grouped_offer_notification', [
            'offers' => $offersData,
            'isMultiple' => count($offersData) > 1,
            'data' => $formFields,
        ]);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => count($offersData) > 1 ? lang('Email.offer_added_requests_title') : lang('Email.offer_added_request_title'),
            'content' => $message,
            'siteConfig' => $platformSiteConfig,
        ])->render('emails/layout');

        // Prepare BCC
        $adminEmails = [$platformSiteConfig->email];
        $bccString = implode(',', $adminEmails);

        // Send email
        $email = \Config\Services::email();
        $email->setFrom($platformSiteConfig->email, getEmailFromName($platformSiteConfig));
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
            return false;
        }

        log_message('info', "Gruppierte E-Mail gesendet an $userEmail für " . count($offersData) . " Offerten");

        // Mark all offers as confirmed
        $db = \Config\Database::connect();
        $builder = $db->table('offers');
        $offerIds = array_column($offers, 'id');
        $builder->whereIn('id', $offerIds)->update([
            'confirmation_sent_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', 'confirmation_sent_at gesetzt für Offerten IDs: ' . implode(', ', $offerIds));

        // Benachrichtige passende Firmen über alle Offerten dieser Gruppe
        $notifier = new \App\Libraries\OfferNotificationSender();
        $totalSent = 0;
        foreach ($offers as $offer) {
            $sentCount = $notifier->notifyMatchingUsers($offer);
            $totalSent += $sentCount;
            log_message('info', "Firmen-Benachrichtigung für gruppierte Offerte ID {$offer['id']}: {$sentCount} Firma(n) benachrichtigt");
        }
        log_message('info', "Gesamt Firmen-Benachrichtigungen für gruppierte Offerten: {$totalSent} Firma(n)");

        return true;
    }
}
