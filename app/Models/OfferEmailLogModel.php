<?php

namespace App\Models;

use CodeIgniter\Model;

class OfferEmailLogModel extends Model
{
    protected $table = 'offer_email_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'offer_id',
        'email_type',
        'recipient_email',
        'recipient_type',
        'company_id',
        'notified_company_ids',
        'subject',
        'status',
        'error_message',
        'sent_at',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    /**
     * Logge gesendete E-Mail
     */
    public function logEmail(
        int $offerId,
        string $emailType,
        string $recipientEmail,
        string $recipientType = 'customer',
        ?int $companyId = null,
        ?string $subject = null,
        string $status = 'sent',
        ?string $errorMessage = null,
        ?array $notifiedCompanyIds = null
    ): int {
        return $this->insert([
            'offer_id' => $offerId,
            'email_type' => $emailType,
            'recipient_email' => $recipientEmail,
            'recipient_type' => $recipientType,
            'company_id' => $companyId,
            'notified_company_ids' => $notifiedCompanyIds ? json_encode($notifiedCompanyIds) : null,
            'subject' => $subject,
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Hole alle E-Mails für ein Angebot
     */
    public function getEmailsByOfferId(int $offerId): array
    {
        return $this->where('offer_id', $offerId)
            ->orderBy('sent_at', 'DESC')
            ->findAll();
    }

    /**
     * Hole E-Mails mit Firmen-Info
     */
    public function getEmailsWithCompanyInfo(int $offerId): array
    {
        return $this->db->table('offer_email_log')
            ->select('offer_email_log.*, users.company_name, users.contact_person')
            ->join('users', 'users.id = offer_email_log.company_id', 'left')
            ->where('offer_email_log.offer_id', $offerId)
            ->orderBy('offer_email_log.sent_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
