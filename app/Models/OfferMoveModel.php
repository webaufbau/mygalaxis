<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferMoveModel extends Model
{
    protected $table = 'offers_move';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'from_object_type',
        'from_city',
        'from_room_count',
        'to_object_type',
        'to_city',
        'to_room_count',
        'service_details',
        'move_date',
        'customer_type',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
