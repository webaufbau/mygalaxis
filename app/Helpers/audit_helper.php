<?php

if (!function_exists('auditLog')) {
    /**
     * Helper-Funktion für schnelles Audit Logging
     *
     * @param string $eventType z.B. 'form_submit', 'redirect', 'verification_send'
     * @param string $message Lesbare Beschreibung
     * @param array $context Kontextdaten (uuid, offer_id, phone, email, platform, group_id)
     * @param array|null $details Zusätzliche strukturierte Daten
     * @param string $category Event-Kategorie (default: auto-detect from event_type)
     * @return bool|int
     */
    function auditLog(
        string $eventType,
        string $message,
        array $context = [],
        ?array $details = null,
        ?string $category = null
    ) {
        $auditModel = new \App\Models\FormAuditLogModel();

        // Auto-detect category if not provided
        if ($category === null) {
            if (str_contains($eventType, 'form_')) {
                $category = 'form';
            } elseif (str_contains($eventType, 'verification_') || str_contains($eventType, 'verify_')) {
                $category = 'verification';
            } elseif (str_contains($eventType, 'email_') || str_contains($eventType, 'mail_')) {
                $category = 'email';
            } elseif (str_contains($eventType, 'redirect_')) {
                $category = 'redirect';
            } else {
                $category = 'general';
            }
        }

        return $auditModel->logEvent($eventType, $category, $message, $context, $details);
    }
}
