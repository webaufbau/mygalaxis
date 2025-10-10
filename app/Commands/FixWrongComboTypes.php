<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;

class FixWrongComboTypes extends BaseCommand
{
    protected $group       = 'Offers';
    protected $name        = 'offers:fix-wrong-combo';
    protected $description = 'Korrigiert Angebote, die fälschlicherweise als move_cleaning gespeichert wurden, obwohl sie andere Typen (sanitär, elektriker, etc.) sein sollten.';

    protected $usage = 'offers:fix-wrong-combo [options]';
    protected $arguments = [];
    protected $options = [
        '--force' => 'Korrigiert auch Angebote mit move/cleaning in form_fields_combo (VORSICHT!)',
        '--dry-run' => 'Zeigt nur was korrigiert würde, ohne Änderungen zu speichern',
    ];

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $force = CLI::getOption('force');
        $dryRun = CLI::getOption('dry-run');

        // Finde alle move_cleaning Angebote
        $offers = $offerModel->where('type', 'move_cleaning')->findAll();

        if ($dryRun) {
            CLI::write("=== DRY RUN MODE - Keine Änderungen werden gespeichert ===", 'yellow');
        }
        if ($force) {
            CLI::write("=== FORCE MODE - Korrigiert auch Angebote mit move/cleaning in combo ===", 'red');
        }
        CLI::newLine();

        CLI::write("Prüfe " . count($offers) . " move_cleaning Angebote auf falsche Typisierung...", 'yellow');
        CLI::newLine();

        $fixed = 0;
        $errors = [];

        foreach ($offers as $offer) {
            $formFields = json_decode($offer['form_fields'] ?? '{}', true);
            $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true);

            // Prüfe ob form_fields tatsächlich move oder cleaning Felder enthält
            $hasMoveFields = !empty($formFields['auszug_adresse']) ||
                            !empty($formFields['auszug_adresse_firma']) ||
                            !empty($formFields['objekt']) ||
                            !empty($formFields['auszug_zimmer']);

            $hasCleaningFields = !empty($formFields['reinigung_objekt']) ||
                                !empty($formFields['reinigung_zimmer']) ||
                                !empty($formFields['reinigung_art']);

            // Prüfe ob es andere Service-Typen sind
            $detectedType = null;
            $serviceTypeMap = [
                'sanitaer' => ['arbeiten_sanitaer', 'sanitaer_grundflaeche'],
                'electrician' => ['arbeiten_elektriker', 'elektriker_grundflaeche'],
                'painting' => ['wand_teil_komplett', 'malerarbeiten_flaeche'],
                'gardening' => ['garten_art', 'garten_flaeche'],
                'plumbing' => ['arbeiten_sanitaer'], // alias
                'heating' => ['heizung_art', 'heizung_arbeiten'],
                'flooring' => ['bodenbelag_art', 'bodenbelag_flaeche'],
                'tiling' => ['plattenarbeiten_art', 'plattenarbeiten_flaeche'],
            ];

            foreach ($serviceTypeMap as $type => $fieldNames) {
                foreach ($fieldNames as $fieldName) {
                    if (isset($formFields[$fieldName]) && !empty($formFields[$fieldName])) {
                        $detectedType = $type;
                        break 2;
                    }
                }
            }

            // Prüfe auch ob original_type korrekt ist
            $validMoveCleaningOriginalTypes = ['move', 'cleaning', 'umzug', 'umzug_firma', 'reinigung', 'reinigung_wohnung', 'reinigung_gewerbe'];
            $hasInvalidOriginalType = !in_array($offer['original_type'], $validMoveCleaningOriginalTypes);

            // Wenn ein anderer Typ erkannt wurde UND keine move/cleaning Felder vorhanden sind
            // ODER wenn original_type nicht move/cleaning ist
            if (($detectedType && !$hasMoveFields && !$hasCleaningFields) || $hasInvalidOriginalType) {
                CLI::write("Offer #{$offer['id']}: Falsche Typisierung erkannt!", 'red');
                CLI::write("  → Aktueller Typ: move_cleaning", 'yellow');
                CLI::write("  → Original Typ: {$offer['original_type']}", 'yellow');

                if ($detectedType) {
                    CLI::write("  → Erkannter Typ aus form_fields: {$detectedType}", 'cyan');
                } else {
                    CLI::write("  → Original Typ ist nicht move/cleaning-konform!", 'cyan');
                    $detectedType = $offer['original_type']; // Verwende original_type als neuen Typ
                }

                // Bestimme original_type basierend auf form_fields
                $originalType = $formFields['type'] ?? $detectedType;

                // Prüfe ob form_fields_combo tatsächlich move/cleaning enthält
                $comboHasMoveFields = !empty($formFieldsCombo['auszug_adresse']) ||
                                     !empty($formFieldsCombo['auszug_adresse_firma']) ||
                                     !empty($formFieldsCombo['objekt']);

                $comboHasCleaningFields = !empty($formFieldsCombo['reinigung_objekt']) ||
                                         !empty($formFieldsCombo['reinigung_zimmer']);

                if ($comboHasMoveFields || $comboHasCleaningFields) {
                    CLI::write("  → WARNUNG: form_fields_combo enthält move/cleaning Daten", 'yellow');

                    if (!$force) {
                        CLI::write("  → Dies könnte eine legitime Combo sein - bitte manuell prüfen!", 'yellow');
                        CLI::write("  → Verwenden Sie --force um trotzdem zu korrigieren", 'yellow');
                        $errors[] = "Offer #{$offer['id']} hat {$detectedType} in form_fields aber move/cleaning in combo - manuelle Prüfung nötig";
                        CLI::newLine();
                        continue;
                    } else {
                        CLI::write("  → FORCE: Korrigiere trotz move/cleaning in combo", 'red');
                    }
                }

                // Type-Mapping anwenden (original_type → type)
                $typeMapping = [
                    'sanitaer' => 'plumbing',
                    'elektrik' => 'electrician',
                    'heizung' => 'heating',
                    'boden' => 'flooring',
                    'platten' => 'tiling',
                    'maler' => 'painting',
                    'maler_andere' => 'painting',
                    'garten' => 'gardening',
                ];

                $mappedType = $typeMapping[$detectedType] ?? $detectedType;

                // Korrigiere den Typ
                $updates = [
                    'type' => $mappedType,
                    'original_type' => $originalType,
                    'form_fields_combo' => null, // Entferne falsches combo
                ];

                CLI::write("  → Type-Mapping: {$detectedType} → {$mappedType}", 'cyan');

                // Preis neu berechnen (mit gemapptem Type!)
                $priceCalculator = new \App\Libraries\OfferPriceCalculator();
                $newPrice = $priceCalculator->calculatePrice(
                    $mappedType,
                    $originalType,
                    $formFields,
                    []
                );

                if ($newPrice > 0) {
                    $updates['price'] = $newPrice;
                    CLI::write("  → Neuer Preis: {$newPrice} CHF (vorher: {$offer['price']} CHF)", 'cyan');
                } else {
                    CLI::write("  → WARNUNG: Preis-Berechnung ergab 0 - behalte alten Preis {$offer['price']} CHF", 'yellow');
                }

                // Führe Update aus
                if (!$dryRun) {
                    $offerModel->update($offer['id'], $updates);
                    CLI::write("  ✓ Korrigiert: move_cleaning → {$mappedType} (original: {$originalType})", 'green');
                } else {
                    CLI::write("  [DRY RUN] Würde korrigieren: move_cleaning → {$mappedType} (original: {$originalType})", 'cyan');
                }

                CLI::newLine();
                $fixed++;
            }
        }

        CLI::newLine();
        CLI::write("=".str_repeat("=", 60), 'white');
        CLI::write("Fertig! {$fixed} Angebote korrigiert.", 'green');

        if (!empty($errors)) {
            CLI::newLine();
            CLI::write("WARNUNGEN (manuelle Prüfung erforderlich):", 'yellow');
            foreach ($errors as $error) {
                CLI::write("  - {$error}", 'yellow');
            }
        }

        CLI::newLine();
    }
}
