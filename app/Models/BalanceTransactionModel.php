<?php
namespace App\Models;

use CodeIgniter\Model;

class BalanceTransactionModel extends Model
{
    protected $table = 'balance_transactions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'company_id', 'amount',
        'method', 'transaction_id',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $dateFormat = 'datetime';
}
