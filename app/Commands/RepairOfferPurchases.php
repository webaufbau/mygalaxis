<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\BookingModel;
use App\Models\OfferPurchaseModel;
use App\Models\OfferModel;

class RepairOfferPurchases extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:repair-purchases';
    protected $description = 'Erstellt fehlende offer_purchases Einträge für bestehende Buchungen.';

    public function run(array $params)
    {
        $bookingModel = new BookingModel();
        $offerPurchaseModel = new OfferPurchaseModel();
        $offerModel = new OfferModel();

        // Alle offer_purchase Buchungen
        $bookings = $bookingModel
            ->where('type', 'offer_purchase')
            ->findAll();

        CLI::write("Gefundene offer_purchase Buchungen: " . count($bookings), 'yellow');

        $created = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            // Prüfe ob bereits ein offer_purchases Eintrag existiert
            $existing = $offerPurchaseModel
                ->where('user_id', $booking['user_id'])
                ->where('offer_id', $booking['reference_id'])
                ->first();

            if ($existing) {
                CLI::write("  Booking #{$booking['id']} → Bereits vorhanden", 'blue');
                $skipped++;
                continue;
            }

            // Hole Offer-Daten
            $offer = $offerModel->find($booking['reference_id']);
            if (!$offer) {
                CLI::write("  Booking #{$booking['id']} → Offer nicht gefunden!", 'red');
                $skipped++;
                continue;
            }

            // Erstelle offer_purchases Eintrag
            $pricePaid = abs($booking['amount']);
            $offerPurchaseModel->insert([
                'user_id' => $booking['user_id'],
                'offer_id' => $booking['reference_id'],
                'price' => $offer['price'],
                'price_paid' => $pricePaid,
                'payment_method' => 'wallet',
                'status' => 'completed',
                'created_at' => $booking['created_at'],
            ]);

            CLI::write("  Booking #{$booking['id']} → offer_purchases Eintrag erstellt", 'green');
            $created++;
        }

        CLI::newLine();
        CLI::write("Fertig!", 'green');
        CLI::write("Erstellt: {$created}", 'green');
        CLI::write("Übersprungen: {$skipped}", 'blue');
    }
}
