<?php

namespace App\Commands;

use App\Models\OfferModel;
use App\Models\OfferMoveCleaningModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MergeComboOffers extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:merge-combo';
    protected $description = 'Fasst Angebote vom Typ move und cleaning mit gleicher E-Mail am selben Tag zu move_cleaning zusammen.';

    public function run(array $params)
    {
        // Command deaktiviert - Combo Offers werden nicht mehr verwendet
        CLI::write('Command ist deaktiviert. Combo Offers werden nicht mehr zusammengeführt.', 'yellow');
        return;

        $offerModel = new OfferModel();

        // Alle potenziellen Kandidaten vom Typ move
        $moveOffers = $offerModel
            ->where('type', 'move')
            ->where('group_id IS NULL')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($moveOffers as $moveOffer) {
            $email = $moveOffer['email'];
            $date = date('Y-m-d', strtotime($moveOffer['created_at']));

            // Finde passenden Cleaning-Eintrag am selben Tag
            $cleaningOffer = $offerModel
                ->where('type', 'cleaning')
                ->where('group_id IS NULL')
                ->where('email', $email)
                ->like('created_at', $date)
                ->first();

            if (!$cleaningOffer) {
                continue;
            }

            // Entscheide, wer behalten und wer migriert wird
            if ($moveOffer['verified'] == 1) {
                $kept = $moveOffer;
                $deleted = $cleaningOffer;
            } elseif ($cleaningOffer['verified'] == 1) {
                $kept = $cleaningOffer;
                $deleted = $moveOffer;
            } else {
                // Keiner verified -> zufällig
                $kept = $moveOffer;
                $deleted = $cleaningOffer;
            }

            CLI::write("Merging offers {$kept['id']} (keep) + {$deleted['id']} (delete)", 'yellow');

            // Form-Felder zusammenführen
            $formFieldsPrimary = json_decode($kept['form_fields'], true);
            $formFieldsSecondary = json_decode($deleted['form_fields'], true);

            $mergedFormFields = $formFieldsPrimary;
            $formFieldsCombo = $formFieldsSecondary;

            $categoryManager = new \App\Libraries\CategoryManager();
            $categories = $categoryManager->getAll();
            $moveCleaning = $categories['move_cleaning'] ?? null;

            // Prüfen ob Kategorie existiert
            if (!$moveCleaning) {
                CLI::write("Kategorie 'move_cleaning' nicht gefunden. Überspringe Offers {$kept['id']} + {$deleted['id']}", 'red');
                continue;
            }

            // Neuen Typ setzen
            $offerModel->update($kept['id'], [
                'type' => 'move_cleaning',
                'title' => $moveCleaning['name'] .' in ' . $kept['city'],
                'price' => $moveCleaning['price'],
                'form_fields' => json_encode($mergedFormFields, JSON_UNESCAPED_UNICODE),
                'form_fields_combo' => json_encode($formFieldsCombo, JSON_UNESCAPED_UNICODE),
            ]);

            // Zusatzdaten-Modell aktualisieren
            $moveCleaningModel = new OfferMoveCleaningModel();
            $moveCleaningModel->insert([
                'offer_id' => $kept['id']
            ]);

            // Gelöschten Eintrag entfernen
            $offerModel->delete($deleted['id']);

            CLI::write("Erstellt move_cleaning für {$kept['id']} und gelöscht {$deleted['id']}", 'green');
        }

        CLI::write('Fertig.', 'cyan');
    }

}
