<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Libraries\OfferTitleGenerator;

class UpdateOfferTitles extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'offers:update-titles';
    protected $description = 'Aktualisiert alle Angebots-Titel mit aussagekräftigen Details.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $titleGenerator = new OfferTitleGenerator();

        $offers = $offerModel->findAll();
        $total = count($offers);

        CLI::write("Aktualisiere Titel für {$total} Angebote...", 'yellow');
        CLI::newLine();

        $updated = 0;
        $skipped = 0;

        foreach ($offers as $offer) {
            $oldTitle = $offer['title'] ?? '';
            $newTitle = $titleGenerator->generateTitle($offer);

            if ($oldTitle !== $newTitle) {
                $offerModel->update($offer['id'], ['title' => $newTitle]);
                $updated++;
                CLI::write("✓ #{$offer['id']}: {$newTitle}", 'green');
            } else {
                $skipped++;
                CLI::write("- #{$offer['id']}: Unverändert", 'dark_gray');
            }
        }

        CLI::newLine();
        CLI::write("=== Zusammenfassung ===", 'yellow');
        CLI::write("Gesamt: {$total}");
        CLI::write("Aktualisiert: {$updated}", 'green');
        CLI::write("Übersprungen: {$skipped}", 'dark_gray');
    }
}
