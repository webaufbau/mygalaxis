<?php

namespace App\Commands;

use App\Entities\User;
use App\Libraries\ZipcodeService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Libraries\OfferPriceCalculator;

class CalculateOfferPrices extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:calculate-prices';
    protected $description = 'Berechnet Preis und discounted_price für Angebote basierend auf aktuellen Regeln.';

    /**
     * @throws \DateMalformedStringException
     * @throws \ReflectionException
     */
    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $calculator = new OfferPriceCalculator();

        // Alle Angebote auswählen, bei denen Preis oder discounted_price aktualisiert werden soll
        $offers = $offerModel
            ->where('type IS NOT NULL')
            ->where('original_type IS NOT NULL')
            //->where('type', 'tiling') // Test
            ->orderBy('updated_at', 'ASC')
            ->findAll(100); // Die ältesten 100

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $formFieldsCombo = json_decode($offer['form_fields_combo'], true);
            $detectedType = $offer['type'] ?? null;

            if (!$detectedType) {
                CLI::write("Angebot {$offer['id']} hat keinen Typ, übersprungen.", 'yellow');
                continue;
            }

            $originalType = $offer['original_type'] ?? null;

            // Basispreis berechnen
            $price = $calculator->calculatePrice($detectedType ?? '', $originalType ?? '', $formFields ?? [], $formFieldsCombo ?? []);
            $updateData = [];

            if ($price > 0) {
                $updateData['price'] = $price;
                CLI::write("Angebot {$offer['id']} Basispreis: {$price} CHF", 'green');
            }

            // Discount anwenden
            $createdAt = new \DateTime($offer['created_at']);
            $now = new \DateTime();
            $hoursDiff = $createdAt->diff($now)->h + ($createdAt->diff($now)->days * 24);

            $discountedPrice = $calculator->applyDiscount($price, $hoursDiff);

            if ($discountedPrice < $price) {
                if ($offer['discounted_price'] != $discountedPrice) {
                    $updateData['discounted_price'] = $discountedPrice;
                    CLI::write("Angebot {$offer['id']} Discount angewendet: {$discountedPrice} CHF", 'blue');

                    // --- E-Mail an passende Firmen versenden ---
                    $userModel = new \App\Models\UserModel();
                    $zipcodeService = new \App\Libraries\ZipcodeService();
                    $users = $userModel->findAll();

                    foreach ($users as $user) {
                        if(!$user->inGroup('user')) continue;

                        // Prüfen, ob das Angebot in den Filter des Users passt
                        if ($this->doesOfferMatchUser($offer, $user, $zipcodeService)) {
                            $this->sendPriceUpdateEmail($user, $offer, $price, $discountedPrice);
                            CLI::write("✅ Price Update E-Mail gesendet an {$user->getEmail()} für Angebot #{$offer['id']}", 'green');
                        }
                    }

                    // Für E-Mail alten und neuen Preis mitgeben
                    $oldPrice = $offer['discounted_price'] ?: $price;
                    $newPrice = $discountedPrice;

                    $this->sendPriceUpdateEmail($user, $offer, $oldPrice, $newPrice);
                } else {
                    CLI::write("Angebot {$offer['id']} Discount unverändert ({$discountedPrice} CHF).", 'yellow');
                }

            } else {
                // Kein Discount möglich, eventuell discounted_price zurücksetzen?
                if ($offer['discounted_price']) {
                    $updateData['discounted_price'] = null;
                    CLI::write("Angebot {$offer['id']} Discount entfernt.", 'red');
                }
            }

            if (!empty($updateData)) {
                $offerModel->update($offer['id'], $updateData);
            }
        }

        CLI::write(count($offers ?? []) . " Preise aktualisiert.", 'cyan');
    }






    protected function doesOfferMatchUser(array $offer, User $user, ZipcodeService $zipcodeService): bool
    {
        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $services = is_string($user->filter_absences) ? json_decode($user->filter_absences, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        // ZIP prüfen
        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) {
            return false;
        }

        // Kategorie prüfen
        if (!empty($categories) && !in_array($offer['type'], $categories)) {
            return false;
        }

        // Sprache prüfen
        if (!empty($languages) && !in_array($offer['language'], $languages)) {
            return false;
        }

        // Services prüfen
        if (!empty($services)) {
            foreach ($services as $service) {
                if (stripos($offer['services'] ?? '', $service) === false) {
                    return false;
                }
            }
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

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }


}
