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

        $updater = new \App\Libraries\OfferPriceUpdater();

        foreach ($offers as $offer) {
            $updater->updateOfferAndNotify($offer);
        }
    }

}
