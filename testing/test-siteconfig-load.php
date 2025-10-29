<?php

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

// Load SiteConfig
$siteConfig = siteconfig();

echo "=== SiteConfig Test ===\n\n";
echo "Name: " . ($siteConfig->name ?? 'NULL') . "\n";
echo "Email: " . ($siteConfig->email ?? 'NULL') . "\n";
echo "Domain Extension: " . ($siteConfig->domain_extension ?? 'NULL') . "\n";

// Test getEmailFromName
helper('email_template');
$fromName = getEmailFromName($siteConfig);
echo "\n=== Test getEmailFromName() ===\n";
echo "Absendername: '{$fromName}'\n";

echo "\n=== Test abgeschlossen ===\n";
