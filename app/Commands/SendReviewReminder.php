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

// ReviewModel importieren

class SendReviewReminder extends BaseCommand
{
    protected $group       = 'Reviews';
    protected $name        = 'reviews:send-reminder';
    protected $description = 'Sendet 5 Tage nach Kauf einen Review-Link an den Anbieter (Ersteller des Angebots).';

    // Models als Klassenvariablen definieren
    protected $bookingModel;
    protected $offerModel;
    protected $userModel;
    protected $reviewModel;

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        $this->logger   = $logger;
        $this->commands = $commands;

        // Models initialisieren
        $this->bookingModel = new BookingModel();
        $this->offerModel = new OfferModel();
        $this->userModel = new UserModel();
        $this->reviewModel = new ReviewModel();
    }

    public function run(array $params)
    {
        $fiveDaysAgo = date('Y-m-d H:i:s', strtotime('-5 days'));

        $bookings = $this->bookingModel
            ->where('created_at <=', $fiveDaysAgo)
            ->where('review_reminder_sent_at IS NULL')
            ->findAll(100);

        if (!$bookings) {
            CLI::write('Keine Buchungen gefunden, die eine Review-Erinnerung benötigen.', 'yellow');
            return;
        }

        foreach ($bookings as $booking) {
            $offer = $this->offerModel->find($booking['offer_id']);
            if (!$offer) {
                CLI::write("Offer ID {$booking['offer_id']} nicht gefunden.", 'red');
                continue;
            }

            $creator = $this->userModel->find($offer['created_by']);
            if (!$creator) {
                CLI::write("Anbieter (User ID {$offer['created_by']}) nicht gefunden.", 'red');
                continue;
            }

            // Review-Link generieren
            $reviewLink = $this->createReviewLink($booking['offer_id'], $booking['recipient_id']);

            // E-Mail Daten vorbereiten
            $emailData = [
                'offerTitle' => $offer['title'],
                'companyName' => ($offer['firstname'] ?? '') . ' ' . ($offer['lastname'] ?? ''),
                'reviewLink' => $reviewLink,
                'bookingDate' => $booking['created_at'],
            ];

            $subject = "Bitte um Bewertung für dein Angebot: {$offer['title']}";

            $message = view('emails/review_reminder', $emailData);

            if ($this->sendEmail($creator['email'], $subject, $message)) {
                CLI::write("Review-Erinnerung an {$creator['email']} gesendet (Booking-ID {$booking['id']}).", 'green');

                $this->bookingModel->update($booking['id'], ['review_reminder_sent_at' => date('Y-m-d H:i:s')]);
            } else {
                CLI::write("Fehler beim Senden der E-Mail an {$creator['email']} (Booking-ID {$booking['id']}).", 'red');
            }
        }
    }

    protected function sendEmail(string $to, string $subject, string $message): bool
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom('info@offertenschweiz.ch', 'Offertenschweiz');
        $email->setSubject($subject);
        $email->setMessage($message);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden der Review-Erinnerung an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

    public function createReviewLink(int $offerId, int $recipientId): string
    {
        $offer = $this->offerModel->find($offerId);
        if (!$offer) {
            throw new \Exception("Angebot nicht gefunden.");
        }

        $hash = bin2hex(random_bytes(16));

        $this->reviewModel->insert([
            'offer_id' => $offer->id,
            'recipient_id' => $recipientId,
            'hash' => $hash,
            'reviewer_firstname' => $offer->firstname,
            'reviewer_lastname' => $offer->lastname,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return site_url('bewerten/' . $hash);
    }
}
