<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class OfferAutoBuy extends BaseCommand
{
    protected $group       = 'Offerten';
    protected $name        = 'offer:autobuy';
    protected $description = 'Kauft automatisch neue Offerten, wenn kein Sperrdatum gesetzt ist.';

    public function run(array $params)
    {
        $today = date('Y-m-d');
        $db = \Config\Database::connect();

        $userTable = $db->table('users');
        $blockedTable = $db->table('blocked_days');

        $users = $userTable
            ->select('id, firstname, lastname, email')
            ->where('auto_offer_buy', 1)
            ->get()
            ->getResult();

        if (empty($users)) {
            $this->log('Keine Nutzer mit aktiviertem Auto-Kauf gefunden.', 'yellow');
            return;
        }

        foreach ($users as $user) {
            $isBlocked = $blockedTable
                ->where('user_id', $user->id)
                ->where('date', $today)
                ->countAllResults();

            if ($isBlocked) {
                $this->log("⏸  Benutzer #{$user->id} ({$user->email}) ist heute blockiert ({$today}).", 'blue');
                continue;
            }

            // Automatischen Kauf durchführen
            $this->handleAutoBuy($user);
        }

        $this->log('Auto-Buy-Verarbeitung abgeschlossen.', 'green');
    }

    protected function handleAutoBuy($user)
    {
        // Beispiel: Hier kannst du deinen Offerten-Kauf-Code einfügen
        // z. B. neue Offerte suchen, prüfen, kaufen, etc.

        // Dummy-Ausgabe zur Veranschaulichung:
        $this->log("✅  Auto-Kauf ausgeführt für Benutzer #{$user->id} ({$user->email})", 'green');

        // Hier könnte dein Service/Modell den eigentlichen Kauf abwickeln.
        // z. B.:
        // $this->offerService->autoBuyForUser($user->id);
    }

    protected function log(string $message, string $color = 'white')
    {
        $timestamp = date('[Y-m-d H:i:s]');
        CLI::write("{$timestamp} {$message}", $color);

        // Optional in Logdatei schreiben
        file_put_contents(WRITEPATH . 'logs/offer_autobuy.log', "{$timestamp} {$message}\n", FILE_APPEND);
    }
}
