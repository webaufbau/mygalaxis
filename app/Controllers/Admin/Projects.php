<?php

namespace App\Controllers\Admin;

use App\Libraries\CategoryManager;
use App\Models\ProjectModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Projects extends AdminBase
{
    protected ProjectModel $projectModel;
    protected CategoryManager $categoryManager;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->projectModel = new ProjectModel();
        $this->categoryManager = new CategoryManager();
    }

    public function index()
    {
        $projects = $this->projectModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name_de', 'ASC')
            ->findAll();

        return view('admin/projects/index', [
            'projects' => $projects,
            'categoryManager' => $this->categoryManager,
        ]);
    }

    public function create()
    {
        $forms = $this->categoryManager->getAllForms('de');

        return view('admin/projects/form', [
            'project' => null,
            'title' => 'Neues Projekt erstellen',
            'forms' => $forms,
        ]);
    }

    public function store()
    {
        $formId = $this->request->getPost('form_id');

        // category_type aus form_id extrahieren (z.B. "move:0" -> "move")
        $categoryType = null;
        if (!empty($formId) && strpos($formId, ':') !== false) {
            $categoryType = explode(':', $formId)[0];
        }

        $data = [
            'slug' => $this->request->getPost('slug'),
            'name_de' => $this->request->getPost('name_de'),
            'name_en' => $this->request->getPost('name_en'),
            'name_fr' => $this->request->getPost('name_fr'),
            'name_it' => $this->request->getPost('name_it'),
            'form_id' => $formId,
            'category_type' => $categoryType,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($this->projectModel->insert($data)) {
            return redirect()->to('/admin/projects')->with('message', 'Projekt erfolgreich erstellt.');
        }

        return redirect()->back()->withInput()->with('error', 'Fehler beim Erstellen.');
    }

    public function edit($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Projekt nicht gefunden.');
        }

        $forms = $this->categoryManager->getAllForms('de');

        return view('admin/projects/form', [
            'project' => $project,
            'title' => 'Projekt bearbeiten',
            'forms' => $forms,
        ]);
    }

    public function update($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Projekt nicht gefunden.');
        }

        $formId = $this->request->getPost('form_id');

        // category_type aus form_id extrahieren
        $categoryType = null;
        if (!empty($formId) && strpos($formId, ':') !== false) {
            $categoryType = explode(':', $formId)[0];
        }

        $data = [
            'id' => $id, // Wichtig für {id} Platzhalter in Validierung
            'slug' => $this->request->getPost('slug'),
            'name_de' => $this->request->getPost('name_de'),
            'name_en' => $this->request->getPost('name_en'),
            'name_fr' => $this->request->getPost('name_fr'),
            'name_it' => $this->request->getPost('name_it'),
            'form_id' => $formId,
            'category_type' => $categoryType,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($this->projectModel->update($id, $data)) {
            return redirect()->to('/admin/projects')->with('message', 'Projekt erfolgreich aktualisiert.');
        }

        // Validation errors anzeigen
        $errors = $this->projectModel->errors();
        $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Fehler beim Aktualisieren.';
        return redirect()->back()->withInput()->with('error', $errorMsg);
    }

    public function delete($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Projekt nicht gefunden.');
        }

        if ($this->projectModel->delete($id)) {
            return redirect()->to('/admin/projects')->with('message', 'Projekt erfolgreich gelöscht.');
        }

        return redirect()->back()->with('error', 'Fehler beim Löschen.');
    }

    public function updateOrder()
    {
        $json = $this->request->getJSON(true);
        $order = $json['order'] ?? null;

        if (!is_array($order)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid order data']);
        }

        foreach ($order as $position => $id) {
            $this->projectModel->update($id, ['sort_order' => $position + 1]);
        }

        return $this->response->setJSON(['success' => true]);
    }
}
