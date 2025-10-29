<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

// Ändere den Rabattpreis leicht, um eine neue E-Mail auszulösen
$mysqli->query("UPDATE offers SET discounted_price = discounted_price + 0.50 WHERE id = 453");
echo "✓ Rabattpreis für Offerte #453 leicht geändert, um neue E-Mail auszulösen\n\n";

echo "Führe jetzt aus: ddev exec php spark offers:discount-old\n";
