<?php
namespace App\Models;

class CompanyModel extends BaseModel
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
