<?php

require 'vendor/autoload.php';

$bootstrap = \CodeIgniter\Config\Services::autoloader();
$bootstrap->initialize(new \Config\Autoload(), new \Config\Modules());

// Lade CodeIgniter
$app = new \CodeIgniter\CodeIgniter(new \Config\App());
$app->initialize();

$mysqli = new mysqli('db', 'db', 'db', 'db');

// Reset last_price_update_sent
$mysqli->query("UPDATE offers SET last_price_update_sent = NULL WHERE id = 453");
echo "Reset last_price_update_sent für Offerte #453\n";

// Führe discount command aus
echo "\nFühre Rabatt-Command aus...\n";
passthru("cd /var/www/html && php spark offers:discount-old 2>&1");

echo "\n\nPrüfe MailPit: https://mygalaxis.ddev.site:8026\n";
