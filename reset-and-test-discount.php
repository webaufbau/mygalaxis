<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

// Reset last_price_update_sent für Offerte #453
$mysqli->query("UPDATE offers SET last_price_update_sent = NULL WHERE id = 453");
echo "✓ Reset last_price_update_sent für Offerte #453\n\n";

echo "Führe jetzt aus: ddev exec php spark offers:discount-old\n";
echo "Erwartung: E-Mail sollte NICHT \"Vielen Dank für Ihre Anfrage!\" enthalten\n";
echo "           Sondern den Inhalt aus emails/price_update.php verwenden\n";
