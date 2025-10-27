<?php

namespace App\Libraries;

use Throwable;

/**
 * Alert Service that sends webhooks for critical errors
 * Uses event-based approach instead of extending ExceptionHandler
 */
class AlertExceptionHandler
{
    private const RATE_LIMIT_WINDOW = 300; // 5 minutes in seconds
    private const MAX_ALERTS_PER_WINDOW = 5; // Max alerts per error type in 5 minutes

    private static function getRateLimitFile(): string
    {
        return WRITEPATH . 'cache/alert_rate_limit.json';
    }

    private static function getSmsRateLimitFile(): string
    {
        return WRITEPATH . 'cache/sms_rate_limit.json';
    }

    /**
     * Process an exception and send alerts if needed
     */
    public static function handleException(Throwable $exception, int $statusCode = 500): void
    {
        log_message('debug', '[AlertSystem] Verarbeite Exception: ' . get_class($exception));

        $handler = new self();

        // Send alert if it's a critical error
        if ($handler->isCriticalError($exception, $statusCode)) {
            log_message('info', '[AlertSystem] Kritischer Fehler erkannt, sende Alerts');
            $handler->sendAlert($exception, $statusCode);
        } else {
            log_message('debug', '[AlertSystem] Fehler nicht kritisch, keine Alerts');
        }
    }

    /**
     * Determine if this error is critical enough to send an alert
     */
    private function isCriticalError(Throwable $exception, int $statusCode): bool
    {
        // Don't alert on 404s or other client errors
        if ($statusCode >= 400 && $statusCode < 500) {
            return false;
        }

        // Alert on all server errors (500+)
        if ($statusCode >= 500) {
            return true;
        }

        // Alert on database errors
        if (strpos($exception->getMessage(), 'Database') !== false) {
            return true;
        }

        // Alert on fatal errors
        $criticalKeywords = ['Fatal', 'Call to undefined', 'Class not found', 'Parse error'];
        foreach ($criticalKeywords as $keyword) {
            if (stripos($exception->getMessage(), $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send alert via webhook with rate limiting
     */
    private function sendAlert(Throwable $exception, int $statusCode): void
    {
        $config = config('AlertWebhooks');

        if (!$config->enabled) {
            return;
        }

        $errorKey = $this->getErrorKey($exception);

        // Check rate limiting
        if (!$this->shouldSendAlert($errorKey)) {
            log_message('info', "[AlertSystem] Alert-Limit erreicht für Error: {$errorKey}");
            return;
        }

        // Build alert message
        $message = $this->buildAlertMessage($exception, $statusCode);

        // Determine severity level
        $severity = $this->determineSeverity($exception, $statusCode);
        $message['severity'] = $severity;

        $channels = $config->severityChannels[$severity] ?? ['email'];

        // Send to webhooks (Slack, etc.) if severity allows
        if (in_array('slack', $channels) || in_array('webhook', $channels)) {
            foreach ($config->webhooks as $webhook) {
                if ($webhook['enabled'] ?? false) {
                    $this->sendWebhook($webhook['url'], $webhook['type'], $message);
                }
            }
        }

        // Send email alerts if severity allows
        if (in_array('email', $channels) && $config->emailEnabled && !empty($config->emailRecipients)) {
            log_message('info', '[AlertSystem] Sende Email-Alert an: ' . implode(', ', $config->emailRecipients));
            $this->sendEmailAlert($message);
        } else {
            log_message('debug', '[AlertSystem] Email-Alerts nicht gesendet. Kanäle: ' . implode(',', $channels) . ', Aktiviert: ' . ($config->emailEnabled ? 'ja' : 'nein'));
        }

        // Send SMS for CRITICAL errors only
        if (in_array('sms', $channels) && $config->smsEnabled && !empty($config->smsRecipients)) {
            $this->sendSmsAlert($message);
        }

        // Update rate limit tracker
        $this->recordAlert($errorKey);
    }

    /**
     * Determine the severity level of the error
     */
    private function determineSeverity(Throwable $exception, int $statusCode): string
    {
        // CRITICAL: Database errors, payment errors, fatal system errors
        $criticalKeywords = ['Database', 'PDO', 'Payment', 'Stripe', 'PayPal', 'Fatal error'];
        foreach ($criticalKeywords as $keyword) {
            if (stripos($exception->getMessage(), $keyword) !== false) {
                return 'critical';
            }
        }

        // CRITICAL: Errors in critical files
        $criticalPaths = ['/Database/', '/Payment/', '/Order/', '/Checkout/'];
        foreach ($criticalPaths as $path) {
            if (strpos($exception->getFile(), $path) !== false) {
                return 'critical';
            }
        }

        // HIGH: All 500 errors
        if ($statusCode >= 500) {
            return 'high';
        }

        // MEDIUM: Everything else
        return 'medium';
    }

    /**
     * Generate a unique key for this error type (for rate limiting)
     */
    private function getErrorKey(Throwable $exception): string
    {
        return md5($exception->getFile() . ':' . $exception->getLine() . ':' . get_class($exception));
    }

    /**
     * Check if we should send an alert based on rate limiting
     */
    private function shouldSendAlert(string $errorKey): bool
    {
        $file = self::getRateLimitFile();
        if (!file_exists($file)) {
            return true;
        }

        $data = json_decode(file_get_contents($file), true) ?? [];
        $now = time();

        // Clean up old entries
        $data = array_filter($data, fn($timestamps) =>
            count(array_filter($timestamps, fn($t) => $now - $t < self::RATE_LIMIT_WINDOW)) > 0
        );

        // Check if this error has hit the rate limit
        if (isset($data[$errorKey])) {
            $recentAlerts = array_filter($data[$errorKey], fn($t) => $now - $t < self::RATE_LIMIT_WINDOW);
            if (count($recentAlerts) >= self::MAX_ALERTS_PER_WINDOW) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record that an alert was sent
     */
    private function recordAlert(string $errorKey): void
    {
        $file = self::getRateLimitFile();
        $data = [];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? [];
        }

        if (!isset($data[$errorKey])) {
            $data[$errorKey] = [];
        }

        $data[$errorKey][] = time();

        // Keep only recent timestamps
        $now = time();
        $data[$errorKey] = array_filter($data[$errorKey], fn($t) => $now - $t < self::RATE_LIMIT_WINDOW);

        // Ensure cache directory exists
        $cacheDir = dirname($file);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        file_put_contents($file, json_encode($data));
    }

    /**
     * Build the alert message
     */
    private function buildAlertMessage(Throwable $exception, int $statusCode): array
    {
        $config = config('AlertWebhooks');

        return [
            'server' => $config->serverName ?? gethostname(),
            'environment' => ENVIRONMENT,
            'timestamp' => date('d.m.Y H:i:s'), // Deutsches Format
            'status_code' => $statusCode,
            'error_type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'trace' => $this->getCleanTrace($exception),
        ];
    }

    /**
     * Get cleaned up stack trace (first 5 lines)
     */
    private function getCleanTrace(Throwable $exception): array
    {
        $trace = $exception->getTrace();
        $cleanTrace = [];

        for ($i = 0; $i < min(5, count($trace)); $i++) {
            $cleanTrace[] = sprintf(
                "%s:%s %s%s%s()",
                $trace[$i]['file'] ?? 'unknown',
                $trace[$i]['line'] ?? '?',
                $trace[$i]['class'] ?? '',
                $trace[$i]['type'] ?? '',
                $trace[$i]['function'] ?? ''
            );
        }

        return $cleanTrace;
    }

    /**
     * Send webhook to the configured service
     */
    private function sendWebhook(string $url, string $type, array $message): void
    {
        $payload = match($type) {
            'slack' => $this->formatSlack($message),
            'discord' => $this->formatDiscord($message),
            'mattermost' => $this->formatSlack($message), // Same format as Slack
            'generic' => $message,
            default => $message,
        };

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200 && $httpCode !== 204) {
            log_message('error', "[AlertSystem] Webhook-Versand fehlgeschlagen an {$url}. HTTP Code: {$httpCode}");
        }

        curl_close($ch);
    }

    /**
     * Send SMS alert using existing TwilioService or InfobipService
     */
    private function sendSmsAlert(array $message): void
    {
        $config = config('AlertWebhooks');

        if (empty($config->smsRecipients)) {
            return;
        }

        // Check global SMS rate limits
        if (!$this->checkSmsRateLimit($config)) {
            log_message('warning', '[AlertSystem] SMS blockiert: Globales Rate-Limit erreicht');
            return;
        }

        // Build short SMS message (SMS has character limits)
        $severityText = match($message['severity']) {
            'critical' => 'KRITISCH',
            'high' => 'FEHLER',
            'medium' => 'WARNUNG',
            default => 'FEHLER',
        };
        $smsText = "🔥 [{$severityText}] Fehler auf {$message['server']}\n\n";
        $smsText .= substr($message['message'], 0, 100);
        $smsText .= "\n\nDatei: " . basename($message['file']) . ":{$message['line']}";
        $smsText .= "\nZeit: {$message['timestamp']}";

        try {
            $smsService = match($config->smsProvider) {
                'infobip' => new \App\Libraries\InfobipService(),
                'twilio' => new \App\Libraries\TwilioService(),
                default => new \App\Libraries\TwilioService(),
            };

            foreach ($config->smsRecipients as $recipient) {
                if ($config->smsProvider === 'infobip') {
                    $result = $smsService->sendSms($recipient, $smsText);
                    if ($result['success'] ?? false) {
                        log_message('info', "[AlertSystem] SMS-Alert gesendet an {$recipient} via Infobip");
                        $this->recordSms(); // Track SMS for rate limiting
                    } else {
                        log_message('error', "[AlertSystem] SMS-Versand fehlgeschlagen an {$recipient}: " . ($result['error'] ?? 'Unbekannter Fehler'));
                    }
                } else {
                    // Twilio
                    $success = $smsService->sendSms($recipient, $smsText);
                    if ($success) {
                        log_message('info', "[AlertSystem] SMS-Alert gesendet an {$recipient} via Twilio");
                        $this->recordSms(); // Track SMS for rate limiting
                    } else {
                        log_message('error', "[AlertSystem] SMS-Versand fehlgeschlagen an {$recipient} via Twilio");
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', "[AlertSystem] SMS-System Fehler: " . $e->getMessage());
        }
    }

    /**
     * Check if we're within global SMS rate limits
     */
    private function checkSmsRateLimit($config): bool
    {
        $file = self::getSmsRateLimitFile();

        if (!file_exists($file)) {
            return true;
        }

        $data = json_decode(file_get_contents($file), true) ?? [];
        $now = time();

        // Clean up old entries
        $data = array_filter($data, fn($timestamp) => $now - $timestamp < 86400); // Keep last 24h

        // Check hourly limit
        $lastHour = array_filter($data, fn($t) => $now - $t < 3600);
        if (count($lastHour) >= $config->maxSmsPerHour) {
            log_message('warning', sprintf(
                '[AlertSystem] SMS Stunden-Limit erreicht: %d/%d in letzter Stunde',
                count($lastHour),
                $config->maxSmsPerHour
            ));
            return false;
        }

        // Check daily limit
        if (count($data) >= $config->maxSmsPerDay) {
            log_message('warning', sprintf(
                '[AlertSystem] SMS Tages-Limit erreicht: %d/%d in letzten 24h',
                count($data),
                $config->maxSmsPerDay
            ));
            return false;
        }

        return true;
    }

    /**
     * Record that an SMS was sent
     */
    private function recordSms(): void
    {
        $file = self::getSmsRateLimitFile();
        $data = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? [];
        }

        $data[] = time();

        // Keep only last 24h
        $now = time();
        $data = array_filter($data, fn($t) => $now - $t < 86400);
        $data = array_values($data); // Re-index array

        // Ensure cache directory exists
        $cacheDir = dirname($file);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        file_put_contents($file, json_encode($data));
    }

    /**
     * Format message for Slack/Mattermost
     */
    private function formatSlack(array $message): array
    {
        $emoji = match(true) {
            $message['status_code'] >= 500 => ':fire:',
            strpos($message['message'], 'Database') !== false => ':database:',
            default => ':warning:',
        };

        return [
            'text' => "{$emoji} *Critical Error on {$message['server']}*",
            'attachments' => [
                [
                    'color' => 'danger',
                    'fields' => [
                        [
                            'title' => 'Error Type',
                            'value' => $message['error_type'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Status Code',
                            'value' => (string)$message['status_code'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Message',
                            'value' => $message['message'],
                            'short' => false,
                        ],
                        [
                            'title' => 'Location',
                            'value' => "{$message['file']}:{$message['line']}",
                            'short' => false,
                        ],
                        [
                            'title' => 'Request',
                            'value' => "{$message['method']} {$message['url']}",
                            'short' => false,
                        ],
                        [
                            'title' => 'Stack Trace',
                            'value' => implode("\n", $message['trace']),
                            'short' => false,
                        ],
                    ],
                    'footer' => "Environment: {$message['environment']}",
                    'ts' => time(),
                ],
            ],
        ];
    }

    /**
     * Send email alert
     */
    private function sendEmailAlert(array $message): void
    {
        $config = config('AlertWebhooks');
        $email = \Config\Services::email();

        $severityText = match($message['severity']) {
            'critical' => 'KRITISCHER FEHLER',
            'high' => 'FEHLER',
            'medium' => 'WARNUNG',
            default => 'FEHLER',
        };

        $subject = "[{$config->serverName}] {$severityText}: {$message['error_type']}";

        $severityEmoji = match($message['severity']) {
            'critical' => '🔥',
            'high' => '⚠️',
            'medium' => '📝',
            default => '⚠️',
        };

        $headerColor = match($message['severity']) {
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            default => '#dc3545',
        };

        $body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: {$headerColor}; color: white; padding: 15px; border-radius: 5px; }
        .section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid {$headerColor}; }
        .label { font-weight: bold; color: #666; }
        .value { color: #333; font-family: monospace; }
        .trace { background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>{$severityEmoji} Kritischer Fehler erkannt</h2>
            <p>Server: {$message['server']}</p>
        </div>

        <div class='section'>
            <p><span class='label'>Zeit:</span> <span class='value'>{$message['timestamp']}</span></p>
            <p><span class='label'>Umgebung:</span> <span class='value'>{$message['environment']}</span></p>
            <p><span class='label'>Status Code:</span> <span class='value'>{$message['status_code']}</span></p>
            <p><span class='label'>Schweregrad:</span> <span class='value'>" . strtoupper($message['severity']) . "</span></p>
        </div>

        <div class='section'>
            <p><span class='label'>Fehlertyp:</span> <span class='value'>{$message['error_type']}</span></p>
            <p><span class='label'>Fehlermeldung:</span></p>
            <p class='value'>{$message['message']}</p>
        </div>

        <div class='section'>
            <p><span class='label'>Datei:</span> <span class='value'>{$message['file']}:{$message['line']}</span></p>
            <p><span class='label'>Request:</span> <span class='value'>{$message['method']} {$message['url']}</span></p>
        </div>

        <div class='section'>
            <p><span class='label'>Stack Trace:</span></p>
            <div class='trace'>" . implode('<br>', array_map('htmlspecialchars', $message['trace'])) . "</div>
        </div>
    </div>
</body>
</html>
        ";

        $email->setTo($config->emailRecipients);

        // Use emailFrom from config, or fall back to .env email.fromEmail
        if (!empty($config->emailFrom)) {
            $email->setFrom($config->emailFrom, $config->serverName . ' Alert System');
        }
        // If emailFrom is empty, CI4 will use email.fromEmail from .env automatically

        $email->setSubject($subject);
        $email->setMailType('html'); // Send as HTML
        $email->setMessage($body);

        if (!$email->send()) {
            log_message('error', '[AlertSystem] Email-Versand fehlgeschlagen: ' . $email->printDebugger(['headers']));
        } else {
            log_message('info', '[AlertSystem] Email-Alert erfolgreich gesendet');
        }
    }

    /**
     * Format message for Discord
     */
    private function formatDiscord(array $message): array
    {
        $color = match(true) {
            $message['status_code'] >= 500 => 0xFF0000, // Red
            default => 0xFFA500, // Orange
        };

        return [
            'embeds' => [
                [
                    'title' => "🔥 Critical Error on {$message['server']}",
                    'description' => $message['message'],
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'Error Type',
                            'value' => $message['error_type'],
                            'inline' => true,
                        ],
                        [
                            'name' => 'Status Code',
                            'value' => (string)$message['status_code'],
                            'inline' => true,
                        ],
                        [
                            'name' => 'Location',
                            'value' => "{$message['file']}:{$message['line']}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Request',
                            'value' => "{$message['method']} {$message['url']}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Stack Trace',
                            'value' => '```' . implode("\n", array_slice($message['trace'], 0, 3)) . '```',
                            'inline' => false,
                        ],
                    ],
                    'footer' => [
                        'text' => "Environment: {$message['environment']}",
                    ],
                    'timestamp' => date('c'),
                ],
            ],
        ];
    }
}
