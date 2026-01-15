<?php

namespace App\Controllers\Admin;

use App\Libraries\CategoryManager;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Form extends AdminBase
{
    protected string $url_prefix = 'admin/';
    protected CategoryManager $categoryManager;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->categoryManager = new CategoryManager();
    }

    public function index()
    {
        if (!auth()->user()->can('my.category_view')) {
            return redirect()->to('/');
        }

        $data = $this->categoryManager->getAll();
        $forms = [];

        // Alle Formulare aus allen Kategorien sammeln
        foreach ($data['categories'] as $catKey => $cat) {
            $categoryForms = $cat['forms'] ?? [];
            foreach ($categoryForms as $index => $form) {
                $forms[] = [
                    'form_id' => $catKey . ':' . $index,
                    'category_key' => $catKey,
                    'category_name' => $cat['name'],
                    'category_color' => $cat['color'] ?? '#6c757d',
                    'category_hidden' => !empty($cat['hidden']),
                    'form_index' => $index,
                    'name_de' => $form['name_de'] ?? '',
                    'name_en' => $form['name_en'] ?? '',
                    'name_fr' => $form['name_fr'] ?? '',
                    'name_it' => $form['name_it'] ?? '',
                    'form_link_de' => $form['form_link_de'] ?? '',
                    'form_link_en' => $form['form_link_en'] ?? '',
                    'form_link_fr' => $form['form_link_fr'] ?? '',
                    'form_link_it' => $form['form_link_it'] ?? '',
                ];
            }
        }

        // Nach Kategorie-Name sortieren
        usort($forms, function($a, $b) {
            $catCompare = strcmp($a['category_name'], $b['category_name']);
            if ($catCompare !== 0) return $catCompare;
            return $a['form_index'] - $b['form_index'];
        });

        return view('admin/forms/index', [
            'page_title' => 'Formulare',
            'forms' => $forms,
            'categories' => $data['categories'],
        ]);
    }

    public function edit(string $formId = '')
    {
        if (!auth()->user()->can('my.category_edit')) {
            return redirect()->to('/');
        }

        // Form ID aufteilen (format: "category_key:index")
        $parts = explode(':', $formId);
        if (count($parts) !== 2) {
            $this->setFlash('Ungültige Formular-ID', 'error');
            return redirect()->to('/admin/form');
        }

        [$catKey, $formIndex] = $parts;
        $formIndex = (int) $formIndex;

        $data = $this->categoryManager->getAll();

        if (!isset($data['categories'][$catKey])) {
            $this->setFlash('Kategorie nicht gefunden', 'error');
            return redirect()->to('/admin/form');
        }

        $category = $data['categories'][$catKey];
        $forms = $category['forms'] ?? [];

        if (!isset($forms[$formIndex])) {
            $this->setFlash('Formular nicht gefunden', 'error');
            return redirect()->to('/admin/form');
        }

        $form = $forms[$formIndex];

        // POST verarbeiten
        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getPost();
            $newCatKey = $postData['category_key'] ?? $catKey;

            // Formular-Daten
            $formData = [
                'name_de' => $postData['name_de'] ?? '',
                'name_en' => $postData['name_en'] ?? '',
                'name_fr' => $postData['name_fr'] ?? '',
                'name_it' => $postData['name_it'] ?? '',
                'form_link_de' => $postData['form_link_de'] ?? '',
                'form_link_en' => $postData['form_link_en'] ?? '',
                'form_link_fr' => $postData['form_link_fr'] ?? '',
                'form_link_it' => $postData['form_link_it'] ?? '',
            ];

            // Prüfen ob Branche geändert wurde
            if ($newCatKey !== $catKey) {
                // Formular aus alter Kategorie entfernen
                array_splice($forms, $formIndex, 1);
                $data['categories'][$catKey]['forms'] = array_values($forms);

                // Formular zu neuer Kategorie hinzufügen
                $data['categories'][$newCatKey]['forms'][] = $formData;

                $newIndex = count($data['categories'][$newCatKey]['forms']) - 1;
                $newFormId = $newCatKey . ':' . $newIndex;

                if ($this->categoryManager->save($data['categories'], $data['discountRules'] ?? [])) {
                    $this->setFlash("Formular in Branche '{$data['categories'][$newCatKey]['name']}' verschoben. Neue ID: {$newFormId}", 'success');
                    return redirect()->to('/admin/form');
                } else {
                    $this->setFlash('Fehler beim Speichern', 'error');
                }
            } else {
                // Nur Formular aktualisieren (gleiche Kategorie)
                $forms[$formIndex] = $formData;
                $data['categories'][$catKey]['forms'] = $forms;

                if ($this->categoryManager->save($data['categories'], $data['discountRules'] ?? [])) {
                    $this->setFlash('Formular gespeichert', 'success');
                    return redirect()->to('/admin/form');
                } else {
                    $this->setFlash('Fehler beim Speichern', 'error');
                }
            }
        }

        // Formular-Anzahl pro Kategorie für JavaScript
        $formCounts = [];
        foreach ($data['categories'] as $key => $cat) {
            $formCounts[$key] = count($cat['forms'] ?? []);
        }

        return view('admin/forms/edit', [
            'page_title' => 'Formular bearbeiten',
            'form_id' => $formId,
            'form' => $form,
            'category' => $category,
            'category_key' => $catKey,
            'categories' => $data['categories'],
            'form_counts' => $formCounts,
        ]);
    }

    public function create()
    {
        if (!auth()->user()->can('my.category_edit')) {
            return redirect()->to('/');
        }

        $data = $this->categoryManager->getAll();

        // POST verarbeiten
        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getPost();
            $catKey = $postData['category_key'] ?? '';

            if (empty($catKey) || !isset($data['categories'][$catKey])) {
                $this->setFlash('Bitte eine Kategorie auswählen', 'error');
            } elseif (empty($postData['name_de'])) {
                $this->setFlash('Bitte einen deutschen Namen eingeben', 'error');
            } else {
                // Neues Formular zur Kategorie hinzufügen
                $newForm = [
                    'name_de' => $postData['name_de'] ?? '',
                    'name_en' => $postData['name_en'] ?? '',
                    'name_fr' => $postData['name_fr'] ?? '',
                    'name_it' => $postData['name_it'] ?? '',
                    'form_link_de' => $postData['form_link_de'] ?? '',
                    'form_link_en' => $postData['form_link_en'] ?? '',
                    'form_link_fr' => $postData['form_link_fr'] ?? '',
                    'form_link_it' => $postData['form_link_it'] ?? '',
                ];

                $data['categories'][$catKey]['forms'][] = $newForm;

                if ($this->categoryManager->save($data['categories'], $data['discountRules'] ?? [])) {
                    $this->setFlash('Formular erstellt', 'success');
                    return redirect()->to('/admin/form');
                } else {
                    $this->setFlash('Fehler beim Speichern', 'error');
                }
            }
        }

        return view('admin/forms/create', [
            'page_title' => 'Neues Formular',
            'categories' => $data['categories'],
        ]);
    }

    public function delete(string $formId = '')
    {
        if (!auth()->user()->can('my.category_edit')) {
            return redirect()->to('/');
        }

        // Form ID aufteilen
        $parts = explode(':', $formId);
        if (count($parts) !== 2) {
            $this->setFlash('Ungültige Formular-ID', 'error');
            return redirect()->to('/admin/form');
        }

        [$catKey, $formIndex] = $parts;
        $formIndex = (int) $formIndex;

        $data = $this->categoryManager->getAll();

        if (!isset($data['categories'][$catKey])) {
            $this->setFlash('Kategorie nicht gefunden', 'error');
            return redirect()->to('/admin/form');
        }

        $forms = $data['categories'][$catKey]['forms'] ?? [];

        if (!isset($forms[$formIndex])) {
            $this->setFlash('Formular nicht gefunden', 'error');
            return redirect()->to('/admin/form');
        }

        // Formular entfernen und Array neu indizieren
        array_splice($forms, $formIndex, 1);
        $data['categories'][$catKey]['forms'] = array_values($forms);

        if ($this->categoryManager->save($data['categories'], $data['discountRules'] ?? [])) {
            $this->setFlash('Formular gelöscht', 'success');
        } else {
            $this->setFlash('Fehler beim Löschen', 'error');
        }

        return redirect()->to('/admin/form');
    }
}
