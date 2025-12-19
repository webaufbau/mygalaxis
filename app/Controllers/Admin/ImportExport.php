<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ImportExport extends AdminBase
{
    protected array $exportableTables = [
        'users' => [
            'label' => 'Firmen (Users)',
            'model' => \App\Models\UserModel::class,
            'fields' => ['id', 'username', 'company_name', 'email', 'phone', 'address', 'zip', 'city', 'active', 'created_at'],
        ],
        'offers' => [
            'label' => 'Anfragen (Offers)',
            'model' => \App\Models\OfferModel::class,
            'fields' => ['id', 'type', 'firstname', 'lastname', 'email', 'phone', 'zip', 'city', 'price', 'verified', 'status', 'created_at'],
        ],
        'reviews' => [
            'label' => 'Bewertungen',
            'model' => \App\Models\ReviewModel::class,
            'fields' => ['id', 'company_id', 'offer_id', 'rating', 'title', 'text', 'author_name', 'approved', 'created_at'],
        ],
        'email_templates' => [
            'label' => 'E-Mail Templates',
            'model' => \App\Models\EmailTemplateModel::class,
            'fields' => ['id', 'slug', 'name', 'subject', 'body_html', 'offer_types', 'platform', 'active', 'created_at'],
        ],
        'offer_purchases' => [
            'label' => 'Käufe',
            'model' => \App\Models\OfferPurchaseModel::class,
            'fields' => ['id', 'offer_id', 'user_id', 'price', 'discount_level', 'created_at'],
        ],
        'balance_transactions' => [
            'label' => 'Guthaben-Transaktionen',
            'model' => \App\Models\BalanceTransactionModel::class,
            'fields' => ['id', 'user_id', 'amount', 'type', 'description', 'created_at'],
        ],
        'referrals' => [
            'label' => 'Empfehlungen',
            'model' => \App\Models\ReferralModel::class,
            'fields' => ['id', 'referrer_id', 'referred_id', 'status', 'credit_amount', 'created_at'],
        ],
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    public function index()
    {
        return view('admin/import_export', [
            'tables' => $this->exportableTables,
        ]);
    }

    /**
     * Export selected tables as CSV or JSON
     */
    public function export()
    {
        $tables = $this->request->getPost('tables') ?? [];
        $format = $this->request->getPost('format') ?? 'csv';

        if (empty($tables)) {
            return redirect()->back()->with('error', 'Bitte mindestens eine Tabelle auswählen.');
        }

        $exportData = [];

        foreach ($tables as $tableKey) {
            if (!isset($this->exportableTables[$tableKey])) {
                continue;
            }

            $config = $this->exportableTables[$tableKey];
            $modelClass = $config['model'];
            $model = new $modelClass();

            $data = $model->findAll();
            $exportData[$tableKey] = [
                'label' => $config['label'],
                'fields' => $config['fields'],
                'data' => $data,
            ];
        }

        if ($format === 'json') {
            return $this->exportAsJson($exportData);
        }

        return $this->exportAsCsv($exportData);
    }

    /**
     * Export as JSON
     */
    protected function exportAsJson(array $exportData): ResponseInterface
    {
        $filename = 'export_' . date('Y-m-d_His') . '.json';

        $jsonData = [];
        foreach ($exportData as $tableKey => $tableData) {
            $jsonData[$tableKey] = $tableData['data'];
        }

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Export as CSV (ZIP with multiple CSV files)
     */
    protected function exportAsCsv(array $exportData): ResponseInterface
    {
        // If only one table, return single CSV
        if (count($exportData) === 1) {
            $tableKey = array_key_first($exportData);
            $tableData = $exportData[$tableKey];
            $filename = $tableKey . '_' . date('Y-m-d_His') . '.csv';

            $csv = $this->arrayToCsv($tableData['data'], $tableData['fields']);

            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody("\xEF\xBB\xBF" . $csv); // UTF-8 BOM for Excel
        }

        // Multiple tables: create ZIP
        $zipFilename = 'export_' . date('Y-m-d_His') . '.zip';
        $tempZipPath = WRITEPATH . 'uploads/' . $zipFilename;

        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Fehler beim Erstellen der ZIP-Datei.');
        }

        foreach ($exportData as $tableKey => $tableData) {
            $csv = $this->arrayToCsv($tableData['data'], $tableData['fields']);
            $zip->addFromString($tableKey . '.csv', "\xEF\xBB\xBF" . $csv);
        }

        $zip->close();

        $zipContent = file_get_contents($tempZipPath);
        unlink($tempZipPath);

        return $this->response
            ->setHeader('Content-Type', 'application/zip')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $zipFilename . '"')
            ->setBody($zipContent);
    }

    /**
     * Convert array to CSV string
     */
    protected function arrayToCsv(array $data, array $fields): string
    {
        if (empty($data)) {
            return implode(';', $fields) . "\n";
        }

        $output = fopen('php://temp', 'r+');

        // Header row
        fputcsv($output, $fields, ';');

        // Data rows
        foreach ($data as $row) {
            // Convert Entity to array if needed
            if (is_object($row)) {
                $row = method_exists($row, 'toArray') ? $row->toArray() : (array) $row;
            }

            $csvRow = [];
            foreach ($fields as $field) {
                $value = $row[$field] ?? '';
                // Handle JSON fields
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Import data from file
     */
    public function import()
    {
        $table = $this->request->getPost('import_table');
        $file = $this->request->getFile('import_file');

        if (!$table || !isset($this->exportableTables[$table])) {
            return redirect()->back()->with('error', 'Ungültige Tabelle ausgewählt.');
        }

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Keine gültige Datei hochgeladen.');
        }

        $extension = $file->getClientExtension();

        if ($extension === 'csv') {
            return $this->importCsv($table, $file);
        } elseif ($extension === 'json') {
            return $this->importJson($table, $file);
        }

        return redirect()->back()->with('error', 'Ungültiges Dateiformat. Nur CSV und JSON erlaubt.');
    }

    /**
     * Import from CSV file
     */
    protected function importCsv(string $tableKey, $file)
    {
        $config = $this->exportableTables[$tableKey];
        $modelClass = $config['model'];
        $model = new $modelClass();

        $content = file_get_contents($file->getTempName());
        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $lines = explode("\n", $content);
        $header = str_getcsv(array_shift($lines), ';');

        $imported = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line, ';');
            $data = array_combine($header, $values);

            if ($data === false) {
                $errors[] = 'Zeile ' . ($lineNum + 2) . ': Spaltenanzahl stimmt nicht überein.';
                continue;
            }

            // Remove id for insert (let DB auto-generate)
            unset($data['id']);
            // Remove timestamps
            unset($data['created_at']);
            unset($data['updated_at']);

            try {
                $model->insert($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Zeile ' . ($lineNum + 2) . ': ' . $e->getMessage();
            }
        }

        $message = $imported . ' Einträge importiert.';
        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', $message . ' ' . count($errors) . ' Fehler.')
                ->with('errors', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Import from JSON file
     */
    protected function importJson(string $tableKey, $file)
    {
        $config = $this->exportableTables[$tableKey];
        $modelClass = $config['model'];
        $model = new $modelClass();

        $content = file_get_contents($file->getTempName());
        $jsonData = json_decode($content, true);

        if ($jsonData === null) {
            return redirect()->back()->with('error', 'Ungültiges JSON-Format.');
        }

        // Check if data is wrapped in table key
        if (isset($jsonData[$tableKey])) {
            $jsonData = $jsonData[$tableKey];
        }

        $imported = 0;
        $errors = [];

        foreach ($jsonData as $index => $data) {
            // Remove id for insert
            unset($data['id']);
            unset($data['created_at']);
            unset($data['updated_at']);

            try {
                $model->insert($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Eintrag ' . ($index + 1) . ': ' . $e->getMessage();
            }
        }

        $message = $imported . ' Einträge importiert.';
        if (!empty($errors)) {
            return redirect()->back()
                ->with('warning', $message . ' ' . count($errors) . ' Fehler.')
                ->with('errors', array_slice($errors, 0, 10)); // Limit errors shown
        }

        return redirect()->back()->with('success', $message);
    }
}
