<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\CategoryManager;
use CodeIgniter\API\ResponseTrait;

class FormMappings extends BaseController
{
    use ResponseTrait;

    /**
     * Get form mappings for WordPress redirect
     * Returns: wordpress-path => category:form_index
     *
     * GET /api/form-mappings
     * Optional: ?lang=de (default: de)
     */
    public function index()
    {
        // CORS Headers für WordPress
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

        if ($this->request->getMethod() === 'options') {
            return $this->respond(null, 200);
        }

        $locale = $this->request->getGet('lang') ?? 'de';
        $categoryManager = new CategoryManager();

        // Alle Formulare holen (inkl. versteckte für vollständige Mappings)
        $forms = $categoryManager->getAllForms($locale, true);

        $mappings = [];

        foreach ($forms as $form) {
            $formLink = $form['form_link'] ?? '';

            if (empty($formLink)) {
                continue;
            }

            // URL parsen und Pfad extrahieren
            $parsed = parse_url($formLink);
            $path = trim($parsed['path'] ?? '', '/');

            if (empty($path)) {
                continue;
            }

            // Mapping erstellen: pfad => category:index
            $formId = $form['form_id']; // z.B. "electrician:0"

            $mappings[] = [
                'path' => $path,
                'form_id' => $formId,
                'category_key' => $form['category_key'],
                'form_index' => $form['form_index'],
                'name' => $form['name'],
            ];
        }

        return $this->respond([
            'success' => true,
            'mappings' => $mappings,
            'count' => count($mappings),
        ]);
    }
}
