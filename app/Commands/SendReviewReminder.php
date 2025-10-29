<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BookingModel;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Models\ReviewModel;
use CodeIgniter\CLI\Commands;
use Psr\Log\LoggerInterface;

class SendReviewReminder extends BaseCommand
{
    protected $group       = 'Reviews';
    protected $name        = 'reviews:send-reminder';
    protected $description = 'Sendet Review-Links an den Anbieter basierend auf kategoriespezifischen Einstellungen.';

    protected $bookingModel;
    protected $offerModel;
    protected $userModel;
    protected $reviewModel;
    protected $categoryManager;

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        $this->bookingModel = new BookingModel();
        $this->offerModel = new OfferModel();
        $this->userModel = new UserModel();
        $this->reviewModel = new ReviewModel();
        $this->categoryManager = new \App\Libraries\CategoryManager();
    }

    public function run(array $params)
    {
        // Kategorie-Einstellungen laden
        $categories = $this->categoryManager->getAll();

        CLI::write('Starte Review-Reminder Versand basierend auf Kategorie-Einstellungen...', 'yellow');

        $sentFirstCount = 0;
        $sentReminderCount = 0;

        // Für jede Kategorie die spezifischen Zeiträume verwenden
        foreach ($categories['categories'] as $categoryKey => $categorySettings) {
            $reviewEmailDays = $categorySettings['review_email_days'] ?? 5;
            $reviewReminderDays = $categorySettings['review_reminder_days'] ?? 10;

            CLI::write("Verarbeite Kategorie: {$categoryKey} (Erste Email: {$reviewEmailDays}d, Erinnerung: {$reviewReminderDays}d)", 'blue');

            // === ERSTE BEWERTUNGS-EMAIL ===
            // Finde Buchungen die vor X Tagen erstellt wurden für diese Kategorie
            $targetDate = date('Y-m-d H:i:s', strtotime("-{$reviewEmailDays} days"));

            $bookings = $this->bookingModel
                ->select('bookings.*')
                ->join('offers', 'offers.id = bookings.reference_id')
                ->where('offers.type', $categoryKey)
                ->where('bookings.created_at <=', $targetDate)
                ->where('bookings.review_reminder_sent_at', null)
                ->findAll(100);

            foreach ($bookings as $booking) {
            $offer = $this->offerModel->find($booking['reference_id']);
            if (!$offer) {
                CLI::write(lang('Reviews.offerNotFound', [$booking['reference_id']]), 'red');
                continue;
            }

            // Prüfen, ob bereits eine Bewertung für diese Offer existiert egal an welche Firma
            $existingReview = $this->reviewModel
                ->where('offer_id', $offer['id'])
                ->first();

            if ($existingReview) {
                CLI::write(lang('Reviews.alreadyReviewed', [$booking['id']]), 'yellow');
                // Reminder nicht senden, da Bewertung schon existiert
                continue;
            }

            // Creator der Offer (kein User)
            $creatorEmail = $offer['email'] ?? null;
            if (!$creatorEmail) {
                CLI::write(lang('Reviews.creatorEmailMissing', [$offer['id']]), 'red');
                continue;
            }

            // Lade SiteConfig basierend auf Offer-Platform
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform']);

            // Stelle sicher dass access_hash existiert (generiert automatisch falls nicht vorhanden)
            $accessHash = $this->offerModel->ensureAccessHash($offer['id']);

            // Review-Link generieren: öffnet Seite für Anbieter (mit offer hash)
            $reviewLink = rtrim($siteConfig->backendUrl, '/') . '/offer/interested/' . $accessHash;

            // Extrahiere Domain aus Platform
            $domain = '';
            if (!empty($offer['platform'])) {
                $domain = str_replace('my_', '', $offer['platform']);
                $domain = str_replace('_', '.', $domain);
            } else {
                // Fallback: extrahiere aus frontendUrl
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
            $type = $this->getOfferTypeForSubject($offer['type']);

            // Maildaten
            $emailData = [
                'offerTitle' => $offer['title'],
                'creatorFirstname' => $offer['firstname'] ?? '',
                'creatorLastname' => $offer['lastname'] ?? '',
                'reviewLink' => $reviewLink,
                'bookingDate' => $booking['created_at'],
                'siteConfig' => $siteConfig,
            ];

            // Neuer Betreff: "Domain.ch - Bitte bewerten Sie die Anfrage - Reinigung ID 353 3000 Bern"
            $subject = "{$domain} - Bitte bewerten Sie die Anfrage - {$type} ID {$offer['id']} {$offer['zip']} {$offer['city']}";
            $message = view('emails/review_reminder', $emailData);

                if ($this->sendEmail($creatorEmail, $subject, $message, $siteConfig)) {
                    CLI::write(lang('Reviews.reminderSent', [$creatorEmail, $booking['id']]), 'green');

                    $this->bookingModel->update($booking['id'], ['review_reminder_sent_at' => date('Y-m-d H:i:s')]);
                    $sentFirstCount++;
                } else {
                    CLI::write(lang('Reviews.emailSendFailed', [$creatorEmail, $booking['id']]), 'red');
                }
            }

            // === ERINNERUNGS-EMAIL (ZWEITE EMAIL) ===
            // Finde Buchungen wo erste Email bereits versendet wurde vor (reminderDays - emailDays) Tagen
            if ($reviewReminderDays > $reviewEmailDays) {
                $daysSinceFirst = $reviewReminderDays - $reviewEmailDays;
                $targetDateReminder = date('Y-m-d H:i:s', strtotime("-{$daysSinceFirst} days"));

                $bookingsForReminder = $this->bookingModel
                    ->select('bookings.*')
                    ->join('offers', 'offers.id = bookings.reference_id')
                    ->where('offers.type', $categoryKey)
                    ->where('bookings.review_reminder_sent_at IS NOT NULL')
                    ->where('bookings.review_reminder_sent_at <=', $targetDateReminder)
                    ->where('bookings.review_second_reminder_sent_at', null)
                    ->findAll(100);

                foreach ($bookingsForReminder as $booking) {
                    $offer = $this->offerModel->asArray()->find($booking['reference_id']);
                    if (!$offer) continue;

                    $existingReview = $this->reviewModel
                        ->where('offer_id', $offer['id'])
                        ->first();

                    if ($existingReview) continue;

                    $creatorEmail = $offer['email'] ?? null;
                    if (!$creatorEmail) continue;

                    $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform']);

                    // Stelle sicher dass access_hash existiert (generiert automatisch falls nicht vorhanden)
                    $accessHash = $this->offerModel->ensureAccessHash($offer['id']);

                    $reviewLink = rtrim($siteConfig->backendUrl, '/') . '/offer/interested/' . $accessHash;

                    // Extrahiere Domain aus Platform
                    $domain = '';
                    if (!empty($offer['platform'])) {
                        $domain = str_replace('my_', '', $offer['platform']);
                        $domain = str_replace('_', '.', $domain);
                    } else {
                        // Fallback: extrahiere aus frontendUrl
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
                    $type = $this->getOfferTypeForSubject($offer['type']);

                    $emailData = [
                        'offerTitle' => $offer['title'],
                        'creatorFirstname' => $offer['firstname'] ?? '',
                        'creatorLastname' => $offer['lastname'] ?? '',
                        'reviewLink' => $reviewLink,
                        'bookingDate' => $booking['created_at'],
                        'siteConfig' => $siteConfig,
                        'isReminder' => true,
                    ];

                    $subject = "{$domain} - Bitte bewerten Sie die Anfrage - {$type} ID {$offer['id']} {$offer['zip']} {$offer['city']}";
                    $message = view('emails/review_reminder', $emailData);

                    if ($this->sendEmail($creatorEmail, $subject, $message, $siteConfig)) {
                        CLI::write("Erinnerungs-Email gesendet an {$creatorEmail} für Booking #{$booking['id']}", 'green');
                        $db = \Config\Database::connect();
                        $db->table('bookings')
                            ->where('id', $booking['id'])
                            ->update(['review_second_reminder_sent_at' => date('Y-m-d H:i:s')]);
                        $sentReminderCount++;
                    }
                }
            }
        }

        CLI::write("\nFertig! {$sentFirstCount} erste Review-Emails und {$sentReminderCount} Erinnerungen versendet.", 'green');
    }

    protected function sendEmail(string $to, string $subject, string $message, $siteConfig = null): bool
    {
        $siteConfig = $siteConfig ?? siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => lang('Reviews.emailTitle'),
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden der Review-Erinnerung an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

    /**
     * Gibt den korrekten Typ-Namen für E-Mail-Betreffs zurück
     * Spezielle Formulierungen je nach Branche für Bewertungs-E-Mails
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
}
