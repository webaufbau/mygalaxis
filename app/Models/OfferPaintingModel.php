<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferPaintingModel extends Model
{
    protected $table = 'offers_painting';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'object_type',
        'business_type',
        'painting_overview',
        'service_details',
        'address_city',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
