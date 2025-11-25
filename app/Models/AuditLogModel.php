<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Loggt eine Aktion
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): int|false {
        $model = new self();
        $request = service('request');

        return $model->insert([
            'user_id' => auth()->user()?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => substr($request->getUserAgent()->getAgentString(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Loggt Änderungen zwischen zwei Arrays (nur geänderte Felder)
     */
    public static function logChanges(
        string $action,
        ?string $entityType,
        ?int $entityId,
        array $oldValues,
        array $newValues
    ): int|false {
        // Nur geänderte Werte speichern
        $changedOld = [];
        $changedNew = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            // Vergleich (auch bei Arrays)
            if (is_array($newValue) || is_array($oldValue)) {
                if (json_encode($newValue) !== json_encode($oldValue)) {
                    $changedOld[$key] = $oldValue;
                    $changedNew[$key] = $newValue;
                }
            } elseif ((string)$newValue !== (string)$oldValue) {
                $changedOld[$key] = $oldValue;
                $changedNew[$key] = $newValue;
            }
        }

        // Nur loggen wenn es Änderungen gibt
        if (empty($changedNew)) {
            return false;
        }

        return self::log($action, $entityType, $entityId, $changedOld, $changedNew);
    }

    /**
     * Holt die letzten Logs für eine bestimmte Entity
     */
    public function getLogsForEntity(string $entityType, ?int $entityId = null, int $limit = 50): array
    {
        $builder = $this->where('entity_type', $entityType);

        if ($entityId !== null) {
            $builder->where('entity_id', $entityId);
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Holt die letzten Logs eines Users
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return $this
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
