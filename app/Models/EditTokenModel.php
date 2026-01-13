<?php

namespace App\Models;

use CodeIgniter\Model;

class EditTokenModel extends Model
{
    protected $table = 'edit_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'token',
        'offer_id',
        'form_url',
        'expires_at',
        'used_at',
        'created_by',
        'created_at',
    ];
    protected $useTimestamps = false;

    /**
     * Generate a new edit token for an offer
     *
     * @param int $offerId
     * @param string $formUrl The form URL to redirect to
     * @param string $createdBy 'user' or 'admin'
     * @param int $validHours Token validity in hours (default 24)
     * @return string The generated token
     */
    public function generateToken(int $offerId, string $formUrl, string $createdBy = 'user', int $validHours = 24): string
    {
        $token = bin2hex(random_bytes(32));

        $this->insert([
            'token' => $token,
            'offer_id' => $offerId,
            'form_url' => $formUrl,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$validHours} hours")),
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    /**
     * Validate and get token data
     *
     * @param string $token
     * @return array|null Token data if valid, null otherwise
     */
    public function validateToken(string $token): ?array
    {
        $tokenData = $this->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        return $tokenData;
    }

    /**
     * Mark token as used
     *
     * @param string $token
     */
    public function markAsUsed(string $token): void
    {
        $this->where('token', $token)
            ->set('used_at', date('Y-m-d H:i:s'))
            ->update();
    }

    /**
     * Get all tokens for an offer (for debugging/admin)
     *
     * @param int $offerId
     * @return array
     */
    public function getTokensForOffer(int $offerId): array
    {
        return $this->where('offer_id', $offerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Clean up expired tokens
     *
     * @return int Number of deleted tokens
     */
    public function cleanupExpired(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
    }

    /**
     * Generate edit URL with token
     *
     * @param int $offerId
     * @param string $formUrl
     * @param string $createdBy
     * @return string Full edit URL
     */
    public function generateEditUrl(int $offerId, string $formUrl, string $createdBy = 'user'): string
    {
        $token = $this->generateToken($offerId, $formUrl, $createdBy);

        // Append token to form URL
        $separator = strpos($formUrl, '?') !== false ? '&' : '?';
        return $formUrl . $separator . 'edit_token=' . $token;
    }
}
