<?php

namespace App\Models;

use CodeIgniter\Model;

class MonthlyInvoiceModel extends Model
{
    protected $table = 'monthly_invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'invoice_number',
        'period',
        'amount',
        'purchase_count',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'period' => 'required|regex_match[/^\d{4}-\d{2}$/]',
        'amount' => 'required|decimal',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Generiere oder hole Monatsrechnung für User und Periode
     */
    public function getOrCreateForPeriod(int $userId, string $period): ?array
    {
        // Prüfe ob Rechnung bereits existiert
        $existing = $this->where('user_id', $userId)
            ->where('period', $period)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Berechne Betrag basierend auf Käufen in diesem Monat
        $bookingModel = new \App\Models\BookingModel();

        $startDate = $period . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $purchases = $bookingModel
            ->where('user_id', $userId)
            ->where('type', 'offer_purchase')
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->findAll();

        $totalAmount = 0;
        foreach ($purchases as $purchase) {
            $totalAmount += abs($purchase['paid_amount'] ?? $purchase['amount']);
        }

        // Generiere Rechnungsnummer
        $invoiceNumber = $this->generateInvoiceNumber($userId, $period);

        // Erstelle Rechnung
        $data = [
            'user_id' => $userId,
            'invoice_number' => $invoiceNumber,
            'period' => $period,
            'amount' => $totalAmount,
            'purchase_count' => count($purchases),
        ];

        $this->insert($data);
        return $this->find($this->insertID());
    }

    /**
     * Generiere eindeutige Rechnungsnummer
     */
    private function generateInvoiceNumber(int $userId, string $period): string
    {
        $year = substr($period, 0, 4);
        $month = substr($period, 5, 2);
        $country = strtoupper(siteconfig()->siteCountry ?? 'CH');

        return "M{$country}-{$year}{$month}-{$userId}";
    }
}
