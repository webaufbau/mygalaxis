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

    /**
     * Export der Branchen-Konfiguration als JSON
     */
    public function export()
    {
        $manager = new CategoryManager();
        $data = $manager->getAll();

        // Export-Daten vorbereiten (nur relevante Felder)
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'categories' => $data['categories'],
            'discountRules' => $data['discountRules'],
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'branchen_export_' . date('Y-m-d_His') . '.json';

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($json);
    }

    /**
     * Import der Branchen-Konfiguration aus JSON
     */
    public function import()
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/admin/category')->with('error', 'Ungültige Anfrage.');
        }

        $file = $this->request->getFile('import_file');

        if (!$file || !$file->isValid()) {
            return redirect()->to('/admin/category')->with('error', 'Keine gültige Datei hochgeladen.');
        }

        // Prüfen ob JSON
        if ($file->getClientMimeType() !== 'application/json' && $file->getClientExtension() !== 'json') {
            return redirect()->to('/admin/category')->with('error', 'Nur JSON-Dateien sind erlaubt.');
        }

        // Datei lesen
        $content = file_get_contents($file->getTempName());
        $importData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->to('/admin/category')->with('error', 'Ungültiges JSON-Format: ' . json_last_error_msg());
        }

        // Validierung
        if (!isset($importData['categories']) || !is_array($importData['categories'])) {
            return redirect()->to('/admin/category')->with('error', 'Import-Datei enthält keine gültigen Branchen-Daten.');
        }

        // Import durchführen
        $manager = new CategoryManager();
        $categories = $importData['categories'];
        $discountRules = $importData['discountRules'] ?? [];

        if ($manager->save($categories, $discountRules)) {
            $count = count($categories);
            return redirect()->to('/admin/category')->with('message', "Import erfolgreich: {$count} Branchen importiert.");
        }

        return redirect()->to('/admin/category')->with('error', 'Fehler beim Importieren.');
    }
}
