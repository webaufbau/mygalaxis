<?php
namespace App\Models;

use CodeIgniter\Model;

class CreditModel extends Model
{
    protected $table = 'credits';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $allowedFields = [
        'company_id',
        'amount',
        'type',
        'description',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function getBalance(int $companyId): float
    {
        return (float) $this->where('company_id', $companyId)->selectSum('amount')->first()['amount'] ?? 0;
    }
}
