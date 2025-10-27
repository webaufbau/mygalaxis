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

    public function updateOfferAndNotify(array $offer): bool
    {
        // Prüfe ob Offerte ausverkauft ist (>= MAX_PURCHASES paid purchases)
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
        $purchaseCount = $offerPurchaseModel
            ->where('offer_id', $offer['id'])
            ->where('status', 'paid')
            ->countAllResults();

        if ($purchaseCount >= \App\Models\OfferModel::MAX_PURCHASES) {
            return false; // Ausverkauft, keine Updates/Benachrichtigungen
        }

        $formFields = json_decode($offer['form_fields'] ?? '{}', true) ?? [];
        $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true) ?? [];

        $price = $this->calculator->calculatePrice(
            $offer['type'] ?? '',
            $offer['original_type'] ?? '',
            $formFields ?? [],
            $formFieldsCombo ?? []
        );

        // Wenn Preis 0 ist, logge Debug-Info und überspringe Update
        if ($price == 0) {
            $debugInfo = $this->calculator->getDebugInfo();
            log_message('warning', "Offer #{$offer['id']}: Preis ist 0. Debug: " . implode(' | ', $debugInfo));
            return false; // Return false um anzuzeigen dass nichts aktualisiert wurde
        }

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
                        // Immer Original-Preis als oldPrice verwenden, nicht den vorherigen reduzierten Preis
                        $this->sendPriceUpdateEmail($user, $offer, $price, $discountedPrice);
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

        return true; // Update erfolgreich
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
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        // Lade vollständige Offertendaten inkl. data-Feld
        $offerModel = new \App\Models\OfferModel();
        $fullOffer = $offerModel->find($offer['id']);

        if (!$fullOffer) {
            log_message('error', 'Offerte ID ' . $offer['id'] . ' nicht gefunden für Preis-Update E-Mail');
            return;
        }

        // Dekodiere form_fields als data-Feld
        if (isset($fullOffer['form_fields']) && is_string($fullOffer['form_fields'])) {
            $fullOffer['data'] = json_decode($fullOffer['form_fields'], true) ?? [];
        } elseif (isset($fullOffer['data']) && is_string($fullOffer['data'])) {
            $fullOffer['data'] = json_decode($fullOffer['data'], true) ?? [];
        } else {
            $fullOffer['data'] = [];
        }

        // Prüfe ob User diese Offerte bereits gekauft hat
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchase = $purchaseModel
            ->where('offer_id', $offer['id'])
            ->where('company_id', $user->id)
            ->first();

        $alreadyPurchased = !empty($purchase);

        $type = lang('Offers.type.' . $offer['type']);
        // Fallback falls Übersetzung fehlt
        if (str_starts_with($type, 'Offers.')) {
            $type = ucfirst(strtolower(str_replace(['_', '-'], ' ', $offer['type'])));
        }

        // Division durch 0 vermeiden
        if ($oldPrice > 0) {
            $discount = round(($oldPrice - $newPrice) / $oldPrice * 100);
        } else {
            $discount = 0;
        }

        // Format: "20% Rabatt auf Angebot #32 Heizung 3000 Bern"
        $subject = "{$discount}% Rabatt auf Angebot #{$offer['id']} {$type} {$offer['zip']} {$offer['city']}";

        $message = view('emails/price_update', [
            'firma' => $user,
            'offer' => $fullOffer,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'discount' => $discount,
            'siteConfig' => $siteConfig,
            'alreadyPurchased' => $alreadyPurchased,
        ]);

        $to = $siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail();

        $this->sendEmail($to, $subject, $message, $siteConfig);
    }


    protected function sendEmail(string $to, string $subject, string $message, $siteConfig = null): bool
    {
        $siteConfig = $siteConfig ?? siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => 'Neue passende Offerten',
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
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

}
