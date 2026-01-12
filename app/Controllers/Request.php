<?php

namespace App\Controllers;

use App\Libraries\CategoryManager;
use App\Models\ProjectModel;

class Request extends BaseController
{
    public function start()
    {
        // Formulare aus CategoryManager laden
        $categoryManager = new CategoryManager();
        $locale = $this->request->getGet('lang') ?? service('request')->getLocale();
        $forms = $categoryManager->getAllForms($locale);

        // Projekte aus DB laden
        $projectModel = new ProjectModel();
        $projects = $projectModel->getActiveProjectsWithNames($locale);

        // Initial ausgewähltes Formular (aus URL-Parameter)
        $initial = $this->request->getGet('initial');

        return view('request/start', [
            'forms' => $forms,
            'projects' => $projects,
            'initial' => $initial,
            'lang' => $locale,
            'categoryManager' => $categoryManager,
        ]);
    }

    public function submit()
    {
        // Ausgewählte Formulare und Projekte
        $selectedForms = $this->request->getPost('forms') ?? [];
        $selectedProjects = $this->request->getPost('projects') ?? [];

        // Validierung
        if (empty($selectedForms) && empty($selectedProjects)) {
            return redirect()->back()->withInput()->with('error', 'Bitte wähle mindestens ein Formular oder ein Projekt aus.');
        }

        // Session erstellen
        $sessionId = bin2hex(random_bytes(16));

        // Daten in Session speichern
        $sessionData = [
            'id' => $sessionId,
            'forms' => $selectedForms,
            'projects' => $selectedProjects,
            'current_index' => 0,
            'form_data' => [],
            'created_at' => time(),
        ];

        session()->set('request_' . $sessionId, $sessionData);

        // Formular-Links zusammenstellen
        $locale = service('request')->getLocale();
        $formLinks = $this->getFormLinks($selectedForms, $selectedProjects, $locale);

        if (empty($formLinks)) {
            return redirect()->back()->withInput()->with('error', 'Für die ausgewählten Formulare/Projekte sind keine Links hinterlegt.');
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
     * Formular-Links für ausgewählte Formulare/Projekte zusammenstellen
     */
    protected function getFormLinks(array $formIds, array $projects, string $locale = 'de'): array
    {
        $categoryManager = new CategoryManager();
        $projectModel = new ProjectModel();

        $links = [];
        $addedUrls = []; // Um Duplikate zu vermeiden

        // Formular-Links (direkt ausgewählt)
        foreach ($formIds as $formId) {
            $form = $categoryManager->getFormById($formId, $locale);
            if ($form && !empty($form['form_link'])) {
                if (!in_array($form['form_link'], $addedUrls)) {
                    $links[] = [
                        'type' => 'form',
                        'form_id' => $formId,
                        'name' => $form['name'],
                        'category_key' => $form['category_key'],
                        'url' => $form['form_link'],
                    ];
                    $addedUrls[] = $form['form_link'];
                }
            }
        }

        // Projekt-Links (über zugewiesenes Formular)
        foreach ($projects as $projectSlug) {
            $project = $projectModel->findBySlug($projectSlug);
            if ($project && !empty($project['form_id'])) {
                $form = $categoryManager->getFormById($project['form_id'], $locale);
                if ($form && !empty($form['form_link'])) {
                    if (!in_array($form['form_link'], $addedUrls)) {
                        $links[] = [
                            'type' => 'project',
                            'key' => $projectSlug,
                            'name' => $project['name_de'],
                            'form_id' => $project['form_id'],
                            'url' => $form['form_link'],
                        ];
                        $addedUrls[] = $form['form_link'];
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
