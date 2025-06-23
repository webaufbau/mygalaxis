<?php
namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'email', 'password', 'address',
        'cantons', 'categories', 'languages',
        'absences', 'balance', 'auto_purchase',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $dateFormat = 'datetime';
}
