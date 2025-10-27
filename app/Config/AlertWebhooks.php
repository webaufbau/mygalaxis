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
     * Set in .env: alert.enabled=true
     */
    public bool $enabled;

    /**
     * Server name to identify which server sent the alert
     * Set in .env: alert.serverName="MyGalaxis Production"
     * This will appear in all alert messages
     */
    public string $serverName;

    /**
     * Email alert configuration
     * Set in .env: alert.emailEnabled=true
     * Set in .env: alert.emailRecipients="logs@webaufbau.com,admin@example.com"
     */
    public bool $emailEnabled;
    public array $emailRecipients = [];
    public string $emailFrom = ''; // Uses email.fromEmail from .env

    public function __construct()
    {
        parent::__construct();

        // Load from .env with defaults
        $this->enabled = env('alert.enabled', false);
        $this->serverName = env('alert.serverName', 'Unknown Server');
        $this->emailEnabled = env('alert.emailEnabled', false);

        // Parse comma-separated email list from .env
        $emailList = env('alert.emailRecipients', '');
        if (!empty($emailList)) {
            $this->emailRecipients = array_map('trim', explode(',', $emailList));
        }

        $this->emailFrom = env('alert.emailFrom', '');

        // SMS configuration
        $this->smsEnabled = env('alert.smsEnabled', false);
        $this->smsProvider = env('alert.smsProvider', 'twilio');

        // Parse comma-separated SMS list from .env
        $smsList = env('alert.smsRecipients', '');
        if (!empty($smsList)) {
            $this->smsRecipients = array_map('trim', explode(',', $smsList));
        }

        // SMS rate limits
        $this->maxSmsPerHour = (int) env('alert.maxSmsPerHour', 10);
        $this->maxSmsPerDay = (int) env('alert.maxSmsPerDay', 50);
    }

    /**
     * SMS alert configuration (optional - for CRITICAL errors only)
     *
     * Set in .env:
     * alert.smsEnabled=true
     * alert.smsProvider="twilio"
     * alert.smsRecipients="+41791234567,+41791234568"
     *
     * Note: SMS costs money per message! Only use for critical errors.
     */
    public bool $smsEnabled;
    public string $smsProvider;
    public array $smsRecipients = [];

    /**
     * Global SMS rate limits (prevents SMS spam and cost explosion)
     *
     * Set in .env:
     * alert.maxSmsPerHour=10
     * alert.maxSmsPerDay=50
     *
     * Limits are TOTAL across all error types
     */
    public int $maxSmsPerHour;
    public int $maxSmsPerDay;

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
