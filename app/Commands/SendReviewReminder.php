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
    protected $description = 'Sendet 30 Tage nach Kauf einen Review-Link an den Anbieter (Ersteller des Angebots).';

    protected $bookingModel;
    protected $offerModel;
    protected $userModel;
    protected $reviewModel;

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        $this->bookingModel = new BookingModel();
        $this->offerModel = new OfferModel();
        $this->userModel = new UserModel();
        $this->reviewModel = new ReviewModel();
    }

    public function run(array $params)
    {
        $xDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

        // Buchungen vor mindestens 30 Tagen, wo noch kein Reminder gesendet wurde
        $bookings = $this->bookingModel
            ->where('created_at <=', $xDaysAgo)
            ->where('review_reminder_sent_at', null)
            ->findAll(100);

        if (!$bookings) {
            CLI::write(lang('Reviews.noBookingsFound'), 'yellow');
            return;
        }

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

            // Review-Link generieren: öffnet Seite für Anbieter (mit offer hash)
            $reviewLink = site_url('offer/interested/' . $offer['access_hash']);

            // Maildaten
            $emailData = [
                'offerTitle' => $offer['title'],
                'creatorFirstname' => $offer['firstname'] ?? '',
                'creatorLastname' => $offer['lastname'] ?? '',
                'reviewLink' => $reviewLink,
                'bookingDate' => $booking['created_at'],
            ];

            $subject = lang('Reviews.emailSubject', [$offer['title']]);
            $message = view('emails/review_reminder', $emailData);

            if ($this->sendEmail($creatorEmail, $subject, $message)) {
                CLI::write(lang('Reviews.reminderSent', [$creatorEmail, $booking['id']]), 'green');

                $this->bookingModel->update($booking['id'], ['review_reminder_sent_at' => date('Y-m-d H:i:s')]);
            } else {
                CLI::write(lang('Reviews.emailSendFailed', [$creatorEmail, $booking['id']]), 'red');
            }
        }
    }

    protected function sendEmail(string $to, string $subject, string $message): bool
    {
        $siteConfig = siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => lang('Reviews.emailTitle'),
            'content' => $message,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden der Review-Erinnerung an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }
}
