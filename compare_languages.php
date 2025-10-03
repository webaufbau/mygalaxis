<?php

// Language Key Comparison Script
// Compares DE (German) keys with FR, IT, EN and reports missing keys

$baseDir = __DIR__ . '/app/Language/';
$languages = ['fr', 'it', 'en'];
$referenceLanguage = 'de';

function flattenArray($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $newKey));
        } else {
            $result[$newKey] = $value;
        }
    }
    return $result;
}

function getLanguageKeys($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    $keys = include $filePath;
    return is_array($keys) ? flattenArray($keys) : [];
}

// Get all DE files
$deFiles = glob($baseDir . $referenceLanguage . '/*.php');

$report = "# Language Keys Comparison Report\n";
$report .= "Base Language: DE (German)\n";
$report .= "Comparing with: FR (French), IT (Italian), EN (English)\n";
$report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
$report .= str_repeat("=", 80) . "\n\n";

$totalMissing = ['fr' => 0, 'it' => 0, 'en' => 0];

foreach ($deFiles as $deFile) {
    $fileName = basename($deFile);
    $deKeys = getLanguageKeys($deFile);

    if (empty($deKeys)) {
        continue;
    }

    $report .= "## File: $fileName\n";
    $report .= "DE Keys: " . count($deKeys) . "\n\n";

    $hasMissing = false;

    foreach ($languages as $lang) {
        $langFile = $baseDir . $lang . '/' . $fileName;
        $langKeys = getLanguageKeys($langFile);

        if ($langKeys === null) {
            $report .= "⚠️  **$lang**: FILE MISSING\n";
            $totalMissing[$lang] += count($deKeys);
            $hasMissing = true;
            continue;
        }

        $missingKeys = array_diff_key($deKeys, $langKeys);

        if (!empty($missingKeys)) {
            $report .= "❌ **$lang**: " . count($missingKeys) . " missing key(s)\n";
            foreach (array_keys($missingKeys) as $key) {
                $report .= "   - $key\n";
            }
            $totalMissing[$lang] += count($missingKeys);
            $hasMissing = true;
        } else {
            $report .= "✅ **$lang**: Complete (" . count($langKeys) . " keys)\n";
        }
    }

    if (!$hasMissing) {
        $report .= "✨ All languages complete!\n";
    }

    $report .= "\n" . str_repeat("-", 80) . "\n\n";
}

$report .= "\n" . str_repeat("=", 80) . "\n";
$report .= "# Summary\n\n";
foreach ($totalMissing as $lang => $count) {
    $report .= "**" . strtoupper($lang) . "**: $count missing key(s) total\n";
}

echo $report;

// Also save to file
file_put_contents(__DIR__ . '/language_comparison_report.txt', $report);
echo "\n\n📄 Report saved to: language_comparison_report.txt\n";
