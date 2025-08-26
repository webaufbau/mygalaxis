<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;

class CheckOffers extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:check';
    protected $description = 'Überprüft und korrigiert Offer-Einträge mit fehlenden oder falschen Typen/Zusatzdaten.';

    /**
     * @throws \ReflectionException
     */
    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $offers = $offerModel->where('checked_at IS NULL')->orWhere('type', 'unknown')->orWhere('original_type IS NULL')->orWhere('platform IS NULL')->orWhere('country IS NULL')->findAll(100);

        // Modell dynamisch laden (nach Typ)
        $modelClassMap = [
            'move'          => \App\Models\OfferMoveModel::class,
            'cleaning'      => \App\Models\OfferCleaningModel::class,
            'move_cleaning' => \App\Models\OfferMoveCleaningModel::class,
            'painting'      => \App\Models\OfferPaintingModel::class,
            'gardening'     => \App\Models\OfferGardeningModel::class,
            'plumbing'      => \App\Models\OfferPlumbingModel::class,
            'electrician'   => \App\Models\OfferElectricianModel::class,
            'flooring'      => \App\Models\OfferFlooringModel::class,
            'heating'       => \App\Models\OfferHeatingModel::class,
            'tiling'        => \App\Models\OfferTilingModel::class,
        ];

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $enriched = $offerModel->enrichDataFromFormFields($formFields, $offer);
            $detectedType = $enriched['type'] ?? $offer['type'];

            if (isset($modelClassMap[$detectedType])) {
                $modelClass = $modelClassMap[$detectedType];
                $typeModel = new $modelClass();

                if (!$typeModel->where('offer_id', $offer['id'])->first()) {
                    $typeData = $offerModel->extractFieldsByType($detectedType, $formFields);
                    $typeData['offer_id'] = $offer['id'];
                    foreach ($typeData as $key => $val) {
                        if (is_array($val)) {
                            $typeData[$key] = implode(', ', $val);
                        }
                    }
                    $typeModel->insert($typeData);
                    CLI::write(ucfirst($detectedType) . "-Daten für Angebot {$offer['id']} erstellt.", 'green');
                }
            }

            // Typ und angereicherte Felder aktualisieren
            $updateData = ['checked_at' => date('Y-m-d H:i:s')];

            if ($offer['type'] !== $detectedType) {
                CLI::write("Angebot {$offer['id']} hat falschen Typ (aktuell: {$offer['type']} != detect: {$detectedType}), wird aktualisiert.", 'yellow');
                $updateData['type'] = $detectedType;
            }

            if (is_null($offer['platform'])) {
                $headers = $offer['headers'] ? json_decode($offer['headers'], true) : [];
                $host = $headers['Host'] ?? 'unknown';
                $parts = explode('.', $host);
                $partsCount = count($parts);
                if ($partsCount > 2) {
                    $domain = $parts[$partsCount - 2] . '.' . $parts[$partsCount - 1];
                } else {
                    $domain = $host;
                }
                $updateData['platform'] = $domain;
            }

            if (is_null($offer['country'])) {
                $siteConfig = siteconfig();
                $updateData['country'] = $siteConfig->siteCountry ?? 'ch';
            }

            foreach (['type', 'original_type', 'sub_type', 'city', 'zip', 'customer_type', 'language', 'firstname', 'lastname', 'email', 'phone', 'work_start_date', 'additional_service', 'service_url', 'uuid'] as $key) {
                if (!empty($enriched[$key])) { // empty($offer[$key]) &&
                    $updateData[$key] = $enriched[$key];
                }
            }

            // Preis setzen falls 0
            $categoryManager = new \App\Libraries\CategoryManager();
            $categoryPrices = $categoryManager->getAll();
            if ((empty($offer['price']) || $offer['price']<=0) && isset($categoryPrices[$detectedType]['price']) && $categoryPrices[$detectedType]['price'] > 0) {
                $updateData['price'] = $categoryPrices[$detectedType]['price'];
                CLI::write("Preis für Angebot {$offer['id']} automatisch gesetzt: {$updateData['price']} CHF", 'light_green');
            }

            $translatedType = lang('Offers.type.' . $detectedType);

            // Titel setzen, falls leer
            if (empty($offer['title']) && $translatedType !== 'Offers.type.' . $detectedType && !empty($enriched['city'])) {
                $city = ucwords($enriched['city']);
                $title = "{$translatedType} in {$city}";
                $updateData['title'] = $title;
                CLI::write("Titel für Angebot {$offer['id']} gesetzt: {$title}", 'cyan');
            }

            if (!empty($updateData)) {
                CLI::write("Daten aktualisiert " . print_r($updateData, true), 'yellow');
                $offerModel->update($offer['id'], $updateData);
            }


        }

        CLI::write("Prüfung abgeschlossen.", 'cyan');
    }

}
