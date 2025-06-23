<?php
namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'type', 'amount', 'description', 'reference_id', 'created_at'];
    protected $useTimestamps = false;
}
