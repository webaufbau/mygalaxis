<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferPlumbingModel extends Model
{
    protected $table = 'offers_plumbing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'offer_id', 'work_scope', 'urgency_level', 'affected_rooms', 'special_requests'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}
