<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferPurchaseModel extends Model
{
    protected $table = 'offer_purchases';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',      // neu hinzugefügt
        'company_id',
        'offer_id',
        'price',
        'price_paid',
        'payment_method',
        'rating',       // neu
        'review',       // neu
        'status',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';
    protected $dateFormat = 'datetime';
}
