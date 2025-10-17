<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackfillPurchaseCompanyNames extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'backfill:purchase-company-names';
    protected $description = 'Füllt company_name und external_user_id in offer_purchases auf';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('Lade offer_purchases ohne company_name...', 'yellow');

        // Hole alle Purchases ohne company_name
        $purchases = $db->table('offer_purchases')
            ->where('company_name IS NULL')
            ->orWhere('external_user_id IS NULL')
            ->get()
            ->getResultArray();

        $total = count($purchases);
        CLI::write("Gefunden: {$total} Einträge", 'green');

        if ($total === 0) {
            CLI::write('Nichts zu tun!', 'green');
            return;
        }

        $updated = 0;
        $failed = 0;

        foreach ($purchases as $purchase) {
            $userId = $purchase['user_id'];

            // Hole User-Daten
            $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

            if (!$user) {
                CLI::write("User #{$userId} nicht gefunden (Purchase #{$purchase['id']})", 'red');
                $failed++;
                continue;
            }

            // Update purchase
            $db->table('offer_purchases')
                ->where('id', $purchase['id'])
                ->update([
                    'company_name' => $user['company_name'] ?? null,
                    'external_user_id' => $userId
                ]);

            $updated++;

            if ($updated % 100 === 0) {
                CLI::write("Fortschritt: {$updated}/{$total}", 'yellow');
            }
        }

        CLI::write('', 'white');
        CLI::write("Fertig! Aktualisiert: {$updated}, Fehler: {$failed}", 'green');
    }
}
