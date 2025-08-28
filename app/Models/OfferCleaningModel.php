<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferCleaningModel extends Model
{
    protected $table = 'offers_cleaning';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'user_role',
        'business_type',
        'object_type',
        'client_role',
        'apartment_size',
        'room_count',
        'cleaning_area_sqm',
        'cleaning_type',
        'window_shutter_cleaning',
        'facade_count',
        'address_city',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
