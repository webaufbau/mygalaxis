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

        // Formular-Links zusammenstellen
        $locale = service('request')->getLocale();
        $formLinks = $this->getFormLinks($selectedForms, $selectedProjects, $locale);

        if (empty($formLinks)) {
            return redirect()->back()->withInput()->with('error', 'Für die ausgewählten Formulare/Projekte sind keine Links hinterlegt.');
        }

        // Daten in Session speichern
        $sessionData = [
            'id' => $sessionId,
            'forms' => $selectedForms,
            'projects' => $selectedProjects,
            'form_links' => $formLinks,
            'current_index' => 0,
            'total_forms' => count($formLinks),
            'completed_forms' => [],
            'created_at' => time(),
        ];

        session()->set('request_' . $sessionId, $sessionData);

        // Zum ersten Formular weiterleiten
        return $this->redirectToForm($sessionId, 0);
    }

    /**
     * Wird aufgerufen nachdem ein WordPress-Formular abgeschlossen wurde
     */
    public function next()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Aktuelles Formular als erledigt markieren
        $currentIndex = $sessionData['current_index'];
        $sessionData['completed_forms'][] = $currentIndex;
        $sessionData['current_index'] = $currentIndex + 1;

        session()->set('request_' . $sessionId, $sessionData);

        // Prüfen ob noch Formulare übrig sind
        if ($sessionData['current_index'] < $sessionData['total_forms']) {
            // Zum nächsten Formular weiterleiten
            return $this->redirectToForm($sessionId, $sessionData['current_index']);
        }

        // Alle Formulare erledigt → Zur Finalisierung
        return redirect()->to('/request/finalize?session=' . $sessionId);
    }

    /**
     * Finalisierung: Termin, Auftraggeber, Kontaktdaten, Verifikation
     */
    public function finalize()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Schritt aus URL oder Default
        $step = $this->request->getGet('step') ?? 'termin';

        return view('request/finalize', [
            'sessionId' => $sessionId,
            'sessionData' => $sessionData,
            'step' => $step,
        ]);
    }

    /**
     * Finalisierung speichern
     */
    public function saveFinalize()
    {
        $sessionId = $this->request->getPost('session');
        $step = $this->request->getPost('step');

        if (!$sessionId) {
            return redirect()->to('/request/start')->with('error', 'Keine Session gefunden.');
        }

        $sessionData = session()->get('request_' . $sessionId);

        if (!$sessionData) {
            return redirect()->to('/request/start')->with('error', 'Session abgelaufen.');
        }

        // Daten speichern je nach Schritt
        switch ($step) {
            case 'termin':
                $sessionData['termin'] = [
                    'datum' => $this->request->getPost('datum'),
                    'zeit' => $this->request->getPost('zeit'),
                    'flexibel' => $this->request->getPost('flexibel'),
                ];
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/finalize?session=' . $sessionId . '&step=auftraggeber');

            case 'auftraggeber':
                $sessionData['auftraggeber'] = [
                    'typ' => $this->request->getPost('typ'), // privat/firma
                    'firma' => $this->request->getPost('firma'),
                ];
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/finalize?session=' . $sessionId . '&step=kontakt');

            case 'kontakt':
                $sessionData['kontakt'] = [
                    'vorname' => $this->request->getPost('vorname'),
                    'nachname' => $this->request->getPost('nachname'),
                    'email' => $this->request->getPost('email'),
                    'telefon' => $this->request->getPost('telefon'),
                    'strasse' => $this->request->getPost('strasse'),
                    'plz' => $this->request->getPost('plz'),
                    'ort' => $this->request->getPost('ort'),
                ];
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/finalize?session=' . $sessionId . '&step=verify');

            case 'verify':
                // TODO: Verifikation durchführen (SMS/Email)
                $sessionData['verified'] = true;
                session()->set('request_' . $sessionId, $sessionData);
                return redirect()->to('/request/complete?session=' . $sessionId);
        }

        return redirect()->to('/request/finalize?session=' . $sessionId);
    }

    /**
     * Anfrage abgeschlossen
     */
    public function complete()
    {
        $sessionId = $this->request->getGet('session');

        if (!$sessionId) {
            return redirect()->to('/request/start');
        }

        $sessionData = session()->get('request_' . $sessionId);

        return view('request/complete', [
            'sessionId' => $sessionId,
            'sessionData' => $sessionData,
        ]);
    }

    /**
     * Leitet zum WordPress-Formular weiter mit allen nötigen Parametern
     */
    protected function redirectToForm(string $sessionId, int $index): \CodeIgniter\HTTP\RedirectResponse
    {
        $sessionData = session()->get('request_' . $sessionId);
        $formLink = $sessionData['form_links'][$index];

        $url = $formLink['url'];
        $separator = strpos($url, '?') !== false ? '&' : '?';

        // Parameter für WordPress
        $params = http_build_query([
            'session' => $sessionId,
            'index' => $index,
            'total' => $sessionData['total_forms'],
        ]);

        return redirect()->to($url . $separator . $params);
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
