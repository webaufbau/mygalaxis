<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RecalculateRevenue extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'recalculate:revenue';
    protected $description = 'Recalculate revenue columns for all offers based on offer_purchases';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $offersBuilder = $db->table('offers');
        $purchasesBuilder = $db->table('offer_purchases');

        CLI::write('Starting revenue recalculation for all offers...', 'yellow');

        // Hole alle Offers
        $offers = $offersBuilder->get()->getResultArray();
        $total = count($offers);
        $updated = 0;

        CLI::write("Found {$total} offers to process.", 'cyan');

        foreach ($offers as $offer) {
            $offerId = $offer['id'];

            // Aggregiere Verkäufe aus offer_purchases nach discount_type
            $purchaseStats = $purchasesBuilder
                ->select('
                    discount_type,
                    COUNT(*) as sales_count,
                    SUM(price_paid) as revenue
                ')
                ->where('offer_id', $offerId)
                ->where('status', 'paid')
                ->groupBy('discount_type')
                ->get()
                ->getResultArray();

            // Initialisiere alle Werte mit 0
            $payload = [
                'sales_normal_price' => 0,
                'revenue_normal_price' => 0,
                'sales_discount_1' => 0,
                'revenue_discount_1' => 0,
                'sales_discount_2' => 0,
                'revenue_discount_2' => 0,
                'total_revenue' => 0,
                'avg_sale_price' => 0,
            ];

            $totalSales = 0;
            $totalRevenue = 0;

            // Aggregiere die Verkäufe nach Typ
            foreach ($purchaseStats as $stat) {
                $type = $stat['discount_type'];
                $count = (int)$stat['sales_count'];
                $revenue = (float)$stat['revenue'];

                $totalSales += $count;
                $totalRevenue += $revenue;

                if ($type === 'normal') {
                    $payload['sales_normal_price'] = $count;
                    $payload['revenue_normal_price'] = $revenue;
                } elseif ($type === 'discount_1') {
                    $payload['sales_discount_1'] = $count;
                    $payload['revenue_discount_1'] = $revenue;
                } elseif ($type === 'discount_2') {
                    $payload['sales_discount_2'] = $count;
                    $payload['revenue_discount_2'] = $revenue;
                }
            }

            $payload['total_revenue'] = $totalRevenue;
            $payload['avg_sale_price'] = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

            // Update nur wenn sich etwas geändert hat
            if ($totalSales > 0) {
                $payload['updated_at'] = date('Y-m-d H:i:s');
                $offersBuilder->where('id', $offerId)->update($payload);
                $updated++;
            }

            if ($updated % 50 == 0 && $updated > 0) {
                CLI::showProgress($updated, $total);
            }
        }

        CLI::showProgress($total, $total);
        CLI::write("\nRecalculation complete!", 'green');
        CLI::write("Total offers: {$total}", 'cyan');
        CLI::write("Updated offers: {$updated}", 'green');
        CLI::write("Skipped offers (no sales): " . ($total - $updated), 'yellow');
    }
}
