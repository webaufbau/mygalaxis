<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferGardeningModel extends Model
{
    protected $table = 'offers_gardening';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'user_role',
        'service_details',
        'address_city',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
