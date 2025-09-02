<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Filters extends Controller
{
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth');
        }

        $db = \Config\Database::connect();

        // Alle Zeilen aus zipcodes holen, sortiert nach state und province
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $query = $db->table('zipcodes')
            ->select('canton, state, state_code, province, community')
            ->where('country_code', $siteCountry)
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

        $user = auth()->user();

        $categoryOptions = new \Config\CategoryOptions();
        $appConfig = new \Config\App();

        $data = [
            'cantons' => $cantons,
            'categories' => $categoryOptions->categoryTypes,
            'types' => $categoryOptions->categoryTypes,
            'languages' => $appConfig->supportedLocales,
            'user_filters' => [
                'filter_categories' => explode(',', $user->filter_categories ?? ''),
                'filter_cantons' => explode(',', $user->filter_cantons ?? ''),
                'filter_regions' => explode(',', $user->filter_regions ?? ''),
                'min_rooms' => $user->min_rooms,
                'filter_custom_zip' => $user->filter_custom_zip,
            ]
        ];

        return view('account/filter', $data);
    }

    public function save()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/auth');
        }

        $user = auth()->user();
        $userId = $user->id;

        $postData = $this->request->getPost();

        $userModel = new \App\Models\UserModel();

        // Nur Daten, die in allowedFields definiert sind
        $data = [
            'id' => $userId,
            'filter_categories' => isset($postData['filter_categories']) ? implode(',', $postData['filter_categories']) : '',
            'filter_cantons' => isset($postData['cantons']) ? implode(',', $postData['cantons']) : '',
            'filter_regions' => isset($postData['regions']) ? implode(',', $postData['regions']) : '',
            //'min_rooms' => $postData['min_rooms'] ?? '',
            'filter_custom_zip' => $postData['custom_zip'] ?? '',
            // Weitere Felder kannst du hier auch hinzufügen...
        ];

        $userModel->save($data);

        return redirect()->to('/filter')->with('message', lang('Filter.messageFilterSaved'));
    }

}
