<?php

require __DIR__ . '/../vendor/autoload.php';

// Test: Lade SiteConfig für NULL/leere Platform

echo "=== Test: SiteConfigLoader mit verschiedenen Platforms ===\n\n";

// Test 1: NULL Platform
echo "Test 1: NULL Platform\n";
$config1 = \App\Libraries\SiteConfigLoader::loadForPlatform(null);
echo "  name: '" . ($config1->name ?? 'NULL') . "'\n";
echo "  email: '" . ($config1->email ?? 'NULL') . "'\n";
echo "  domain_extension: '" . ($config1->domain_extension ?? 'NULL') . "'\n\n";

// Test 2: Leere Platform
echo "Test 2: Leere Platform ''\n";
$config2 = \App\Libraries\SiteConfigLoader::loadForPlatform('');
echo "  name: '" . ($config2->name ?? 'NULL') . "'\n";
echo "  email: '" . ($config2->email ?? 'NULL') . "'\n";
echo "  domain_extension: '" . ($config2->domain_extension ?? 'NULL') . "'\n\n";

// Test 3: Nicht existierende Platform
echo "Test 3: Nicht existierende Platform 'my_notexist_ch'\n";
$config3 = \App\Libraries\SiteConfigLoader::loadForPlatform('my_notexist_ch');
echo "  name: '" . ($config3->name ?? 'NULL') . "'\n";
echo "  email: '" . ($config3->email ?? 'NULL') . "'\n";
echo "  domain_extension: '" . ($config3->domain_extension ?? 'NULL') . "'\n\n";

// Test getEmailFromName mit jedem
require_once __DIR__ . '/../app/Helpers/email_template_helper.php';

echo "\n=== Test getEmailFromName() mit verschiedenen Configs ===\n\n";

echo "Config 1 (NULL platform): '" . getEmailFromName($config1) . "'\n";
echo "Config 2 (leere platform): '" . getEmailFromName($config2) . "'\n";
echo "Config 3 (nicht existierende platform): '" . getEmailFromName($config3) . "'\n";

echo "\n=== Test abgeschlossen ===\n";
