<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Cronjob zum Aufräumen alter Telefon-Verifizierungen
 *
 * Verwendung:
 * php spark cleanup:verified-phones [days]
 *
 * Beispiel:
 * php spark cleanup:verified-phones 90
 *
 * Crontab-Beispiel (täglich um 3:00 Uhr):
 * 0 3 * * * cd /pfad/zum/projekt && php spark cleanup:verified-phones 90
 */
class CleanupVerifiedPhones extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'cleanup:verified-phones';
    protected $description = 'Löscht alte Telefon-Verifizierungen aus der Datenbank';

    protected $usage = 'cleanup:verified-phones [days]';
    protected $arguments = [
        'days' => 'Anzahl Tage, nach denen Verifizierungen gelöscht werden sollen (Standard: 90)'
    ];

    public function run(array $params)
    {
        $days = $params[0] ?? 90;

        if (!is_numeric($days) || $days < 1) {
            CLI::error('Ungültige Anzahl Tage. Bitte eine positive Zahl angeben.');
            return;
        }

        CLI::write('Starte Cleanup von Telefon-Verifizierungen älter als ' . $days . ' Tage...', 'yellow');

        $verifiedPhoneModel = new \App\Models\VerifiedPhoneModel();
        $deletedCount = $verifiedPhoneModel->cleanupOldVerifications($days);

        if ($deletedCount > 0) {
            CLI::write('✓ ' . $deletedCount . ' alte Verifizierungen wurden gelöscht.', 'green');
        } else {
            CLI::write('✓ Keine alten Verifizierungen gefunden.', 'green');
        }

        // Statistik ausgeben
        $totalVerifications = $verifiedPhoneModel->countAllResults();
        CLI::write('Aktuelle Anzahl gespeicherter Verifizierungen: ' . $totalVerifications, 'blue');
    }
}
