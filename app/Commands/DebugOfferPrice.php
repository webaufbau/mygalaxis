<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Libraries\OfferPriceCalculator;

class DebugOfferPrice extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:debug-price';
    protected $description = 'Debuggt Preis-Berechnung für ein Angebot.';

    public function run(array $params)
    {
        $offerId = $params[0] ?? 62;

        $offerModel = new OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer) {
            CLI::error("Angebot #{$offerId} nicht gefunden!");
            return;
        }

        CLI::write("=== Angebot #{$offerId} ===", 'yellow');
        CLI::write("Type: " . ($offer['type'] ?? 'NULL'));
        CLI::write("OriginalType: " . ($offer['original_type'] ?? 'NULL'));
        CLI::write("Aktueller Preis: " . ($offer['price'] ?? '0'));
        CLI::newLine();

        $formFields = json_decode($offer['form_fields'] ?? '{}', true);
        $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true);

        CLI::write("=== Form Fields ===", 'yellow');
        CLI::write(print_r($formFields, true));
        CLI::newLine();

        if (!empty($formFieldsCombo)) {
            CLI::write("=== Form Fields Combo ===", 'yellow');
            CLI::write(print_r($formFieldsCombo, true));
            CLI::newLine();
        }

        // Preis berechnen
        $calculator = new OfferPriceCalculator();
        $price = $calculator->calculatePrice(
            $offer['type'] ?? '',
            $offer['original_type'] ?? '',
            $formFields,
            $formFieldsCombo
        );

        CLI::write("=== Berechneter Preis: {$price} CHF ===", 'green');

        // Spezifisches Feld für move prüfen
        if ($offer['type'] === 'move') {
            $auszugZimmer = $formFields['auszug_zimmer'] ?? null;
            CLI::newLine();
            CLI::write("=== Move Debug ===", 'yellow');
            CLI::write("auszug_zimmer Wert: " . print_r($auszugZimmer, true));
            CLI::write("auszug_zimmer Typ: " . gettype($auszugZimmer));

            if (is_array($auszugZimmer)) {
                CLI::write("auszug_zimmer ist ein Array mit " . count($auszugZimmer) . " Elementen");
                CLI::write("Erster Wert: " . ($auszugZimmer[0] ?? 'NULL'));
            }
        }
    }
}
