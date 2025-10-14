<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use CodeIgniter\Email\Email;

class RemindVerification extends BaseCommand
{
    protected $group       = 'Offers';
    protected $name        = 'offers:remind-verification';
    protected $description = 'Sende Erinnerung an Nutzer, die Verifizierung nicht abgeschlossen haben.';

    /**
     * @throws \ReflectionException
     */
    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $twoMinutesAgo = date('Y-m-d H:i:s', strtotime('-2 minutes'));
        $offers = $offerModel
            ->where('verified', 0)
            ->where('reminder_sent_at IS NULL')
            ->where('created_at <=', $twoMinutesAgo)
            ->findAll(100);

        if (!$offers) {
            CLI::write('Keine offenen Verifizierungen gefunden.', 'yellow');
            return;
        }

        // Gruppiere Offerten nach E-Mail und group_id
        $groupedOffers = $this->groupOffersByEmail($offers);

        foreach ($groupedOffers as $emailKey => $offerGroup) {
            $firstOffer = $offerGroup[0];
            $formFields = json_decode($firstOffer['form_fields'], true);
            $email = $formFields['email'] ?? $firstOffer['email'] ?? null;

            if (!$email) {
                CLI::write("Überspringe Gruppe – E-Mail fehlt.", 'red');
                continue;
            }

            // Sprache aus Offer-Daten setzen
            $language = $firstOffer['language'] ?? 'de'; // Fallback: Deutsch
            $request = service('request');
            if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
                service('language')->setLocale($language);
            } else {
                $request->setLocale($language);
            }

            $languageService = service('language');
            $languageService->setLocale($language);

            // Lade SiteConfig basierend auf Offer-Platform
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($firstOffer['platform']);

            // Erstelle Token und Links für alle Offerten in der Gruppe
            $offersList = [];
            foreach ($offerGroup as $offer) {
                // Fallback Token erzeugen, falls nicht vorhanden
                if (empty($offer['verification_token'])) {
                    $newToken = bin2hex(random_bytes(32)); // 64 Zeichen Token
                    $offerModel->update($offer['id'], ['verification_token' => $newToken]);
                    $offer['verification_token'] = $newToken;
                }

                $verifyLink = rtrim($siteConfig->backendUrl, '/') . '/verification/verify-offer/' . $offer['id'] . '/' . $offer['verification_token'];
                $offerFields = json_decode($offer['form_fields'], true);

                $offersList[] = [
                    'id' => $offer['id'],
                    'type' => $offer['type'] ?? 'unknown',
                    'verifyLink' => $verifyLink,
                    'formFields' => $offerFields,
                ];
            }

            $emailData = [
                'data' => $formFields,
                'offers' => $offersList,
                'siteConfig' => $siteConfig,
                'isMultiple' => count($offersList) > 1,
            ];

            $htmlMessage = view('emails/verification_reminder', $emailData);
            $subject = count($offersList) > 1
                ? 'Bitte bestätigen Sie Ihre Telefonnummer für Ihre Anfragen'
                : 'Bitte bestätigen Sie Ihre Telefonnummer für Ihre Anfrage';

            if ($this->sendEmail($email, $subject, $htmlMessage, $siteConfig)) {
                $offerIds = array_column($offerGroup, 'id');
                CLI::write("Erinnerung an {$email} gesendet für " . count($offersList) . " Offerte(n) (IDs: " . implode(', ', $offerIds) . ").", 'green');

                // Markiere ALLE Offerten in der Gruppe als "reminder gesendet"
                foreach ($offerGroup as $offer) {
                    $offerModel->update($offer['id'], ['reminder_sent_at' => date('Y-m-d H:i:s')]);
                }
            } else {
                CLI::write("Erinnerung an {$email} konnte nicht gesendet werden.", 'red');
            }
        }
    }

    /**
     * Gruppiert Offerten nach E-Mail (und optional group_id)
     * @param array $offers
     * @return array Gruppierte Offerten
     */
    private function groupOffersByEmail(array $offers): array
    {
        $grouped = [];

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $email = $formFields['email'] ?? $offer['email'] ?? null;

            if (!$email) {
                continue;
            }

            // Gruppierungsschlüssel: E-Mail + group_id (falls vorhanden)
            // Wenn group_id leer ist, dann jede Offerte separat
            $groupKey = $email;
            if (!empty($offer['group_id'])) {
                $groupKey .= '_' . $offer['group_id'];
            } else {
                // Ohne group_id: jede Offerte bekommt eigenen Schlüssel
                $groupKey .= '_individual_' . $offer['id'];
            }

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }

            $grouped[$groupKey][] = $offer;
        }

        return $grouped;
    }

    protected function sendEmail(string $to, string $subject, string $message, $siteConfig = null): bool
    {
        if (!$siteConfig) {
            $siteConfig = siteconfig();
        }

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($message);
        $email->setMailType('html');

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden der Erinnerungs-E-Mail an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }
}
