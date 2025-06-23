<?php
namespace App\Models;

use CodeIgniter\Model;

class UserPaymentMethodModel extends Model
{
    protected $table = 'user_payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'payment_method_code', 'provider_data', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}
