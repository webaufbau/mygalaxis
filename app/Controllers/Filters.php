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

        // Übersetze Branchen-Namen in die aktuelle Sprache
        $translatedTypes = [];
        foreach ($categoryOptions->categoryTypes as $typeKey => $typeName) {
            $translatedTypes[$typeKey] = lang('Filter.' . $typeKey) ?: lang('Offers.type.' . $typeKey);
        }

        // Projekte laden
        $projectModel = new \App\Models\ProjectModel();
        $projects = $projectModel->getActiveProjectsWithNames();

        // User's filter_projects als Array
        $userFilterProjects = [];
        if (!empty($user->filter_projects)) {
            $decoded = json_decode($user->filter_projects, true);
            $userFilterProjects = is_array($decoded) ? $decoded : explode(',', $user->filter_projects);
        }

        $data = [
            'cantons' => $cantons,
            'categories' => $translatedTypes,
            'types' => $translatedTypes,
            'projects' => $projects,
            'languages' => $appConfig->supportedLocales,
            'user_filters' => [
                'filter_categories' => explode(',', $user->filter_categories ?? ''),
                'filter_projects' => $userFilterProjects,
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
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $user = auth()->user();
        $userId = $user->id;

        $postData = $this->request->getPost();

        $userModel = new \App\Models\UserModel();

        $data = [
            'id' => $userId,
            'filter_categories' => isset($postData['filter_categories']) ? implode(',', $postData['filter_categories']) : '',
            'filter_projects' => isset($postData['filter_projects']) ? json_encode($postData['filter_projects']) : '[]',
            'filter_cantons' => isset($postData['cantons']) ? implode(',', $postData['cantons']) : '',
            'filter_regions' => isset($postData['regions']) ? implode(',', $postData['regions']) : '',
            'filter_custom_zip' => $postData['custom_zip'] ?? '',
        ];

        $userModel->save($data);

        // Wenn AJAX -> JSON zurückgeben
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Filter.messageFilterSaved'),
                'csrf_name' => csrf_token(),       // Token-Name
                'csrf_hash' => csrf_hash()         // neuer Token-Wert
            ]);
        }

        return redirect()->to('/filter')->with('message', lang('Filter.messageFilterSaved'));
    }


}
