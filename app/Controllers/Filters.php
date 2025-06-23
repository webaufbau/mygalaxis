<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Filters extends Controller
{
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login231');
        }

        $db = \Config\Database::connect();

        // Alle Zeilen aus zipcodes holen, sortiert nach state und province
        $query = $db->table('zipcodes')
            ->select('canton, state, state_code, province, community')
            ->where('country_code', 'CH')
            ->orderBy('canton', 'ASC')
            ->orderBy('province', 'ASC')
            ->get();

        $results = $query->getResult();

        $cantons = [];

        foreach ($results as $row) {
            // Kanton anlegen, falls noch nicht vorhanden
            if (!isset($cantons[$row->canton])) {
                $cantons[$row->canton] = [
                    'code' => $row->state_code,
                    'regions' => []
                ];
            }

            // Regionen (provinces)
            if (!isset($cantons[$row->canton]['regions'][$row->province])) {
                $cantons[$row->canton]['regions'][$row->province] = [
                    'communities' => []
                ];
            }

            // Community hinzufügen
            $cantons[$row->canton]['regions'][$row->province]['communities'][] = $row->community;
        }

        // Jetzt für jede Region die Communities als String zusammenfassen
        foreach ($cantons as $state => &$stateData) {
            foreach ($stateData['regions'] as $province => &$regionData) {
                // Duplikate entfernen, sortieren und als String zusammenfügen
                $uniqueCommunities = array_unique($regionData['communities']);
                sort($uniqueCommunities);
                $regionData['communities'] = implode(', ', $uniqueCommunities);
            }
        }

        $data = [
            'cantons' => $cantons,
            // weitere Daten
        ];

        return view('account/filter', $data);
    }

}
