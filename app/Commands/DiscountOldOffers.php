<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Libraries\ZipcodeService;
use App\Entities\User;
use DateTime;

class DiscountOldOffers extends BaseCommand
{
    protected $group       = 'Offers';
    protected $name        = 'offers:discount-old';
    protected $description = 'Halbiere Preise von Angeboten, die älter als 3 Tage sind und noch keinen discounted_price haben.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $userModel = new UserModel();

        // Hol alle relevanten Offers
        $offers = $offerModel
            ->where('discounted_price IS NULL')
            ->where('created_at <', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->findAll();

        if (empty($offers)) {
            CLI::write('Keine Offers gefunden.', 'yellow');
            return;
        }

        foreach ($offers as $offer) {
            $oldPrice = $offer['price'];
            $newPrice = $oldPrice / 2;

            // Update Offer
            $offerModel->update($offer['id'], [
                'discounted_price' => $newPrice,
                'discounted_at'    => date('Y-m-d H:i:s'),
            ]);

            CLI::write("Offer #{$offer['id']} Preis halbiert: {$oldPrice} → {$newPrice}", 'green');

            // Benachrichtige passende Firmen
            $users = $userModel->findAll();
            $today = date('Y-m-d');
            $notifiedCount = 0;
            foreach ($users as $user) {
                if (!$user->inGroup('user')) {
                    continue;
                }

                // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
                if ($this->isUserBlockedToday($user->id, $today)) {
                    continue;
                }

                if ($this->doesOfferMatchUser($offer, $user)) {
                    $this->sendPriceUpdateEmail($user, $offer, $oldPrice, $newPrice);
                    $notifiedCount++;
                }
            }

            CLI::write("  → {$notifiedCount} Firma(n) benachrichtigt", 'cyan');
        }

        CLI::write(count($offers) . ' Offers wurden rabattiert.', 'green');
    }

    /**
     * Prüft ob ein User heute blockiert ist (Agenda-Eintrag)
     */
    protected function isUserBlockedToday(int $userId, string $today): bool
    {
        $blockedModel = model(\App\Models\BlockedDayModel::class);
        return $blockedModel
            ->where('user_id', $userId)
            ->where('date', $today)
            ->countAllResults() > 0;
    }

    protected function doesOfferMatchUser(array $offer, User $user): bool
    {
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

        $type = lang('Offers.type.' . $offer['type']);
        // Fallback falls Übersetzung fehlt
        if (str_starts_with($type, 'Offers.')) {
            $type = ucfirst(strtolower(str_replace(['_', '-'], ' ', $offer['type'])));
        }

        $discount = round(($oldPrice - $newPrice) / $oldPrice * 100);

        // Format: "20% Rabatt auf Angebot #32 Heizung 3000 Bern"
        $subject = "{$discount}% Rabatt auf Angebot #{$offer['id']} {$type} {$offer['zip']} {$offer['city']}";

        $message = view('emails/price_update', [
            'firma' => $user,
            'offer' => $offer,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'discount' => $discount,
            'siteConfig' => $siteConfig,
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

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }
}
