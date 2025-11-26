<?php

namespace App\Controllers\Admin;

use App\Models\EmailTemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class EmailTemplates extends AdminBase
{
    protected EmailTemplateModel $templateModel;

    public function __construct()
    {
        $this->templateModel = new EmailTemplateModel();
    }

    /**
     * List all email templates
     */
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $templates = $this->templateModel->getAllGrouped();

        return view('admin/email_templates/index', [
            'title' => 'Templates',
            'templates' => $templates,
        ]);
    }

    /**
     * Create new template
     */
    public function create()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'offer_type'             => $this->request->getPost('offer_type'),
                'subtype'                => $this->request->getPost('subtype') ?: null, // Empty string => NULL
                'language'               => $this->request->getPost('language'),
                'subject'                => $this->request->getPost('subject'),
                'body_template'          => $this->request->getPost('body_template'),
                'field_display_template' => $this->request->getPost('field_display_template'),
                'is_active'              => $this->request->getPost('is_active') ? 1 : 0,
                'notes'                  => $this->request->getPost('notes'),
            ];

            if ($this->templateModel->insert($data)) {
                return redirect()->to('/admin/email-templates')->with('success', 'Template erfolgreich erstellt');
            }

            return redirect()->back()->withInput()->with('errors', $this->templateModel->errors());
        }

        // Load category types from config
        $categoryConfig = config('CategoryOptions');
        $offerTypes = $categoryConfig->categoryTypes;

        // Add 'default' option
        $offerTypes = array_merge(['default' => 'Standard (Fallback)'], $offerTypes);

        return view('admin/email_templates/form', [
            'title'      => 'Neues Template erstellen',
            'template'   => null,
            'action'     => 'create',
            'offerTypes' => $offerTypes,
        ]);
    }

    /**
     * Edit existing template
     */
    public function edit($id = null)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $template = $this->templateModel->find($id);

        if (!$template) {
            throw PageNotFoundException::forPageNotFound('Template nicht gefunden');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'offer_type'             => $this->request->getPost('offer_type'),
                'subtype'                => $this->request->getPost('subtype') ?: null, // Empty string => NULL
                'language'               => $this->request->getPost('language'),
                'subject'                => $this->request->getPost('subject'),
                'body_template'          => $this->request->getPost('body_template'),
                'field_display_template' => $this->request->getPost('field_display_template'),
                'is_active'              => $this->request->getPost('is_active') ? 1 : 0,
                'notes'                  => $this->request->getPost('notes'),
            ];

            if ($this->templateModel->update($id, $data)) {
                return redirect()->to('/admin/email-templates/edit/' . $id)->with('success', 'Template erfolgreich aktualisiert');
            }

            return redirect()->back()->withInput()->with('errors', $this->templateModel->errors());
        }

        // Load category types from config
        $categoryConfig = config('CategoryOptions');
        $offerTypes = $categoryConfig->categoryTypes;

        // Add 'default' option
        $offerTypes = array_merge(['default' => 'Standard (Fallback)'], $offerTypes);

        return view('admin/email_templates/form', [
            'title'      => 'Template bearbeiten',
            'template'   => $template,
            'action'     => 'edit',
            'offerTypes' => $offerTypes,
        ]);
    }

    /**
     * Copy template
     */
    public function copy($id = null)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/admin/email-templates')->with('error', 'Template nicht gefunden');
        }

        // Create a copy with modified name
        $copyData = [
            'offer_type'             => $template['offer_type'],
            'subtype'                => $template['subtype'] ?? null,
            'language'               => $template['language'],
            'subject'                => $template['subject'] . ' (Kopie)',
            'body_template'          => $template['body_template'],
            'field_display_template' => $template['field_display_template'] ?? null,
            'is_active'              => 0, // Copies are inactive by default
            'notes'                  => $template['notes'] ? $template['notes'] . ' (Kopie)' : 'Kopie',
        ];

        if ($this->templateModel->insert($copyData)) {
            $newId = $this->templateModel->getInsertID();
            return redirect()->to('/admin/email-templates/edit/' . $newId)
                           ->with('success', 'Template erfolgreich kopiert. Bitte bearbeiten Sie die Kopie.');
        }

        return redirect()->to('/admin/email-templates')->with('error', 'Fehler beim Kopieren');
    }

    /**
     * Delete template
     */
    public function delete($id = null)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/admin/email-templates')->with('error', 'Template nicht gefunden');
        }

        // Don't allow deletion of default template
        if ($template['offer_type'] === 'default' && $template['language'] === 'de') {
            return redirect()->to('/admin/email-templates')->with('error', 'Das Default-Template kann nicht gelöscht werden');
        }

        if ($this->templateModel->delete($id)) {
            return redirect()->to('/admin/email-templates')->with('success', 'Template erfolgreich gelöscht');
        }

        return redirect()->to('/admin/email-templates')->with('error', 'Fehler beim Löschen');
    }

    /**
     * Preview template with real offer data
     */
    public function preview($id = null)
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $template = $this->templateModel->find($id);

        if (!$template) {
            throw PageNotFoundException::forPageNotFound('Template nicht gefunden');
        }

        // Load offers for dropdown
        $offerModel = new \App\Models\OfferModel();

        // Get offer type from template
        $offerType = $template['offer_type'];

        // Load offers based on type
        if ($offerType === 'default') {
            // For default template, show offers from all types
            $offers = $offerModel->orderBy('created_at', 'DESC')->findAll(100);
        } else {
            // For specific type, show only offers of that type
            $offers = $offerModel->where('type', $offerType)
                                 ->orderBy('created_at', 'DESC')
                                 ->findAll(100);
        }

        // Get selected offer ID from request or use first offer
        $selectedOfferId = $this->request->getGet('offer_id');

        if (!$selectedOfferId && !empty($offers)) {
            $selectedOfferId = $offers[0]['id'];
        }

        // Load selected offer data
        $formData = [];
        $selectedOffer = null;

        if ($selectedOfferId) {
            $selectedOffer = $offerModel->find($selectedOfferId);
            if ($selectedOffer) {
                $formData = json_decode($selectedOffer['form_fields'] ?? '{}', true);
                // Also include combo fields if available
                $comboFields = json_decode($selectedOffer['form_fields_combo'] ?? '{}', true);
                if (!empty($comboFields)) {
                    $formData = array_merge($formData, $comboFields);
                }
            }
        }

        // Fallback zu Testdaten wenn keine Offerte gewählt
        if (empty($formData)) {
            $formData = [
                'vorname' => 'Max',
                'nachname' => 'Mustermann',
                'email' => 'max@example.com',
                'telefon' => '+41 79 123 45 67',
                'adresse' => 'Musterstrasse 123',
                'plz' => '8000',
                'ort' => 'Zürich',
                'umzugsdatum' => '15/12/2025',
                'anzahl_zimmer' => '4',
                'quadratmeter' => '85',
                'objekttyp' => 'Wohnung',
                'reinigungsart' => 'Endreinigung',
                'zusatzleistung' => 'Ja',
            ];
        }

        // Get platform from selected offer
        $platform = $selectedOffer['platform'] ?? null;

        // Create parser with platform
        $parser = new \App\Services\EmailTemplateParser($platform);

        // Parse field_display_template if available
        $fieldDisplayHtml = '';
        if (!empty($template['field_display_template'])) {
            // Load excluded fields config
            $fieldConfigForExclusion = new \Config\FormFieldOptions();
            $excludedFields = $fieldConfigForExclusion->excludedFieldsAlways;

            // Parse the field display template with form data
            $fieldDisplayHtml = $parser->parse($template['field_display_template'], $formData, $excludedFields);
        } else {
            // Fallback: use show_all if no field_display_template
            $fieldDisplayHtml = '[show_all]';
            // Load excluded fields config
            $fieldConfigForExclusion = new \Config\FormFieldOptions();
            $excludedFields = $fieldConfigForExclusion->excludedFieldsAlways;
            $fieldDisplayHtml = $parser->parse($fieldDisplayHtml, $formData, $excludedFields);
        }

        // Replace {{FIELD_DISPLAY}} in body_template
        $bodyTemplate = str_replace('{{FIELD_DISPLAY}}', $fieldDisplayHtml, $template['body_template']);

        // Parse the complete body with all shortcodes
        $parsedBody = $parser->parse($bodyTemplate, $formData);
        $parsedSubject = $parser->parse($template['subject'], $formData);

        // Translate field values if template language is not German
        helper('email_translation');
        if ($template['language'] !== 'de') {
            $parsedBody = translate_email_field_values($parsedBody, $template['language']);
            $parsedSubject = translate_email_field_values($parsedSubject, $template['language']);
        }

        // Extrahiere separate Felder für Vorschau (wie im OfferNotificationSender)
        $extractedFields = [];
        if ($selectedOffer) {
            $notificationSender = new \App\Libraries\OfferNotificationSender();
            // Erstelle temporäres offer-Array mit dekodiertem data-Feld
            $tempOffer = $selectedOffer;
            $tempOffer['data'] = $formData;
            // Nutze Reflection um auf protected Methode zuzugreifen
            $reflection = new \ReflectionClass($notificationSender);
            $method = $reflection->getMethod('extractFieldsForTemplate');
            $method->setAccessible(true);
            $extractedFields = $method->invoke($notificationSender, $tempOffer);
        }

        return view('admin/email_templates/preview', [
            'title'           => 'Template Vorschau',
            'template'        => $template,
            'subject'         => $parsedSubject,
            'body'            => $parsedBody,
            'offers'          => $offers,
            'selectedOfferId' => $selectedOfferId,
            'selectedOffer'   => $selectedOffer,
            'extractedFields' => $extractedFields,
        ]);
    }

    /**
     * Get shortcode help (for AJAX)
     */
    public function shortcodeHelp()
    {
        return $this->response->setJSON([
            'shortcodes' => [
                [
                    'code' => '{field:vorname}',
                    'description' => 'Zeigt den Wert des Feldes "vorname" an',
                ],
                [
                    'code' => '{field:umzugsdatum|date:d.m.Y}',
                    'description' => 'Zeigt ein Datum formatiert an (z.B. 15.12.2025)',
                ],
                [
                    'code' => '{site_name}',
                    'description' => 'Name der Website aus der Config',
                ],
                [
                    'code' => '{site_url}',
                    'description' => 'URL der Website',
                ],
                [
                    'code' => '[if field:anzahl_zimmer]...[/if]',
                    'description' => 'Zeigt Inhalt nur wenn das Feld existiert und nicht leer ist',
                ],
                [
                    'code' => '[if field:anzahl_zimmer > 3]...[/if]',
                    'description' => 'Zeigt Inhalt nur wenn Bedingung erfüllt ist (>, <, >=, <=, ==, !=)',
                ],
                [
                    'code' => '[if field:material == Holz]...[else]...[/if]',
                    'description' => 'Zeigt ersten Teil wenn Bedingung erfüllt, sonst den [else] Teil',
                ],
                [
                    'code' => '[show_field name="qm" label="Quadratmeter"]',
                    'description' => 'Zeigt ein einzelnes Feld mit benutzerdefiniertem Label',
                ],
                [
                    'code' => '[show_all exclude="email,phone,terms"]',
                    'description' => 'Zeigt alle Felder außer den ausgeschlossenen',
                ],
            ],
        ]);
    }

    /**
     * Export all templates and field translations as JSON
     */
    public function export()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $templates = $this->templateModel->findAll();

        // Remove id and timestamps for clean import
        $templatesData = array_map(function($template) {
            unset($template['id']);
            unset($template['created_at']);
            unset($template['updated_at']);
            return $template;
        }, $templates);

        // Load field translations
        $translationsPath = WRITEPATH . 'data/email_field_translations.json';
        $fieldTranslations = [];
        if (file_exists($translationsPath)) {
            $loaded = json_decode(file_get_contents($translationsPath), true);
            if ($loaded) {
                $fieldTranslations = $loaded;
            }
        }

        // Combined export data
        $exportData = [
            'templates' => $templatesData,
            'field_translations' => $fieldTranslations,
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="email_templates_' . date('Y-m-d_His') . '.json"')
            ->setBody($json);
    }

    /**
     * Import templates and field translations from JSON file
     */
    public function import()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $file = $this->request->getFile('import_file');
        $mode = $this->request->getPost('import_mode');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Bitte eine gültige Datei auswählen');
        }

        $json = file_get_contents($file->getTempName());
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Ungültige JSON-Datei: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            return redirect()->back()->with('error', 'Ungültiges Format der JSON-Datei');
        }

        // Support both new format (with templates/field_translations keys) and old format (array of templates)
        if (isset($data['templates'])) {
            // New format
            $templates = $data['templates'];
            $fieldTranslations = $data['field_translations'] ?? null;
        } else {
            // Old format (backwards compatibility)
            $templates = $data;
            $fieldTranslations = null;
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($templates as $template) {
            // Prüfe ob Template bereits existiert (gleicher offer_type, subtype und language)
            $existing = $this->templateModel
                ->where('offer_type', $template['offer_type'])
                ->where('language', $template['language']);

            if (isset($template['subtype']) && $template['subtype'] !== null) {
                $existing->where('subtype', $template['subtype']);
            } else {
                $existing->where('subtype', null);
            }

            $existingTemplate = $existing->first();

            if ($existingTemplate) {
                if ($mode === 'update') {
                    // Überschreibe vorhandenes Template
                    $this->templateModel->update($existingTemplate['id'], $template);
                    $updated++;
                } else {
                    // Überspringe vorhandenes Template
                    $skipped++;
                }
            } else {
                // Füge neues Template ein
                $this->templateModel->insert($template);
                $inserted++;
            }
        }

        // Import field translations if present
        $translationsImported = false;
        if ($fieldTranslations !== null && !empty($fieldTranslations)) {
            $translationsPath = WRITEPATH . 'data/email_field_translations.json';
            $dir = dirname($translationsPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if ($mode === 'update') {
                // Überschreibe Feldwerte-Übersetzungen
                file_put_contents($translationsPath, json_encode($fieldTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $translationsImported = true;
            } else {
                // Insert mode: nur wenn noch keine existieren
                if (!file_exists($translationsPath)) {
                    file_put_contents($translationsPath, json_encode($fieldTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $translationsImported = true;
                }
            }
        }

        $message = "Import abgeschlossen: {$inserted} Templates neu eingefügt";
        if ($updated > 0) {
            $message .= ", {$updated} aktualisiert";
        }
        if ($skipped > 0) {
            $message .= ", {$skipped} übersprungen";
        }
        if ($translationsImported) {
            $message .= ". Feldwerte-Übersetzungen importiert.";
        }

        return redirect()->to('/admin/email-templates')->with('success', $message);
    }
}
