<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferMoveCleaningModel extends Model
{
    protected $table = 'offers_move';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'offer_id',
        'apartment_size',
        'move_date',
        'distance',
        'additional_services',
        'cleaning_type',
        'property_size',
        'extras',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}
