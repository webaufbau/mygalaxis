<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

/**
 * Custom Email class that automatically adds logs@webaufbau.com as BCC
 */
class CustomEmail extends Email
{
    protected $autoBCC = 'logs@webaufbau.com';

    /**
     * Override setBCC to always include logs@webaufbau.com
     */
    public function setBCC($bcc, $limit = '')
    {
        // Konvertiere BCC in Array falls String
        if (is_string($bcc)) {
            $bccArray = array_map('trim', explode(',', $bcc));
        } else {
            $bccArray = (array) $bcc;
        }

        // Füge logs@webaufbau.com hinzu wenn nicht bereits vorhanden
        if (!in_array($this->autoBCC, $bccArray)) {
            $bccArray[] = $this->autoBCC;
        }

        // Rufe Parent-Methode mit erweiterter BCC-Liste auf
        return parent::setBCC($bccArray, $limit);
    }

    public function send($autoClear = true): bool
    {
        // Falls setBCC nie aufgerufen wurde, füge logs@webaufbau.com hinzu
        if (empty($this->BCCArray)) {
            $this->setBCC($this->autoBCC);
        }

        // Log email before sending
        $this->logEmailSending();

        $result = parent::send($autoClear);

        // Log result
        if (!$result) {
            log_message('error', '[EMAIL-SEND-FAILED] Failed to send email');
        }

        return $result;
    }

    /**
     * Log email sending details for duplicate detection
     */
    protected function logEmailSending(): void
    {
        // Use reflection to access protected properties from parent class
        $reflection = new \ReflectionClass(parent::class);

        // Get recipients (TO)
        $recipients = $reflection->getProperty('recipients')->getValue($this);
        $to = is_array($recipients) ? implode(', ', $recipients) : '';

        // Get CC
        $ccArray = $reflection->getProperty('CCArray')->getValue($this);
        $cc = is_array($ccArray) && !empty($ccArray) ? implode(', ', array_keys($ccArray)) : '';

        // Get BCC
        $bccArray = $reflection->getProperty('BCCArray')->getValue($this);
        $bcc = is_array($bccArray) && !empty($bccArray) ? implode(', ', array_keys($bccArray)) : '';

        // Get subject
        $subject = $reflection->getProperty('subject')->getValue($this) ?? '(no subject)';

        // Create log entry with structured format
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $subject,
        ];

        // Log as JSON for easy parsing
        log_message('info', '[EMAIL-SENT] ' . json_encode($logData, JSON_UNESCAPED_UNICODE));
    }
}
