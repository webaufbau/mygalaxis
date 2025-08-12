<?php

namespace App\Commands;

use App\Entities\User;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BookingModel;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Models\ReviewModel;
use CodeIgniter\CLI\Commands;
use Psr\Log\LoggerInterface;

class SendOfferPurchaseNotification extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'offers:send-purchase-notification';
    protected $description = 'Sendet Mails an Anbieter und Kunden nach dem Kauf einer Offerte.';

    protected $bookingModel;
    protected $offerModel;
    protected $userModel;
    protected $reviewModel;

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        $this->logger       = $logger;
        $this->commands     = $commands;
        $this->bookingModel = new BookingModel();
        $this->offerModel   = new OfferModel();
        $this->userModel    = new UserModel();
        $this->reviewModel  = new ReviewModel();
    }

    public function run(array $params)
    {
        $bookings = $this->bookingModel
            ->where('type', 'offer_purchase')
            ->where('offer_notification_sent_at IS NULL')
            ->findAll(100);

        if (!$bookings) {
            CLI::write('Keine neuen Offertenkäufe gefunden.', 'yellow');
            return;
        }

        foreach ($bookings as $booking) {
            $offer = $this->offerModel->find($booking['reference_id']);
            if (!$offer) {
                CLI::write("Offer ID {$booking['reference_id']} nicht gefunden.", 'red');
                continue;
            }

            // Firma ist ein User-Objekt
            $company = $this->userModel->find($booking['user_id']);

            if (!$company) {
                CLI::write("Firmen-Benutzer nicht gefunden für Booking-ID {$booking['id']}.", 'red');
                continue;
            }

            // Kunde ist kein User, Daten aus $offer-Array
            $customerData = [
                'firstname' => $offer['firstname'] ?? '',
                'lastname'  => $offer['lastname'] ?? '',
                'email'     => $offer['email'] ?? '',
                'phone'     => $offer['phone'] ?? '',
                // Weitere Kundendaten aus $offer hier hinzufügen falls benötigt
            ];

            // 1. Mail an Firma (Käufer)
            $this->sendEmailToCompany($company, $offer, $customerData);

            // 2. Mail an Kunde (Offerten-Daten als Array)
            $this->sendEmailToCustomer($customerData, $company, $offer);

            // Booking aktualisieren
            $this->bookingModel->update($booking['id'], [
                'offer_notification_sent_at' => date('Y-m-d H:i:s'),
            ]);

            CLI::write("Benachrichtigungen gesendet für Booking-ID {$booking['id']}.", 'green');
        }
    }

    protected function sendEmailToCompany(User $company, array $offer, array $customer): void
    {
        $siteConfig = siteconfig();

        // Sprache aus Offer-Daten setzen
        $language = $user->language ?? $offer['language'] ?? 'de'; // Fallback: Deutsch
        $request = service('request');
        if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
            service('language')->setLocale($language);
        } else {
            $request->setLocale($language);
        }

        $company_backend_offer_link = site_url('/offers/mine#detailsview-' . $offer['id']);

        $data = [
            'siteConfig'        => $siteConfig,
            'kunde'             => $customer,
            'firma'             => $company,
            'offer'             => $offer,
            'company_backend_offer_link' => $company_backend_offer_link,
        ];

        $subject = lang('Email.offerPurchasedCompanySubject', [$offer['title']]);
        $message = view('emails/offer_purchase_to_company', $data);

        $this->sendEmail($company->email, $subject, $message);
    }

    protected function sendEmailToCustomer(array $customer, User $company, array $offer): void
    {
        $siteConfig = siteconfig();

        // Sprache aus Offer-Daten setzen
        $language = $user->language ?? $offer['language'] ?? 'de'; // Fallback: Deutsch
        $request = service('request');
        if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
            service('language')->setLocale($language);
        } else {
            $request->setLocale($language);
        }

        $accessHash = bin2hex(random_bytes(16));
        $this->offerModel->update($offer['id'], ['access_hash' => $accessHash]);

        $interessentenLink = site_url('offer/interested/' . $accessHash);

        $data = [
            'siteConfig'        => $siteConfig,
            'kunde'             => $customer,
            'firma'             => $company,
            'offer'             => $offer,
            'interessentenLink' => $interessentenLink,
        ];

        // Betreff aus Sprachdateien holen
        $subject = lang('Email.offerPurchasedSubject', [$offer['title']]);
        $message = view('emails/offer_purchase_to_customer', $data);

        $originalEmail = $customer['email'];

        // Prüfen, ob Testmodus aktiv ist
        if ($siteConfig->testMode) {
            $emailTo = $siteConfig->testEmail;
            $subject = 'TEST EMAIL – NICHT AN ECHTEN BENUTZER! (eigentlich an: ' . $originalEmail . ') – ' . $subject;
        } else {
            $emailTo = $originalEmail;
        }

        $this->sendEmail($emailTo, $subject, $message);
    }

    protected function sendEmail(string $to, string $subject, string $message): bool
    {
        $siteConfig = siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => 'Ihre Anfrage',
            'content' => $message,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden der E-Mail an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }
}
