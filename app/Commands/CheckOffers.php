<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\OfferMoveModel;
use App\Models\OfferCleaningModel;
use App\Models\OfferPaintingModel;
use App\Models\OfferGardeningModel;
use App\Models\OfferPlumbingModel;

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
        $offers = $offerModel->where('checked_at IS NULL')->findAll(100);

        // Modell dynamisch laden (nach Typ)
        $modelClassMap = [
            'move'      => \App\Models\OfferMoveModel::class,
            'cleaning'  => \App\Models\OfferCleaningModel::class,
            'painting'  => \App\Models\OfferPaintingModel::class,
            'gardening' => \App\Models\OfferGardeningModel::class,
            'plumbing'  => \App\Models\OfferPlumbingModel::class,
        ];

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'], true);
            $enriched = $offerModel->enrichDataFromFormFields($formFields, $offer);
            $detectedType = $enriched['type'] ?? $offerModel->detectType($formFields);

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

            foreach (['type', 'city', 'zip', 'customer_type', 'firstname', 'lastname', 'email', 'phone', 'additional_service', 'service_url', 'uuid'] as $key) {
                if (empty($offer[$key]) && !empty($enriched[$key])) {
                    $updateData[$key] = $enriched[$key];
                }
            }

            if (!empty($updateData)) {
                CLI::write("Daten aktualisiert " . print_r($updateData, true), 'yellow');
                $offerModel->update($offer['id'], $updateData);
            }
        }

        CLI::write("Prüfung abgeschlossen.", 'cyan');
    }

    protected function detectType(array $fields): string
    {
        $source =
            $fields['_wp_http_referer']
            ?? $fields['__submission']['source_url']
            ?? $fields['service_url']
            ?? '';

        if (str_contains($source, 'umzug')) return 'move';
        if (str_contains($source, 'umzuege')) return 'move';
        if (str_contains($source, 'reinigung')) return 'cleaning';
        if (str_contains($source, 'maler')) return 'painting';
        if (str_contains($source, 'garten')) return 'gardening';
        if (str_contains($source, 'sanitaer')) return 'plumbing';

        return 'unknown';
    }
}
