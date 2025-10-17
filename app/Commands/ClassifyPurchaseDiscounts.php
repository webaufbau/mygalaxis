<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ClassifyPurchaseDiscounts extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'classify:purchase-discounts';
    protected $description = 'Classify existing offer purchases by discount type';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('offer_purchases');

        CLI::write('Starting classification of existing purchases...', 'yellow');

        // Hole alle Purchases
        $purchases = $builder->get()->getResultArray();
        $total = count($purchases);
        $classified = 0;

        CLI::write("Found {$total} purchases to classify.", 'cyan');

        foreach ($purchases as $purchase) {
            $price = (float)$purchase['price'];
            $pricePaid = (float)$purchase['price_paid'];
            $discountType = 'normal';

            if ($price > 0 && $pricePaid < $price) {
                // Berechne Rabatt-Prozentsatz
                $discountPercent = (($price - $pricePaid) / $price) * 100;

                if ($discountPercent > 20) {
                    $discountType = 'discount_2'; // > 20%
                } else {
                    $discountType = 'discount_1'; // <= 20%
                }
            }

            // Update discount_type
            $builder->where('id', $purchase['id'])->update(['discount_type' => $discountType]);
            $classified++;

            CLI::write("Purchase #{$purchase['id']}: price={$price}, paid={$pricePaid}, discount={$discountType}", 'green');
        }

        CLI::write("\nClassification complete!", 'green');
        CLI::write("Total purchases: {$total}", 'cyan');
        CLI::write("Classified: {$classified}", 'green');
    }
}