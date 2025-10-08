<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class ZipcodeService
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Gibt eine Liste aller PLZ (zipcodes) zurück, die entweder zum Kanton oder zur Region gehören.
     *
     * @param array $cantons  Liste von Kantonen (z. B. "Basel-Landschaft")
     * @param array $regions  Liste von Regionen (z. B. "Bezirk Arlesheim")
     * @return array Liste der passenden Zipcodes
     */
    public function getZipsByCantonAndRegion(array $cantons, array $regions, string $country_code = 'CH'): array
    {
        $builder = $this->db->table('zipcodes')->select('zipcode');

        // Country fixieren (CH)
        $builder->where('country_code', $country_code);

        // Nur falls überhaupt etwas vorhanden ist
        if (!empty($cantons)) {
            $builder->groupStart()
                ->whereIn('canton', $cantons)
                ->groupEnd();
        }

        if (!empty($regions)) {
            $builder->groupStart()
                ->orWhereIn('province', $regions)
                ->groupEnd();
        }

        $results = $builder->get()->getResultArray();

        // Nur die Zipcodes extrahieren
        return array_column($results, 'zipcode');
    }
}
