<?php
/**
 * Analyze Email Logs for Duplicates
 *
 * This script scans CodeIgniter log files for [EMAIL-SENT] entries
 * and detects duplicate emails sent to the same recipient.
 *
 * Usage: php analyze-duplicate-emails.php [hours]
 * Example: php analyze-duplicate-emails.php 24  (analyze last 24 hours)
 */

$hoursToAnalyze = isset($argv[1]) ? (int)$argv[1] : 24;
$logDir = __DIR__ . '/writable/logs';

echo "===========================================\n";
echo "Email Duplicate Analyzer\n";
echo "===========================================\n";
echo "Analyzing last {$hoursToAnalyze} hours...\n\n";

// Get all log files from the last N hours
$cutoffTime = time() - ($hoursToAnalyze * 3600);
$logFiles = glob($logDir . '/*.log');
$emails = [];

foreach ($logFiles as $logFile) {
    $fileTime = filemtime($logFile);
    if ($fileTime < $cutoffTime) {
        continue; // Skip old log files
    }

    echo "Reading: " . basename($logFile) . "\n";

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Look for [EMAIL-SENT] entries
        if (strpos($line, '[EMAIL-SENT]') !== false) {
            // Extract JSON data
            preg_match('/\[EMAIL-SENT\] (.+)$/', $line, $matches);
            if (isset($matches[1])) {
                $data = json_decode($matches[1], true);
                if ($data && isset($data['to'], $data['subject'], $data['timestamp'])) {
                    // Parse timestamp from log line (format: YYYY-MM-DD HH:MM:SS)
                    $emailTime = strtotime($data['timestamp']);

                    // Skip if outside time window
                    if ($emailTime < $cutoffTime) {
                        continue;
                    }

                    // Store email data
                    $emails[] = [
                        'timestamp' => $data['timestamp'],
                        'to' => $data['to'],
                        'cc' => $data['cc'] ?? '',
                        'subject' => $data['subject'],
                        'unix_time' => $emailTime,
                    ];
                }
            }
        }
    }
}

echo "\nTotal emails found: " . count($emails) . "\n\n";

if (empty($emails)) {
    echo "No emails found in the specified time period.\n";
    echo "Make sure emails have been sent after the logging was implemented.\n";
    exit(0);
}

// Sort by timestamp
usort($emails, function($a, $b) {
    return $a['unix_time'] - $b['unix_time'];
});

// Detect duplicates
echo "===========================================\n";
echo "Duplicate Detection Analysis\n";
echo "===========================================\n\n";

$duplicates = [];
$seenEmails = [];

foreach ($emails as $email) {
    // Create a unique key for recipient + subject
    $key = $email['to'] . '|||' . $email['subject'];

    if (!isset($seenEmails[$key])) {
        $seenEmails[$key] = [];
    }

    $seenEmails[$key][] = $email;
}

// Find duplicates (same recipient + subject sent multiple times)
$foundDuplicates = false;
foreach ($seenEmails as $key => $emailList) {
    if (count($emailList) > 1) {
        $foundDuplicates = true;
        list($recipient, $subject) = explode('|||', $key, 2);

        echo "⚠️  DUPLICATE DETECTED\n";
        echo "Recipient: {$recipient}\n";
        echo "Subject: {$subject}\n";
        echo "Sent " . count($emailList) . " times:\n";

        foreach ($emailList as $idx => $email) {
            echo "  " . ($idx + 1) . ". {$email['timestamp']}\n";
        }

        // Calculate time difference between first and last
        if (count($emailList) >= 2) {
            $timeDiff = $emailList[count($emailList) - 1]['unix_time'] - $emailList[0]['unix_time'];
            $minutes = floor($timeDiff / 60);
            $seconds = $timeDiff % 60;
            echo "  Time between first and last: {$minutes}m {$seconds}s\n";
        }

        echo "\n";
    }
}

if (!$foundDuplicates) {
    echo "✓ No duplicates found! All emails sent only once.\n\n";
}

// Statistics
echo "===========================================\n";
echo "Statistics\n";
echo "===========================================\n";
echo "Total emails analyzed: " . count($emails) . "\n";
echo "Unique recipient+subject combinations: " . count($seenEmails) . "\n";

// Count duplicates
$duplicateCount = 0;
foreach ($seenEmails as $emailList) {
    if (count($emailList) > 1) {
        $duplicateCount += count($emailList) - 1; // Count extra sends
    }
}
echo "Duplicate sends: {$duplicateCount}\n";

// Top recipients
echo "\nTop recipients:\n";
$recipientCounts = [];
foreach ($emails as $email) {
    $to = $email['to'];
    if (!isset($recipientCounts[$to])) {
        $recipientCounts[$to] = 0;
    }
    $recipientCounts[$to]++;
}
arsort($recipientCounts);
$topRecipients = array_slice($recipientCounts, 0, 5, true);
foreach ($topRecipients as $recipient => $count) {
    echo "  {$recipient}: {$count} emails\n";
}

echo "\n===========================================\n";
echo "Analysis complete!\n";
echo "===========================================\n";
