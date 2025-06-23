<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferPaintingModel extends Model
{
    protected $table = 'offers_painting';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'offer_id', 'work_type', 'area_m2', 'duration_estimation', 'special_requests'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}
