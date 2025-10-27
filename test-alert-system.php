<?php

/**
 * Test script for error alert system
 *
 * Usage:
 * php test-alert-system.php
 *
 * This will trigger test errors to verify your webhook configuration
 */

// Load CodeIgniter bootstrap
require_once __DIR__ . '/vendor/autoload.php';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Error Alert System - Test Script                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Check if system is enabled
$configPath = __DIR__ . '/app/Config/AlertWebhooks.php';
if (!file_exists($configPath)) {
    echo "❌ Config file not found: {$configPath}\n";
    exit(1);
}

// Simple check if any webhook is enabled
$configContent = file_get_contents($configPath);
$enabled = strpos($configContent, "'enabled' => true") !== false || strpos($configContent, '"enabled" => true') !== false;

if (!$enabled) {
    echo "⚠️  Alert system is disabled or no webhooks are configured.\n";
    echo "   Please edit: app/Config/AlertWebhooks.php\n\n";
    echo "   Steps:\n";
    echo "   1. Set \$enabled = true\n";
    echo "   2. Configure at least one webhook URL\n";
    echo "   3. Set that webhook's 'enabled' => true\n\n";
    exit(0);
}

echo "✓ Alert system appears to be configured\n\n";
echo "Select test to run:\n";
echo "1. Test webhook connectivity (sends a test alert)\n";
echo "2. Trigger a 500 Internal Server Error\n";
echo "3. Trigger a database error simulation\n";
echo "4. Trigger multiple errors (rate limiting test)\n";
echo "0. Exit\n\n";
echo "Enter choice: ";

$choice = trim(fgets(STDIN));

switch ($choice) {
    case '1':
        echo "\n🔔 Sending test alert...\n";
        testWebhookConnectivity();
        break;

    case '2':
        echo "\n🔥 Triggering 500 error...\n";
        trigger500Error();
        break;

    case '3':
        echo "\n💾 Triggering database error...\n";
        triggerDatabaseError();
        break;

    case '4':
        echo "\n🔄 Triggering multiple errors (testing rate limiting)...\n";
        testRateLimiting();
        break;

    case '0':
        echo "Goodbye!\n";
        exit(0);

    default:
        echo "Invalid choice\n";
        exit(1);
}

function testWebhookConnectivity()
{
    // Create a simple test alert
    require __DIR__ . '/app/Config/AlertWebhooks.php';
    $config = new \Config\AlertWebhooks();

    $testMessage = [
        'server' => $config->serverName ?? 'Test Server',
        'environment' => 'TEST',
        'timestamp' => date('Y-m-d H:i:s'),
        'status_code' => 500,
        'error_type' => 'Test Alert',
        'message' => '✅ This is a test alert. If you see this, your webhook is working!',
        'file' => __FILE__,
        'line' => __LINE__,
        'url' => '/test',
        'method' => 'CLI',
        'trace' => ['Test trace line 1', 'Test trace line 2'],
    ];

    $sentCount = 0;
    foreach ($config->webhooks as $webhook) {
        if ($webhook['enabled'] ?? false) {
            echo "  Sending to {$webhook['type']} webhook...\n";

            $payload = match($webhook['type']) {
                'slack', 'mattermost' => formatSlack($testMessage),
                'discord' => formatDiscord($testMessage),
                default => $testMessage,
            };

            $ch = curl_init($webhook['url']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 204) {
                echo "  ✓ Success! HTTP {$httpCode}\n";
                $sentCount++;
            } else {
                echo "  ✗ Failed! HTTP {$httpCode}\n";
                echo "    Response: " . substr($result, 0, 100) . "\n";
            }
        }
    }

    if ($sentCount > 0) {
        echo "\n✅ Test alert sent to {$sentCount} webhook(s)!\n";
        echo "   Check your Slack/Discord/Mattermost for the test message.\n";
    } else {
        echo "\n⚠️  No webhooks were sent. Check your configuration.\n";
    }
}

function trigger500Error()
{
    echo "  This will trigger a real error in the system.\n";
    echo "  Navigate to: http://your-site.com/trigger-test-error\n";
    echo "  Or create a test endpoint that throws an exception.\n";
}

function triggerDatabaseError()
{
    echo "  This simulates a database error message.\n";
    echo "  In a real scenario, this would be caught by the exception handler.\n";
}

function testRateLimiting()
{
    echo "  Rate limiting prevents alert spam.\n";
    echo "  The system allows max 5 alerts per error type per 5 minutes.\n";
    echo "  After that, alerts for the same error are suppressed.\n";
}

function formatSlack(array $message): array
{
    return [
        'text' => "🧪 *Test Alert from {$message['server']}*",
        'attachments' => [
            [
                'color' => 'good',
                'fields' => [
                    [
                        'title' => 'Message',
                        'value' => $message['message'],
                        'short' => false,
                    ],
                    [
                        'title' => 'Status',
                        'value' => 'Webhook is working correctly!',
                        'short' => false,
                    ],
                ],
                'footer' => "Test from error alert system",
                'ts' => time(),
            ],
        ],
    ];
}

function formatDiscord(array $message): array
{
    return [
        'embeds' => [
            [
                'title' => "🧪 Test Alert from {$message['server']}",
                'description' => $message['message'],
                'color' => 0x00FF00, // Green
                'fields' => [
                    [
                        'name' => 'Status',
                        'value' => 'Webhook is working correctly!',
                        'inline' => false,
                    ],
                ],
                'footer' => [
                    'text' => 'Test from error alert system',
                ],
                'timestamp' => date('c'),
            ],
        ],
    ];
}
