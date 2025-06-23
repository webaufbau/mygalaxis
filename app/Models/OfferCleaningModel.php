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
        'cleaning_type',
        'property_size',
        'extras',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}
