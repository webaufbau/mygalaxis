<?php

namespace App\Controllers\Admin;

use App\Models\ProjectModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Projects extends AdminBase
{
    protected ProjectModel $projectModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->projectModel = new ProjectModel();
    }

    public function index()
    {
        $projects = $this->projectModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name_de', 'ASC')
            ->findAll();

        return view('admin/projects/index', [
            'projects' => $projects
        ]);
    }

    public function create()
    {
        return view('admin/projects/form', [
            'project' => null,
            'title' => 'Neues Projekt erstellen'
        ]);
    }

    public function store()
    {
        $data = [
            'slug' => $this->request->getPost('slug'),
            'name_de' => $this->request->getPost('name_de'),
            'name_en' => $this->request->getPost('name_en'),
            'name_fr' => $this->request->getPost('name_fr'),
            'name_it' => $this->request->getPost('name_it'),
            'form_link' => $this->request->getPost('form_link'),
            'color' => $this->request->getPost('color') ?: '#6c757d',
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

        return view('admin/projects/form', [
            'project' => $project,
            'title' => 'Projekt bearbeiten'
        ]);
    }

    public function update($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            return redirect()->to('/admin/projects')->with('error', 'Projekt nicht gefunden.');
        }

        $data = [
            'slug' => $this->request->getPost('slug'),
            'name_de' => $this->request->getPost('name_de'),
            'name_en' => $this->request->getPost('name_en'),
            'name_fr' => $this->request->getPost('name_fr'),
            'name_it' => $this->request->getPost('name_it'),
            'form_link' => $this->request->getPost('form_link'),
            'color' => $this->request->getPost('color') ?: '#6c757d',
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($this->projectModel->update($id, $data)) {
            return redirect()->to('/admin/projects')->with('message', 'Projekt erfolgreich aktualisiert.');
        }

        return redirect()->back()->withInput()->with('error', 'Fehler beim Aktualisieren.');
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
        $order = $this->request->getPost('order');

        if (!is_array($order)) {
            return $this->response->setJSON(['success' => false]);
        }

        foreach ($order as $position => $id) {
            $this->projectModel->update($id, ['sort_order' => $position]);
        }

        return $this->response->setJSON(['success' => true]);
    }
}
