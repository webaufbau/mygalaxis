<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use DateTime;

class DiscountOldOffers extends BaseCommand
{
    protected $group       = 'Offers';
    protected $name        = 'offers:discount-old';
    protected $description = 'Halbiere Preise von Angeboten, die älter als 3 Tage sind und noch keinen discounted_price haben.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();

        // Hol alle relevanten Offers
        $offers = $offerModel
            ->where('discounted_price IS NULL')
            ->where('work_start_date <', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->findAll();

        if (empty($offers)) {
            CLI::write('Keine Offers gefunden.', 'yellow');
            return;
        }

        foreach ($offers as $offer) {
            $newPrice = $offer['price'] / 2;

            $offerModel->update($offer['id'], [
                'discounted_price' => $newPrice,
                'discounted_at'    => date('Y-m-d H:i:s'),
            ]);

            CLI::write("Offer #{$offer['id']} Preis halbiert: {$offer['price']} → {$newPrice}", 'green');
        }

        CLI::write(count($offers) . ' Offers wurden rabattiert.', 'green');
    }
}
