<?php

namespace App\Commands;

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
            ->where('type', 'heating') // Test
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
            $price = $calculator->calculatePrice($detectedType, $originalType, $formFields, $formFieldsCombo);
            dd($price);
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
                $updateData['discounted_price'] = $discountedPrice;
                CLI::write("Angebot {$offer['id']} Discount angewendet: {$discountedPrice} CHF", 'blue');
            }

            if (!empty($updateData)) {
                $offerModel->update($offer['id'], $updateData);
            }
        }

        CLI::write("Preise aktualisiert.", 'cyan');
    }
}
