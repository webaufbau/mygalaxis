<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Libraries\OfferPriceUpdater;

class RecalculateOfferPrice extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:recalculate-price';
    protected $description = 'Berechnet den Preis für ein einzelnes Angebot neu.';

    public function run(array $params)
    {
        $offerId = $params[0] ?? null;

        if (!$offerId) {
            CLI::error('Bitte gib eine Angebots-ID an: php spark offers:recalculate-price 95');
            return;
        }

        $offerModel = new OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer) {
            CLI::error("Angebot #{$offerId} nicht gefunden!");
            return;
        }

        CLI::write("=== Angebot #{$offerId} ===", 'yellow');
        CLI::write("Aktueller Preis: " . ($offer['price'] ?? '0') . ' CHF');

        $updater = new OfferPriceUpdater();
        $updater->updateOfferAndNotify($offer);

        // Angebot neu laden
        $updatedOffer = $offerModel->find($offerId);
        CLI::write("Neuer Preis: " . ($updatedOffer['price'] ?? '0') . ' CHF', 'green');
        CLI::write("Preis erfolgreich aktualisiert!", 'green');
    }
}
