<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SendMissingConfirmations extends BaseCommand
{
    protected $group = 'Offers';
    protected $name = 'offers:send-missing-confirmations';
    protected $description = 'Sendet fehlende Bestätigungsmails für verifizierte Angebote';
    protected $usage = 'offers:send-missing-confirmations';

    protected $siteConfig;

    public function run(array $params)
    {
        $this->siteConfig = config('SiteConfig');

        CLI::write('Suche nach verifizierten Angeboten ohne Bestätigungsmail...', 'yellow');

        $offerModel = new \App\Models\OfferModel();

        // Finde alle Angebote wo verified=1 UND confirmation_sent_at IS NULL
        $offersWithoutConfirmation = $offerModel
            ->where('verified', 1)
            ->where('confirmation_sent_at IS NULL')
            ->findAll();

        if (empty($offersWithoutConfirmation)) {
            CLI::write('Keine Angebote gefunden, die eine Bestätigungsmail benötigen.', 'green');
            return;
        }

        CLI::write('Gefunden: ' . count($offersWithoutConfirmation) . ' Angebote ohne Bestätigungsmail', 'yellow');

        // Gruppiere nach Email, dann nach platform, dann nach group_id
        $groupedByEmail = [];
        foreach ($offersWithoutConfirmation as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $email = $formFields['email'] ?? null;

            if (!$email) {
                CLI::write('Überspringe Angebot ID ' . $offer['id'] . ' - keine Email-Adresse', 'red');
                continue;
            }

            // Überspringe Angebote ohne platform-Eintrag
            $platform = $offer['platform'] ?? null;
            if (empty($platform)) {
                CLI::write('Überspringe Angebot ID ' . $offer['id'] . ' - kein Platform-Eintrag', 'yellow');
                continue;
            }

            if (!isset($groupedByEmail[$email])) {
                $groupedByEmail[$email] = [];
            }

            // Gruppiere zusätzlich nach platform
            if (!isset($groupedByEmail[$email][$platform])) {
                $groupedByEmail[$email][$platform] = [];
            }

            $groupKey = $offer['group_id'] ?? 'individual_' . $offer['id'];
            if (!isset($groupedByEmail[$email][$platform][$groupKey])) {
                $groupedByEmail[$email][$platform][$groupKey] = [];
            }

            $groupedByEmail[$email][$platform][$groupKey][] = $offer;
        }

        // Verarbeite jede Email-Gruppe
        $totalSent = 0;
        foreach ($groupedByEmail as $email => $platforms) {
            CLI::write("Verarbeite Angebote für $email...", 'cyan');

            foreach ($platforms as $platform => $groups) {
                CLI::write("  Platform: $platform", 'cyan');

                foreach ($groups as $groupKey => $offers) {
                    if (count($offers) === 1) {
                        // Einzelnes Angebot
                        $offer = $offers[0];
                        $this->sendSingleConfirmation($offer);
                        $totalSent++;
                    } else {
                        // Mehrere Angebote in Gruppe - mit platform-spezifischer Config
                        $this->sendGroupedConfirmation($offers, $platform);
                        $totalSent += count($offers);
                    }
                }
            }
        }

        CLI::write("Fertig! $totalSent Bestätigungsmails versendet.", 'green');
    }

    private function sendSingleConfirmation(array $offer): void
    {
        $formFields = json_decode($offer['form_fields'], true);

        // Sprache aus DB-Spalte 'language' verwenden (nicht aus form_fields)
        $language = $offer['language'] ?? $formFields['lang'] ?? 'de';

        // Sprache explizit setzen für CLI-Kontext
        $this->setLanguage($language);

        helper(['text', 'email_template']);

        $userEmail = $formFields['email'] ?? null;
        if (!$userEmail) {
            CLI::write('Fehler: Keine Email für Angebot ID ' . $offer['id'], 'red');
            return;
        }

        // Try to send with template first
        $templateSent = sendOfferNotificationWithTemplate($offer, $formFields, $offer['type'] ?? 'unknown');

        if ($templateSent) {
            CLI::write('✓ Mail mit Template gesendet für Angebot ID ' . $offer['id'] . ' (' . $offer['type'] . ')', 'green');
            return;
        }

        // FALLBACK: Use old method if template not found
        CLI::write('  → Kein Template gefunden, verwende Fallback für Angebot ID ' . $offer['id'], 'yellow');

        // Lade platform-spezifische Config
        $platform = $offer['platform'] ?? null;
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

        // Technische Felder filtern
        $excludeKeys = ['__submission', '__fluent_form_embded_post_id', '_wp_http_referer', 'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method'];
        $filteredFields = array_filter($formFields, function ($key) use ($excludeKeys) {
            if (in_array($key, $excludeKeys)) return false;
            if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) return false;
            return true;
        }, ARRAY_FILTER_USE_KEY);

        // Tracking-Felder entfernen
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer'];
        $filteredFields = array_filter($filteredFields, function ($key) use ($utmKeys) {
            return !in_array($key, $utmKeys);
        }, ARRAY_FILTER_USE_KEY);

        $emailData = [
            'formName' => $offer['type'] ?? 'unknown',
            'formular_page' => null,
            'uuid' => $offer['uuid'],
            'verifyType' => $offer['verify_type'] ?? null,
            'filteredFields' => $filteredFields,
            'data' => $formFields,
            'siteConfig' => $siteConfig,
        ];

        // Sprache nochmals setzen vor View-Rendering
        $this->setLanguage($language);
        $message = view('emails/offer_notification', $emailData);

        // Sprache nochmals setzen vor Layout-Rendering
        $this->setLanguage($language);
        $view = Services::renderer();
        $fullEmail = $view->setData([
            'title' => lang('Email.offer_added_request_title'),
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        helper('email_template');
        $email = Services::email();
        $email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
        $email->setTo($userEmail);
        $email->setBCC($siteConfig->email);

        // Sprache nochmals setzen vor Subject-Generierung
        $this->setLanguage($language);
        $email->setSubject(lang('Email.offer_added_email_subject'));
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if ($email->send()) {
            // Setze confirmation_sent_at
            $db = \Config\Database::connect();
            $db->table('offers')->where('id', $offer['id'])->update([
                'confirmation_sent_at' => date('Y-m-d H:i:s')
            ]);

            CLI::write('✓ Mail (Fallback) gesendet für Angebot ID ' . $offer['id'], 'green');
        } else {
            CLI::write('✗ Fehler beim Senden für Angebot ID ' . $offer['id'], 'red');
        }
    }

    private function sendGroupedConfirmation(array $offers, string $platform): void
    {
        if (empty($offers)) return;

        helper('text');

        $firstOffer = $offers[0];
        $formFields = json_decode($firstOffer['form_fields'], true);

        // Sprache aus DB-Spalte 'language' verwenden (nicht aus form_fields)
        $language = $firstOffer['language'] ?? $formFields['lang'] ?? 'de';

        // Sprache explizit setzen für CLI-Kontext
        $this->setLanguage($language);

        $userEmail = $formFields['email'] ?? null;
        if (!$userEmail) {
            CLI::write('Fehler: Keine Email für gruppierte Angebote', 'red');
            return;
        }

        // Lade platform-spezifische Config
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

        // Bereite Offerten-Daten auf
        $offersData = [];
        foreach ($offers as $offer) {
            $offerFields = json_decode($offer['form_fields'], true);
            $type = $offer['type'] ?? 'unknown';

            $excludeKeys = ['__submission', '__fluent_form_embded_post_id', '_wp_http_referer', 'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method'];
            $filteredFields = array_filter($offerFields, function ($key) use ($excludeKeys) {
                if (in_array($key, $excludeKeys)) return false;
                if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) return false;
                return true;
            }, ARRAY_FILTER_USE_KEY);

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
            'data' => $formFields,
            'siteConfig' => $siteConfig,
        ];

        // Sprache nochmals setzen vor View-Rendering
        $this->setLanguage($language);
        $message = view('emails/grouped_offer_notification', $emailData);

        // Sprache nochmals setzen vor Layout-Rendering
        $this->setLanguage($language);
        $view = Services::renderer();
        $fullEmail = $view->setData([
            'title' => count($offersData) > 1 ? lang('Email.offer_added_requests_title') : lang('Email.offer_added_request_title'),
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        helper('email_template');
        $email = Services::email();
        $email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
        $email->setTo($userEmail);
        $email->setBCC($siteConfig->email);

        // Sprache nochmals setzen vor Subject-Generierung
        $this->setLanguage($language);
        $email->setSubject(
            count($offersData) > 1
                ? lang('Email.offer_added_multiple_subject')
                : lang('Email.offer_added_email_subject')
        );
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if ($email->send()) {
            // Setze confirmation_sent_at für alle Offerten
            $db = \Config\Database::connect();
            $offerIds = array_column($offers, 'id');
            $db->table('offers')->whereIn('id', $offerIds)->update([
                'confirmation_sent_at' => date('Y-m-d H:i:s')
            ]);

            CLI::write('✓ Gruppierte Mail gesendet für Angebote IDs: ' . implode(', ', $offerIds), 'green');
        } else {
            CLI::write('✗ Fehler beim Senden der gruppierten Mail', 'red');
        }
    }

    /**
     * Setzt die Sprache für alle nachfolgenden Aufrufe
     */
    private function setLanguage(string $language): void
    {
        // Language Service Locale setzen
        service('language')->setLocale($language);
    }
}
