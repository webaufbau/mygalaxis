<?php

namespace App\Models;

use CodeIgniter\Model;

class SmsVerificationHistoryModel extends Model
{
    protected $table = 'sms_verification_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'offer_id',
        'uuid',
        'phone',
        'verification_code',
        'method',
        'status',
        'message_id',
        'platform',
        'verified',
        'verified_at',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Hole alle SMS-Verifizierungen für ein bestimmtes Angebot
     *
     * @param int $offerId
     * @return array
     */
    public function getHistoryByOfferId(int $offerId): array
    {
        return $this->where('offer_id', $offerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Hole alle SMS-Verifizierungen für eine UUID
     *
     * @param string $uuid
     * @return array
     */
    public function getHistoryByUuid(string $uuid): array
    {
        return $this->where('uuid', $uuid)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Markiere einen Verifizierungseintrag als verifiziert
     *
     * @param int $id
     * @return bool
     */
    public function markAsVerified(int $id): bool
    {
        return $this->update($id, [
            'verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Hole die letzte SMS-Verifizierung für eine UUID
     *
     * @param string $uuid
     * @return array|null
     */
    public function getLatestByUuid(string $uuid): ?array
    {
        return $this->where('uuid', $uuid)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
