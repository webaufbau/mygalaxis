<?php

/**
 * Testet Rabatt-E-Mail für verschiedene Offer-Types
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

// Finde Offerten mit verschiedenen Types
$types = ['gardening', 'cleaning', 'move', 'electrician', 'painting'];

foreach ($types as $type) {
    $result = $mysqli->query("
        SELECT id, type, zip, city, platform, price
        FROM offers
        WHERE type = '{$type}'
        LIMIT 1
    ");

    if ($row = $result->fetch_assoc()) {
        echo "Typ: {$type}\n";
        echo "  Offerte ID: {$row['id']}\n";
        echo "  Location: {$row['zip']} {$row['city']}\n";
        echo "  Platform: " . ($row['platform'] ?? 'NULL') . "\n";
        echo "  Price: {$row['price']}\n\n";
    }
}

echo "\n=== Test mit Offerte #447 (gardening) ===\n";
echo "Führe aus: ddev exec php spark offers:discount-old\n";
