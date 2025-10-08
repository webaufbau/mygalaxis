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

        foreach ($offers as $offer) {

            // Sprache aus Offer-Daten setzen
            $language = $user->language ?? $offer['language'] ?? 'de'; // Fallback: Deutsch
            $request = service('request');
            if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
                service('language')->setLocale($language);
            } else {
                $request->setLocale($language);
            }

            $formFields = json_decode($offer['form_fields'], true);
            $email = $formFields['email'] ?? $offer['email'] ?? null;
            $vorname = $formFields['firstname'] ?? $offer['firstname'] ?? 'Nutzer';

            if (!$email) {
                CLI::write("Überspringe Offer-ID {$offer['id']} – E-Mail fehlt.", 'red');
                continue;
            }

            // Fallback Token erzeugen, falls nicht vorhanden
            if (empty($offer['verification_token'])) {
                $newToken = bin2hex(random_bytes(32)); // 64 Zeichen Token

                // Token in DB speichern
                $offerModel->update($offer['id'], ['verification_token' => $newToken]);

                // Token auch im aktuellen $offer setzen, damit Link korrekt ist
                $offer['verification_token'] = $newToken;
            }

            // Lade SiteConfig basierend auf Offer-Platform
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform']);

            $verifyLink = rtrim($siteConfig->backendUrl, '/') . '/verification/verify-offer/' . $offer['id'] . '/' . $offer['verification_token'];

            $emailData = [
                'data' => $formFields,
                'verifyLink' => $verifyLink,
                'siteConfig' => $siteConfig,
            ];

            $htmlMessage = view('emails/verification_reminder', $emailData);
            $subject = 'Bitte bestätige deine Telefonnummer für deine Anfrage';

            if ($this->sendEmail($email, $subject, $htmlMessage, $siteConfig)) {
                CLI::write("Erinnerung an {$email} gesendet (Offer-ID {$offer['id']}).", 'green');
                $offerModel->update($offer['id'], ['reminder_sent_at' => date('Y-m-d H:i:s')]);
            } else {
                CLI::write("Erinnerung an {$email} konnte nicht gesendet werden (Offer-ID {$offer['id']}).", 'red');
            }
        }

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
