<?php

namespace App\Controllers;

use App\Libraries\CategoryManager;
use App\Models\ProjectModel;

class Request extends BaseController
{
    public function start()
    {
        // Branchen aus CategoryManager laden
        $categoryManager = new CategoryManager();
        $categoryData = $categoryManager->getAll();
        $categories = $categoryData['categories'] ?? [];

        // Projekte aus DB laden
        $projectModel = new ProjectModel();
        $projects = $projectModel->getActiveProjectsWithNames();

        // Initial ausgewählte Branche (aus URL-Parameter)
        $initial = $this->request->getGet('initial');
        $lang = $this->request->getGet('lang') ?? service('request')->getLocale();

        return view('request/start', [
            'categories' => $categories,
            'projects' => $projects,
            'initial' => $initial,
            'lang' => $lang,
        ]);
    }

    public function submit()
    {
        // Ausgewählte Branchen und Projekte
        $selectedCategories = $this->request->getPost('categories') ?? [];
        $selectedProjects = $this->request->getPost('projects') ?? [];

        // Kontaktdaten
        $contact = [
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'street' => $this->request->getPost('street'),
            'zip' => $this->request->getPost('zip'),
            'city' => $this->request->getPost('city'),
        ];

        // Arbeitsbeginn
        $workStart = [
            'date' => $this->request->getPost('work_start_date'),
            'flexibility' => $this->request->getPost('flexibility'),
        ];

        // Validierung
        if (empty($selectedCategories) && empty($selectedProjects)) {
            return redirect()->back()->withInput()->with('error', 'Bitte wähle mindestens eine Branche oder ein Projekt aus.');
        }

        if (empty($contact['firstname']) || empty($contact['lastname']) || empty($contact['email']) || empty($contact['phone'])) {
            return redirect()->back()->withInput()->with('error', 'Bitte fülle alle Kontaktfelder aus.');
        }

        // Session erstellen
        $sessionId = bin2hex(random_bytes(16));

        // Daten in Session speichern
        $sessionData = [
            'id' => $sessionId,
            'categories' => $selectedCategories,
            'projects' => $selectedProjects,
            'contact' => $contact,
            'work_start' => $workStart,
            'current_index' => 0,
            'form_data' => [],
            'created_at' => time(),
        ];

        session()->set('request_' . $sessionId, $sessionData);

        // Formular-Links zusammenstellen
        $formLinks = $this->getFormLinks($selectedCategories, $selectedProjects);

        if (empty($formLinks)) {
            return redirect()->back()->withInput()->with('error', 'Für die ausgewählten Branchen/Projekte sind keine Formular-Links hinterlegt.');
        }

        // Session mit Formular-Links aktualisieren
        $sessionData['form_links'] = $formLinks;
        session()->set('request_' . $sessionId, $sessionData);

        // Zum ersten Formular weiterleiten
        $firstLink = $formLinks[0]['url'];
        $separator = strpos($firstLink, '?') !== false ? '&' : '?';

        return redirect()->to($firstLink . $separator . 'session=' . $sessionId . '&mode=multi');
    }

    /**
     * Formular-Links für ausgewählte Branchen/Projekte zusammenstellen
     */
    protected function getFormLinks(array $categories, array $projects): array
    {
        $categoryManager = new CategoryManager();
        $categoryData = $categoryManager->getAll();
        $allCategories = $categoryData['categories'] ?? [];

        $projectModel = new ProjectModel();

        $links = [];

        // Branchen-Links
        foreach ($categories as $categoryKey) {
            if (isset($allCategories[$categoryKey])) {
                $cat = $allCategories[$categoryKey];
                $formLink = $cat['form_link'] ?? '';

                if (!empty($formLink)) {
                    $links[] = [
                        'type' => 'category',
                        'key' => $categoryKey,
                        'name' => $cat['name'],
                        'url' => $formLink,
                    ];
                }
            }
        }

        // Projekt-Links (über zugewiesene Branche)
        foreach ($projects as $projectSlug) {
            $project = $projectModel->findBySlug($projectSlug);
            if ($project && !empty($project['category_type'])) {
                $categoryKey = $project['category_type'];
                if (isset($allCategories[$categoryKey])) {
                    $cat = $allCategories[$categoryKey];
                    $formLink = $cat['form_link'] ?? '';

                    if (!empty($formLink)) {
                        // Prüfen ob dieser Link nicht schon hinzugefügt wurde
                        $alreadyAdded = false;
                        foreach ($links as $link) {
                            if ($link['url'] === $formLink) {
                                $alreadyAdded = true;
                                break;
                            }
                        }

                        if (!$alreadyAdded) {
                            $links[] = [
                                'type' => 'project',
                                'key' => $projectSlug,
                                'name' => $project['name_de'],
                                'category' => $categoryKey,
                                'url' => $formLink,
                            ];
                        }
                    }
                }
            }
        }

        return $links;
    }

    /**
     * Debug: Session-Daten anzeigen
     */
    public function debug($sessionId = null)
    {
        if (!$sessionId) {
            return 'No session ID';
        }

        $data = session()->get('request_' . $sessionId);

        if (!$data) {
            return 'Session not found';
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
