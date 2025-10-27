<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration for real-time error alerting via webhooks
 *
 * Setup instructions:
 *
 * For Slack:
 * 1. Go to https://api.slack.com/messaging/webhooks
 * 2. Create an Incoming Webhook
 * 3. Copy the webhook URL
 *
 * For Discord:
 * 1. Go to your Discord server settings
 * 2. Integrations -> Webhooks -> New Webhook
 * 3. Copy the webhook URL
 *
 * For Mattermost:
 * 1. Go to Integrations -> Incoming Webhooks
 * 2. Add Incoming Webhook
 * 3. Copy the webhook URL
 */
class AlertWebhooks extends BaseConfig
{
    /**
     * Enable or disable the alert system
     */
    public bool $enabled = true;

    /**
     * Server name to identify which server sent the alert
     * This will appear in all alert messages
     */
    public string $serverName = 'MyGalaxis Development';

    /**
     * Email alert configuration
     */
    public bool $emailEnabled = true;
    public array $emailRecipients = [
        'logs@webaufbau.com',
    ];
    public string $emailFrom = ''; // Uses email.fromEmail from .env

    /**
     * SMS alert configuration (optional - for CRITICAL errors only)
     *
     * Uses your existing SMS service (Twilio or Infobip)
     *
     * Note: SMS costs money per message! Only use for critical errors.
     */
    public bool $smsEnabled = false;
    public string $smsProvider = 'twilio'; // 'twilio' or 'infobip'
    public array $smsRecipients = [
        // '+41791234567', // Your phone
    ];

    /**
     * Global SMS rate limits (prevents SMS spam and cost explosion)
     *
     * Limits are TOTAL across all error types
     */
    public int $maxSmsPerHour = 10;   // Max 10 SMS pro Stunde (costs ~CHF 0.80/hour)
    public int $maxSmsPerDay = 50;    // Max 50 SMS pro Tag (costs ~CHF 4/day)

    /**
     * Severity levels determine which channels get notified
     *
     * CRITICAL: Database down, payment errors, fatal crashes
     *          → SMS + Email + Slack
     *
     * HIGH: Server errors (500), uncaught exceptions
     *      → Email + Slack
     *
     * MEDIUM: Warnings, deprecation notices
     *        → Slack only (or disabled)
     */
    public array $severityChannels = [
        'critical' => ['sms', 'email', 'slack'],
        'high' => ['email', 'slack'],
        'medium' => ['slack'],
    ];

    /**
     * Webhook configurations
     *
     * Supported types: 'slack', 'discord', 'mattermost', 'generic'
     *
     * Example configurations:
     */
    public array $webhooks = [
        [
            'enabled' => false,
            'type' => 'slack',
            'url' => 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL',
        ],
        [
            'enabled' => false,
            'type' => 'discord',
            'url' => 'https://discord.com/api/webhooks/YOUR/WEBHOOK',
        ],
        [
            'enabled' => false,
            'type' => 'mattermost',
            'url' => 'https://your-mattermost.com/hooks/YOUR-WEBHOOK-ID',
        ],
        // Generic webhook sends raw JSON data
        [
            'enabled' => false,
            'type' => 'generic',
            'url' => 'https://your-custom-endpoint.com/webhook',
        ],
    ];

    /**
     * Rate limiting settings (configured in AlertExceptionHandler)
     *
     * - Max 5 alerts per error type per 5 minutes
     * - Prevents notification spam for recurring errors
     */
}
