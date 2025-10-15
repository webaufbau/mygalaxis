<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupTempContactData extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:cleanup-temp-contacts';
    protected $description = 'Löscht abgelaufene Kontaktdaten aus temp_contact_data';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Lösche alle Einträge die abgelaufen sind
        $deleted = $db->table('temp_contact_data')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();

        CLI::write('Gelöscht: ' . $deleted . ' abgelaufene Einträge aus temp_contact_data', 'green');

        return 0;
    }
}
