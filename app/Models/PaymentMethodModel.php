<?php
namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code', 'name', 'active'];
    protected $useTimestamps = true;

    public function getActiveMethods()
    {
        return $this->where('active', 1)->findAll();
    }
}
