<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\OfferNotificationSender;

class SendMissingCompanyNotifications extends BaseCommand
{
    protected $group = 'Offers';
    protected $name = 'offers:send-missing-company-notifications';
    protected $description = 'Sendet fehlende Benachrichtigungen an interessierte Firmen für verifizierte Angebote';
    protected $usage = 'offers:send-missing-company-notifications';

    public function run(array $params)
    {
        CLI::write('Suche nach verifizierten Angeboten ohne Firmen-Benachrichtigung...', 'yellow');

        $offerModel = new \App\Models\OfferModel();

        // Finde alle Angebote wo:
        // - verified = 1 (verifiziert)
        // - companies_notified_at IS NULL (Firmen noch nicht benachrichtigt)
        // - platform IS NOT NULL (Platform muss gesetzt sein)
        $offersWithoutNotification = $offerModel
            ->where('verified', 1)
            ->where('companies_notified_at IS NULL')
            ->where('platform IS NOT NULL')
            ->findAll();

        if (empty($offersWithoutNotification)) {
            CLI::write('Keine Angebote gefunden, die Firmen-Benachrichtigungen benötigen.', 'green');
            return;
        }

        CLI::write('Gefunden: ' . count($offersWithoutNotification) . ' Angebote ohne Firmen-Benachrichtigung', 'yellow');

        $notifier = new OfferNotificationSender();
        $totalSent = 0;

        foreach ($offersWithoutNotification as $offer) {
            CLI::write("Verarbeite Angebot ID {$offer['id']} (Platform: {$offer['platform']})...", 'cyan');

            try {
                $sentCount = $notifier->notifyMatchingUsers($offer);
                $totalSent += $sentCount;

                if ($sentCount > 0) {
                    CLI::write("  ✓ {$sentCount} Firma(n) benachrichtigt", 'green');
                } else {
                    CLI::write("  → Keine passenden Firmen gefunden", 'yellow');
                }
            } catch (\Exception $e) {
                CLI::write("  ✗ Fehler: " . $e->getMessage(), 'red');
                log_message('error', "Fehler beim Benachrichtigen von Firmen für Offer ID {$offer['id']}: " . $e->getMessage());
            }
        }

        CLI::write("Fertig! Insgesamt {$totalSent} Firmen benachrichtigt für " . count($offersWithoutNotification) . " Angebote.", 'green');
    }
}
