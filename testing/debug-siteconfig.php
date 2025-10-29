<?php

// Debug: Warum ist $siteConfig->name leer?

echo "=== Debug SiteConfig ===\n\n";

// 1. Prüfe site_settings.json direkt
$jsonPath = __DIR__ . '/../writable/config/site_settings.json';
echo "JSON Pfad: $jsonPath\n";
echo "Existiert: " . (file_exists($jsonPath) ? "JA" : "NEIN") . "\n\n";

if (file_exists($jsonPath)) {
    $jsonData = json_decode(file_get_contents($jsonPath), true);
    echo "JSON Inhalt (name):\n";
    echo "  name: " . ($jsonData['name'] ?? 'NULL') . "\n";
    echo "  domain_extension: " . ($jsonData['domain_extension'] ?? 'NULL') . "\n\n";
}

// 2. Test getEmailFromName mit verschiedenen Szenarien
function getEmailFromName($siteConfig): string
{
    echo "\n--- getEmailFromName Debug ---\n";
    echo "siteConfig->name: '" . ($siteConfig->name ?? 'NULL') . "'\n";
    echo "siteConfig->domain_extension: '" . ($siteConfig->domain_extension ?? 'NULL') . "'\n";

    $domain = '';
    if (!empty($siteConfig->name)) {
        $domain = explode(' ', $siteConfig->name)[0];
        echo "Domain (extrahiert): '$domain'\n";
    } else {
        echo "WARNING: siteConfig->name ist leer!\n";
    }
    $domainExtension = $siteConfig->domain_extension ?? '.ch';
    $result = ucfirst($domain) . $domainExtension;
    echo "Ergebnis: '$result'\n";
    return $result;
}

// Test 1: Korrekter Config
echo "\n=== Test 1: Korrekte Config ===\n";
$config1 = (object)[
    'name' => 'Offertenschweiz',
    'domain_extension' => '.ch'
];
$result1 = getEmailFromName($config1);

// Test 2: Leerer Name
echo "\n=== Test 2: Leerer Name ===\n";
$config2 = (object)[
    'name' => '',
    'domain_extension' => '.ch'
];
$result2 = getEmailFromName($config2);

// Test 3: NULL Name
echo "\n=== Test 3: NULL Name ===\n";
$config3 = (object)[
    'domain_extension' => '.ch'
];
$result3 = getEmailFromName($config3);

echo "\n=== Test abgeschlossen ===\n";
