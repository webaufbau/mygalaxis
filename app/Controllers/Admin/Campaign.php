<?php

namespace App\Controllers\Admin;

use App\Controllers\Crud;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Campaign extends Crud {

    protected string $url_prefix = 'admin/';

    public function __construct() {
        parent::__construct('campaign');
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->set('text_add_new', '+ Neue Adresse');
    }

    public function index()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/');
        }

        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $offset  = $page * $perPage - $perPage;

        $filter_configuration = $this->model_class->getFilterConfiguration();

        $request_get_data = $this->request->getGet();
        if ($request_get_data) {
            foreach ($request_get_data as $key => $value) {
                if (isset($filter_configuration['fields'][$key]) && strlen($value)) {
                    $field_config = $filter_configuration['fields'][$key];
                    if (isset($field_config['where'])) {
                        $this->model_class->where(str_replace('%value%', $value, $field_config['where']));
                    } elseif (isset($field_config['like'])) {
                        $this->model_class->groupStart();
                        foreach ($field_config['like'] as $field => $pattern) {
                            $this->model_class->orLike($field, str_replace('%value%', $value, $pattern));
                        }
                        $this->model_class->groupEnd();
                    }
                }
            }
        }

        // CSV Import (Upload-Form mit input name="csvfile")
        if ($this->request->getMethod() === 'post' && $this->request->getFile('csvfile')->isValid()) {
            $csvFile = $this->request->getFile('csvfile');
            $this->importCampaignsFromCSV($csvFile->getTempName());
            return redirect()->to(current_url())->with('success', 'CSV erfolgreich importiert');
        }

        // Datensätze laden
        $total = $this->model_class->getTotalEntries();
        $entries = $this->model_class->getEntries($perPage, $offset);

        // Filter-HTML bauen
        $filter_html = '';
        foreach ($filter_configuration['fields'] as $key => $field_config) {
            $filter_html .= '<li class="nav-item px-3">'
                . form_build_one_field($key, $field_config, $request_get_data, true)
                . '</li>';
        }

        $this->template->set('form_filters', $filter_html);
        $this->template->set('page_title', $this->model_class->getTitle());
        $this->template->set('navbar_html', '
            <a href="' . site_url('admin/campaign/import_csv') . '" class="btn btn-primary btn-sm">Import CSV</a>
        ');

        $table = $this->getCrudTable($this->app_controller, $this->model_name, (array)$entries);
        $this->template->set('pager_table', '<div class="alert alert-warning">E-Mail Versand Kampagnen aktuell inaktiv. Firmen können jedoch bereits mit Betreff und Inhalt erfasst werden.</div>' . $table);

        $pager_links = $pager->makeLinks($page, $perPage, $total, 'bs5_prev_next');
        $this->template->set('pager_links', $pager_links);

        $this->template->load('account/default_list');
    }

    public function downloadCompanySampleCsv()
    {
        $fields = [
            'company_name',
            'company_email',
            'company_contact_person',
            'company_address',
            'company_zip',
            'company_city',
            'company_canton',
            'company_phone',
            'company_website',
            'company_industry',
            'company_categories',
            'company_languages',
            'company_notes',
            'subject',
            'message',
            'status',
            'sent_at',
            'response_at',
        ];

        $filename = "campaign_import_vorlage.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, $fields, ';', '"');

        fputcsv($output, [
            'Musterfirma AG',
            'kontakt@musterfirma.ch',
            'Max Mustermann',
            'Musterstrasse 10',
            '8000',
            'Zürich',
            'ZH',
            '+41 44 123 45 67',
            'https://www.musterfirma.ch',
            'IT',
            'Software,Consulting',
            'de,en',
            'Erster Import',
            'Sommerfest 2025',
            'Ein grosses Fest im Park',
            'pending',
            '',
            '',
        ], ';', '"');

        fclose($output);
        exit;
    }

    // Anzeige des Upload-Formulars für CSV-Import
    public function import_csv()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/');
        }

        // Formular anzeigen
        return view('admin/import_csv_form', [
            'page_title' => 'Firmen CSV importieren',
        ]);
    }

    // Verarbeitung des CSV-Uploads
    public function import_csv_process()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/');
        }

        $file = $this->request->getFile('csv_file');

        if (!$file->isValid()) {
            $this->setFlash('Fehler beim Hochladen der Datei.', 'danger');
            return redirect()->back();
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            $this->setFlash('Nur CSV-Dateien sind erlaubt.', 'danger');
            return redirect()->back();
        }

        $targetDir = WRITEPATH . 'uploads/campaign/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move($targetDir, $fileName);
        $filePath = $targetDir . $fileName;

        try {
            $this->importCampaignsFromCSV($filePath);
            $this->setFlash('Import erfolgreich.', 'success');
        } catch (\Exception $e) {
            log_message('error', 'Fehler beim Campaign-Import: ' . $e->getMessage());
            $this->setFlash('Fehler beim Import: ' . $e->getMessage(), 'danger');
        }

        return redirect()->to('admin/campaign');
    }

    // CSV-Import-Funktion
    protected function importCampaignsFromCSV(string $csvPath)
    {
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            throw new \RuntimeException('CSV-Datei konnte nicht geöffnet werden.');
        }

        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            throw new \RuntimeException('Konnte CSV-Kopfzeile nicht lesen.');
        }

        // BOM entfernen, falls vorhanden
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($header)) {
                continue; // Ungültige Zeile überspringen
            }
            $data = array_combine($header, $row);

            // Minimalvalidierung
            if (empty($data['company_name']) || empty($data['company_email'])) {
                continue;
            }

            $campaignData = [
                'company_name'           => $data['company_name'] ?? '',
                'company_email'          => $data['company_email'] ?? '',
                'company_contact_person' => $data['company_contact_person'] ?? '',
                'company_address'        => $data['company_address'] ?? '',
                'company_zip'            => $data['company_zip'] ?? '',
                'company_city'           => $data['company_city'] ?? '',
                'company_canton'         => $data['company_canton'] ?? '',
                'company_phone'          => $data['company_phone'] ?? '',
                'company_website'        => $data['company_website'] ?? '',
                'company_industry'       => $data['company_industry'] ?? '',
                'company_categories'     => $data['company_categories'] ?? '',
                'company_languages'      => $data['company_languages'] ?? '',
                'company_notes'          => $data['company_notes'] ?? '',
                'subject'                => $data['subject'] ?? '',
                'message'                => $data['message'] ?? '',
                'status'                 => $data['status'] ?? 'pending',
                'sent_at'                => $data['sent_at'] ?? null,
                'response_at'            => $data['response_at'] ?? null,
            ];

            $this->model_class->insert($campaignData);
        }

        fclose($handle);
    }



    protected function getRowActions($entity_entity)
    {
        $primary_key_field = $this->model_class->getPrimaryKeyField();

        $actions = $this->model_class->getEntryActions($entity_entity);

        $review = $this->model_class->find($entity_entity->id);
        $currentUser = auth()->user();
        $userId = $currentUser->id;

        $canEdit = false;
        if(is_object($review)) {
            $canEdit = $currentUser->can('my.' . $this->app_controller . '_admin') ||
                $review->user_id == $userId;
        }

        if($canEdit) {
            //$actions .= anchor($this->url_prefix . $this->app_controller . '/approve/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-check-lg"></i>', 'class="btn btn-default action" title="Freischalten"');
            $actions .= anchor($this->url_prefix . $this->app_controller . '/form/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-pencil"></i>', 'class="btn btn-default action" title="Bearbeiten" data-bs-toggle="tooltip"');
        }
        // Button "Markieren als Kunde hat geantwortet"
        if ($currentUser->can('my.' . $this->app_controller . '_admin')) {
            $actions .= anchor(
                site_url($this->url_prefix . $this->app_controller . '/mark-responded/' . $entity_entity->{$primary_key_field}),
                '<i class="bi bi-chat-left-text"></i>',
                'class="btn " title="Als beantwortet markieren" data-bs-toggle="tooltip"'
            );

            // Button Löschen
            $actions .= anchor(
                site_url($this->url_prefix . $this->app_controller . '/delete/' . $entity_entity->{$primary_key_field}),
                '<i class="bi bi-trash"></i>',
                'class="btn del" title="Löschen" data-bs-toggle="tooltip"'
            );
        }

        return $actions;
    }

    public function markResponded($id) {
        if (!auth()->user()->can('my.' . $this->app_controller . '_admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung.');
        }

        $campaign = $this->model_class->find($id);
        if (!$campaign) {
            return redirect()->back()->with('error', 'Kampagne nicht gefunden.');
        }

        $data = [
            'status' => 'responded',
            'response_at' => date('Y-m-d H:i:s'),
        ];

        $this->model_class->update($id, $data);

        return redirect()->back()->with('success', 'Kampagne als beantwortet markiert.');
    }

    public function delete($entity_id=0) {
        // Berechtigungen prüfen
        if (!auth()->user()->can('my.campaign_admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        $campaign = $this->model_class->find($entity_id);
        if (!$campaign) {
            return redirect()->back()->with('error', 'Kampagne nicht gefunden');
        }

        // Datensatz löschen
        log_message('info', 'Campaign gelöscht: ' . json_encode($campaign->getFields()));
        $this->model_class->delete($entity_id);

        return redirect()->back()->with('success', 'Kampagne erfolgreich gelöscht');
    }



}
