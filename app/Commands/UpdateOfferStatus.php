<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\OfferPurchaseModel;

class UpdateOfferStatus extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:update-status';
    protected $description = 'Aktualisiert den Status von Angeboten basierend auf der Anzahl der Verkäufe.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $purchaseModel = new OfferPurchaseModel();

        // Alle Angebote mit Status 'out_of_stock' oder 'sold' holen
        $offers = $offerModel
            ->groupStart()
                ->where('status', 'out_of_stock')
                ->orWhere('status', 'sold')
            ->groupEnd()
            ->findAll();

        CLI::write("Gefundene Angebote mit Status 'out_of_stock' oder 'sold': " . count($offers), 'yellow');

        $updatedCount = 0;
        foreach ($offers as $offer) {
            // Anzahl der Käufe ermitteln
            $purchaseCount = $purchaseModel
                ->where('offer_id', $offer['id'])
                ->where('status', 'completed')
                ->countAllResults();

            CLI::write("Angebot #{$offer['id']}: {$purchaseCount} Verkäufe, Status: {$offer['status']}");

            // Wenn weniger als 4 Verkäufe, Status auf 'available' setzen
            if ($purchaseCount < 4) {
                $offerModel->update($offer['id'], [
                    'status' => 'available',
                    'buyers' => $purchaseCount
                ]);
                CLI::write("  → Status aktualisiert auf 'available'", 'green');
                $updatedCount++;
            } else {
                CLI::write("  → Bleibt '{$offer['status']}' (>= 4 Verkäufe)", 'blue');
            }
        }

        CLI::newLine();
        CLI::write("Fertig! {$updatedCount} Angebote wurden auf 'available' zurückgesetzt.", 'green');
    }
}
