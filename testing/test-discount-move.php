<?php

/**
 * Test Rabatt-E-Mail für Umzug
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

// Finde Umzug-Offerte
$result = $mysqli->query("
    SELECT id, type, zip, city, platform, price, discounted_price
    FROM offers
    WHERE type = 'move' AND price > 10
    LIMIT 1
");

if ($row = $result->fetch_assoc()) {
    $offerId = $row['id'];
    echo "=== Test Rabatt-E-Mail ===\n";
    echo "Offerte ID: {$offerId}\n";
    echo "Typ: {$row['type']}\n";
    echo "Location: {$row['zip']} {$row['city']}\n";
    echo "Platform: " . ($row['platform'] ?? 'NULL') . "\n";
    echo "Price: {$row['price']}\n";
    echo "Discounted: " . ($row['discounted_price'] ?? 'NULL') . "\n\n";

    // Setze einen niedrigeren Preis für Test
    $newPrice = 10.00;
    $mysqli->query("UPDATE offers SET discounted_price = {$newPrice} WHERE id = {$offerId}");

    echo "Neuer rabattierter Preis gesetzt: {$newPrice} CHF\n";
    echo "Führe aus: ddev exec php spark offers:discount-old\n";
    echo "\nErwarteter Betreff: \"69% Rabatt auf Anfrage für Umzug #{$offerId} {$row['zip']} {$row['city']}\"\n";
    echo "Inhalt sollte NICHT enthalten: \"Vielen Dank für Ihre Anfrage!\"\n";
} else {
    echo "Keine Umzug-Offerte gefunden\n";
}
