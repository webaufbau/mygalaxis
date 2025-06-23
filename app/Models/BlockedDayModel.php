<?php
namespace App\Models;

use CodeIgniter\Model;

class BlockedDayModel extends Model
{
    protected $table = 'blocked_days';
    protected $allowedFields = ['user_id', 'date'];
    public $timestamps = false;

    public function getDatesByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function isBlocked($userId, $date)
    {
        return $this->where(['user_id' => $userId, 'date' => $date])->countAllResults() > 0;
    }

    public function add($userId, $date)
    {
        return $this->insert(['user_id' => $userId, 'date' => $date]);
    }

    public function remove($userId, $date)
    {
        return $this->where(['user_id' => $userId, 'date' => $date])->delete();
    }
}
