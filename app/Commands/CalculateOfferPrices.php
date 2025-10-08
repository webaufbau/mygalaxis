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
        $offers = $offerModel
            ->where('type IS NOT NULL')
            ->where('original_type IS NOT NULL')
            ->findAll(100);

        CLI::write("Starte Preisberechnung für " . count($offers) . " Angebote...", 'yellow');
        CLI::newLine();

        $updater = new \App\Libraries\OfferPriceUpdater();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($offers as $offer) {
            $oldPrice = $offer['price'];

            try {
                $wasUpdated = $updater->updateOfferAndNotify($offer);

                if (!$wasUpdated) {
                    // Preis war 0, wurde übersprungen
                    CLI::write("⚠ Offer #{$offer['id']}: Preis ist 0 - übersprungen (siehe Log für Details)", 'yellow');
                    $skipped++;
                    continue;
                }

                // Neu laden um zu sehen ob aktualisiert
                $updated_offer = $offerModel->find($offer['id']);

                if ($updated_offer['price'] != $oldPrice) {
                    CLI::write("✓ Offer #{$offer['id']}: {$oldPrice} CHF → {$updated_offer['price']} CHF", 'green');
                    $updated++;
                } else {
                    CLI::write("- Offer #{$offer['id']}: Preis unverändert ({$oldPrice} CHF)", 'blue');
                    $skipped++;
                }
            } catch (\Exception $e) {
                CLI::write("✗ Offer #{$offer['id']}: FEHLER - " . $e->getMessage(), 'red');
                $errors++;
            }
        }

        CLI::newLine();
        CLI::write("Fertig!", 'green');
        CLI::write("Aktualisiert: {$updated}", 'green');
        CLI::write("Übersprungen: {$skipped}", 'blue');
        if ($errors > 0) {
            CLI::write("Fehler: {$errors}", 'red');
        }
    }

}
