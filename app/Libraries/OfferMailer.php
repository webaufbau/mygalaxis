<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;
use Config\Services;

class OfferMailer
{
    protected $email;

    public function __construct()
    {
        $this->email = Services::email();
    }

    public function sendOfferPurchasedToRequester(array $offer, array $buyer): bool
    {
        if (empty($offer['email'])) {
            return false;
        }

        // Hash für Link generieren
        $secretKey = $_ENV['app.secret.key'] ?? 'topsecret';
        $hash = hash_hmac('sha256', $offer['id'] . $offer['email'], $secretKey);

        $giveJobLink = site_url("offers/confirm/" . $offer['id'] . "?hash=$hash");
        $reviewLink  = site_url("offers/review/" . $offer['id'] . "?hash=$hash");

        $subject = "Ihre Anfrage wurde gekauft – was jetzt?";
        $message = view('emails/offer_purchased_requester', [
            'offer' => $offer,
            'buyer' => $buyer,
            'giveJobLink' => $giveJobLink,
            'reviewLink' => $reviewLink,
        ]);

        $this->email->setTo($offer['email']);
        $this->email->setSubject($subject);
        $this->email->setMessage($message);

        return $this->email->send();
    }
}
