<?php
namespace App\Models;

use CodeIgniter\Model;

class RequestModel extends Model
{
    protected $table = 'requests';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'form_name',
        'form_fields',
        'headers',
        'referer',
        'verified',
        'verify_type',
        'uuid',
        'created_at',
    ];

    protected $useTimestamps = false;
}
