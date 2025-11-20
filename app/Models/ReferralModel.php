<?php

namespace App\Models;

use CodeIgniter\Model;

class ReferralModel extends Model
{
    protected $table = 'referrals';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'referrer_user_id',
        'referred_user_id',
        'referred_email',
        'referred_company_name',
        'referral_code',
        'ip_address',
        'status',
        'credit_amount',
        'credited_at',
        'credited_by_user_id',
        'admin_note',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Create a new referral entry when someone registers with an affiliate link
     *
     * @param int $referrerUserId User who made the referral
     * @param string $referralCode Affiliate code used
     * @param string $email Email of new registrant
     * @param string|null $companyName Company name of new registrant
     * @param string|null $ipAddress IP address
     * @param int|null $referredUserId New user's ID (if registration already complete)
     * @return int|false Referral ID or false
     */
    public function createReferral(
        int $referrerUserId,
        string $referralCode,
        string $email,
        ?string $companyName = null,
        ?string $ipAddress = null,
        ?int $referredUserId = null
    ) {
        $data = [
            'referrer_user_id' => $referrerUserId,
            'referred_user_id' => $referredUserId,
            'referred_email' => $email,
            'referred_company_name' => $companyName,
            'referral_code' => $referralCode,
            'ip_address' => $ipAddress,
            'status' => 'pending',
            'credit_amount' => 50.00, // Default 50 CHF
        ];

        return $this->insert($data);
    }

    /**
     * Update referral with registered user ID
     *
     * @param string $email
     * @param int $userId
     * @return bool
     */
    public function updateReferredUserId(string $email, int $userId): bool
    {
        return $this->where('referred_email', $email)
            ->where('referred_user_id', null)
            ->set(['referred_user_id' => $userId])
            ->update();
    }

    /**
     * Get all referrals for a specific user (referrer view)
     *
     * @param int $userId
     * @return array
     */
    public function getReferralsByUser(int $userId): array
    {
        return $this->where('referrer_user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get all referrals with filters for admin view
     *
     * @param array $filters
     * @return array
     */
    public function getAllReferrals(array $filters = []): array
    {
        $builder = $this->builder();

        // Join with users to get referrer info AND referred user info
        $builder->select('referrals.*,
                          referrer.username as referrer_username,
                          referrer.company_name as referrer_company,
                          referrer.created_at as referrer_registered_at,
                          credited_by.username as credited_by_username,
                          referred.created_at as referred_registered_at')
            ->join('users as referrer', 'referrer.id = referrals.referrer_user_id', 'left')
            ->join('users as referred', 'referred.id = referrals.referred_user_id', 'left')
            ->join('users as credited_by', 'credited_by.id = referrals.credited_by_user_id', 'left');

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('referrals.status', $filters['status']);
        }

        if (!empty($filters['referrer_id'])) {
            $builder->where('referrals.referrer_user_id', $filters['referrer_id']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('referrals.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('referrals.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $builder->groupStart();
            $builder->like('referrals.referred_email', $filters['search']);
            $builder->orLike('referrals.referred_company_name', $filters['search']);
            $builder->orLike('referrer.company_name', $filters['search']);
            $builder->orLike('referrer.username', $filters['search']);
            $builder->groupEnd();
        }

        return $builder->orderBy('referrals.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Give credit for a referral (admin action)
     *
     * @param int $referralId
     * @param int $adminUserId
     * @param float $amount
     * @param string|null $note
     * @return bool
     */
    public function giveCredit(int $referralId, int $adminUserId, float $amount = 50.00, ?string $note = null): bool
    {
        $referral = $this->find($referralId);

        if (!$referral) {
            log_message('error', "Cannot give credit: Referral #{$referralId} not found");
            return false;
        }

        if ($referral['status'] === 'credited') {
            log_message('warning', "Referral #{$referralId} already credited");
            return false;
        }

        // Update referral status
        $updated = $this->update($referralId, [
            'status' => 'credited',
            'credit_amount' => $amount,
            'credited_at' => date('Y-m-d H:i:s'),
            'credited_by_user_id' => $adminUserId,
            'admin_note' => $note,
        ]);

        if (!$updated) {
            log_message('error', "Failed to update referral #{$referralId} status");
            return false;
        }

        // Create booking entry for credit
        $bookingModel = new \App\Models\BookingModel();
        $description = "Weiterempfehlungs-Gutschrift: " . ($referral['referred_company_name'] ?? $referral['referred_email']);

        $bookingData = [
            'user_id' => $referral['referrer_user_id'],
            'type' => 'topup',
            'amount' => $amount,
            'paid_amount' => 0,
            'payment_method' => 'referral',
            'description' => $description,
            'reference_id' => $referralId,
            'status' => 'completed',
        ];

        $bookingId = $bookingModel->insert($bookingData);

        if ($bookingId) {
            log_message('info', "Credit given for referral #{$referralId}: {$amount} CHF to user #{$referral['referrer_user_id']}");
            return true;
        } else {
            log_message('error', "Failed to create booking for referral #{$referralId}");
            // Rollback referral status
            $this->update($referralId, ['status' => 'pending']);
            return false;
        }
    }

    /**
     * Reject a referral (admin action)
     *
     * @param int $referralId
     * @param string|null $note
     * @return bool
     */
    public function rejectReferral(int $referralId, ?string $note = null): bool
    {
        return $this->update($referralId, [
            'status' => 'rejected',
            'admin_note' => $note,
        ]);
    }

    /**
     * Get referral statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserStats(int $userId): array
    {
        $db = \Config\Database::connect();

        $query = $db->table('referrals')
            ->select('status, COUNT(*) as count, SUM(credit_amount) as total_credit')
            ->where('referrer_user_id', $userId)
            ->groupBy('status')
            ->get();

        $stats = [
            'total' => 0,
            'pending' => 0,
            'credited' => 0,
            'rejected' => 0,
            'total_earned' => 0,
        ];

        foreach ($query->getResultArray() as $row) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];

            if ($row['status'] === 'credited') {
                $stats['total_earned'] = (float)$row['total_credit'];
            }
        }

        return $stats;
    }

    /**
     * Check if referral code exists and get user ID
     *
     * @param string $code
     * @return int|null User ID or null
     */
    public function getUserIdByCode(string $code): ?int
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('affiliate_code', $code)->first();

        return $user ? $user->id : null;
    }
}
