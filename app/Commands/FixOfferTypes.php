<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;

class FixOfferTypes extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:fix-types';
    protected $description = 'Korrigiert falsche type/original_type Werte in Angeboten.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $offers = $offerModel->findAll();

        CLI::write("Prüfe " . count($offers) . " Angebote auf falsche Typen...", 'yellow');
        CLI::newLine();

        $fixed = 0;

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'] ?? '{}', true);
            $needsUpdate = false;
            $updates = [];

            // Fall 1: move_cleaning mit falschem original_type
            if ($offer['type'] === 'move_cleaning') {
                $hasUmzugFields = !empty($formFields['auszug_adresse']) ||
                                  !empty($formFields['objekt']) ||
                                  !empty($formFields['auszug_zimmer']);

                $validUmzugTypes = ['umzug', 'umzug_firma', 'reinigung', 'reinigung_wohnung'];

                if ($hasUmzugFields && !in_array($offer['original_type'], $validUmzugTypes)) {
                    // Bestimme ob Firmen- oder Privat-Umzug
                    $isFirma = !empty($formFields['objekt']) &&
                               in_array($formFields['objekt'], ['Büro', 'Laden', 'Lager', 'Praxis', 'Industrie']);

                    $newOriginalType = $isFirma ? 'umzug_firma' : 'umzug';

                    CLI::write("Offer #{$offer['id']}: Korrigiere original_type '{$offer['original_type']}' → '{$newOriginalType}'", 'yellow');

                    $updates['original_type'] = $newOriginalType;
                    $needsUpdate = true;
                }
            }

            // Fall 2: move mit falschem original_type
            if ($offer['type'] === 'move') {
                $hasUmzugFields = !empty($formFields['auszug_adresse']) ||
                                  !empty($formFields['objekt']) ||
                                  !empty($formFields['auszug_zimmer']);

                $validUmzugTypes = ['umzug', 'umzug_firma'];

                if ($hasUmzugFields && !in_array($offer['original_type'], $validUmzugTypes)) {
                    // Bestimme ob Firmen- oder Privat-Umzug
                    $isFirma = !empty($formFields['objekt']) &&
                               in_array($formFields['objekt'], ['Büro', 'Laden', 'Lager', 'Praxis', 'Industrie']);

                    $newOriginalType = $isFirma ? 'umzug_firma' : 'umzug';

                    CLI::write("Offer #{$offer['id']}: Korrigiere original_type '{$offer['original_type']}' → '{$newOriginalType}'", 'yellow');

                    $updates['original_type'] = $newOriginalType;
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $offerModel->update($offer['id'], $updates);
                CLI::write("  → Aktualisiert", 'green');
                $fixed++;
            }
        }

        CLI::newLine();
        CLI::write("Fertig! {$fixed} Angebote korrigiert.", 'green');
    }
}
