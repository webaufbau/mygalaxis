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
     * Preview template with test data
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

        // Test data
        $testData = [
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'email' => 'max@example.com',
            'phone' => '+41 79 123 45 67',
            'address_line_1' => 'Musterstrasse 123',
            'zip' => '8000',
            'city' => 'Zürich',
            'umzugsdatum' => '15/12/2025',
            'anzahl_zimmer' => '4',
            'qm' => '85',
            'object_type' => 'Wohnung',
            'cleaning_type' => 'Endreinigung',
            'additional_service' => 'Ja',
        ];

        $parser = new \App\Services\EmailTemplateParser();
        $parsedBody = $parser->parse($template['body_template'], $testData);
        $parsedSubject = $parser->parse($template['subject'], $testData);

        return view('admin/email_templates/preview', [
            'title'    => 'Template Vorschau',
            'template' => $template,
            'subject'  => $parsedSubject,
            'body'     => $parsedBody,
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
}
