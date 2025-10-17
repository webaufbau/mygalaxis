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
        'discount_type',
        'payment_method',
        'review',       // neu
        'review',       // neu
        'status',
        'company_name',     // für Analytics
        'external_user_id', // für Analytics
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';
    protected $dateFormat = 'datetime';
}
