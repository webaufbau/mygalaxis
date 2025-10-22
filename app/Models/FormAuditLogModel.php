<?php

namespace App\Models;

use CodeIgniter\Model;

class FormAuditLogModel extends Model
{
    protected $table = 'form_audit_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'uuid',
        'group_id',
        'offer_id',
        'event_type',
        'event_category',
        'message',
        'details',
        'phone',
        'email',
        'platform',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Log ein Event im Audit Log
     *
     * @param string $eventType z.B. 'form_submit', 'redirect', 'verification_send'
     * @param string $eventCategory z.B. 'form', 'verification', 'email'
     * @param string $message Lesbare Beschreibung
     * @param array $context Zusätzliche Kontextdaten (uuid, offer_id, phone, email, etc.)
     * @param array|null $details Strukturierte Detail-Daten (GET/POST params, URLs, etc.)
     * @return bool|int Insert ID oder false
     */
    public function logEvent(
        string $eventType,
        string $eventCategory,
        string $message,
        array $context = [],
        ?array $details = null
    ) {
        $request = service('request');

        // getUserAgent() ist nur bei IncomingRequest verfügbar, nicht bei CLIRequest
        $userAgent = null;
        if (!($request instanceof \CodeIgniter\HTTP\CLIRequest)) {
            $userAgent = $request->getUserAgent()->getAgentString() ?? null;
        }

        $data = [
            'event_type' => $eventType,
            'event_category' => $eventCategory,
            'message' => $message,
            'uuid' => $context['uuid'] ?? null,
            'group_id' => $context['group_id'] ?? session()->get('group_id') ?? null,
            'offer_id' => $context['offer_id'] ?? null,
            'phone' => $context['phone'] ?? null,
            'email' => $context['email'] ?? null,
            'platform' => $context['platform'] ?? null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $userAgent,
            'details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Log auch in CodeIgniter Log für direktes Debugging
        log_message('info', "[AUDIT] [{$eventCategory}] {$eventType}: {$message}" .
            ($context['uuid'] ? " (UUID: {$context['uuid']})" : ""));

        return $this->insert($data);
    }

    /**
     * Hole alle Logs für eine bestimmte UUID
     */
    public function getLogsByUuid(string $uuid): array
    {
        return $this->where('uuid', $uuid)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Hole alle Logs für eine bestimmte Group ID
     */
    public function getLogsByGroupId(string $groupId): array
    {
        return $this->where('group_id', $groupId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Hole alle Logs für eine bestimmte Offer ID
     */
    public function getLogsByOfferId(int $offerId): array
    {
        return $this->where('offer_id', $offerId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Hole Logs mit Filtern
     */
    public function getLogsFiltered(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['event_category'])) {
            $builder->where('event_category', $filters['event_category']);
        }

        if (!empty($filters['event_type'])) {
            $builder->where('event_type', $filters['event_type']);
        }

        if (!empty($filters['platform'])) {
            $builder->where('platform', $filters['platform']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('message', $filters['search'])
                ->orLike('uuid', $filters['search'])
                ->orLike('email', $filters['search'])
                ->orLike('phone', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->getResultArray();
    }
}
