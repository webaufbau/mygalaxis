<?php
/**
 * Test Email Logging
 *
 * This script tests the email logging functionality by sending a test email.
 * Usage: php test-email-logging.php
 */

// Load CodeIgniter
require __DIR__ . '/vendor/autoload.php';

$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require $pathsPath;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require $bootstrap;

$app = Config\Services::codeigniter();
$app->initialize();

// Get email service
$email = \Config\Services::email();

// Send a test email
echo "===========================================\n";
echo "Email Logging Test\n";
echo "===========================================\n\n";

$testEmail = 'test@example.com';
$subject = 'Test Email - Logging Test ' . date('Y-m-d H:i:s');

echo "Sending test email to: {$testEmail}\n";
echo "Subject: {$subject}\n\n";

$email->setFrom('info@galaxisgroup.ch', 'GalaxisGroup Test');
$email->setTo($testEmail);
$email->setSubject($subject);
$email->setMessage('<h1>Test Email</h1><p>This is a test email to verify the logging functionality.</p>');
$email->setMailType('html');

$result = $email->send();

if ($result) {
    echo "✓ Email sent successfully!\n\n";
} else {
    echo "✗ Email sending failed!\n";
    echo $email->printDebugger(['headers']) . "\n\n";
}

// Now check the log file
echo "===========================================\n";
echo "Checking Log File\n";
echo "===========================================\n\n";

$logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
echo "Log file: {$logFile}\n\n";

if (file_exists($logFile)) {
    // Read last 50 lines
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastLines = array_slice($lines, -50);

    echo "Last EMAIL-SENT entries:\n";
    echo "-------------------------------------------\n";

    $found = false;
    foreach ($lastLines as $line) {
        if (strpos($line, '[EMAIL-SENT]') !== false) {
            echo $line . "\n";
            $found = true;
        }
    }

    if (!$found) {
        echo "No [EMAIL-SENT] entries found in last 50 lines.\n";
    }
} else {
    echo "Log file does not exist yet: {$logFile}\n";
}

echo "\n===========================================\n";
echo "Test Complete!\n";
echo "===========================================\n";
echo "\nNext steps:\n";
echo "1. Check the log file for [EMAIL-SENT] entries\n";
echo "2. Run: php analyze-duplicate-emails.php 1\n";
echo "   (to analyze emails from the last 1 hour)\n";
