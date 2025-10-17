<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateBookingsToOfferPurchases extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'migrate:bookings-to-purchases';
    protected $description = 'Migrate offer_purchase bookings to offer_purchases table';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $bookingsBuilder = $db->table('bookings');
        $purchasesBuilder = $db->table('offer_purchases');
        $offersBuilder = $db->table('offers');

        CLI::write('Starting migration of bookings to offer_purchases...', 'yellow');

        // Hole alle offer_purchase Bookings
        $bookings = $bookingsBuilder
            ->where('type', 'offer_purchase')
            ->where('reference_id IS NOT NULL')
            ->get()
            ->getResultArray();

        $total = count($bookings);
        $created = 0;
        $skipped = 0;

        CLI::write("Found {$total} offer_purchase bookings.", 'cyan');

        foreach ($bookings as $booking) {
            $offerId = (int)$booking['reference_id'];
            $userId = (int)$booking['user_id'];
            $pricePaid = abs((float)$booking['amount']); // amount ist negativ
            $createdAt = $booking['created_at'];

            // Prüfe ob bereits ein Eintrag existiert
            $existing = $purchasesBuilder
                ->where('offer_id', $offerId)
                ->where('user_id', $userId)
                ->where('created_at', $createdAt)
                ->get()
                ->getRowArray();

            if ($existing) {
                $skipped++;
                continue;
            }

            // Hole Offer-Details für den Originalpreis
            $offer = $offersBuilder->where('id', $offerId)->get()->getRowArray();

            if (!$offer) {
                CLI::write("Warning: Offer #{$offerId} not found, skipping booking #{$booking['id']}", 'yellow');
                $skipped++;
                continue;
            }

            $price = (float)$offer['price'];

            // Berechne discount_type
            $discountType = 'normal';
            if ($price > 0 && $pricePaid < $price) {
                $discountPercent = (($price - $pricePaid) / $price) * 100;

                if ($discountPercent > 20) {
                    $discountType = 'discount_2'; // > 20%
                } else {
                    $discountType = 'discount_1'; // <= 20%
                }
            }

            // Erstelle offer_purchase Eintrag
            $purchasesBuilder->insert([
                'user_id' => $userId,
                'offer_id' => $offerId,
                'price' => $price,
                'price_paid' => $pricePaid,
                'discount_type' => $discountType,
                'payment_method' => 'wallet',
                'status' => 'paid',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $created++;

            if ($created % 10 == 0) {
                CLI::write("Created {$created} purchases so far...", 'green');
            }
        }

        CLI::write("\nMigration complete!", 'green');
        CLI::write("Total bookings: {$total}", 'cyan');
        CLI::write("Created purchases: {$created}", 'green');
        CLI::write("Skipped (already exists): {$skipped}", 'yellow');
    }
}
