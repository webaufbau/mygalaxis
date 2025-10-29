<?php

require __DIR__ . '/../vendor/autoload.php';

// Manueller Test des SiteConfigLoaders
$loader = new \App\Libraries\SiteConfigLoader();

echo "=== SiteConfigLoader Debug ===\n\n";

// Verwende Reflection um private properties zu lesen
$reflection = new ReflectionClass($loader);
$valuesProperty = $reflection->getProperty('values');
$valuesProperty->setAccessible(true);
$values = $valuesProperty->getValue($loader);

echo "Geladene Values:\n";
echo "  name: " . ($values['name'] ?? 'NULL') . "\n";
echo "  email: " . ($values['email'] ?? 'NULL') . "\n";
echo "  domain_extension: " . ($values['domain_extension'] ?? 'NULL') . "\n\n";

// Teste Magic Getter
echo "Via Magic Getter (__get):\n";
echo "  loader->name: " . ($loader->name ?? 'NULL') . "\n";
echo "  loader->email: " . ($loader->email ?? 'NULL') . "\n";
echo "  loader->domain_extension: " . ($loader->domain_extension ?? 'NULL') . "\n\n";

// Teste mit getEmailFromName
if (file_exists(__DIR__ . '/../app/Helpers/email_template_helper.php')) {
    require_once __DIR__ . '/../app/Helpers/email_template_helper.php';
    echo "Test getEmailFromName():\n";
    $fromName = getEmailFromName($loader);
    echo "  Ergebnis: '$fromName'\n";
}

echo "\n=== Debug abgeschlossen ===\n";
