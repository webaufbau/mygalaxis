<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmailsWithRealData extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'mail:test-with-real-data';
    protected $description = 'Sendet Test-Emails mit echten Daten aus der Datenbank.';

    public function run(array $params)
    {
        if (empty($params)) {
            CLI::error('Bitte Email-Typ angeben: verification ODER review');
            CLI::write('Beispiel: php spark mail:test-with-real-data verification logs@webaufbau.com');
            CLI::write('Beispiel: php spark mail:test-with-real-data review logs@webaufbau.com');
            return;
        }

        $type = $params[0];
        $testEmail = $params[1] ?? 'logs@webaufbau.com';

        if ($type === 'verification') {
            $this->testVerificationEmail($testEmail);
        } elseif ($type === 'review') {
            $this->testReviewEmail($testEmail);
        } else {
            CLI::error('Unbekannter Typ. Verwende "verification" oder "review"');
        }
    }

    protected function testVerificationEmail(string $testEmail)
    {
        $offerModel = new \App\Models\OfferModel();

        // Finde eine unverifizierte Offer
        $offer = $offerModel
            ->where('verified', 0)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$offer) {
            CLI::error('Keine unverifizierte Offer gefunden.');
            return;
        }

        // Fallback Token erzeugen, falls nicht vorhanden
        if (empty($offer['verification_token'])) {
            $newToken = bin2hex(random_bytes(32));
            $offerModel->update($offer['id'], ['verification_token' => $newToken]);
            $offer['verification_token'] = $newToken;
        }

        $formFields = json_decode($offer['form_fields'], true);
        $vorname = $formFields['firstname'] ?? $offer['firstname'] ?? 'Test-Nutzer';

        // Lade SiteConfig
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform']);

        // Sprache setzen
        $language = $offer['language'] ?? 'de';
        service('language')->setLocale($language);

        $verifyLink = rtrim($siteConfig->backendUrl, '/') . '/verification/verify-offer/' . $offer['id'] . '/' . $offer['verification_token'];

        $emailData = [
            'data' => ['vorname' => $vorname],
            'verifyLink' => $verifyLink,
            'siteConfig' => $siteConfig,
        ];

        $htmlMessage = view('emails/verification_reminder', $emailData);
        $subject = lang('Email.verifyPhoneTitle');

        CLI::write('================', 'yellow');
        CLI::write('📧 VERIFIZIERUNGS-EMAIL (Offer #' . $offer['id'] . ')', 'green');
        CLI::write('================', 'yellow');
        CLI::write('An: ' . $testEmail, 'white');
        CLI::write('Betreff: ' . $subject, 'white');
        CLI::write('Link: ' . $verifyLink, 'cyan');
        CLI::write('', 'white');
        CLI::write($htmlMessage, 'white');
        CLI::write('================', 'yellow');

        $this->sendEmail($testEmail, $subject, $htmlMessage, $siteConfig);
    }

    protected function testReviewEmail(string $testEmail)
    {
        $offerModel = new \App\Models\OfferModel();
        $bookingModel = new \App\Models\BookingModel();

        // Finde eine gekaufte Offer mit access_hash
        $booking = $bookingModel
            ->where('type', 'offer_purchase')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$booking) {
            CLI::error('Keine Buchung gefunden.');
            return;
        }

        $offer = $offerModel->find($booking['reference_id']);

        if (!$offer) {
            CLI::error('Offer nicht gefunden.');
            return;
        }

        // Access Hash generieren falls nicht vorhanden
        if (empty($offer['access_hash'])) {
            $accessHash = bin2hex(random_bytes(16));
            $offerModel->update($offer['id'], ['access_hash' => $accessHash]);
            $offer['access_hash'] = $accessHash;
        }

        $creatorFirstname = $offer['firstname'] ?? 'Test-Kunde';

        // Lade SiteConfig
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform']);

        // Sprache setzen
        $language = $offer['language'] ?? 'de';
        service('language')->setLocale($language);

        $reviewLink = rtrim($siteConfig->backendUrl, '/') . '/offer/interested/' . $offer['access_hash'];

        $emailData = [
            'creatorFirstname' => $creatorFirstname,
            'offerTitle' => $offer['title'],
            'reviewLink' => $reviewLink,
            'siteConfig' => $siteConfig,
        ];

        $htmlMessage = view('emails/review_reminder', $emailData);
        $subject = lang('Reviews.reminderTitle');

        CLI::write('================', 'yellow');
        CLI::write('📧 REVIEW-REMINDER-EMAIL (Offer #' . $offer['id'] . ')', 'green');
        CLI::write('================', 'yellow');
        CLI::write('An: ' . $testEmail, 'white');
        CLI::write('Betreff: ' . $subject, 'white');
        CLI::write('Link: ' . $reviewLink, 'cyan');
        CLI::write('', 'white');
        CLI::write($htmlMessage, 'white');
        CLI::write('================', 'yellow');

        $this->sendEmail($testEmail, $subject, $htmlMessage, $siteConfig);
    }

    protected function sendEmail(string $to, string $subject, string $message, $siteConfig): bool
    {
        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'      => $subject,
            'content'    => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        $emailService = \Config\Services::email();
        $emailService->setTo($to);
        $emailService->setFrom($siteConfig->email, $siteConfig->name);
        $emailService->setSubject($subject);
        $emailService->setMessage($fullEmail);
        $emailService->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $emailService->setHeader('Date', date('r'));

        if ($emailService->send()) {
            CLI::write('✅ Email erfolgreich gesendet!', 'green');
            return true;
        } else {
            CLI::error('❌ Fehler beim Senden der Email.');
            CLI::write(print_r($emailService->printDebugger(['headers', 'subject', 'body']), true));
            return false;
        }
    }
}
