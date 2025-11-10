<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailChangeRequestModel extends Model
{
    protected $table = 'email_change_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'expires_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    /**
     * Lösche abgelaufene Anfragen
     */
    public function deleteExpired()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }

    /**
     * Finde Anfrage nach Token
     */
    public function findByToken(string $token)
    {
        return $this->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Lösche alte Anfragen eines Users
     */
    public function deleteOldRequests(int $userId)
    {
        return $this->where('user_id', $userId)->delete();
    }
}
