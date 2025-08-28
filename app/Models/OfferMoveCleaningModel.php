<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferMoveCleaningModel extends Model
{
    protected $table = 'offers_move_cleaning';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        // --- Move Fields ---
        'from_object_type',
        'from_city',
        'from_room_count',
        'to_object_type',
        'to_city',
        'to_room_count',
        'service_details_move',
        'move_date',
        'customer_type',
        // --- Cleaning Fields ---
        'user_role',
        'business_type',
        'object_type',
        'client_role',
        'apartment_size_cleaning',
        'room_count_cleaning',
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
