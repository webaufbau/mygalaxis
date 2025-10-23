<?php
namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'type', 'amount', 'paid_amount', 'description', 'reference_id', 'created_at', 'review_reminder_sent_at', 'offer_notification_sent_at'];
    protected $useTimestamps = false;

    // In BookingModel.php
    public function getUserBalance(int $userId): float
    {
        $result = $this->selectSum('amount')
            ->where('user_id', $userId)
            ->first();

        return (float) ($result['amount'] ?? 0);
    }

}
