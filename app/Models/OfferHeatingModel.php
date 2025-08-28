<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferHeatingModel extends Model
{
    protected $table = 'offers_heating';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'object_type',
        'service_details',
        'address_city',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
