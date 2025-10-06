<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Libraries\OfferPriceCalculator;

class FixZeroPriceOffers extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:fix-zero-prices';
    protected $description = 'Repariert Angebote mit Preis 0 durch Neuberechnung.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $calculator = new OfferPriceCalculator();

        // Finde alle Angebote mit Preis 0
        $offersWithZeroPrice = $offerModel
            ->where('price', 0)
            ->orWhere('price IS NULL')
            ->findAll();

        $total = count($offersWithZeroPrice);
        CLI::write("Gefunden: {$total} Angebote mit Preis 0", 'yellow');

        if ($total === 0) {
            CLI::write("Keine Angebote zum Reparieren gefunden.", 'green');
            return;
        }

        $fixed = 0;
        $stillZero = 0;
        $errors = [];

        foreach ($offersWithZeroPrice as $offer) {
            try {
                $formFields = json_decode($offer['form_fields'], true) ?? [];
                $formFieldsCombo = json_decode($offer['form_fields_combo'], true) ?? [];

                $price = $calculator->calculatePrice(
                    $offer['type'] ?? '',
                    $offer['original_type'] ?? '',
                    $formFields,
                    $formFieldsCombo
                );

                if ($price > 0) {
                    $offerModel->update($offer['id'], ['price' => $price]);
                    $fixed++;
                    CLI::write("✓ Offer #{$offer['id']} ({$offer['type']}): Preis aktualisiert auf {$price} CHF", 'green');
                } else {
                    $stillZero++;
                    CLI::write("✗ Offer #{$offer['id']} ({$offer['type']}): Preis bleibt 0 - Kategorie nicht konfiguriert?", 'red');
                    $errors[] = [
                        'id' => $offer['id'],
                        'type' => $offer['type'],
                        'original_type' => $offer['original_type'],
                    ];
                }
            } catch (\Exception $e) {
                $stillZero++;
                CLI::write("✗ Offer #{$offer['id']}: Fehler - " . $e->getMessage(), 'red');
                $errors[] = [
                    'id' => $offer['id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        CLI::newLine();
        CLI::write("=== Zusammenfassung ===", 'yellow');
        CLI::write("Gesamt: {$total}");
        CLI::write("Repariert: {$fixed}", 'green');
        CLI::write("Weiterhin 0: {$stillZero}", 'red');

        if (!empty($errors)) {
            CLI::newLine();
            CLI::write("=== Fehlerhafte Angebote ===", 'red');
            foreach ($errors as $error) {
                CLI::write("ID: {$error['id']}, Type: " . ($error['type'] ?? 'unknown') . ", OriginalType: " . ($error['original_type'] ?? 'unknown'));
            }
        }
    }
}
