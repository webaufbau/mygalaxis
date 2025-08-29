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
            $categories    = $this->request->getPost('categories') ?? [];
            $discountRules = $this->request->getPost('discountRules') ?? [];

            if ($manager->save($categories, $discountRules)) {
                return redirect()->back()->with('message', 'Einstellungen erfolgreich gespeichert.');
            }

            return redirect()->back()->with('error', 'Fehler beim Speichern.');
        }

        $values = $manager->getAll();

        return view('admin/category_settings', [
            'categories'    => $values['categories'],
            'discountRules' => $values['discountRules']
        ]);
    }



}
