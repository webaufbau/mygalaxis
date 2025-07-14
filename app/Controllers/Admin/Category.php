<?php

namespace App\Controllers\Admin;

use App\Controllers\Crud;
use App\Libraries\CategoryManager;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Category extends AdminBase {

    protected string $url_prefix = 'admin/';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->set('text_add_new', '+ Neue Kategorie hinzufügen');
    }

    public function index()
    {
        $manager = new CategoryManager();

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost('categories');

            if ($manager->save($data)) {
                return redirect()->back()->with('message', 'Kategorien erfolgreich gespeichert.');
            }

            return redirect()->back()->with('error', 'Fehler beim Speichern.');
        }

        $categories = $manager->getAll();

        return view('admin/category_settings', ['categories' => $categories]);
    }


}
