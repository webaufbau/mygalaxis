<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferElectricianModel extends Model
{
    protected $table = 'offers_electrician';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'service_type',
        'urgency_level',
        'power_capacity_kw',
        'special_requests'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
