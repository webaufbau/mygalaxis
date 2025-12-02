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

    /**
     * Gibt alle PLZ im Umkreis einer gegebenen PLZ zurück
     *
     * @param string $zipcode Die Ausgangs-PLZ
     * @param int $radiusKm Radius in Kilometern
     * @param string $country_code Land (Standard: CH)
     * @return array Liste von PLZ mit Distanz und Ort
     */
    public function getZipcodesInRadius(string $zipcode, int $radiusKm = 20, string $country_code = 'CH'): array
    {
        // Hole Koordinaten der Ausgangs-PLZ
        $origin = $this->db->table('zipcodes')
            ->select('latitude, longitude, place, canton')
            ->where('zipcode', $zipcode)
            ->where('country_code', $country_code)
            ->get()
            ->getRowArray();

        if (!$origin || !$origin['latitude'] || !$origin['longitude']) {
            return [];
        }

        $lat = (float)$origin['latitude'];
        $lng = (float)$origin['longitude'];

        // Haversine-Formel für Distanzberechnung
        // 6371 = Erdradius in km
        $sql = "
            SELECT
                zipcode,
                place,
                canton,
                latitude,
                longitude,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance_km
            FROM zipcodes
            WHERE country_code = ?
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            HAVING distance_km <= ?
            ORDER BY distance_km ASC
        ";

        $results = $this->db->query($sql, [$lat, $lng, $lat, $country_code, $radiusKm])->getResultArray();

        return $results;
    }

    /**
     * Gibt Infos zu einer PLZ zurück
     *
     * @param string $zipcode PLZ
     * @param string $country_code Land
     * @return array|null
     */
    public function getZipcodeInfo(string $zipcode, string $country_code = 'CH'): ?array
    {
        return $this->db->table('zipcodes')
            ->where('zipcode', $zipcode)
            ->where('country_code', $country_code)
            ->get()
            ->getRowArray();
    }
}
