<?php

namespace App\Libraries;

use App\Models\OfferModel;
use App\Models\UserModel;
use App\Libraries\ZipcodeService;
use App\Entities\User;

class OfferPriceUpdater
{
    protected OfferPriceCalculator $calculator;
    protected OfferModel $offerModel;
    protected UserModel $userModel;
    protected ZipcodeService $zipcodeService;

    public function __construct()
    {
        $this->calculator = new OfferPriceCalculator();
        $this->offerModel = new OfferModel();
        $this->userModel = new UserModel();
        $this->zipcodeService = new ZipcodeService();
    }

    public function updateOfferAndNotify(array $offer): void
    {
        $formFields = json_decode($offer['form_fields'], true);
        $formFieldsCombo = json_decode($offer['form_fields_combo'], true);

        $price = $this->calculator->calculatePrice(
            $offer['type'] ?? '',
            $offer['original_type'] ?? '',
            $formFields ?? [],
            $formFieldsCombo ?? []
        );

        $updateData = [];
        if ($price > 0) {
            $updateData['price'] = $price;
        }

        // Discount
        $createdAt = new \DateTime($offer['created_at']);
        $now = new \DateTime();
        $hoursDiff = $createdAt->diff($now)->h + ($createdAt->diff($now)->days * 24);

        $discountedPrice = $this->calculator->applyDiscount($price, $hoursDiff);

        if ($discountedPrice < $price) {
            if ($offer['discounted_price'] != $discountedPrice) {
                $updateData['discounted_price'] = $discountedPrice;

                // passende Firmen finden
                $users = $this->userModel->findAll();
                foreach ($users as $user) {
                    if (!$user->inGroup('user')) {
                        continue;
                    }
                    if ($this->doesOfferMatchUser($offer, $user)) {
                        $this->sendPriceUpdateEmail($user, $offer, $offer['discounted_price'] ?: $price, $discountedPrice);
                    }
                }
            }
        } else {
            if ($offer['discounted_price']) {
                $updateData['discounted_price'] = null;
            }
        }

        if (!empty($updateData)) {
            $this->offerModel->update($offer['id'], $updateData);
        }
    }

    protected function doesOfferMatchUser(array $offer, User $user): bool
    {
        // dieselbe Logik wie bei dir
        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        $zipcodeService = new ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;

        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) {
            return false;
        }

        if (!empty($categories) && !in_array($offer['type'], $categories)) {
            return false;
        }

        if (!empty($languages) && !in_array($offer['language'], $languages)) {
            return false;
        }

        return true;
    }


    protected function sendPriceUpdateEmail(User $user, array $offer, float $oldPrice, float $newPrice): void
    {
        $siteConfig = siteconfig();

        $type = lang('Offers.type.' . $offer['type']);
        $subject = "Preisänderung für Angebot #{$offer['id']} – {$type}";
        $message = view('emails/price_update', [
            'firma' => $user,
            'offer' => $offer,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'discount' => round(($oldPrice - $newPrice) / $oldPrice * 100, 2),
            'siteConfig' => $siteConfig,
        ]);

        $to = $siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail();

        $this->sendEmail($to, $subject, $message);
    }


    protected function sendEmail(string $to, string $subject, string $message): bool
    {
        $siteConfig = siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => 'Neue passende Offerten',
            'content' => $message,
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
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

}
