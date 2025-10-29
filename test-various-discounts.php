<?php

/**
 * Testet Rabatt-E-Mails für verschiedene Offer-Types
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== Test verschiedene Rabatt-E-Mails ===\n\n";

// Verschiedene Types testen
$testTypes = [
    ['type' => 'cleaning', 'label' => 'Reinigung'],
    ['type' => 'move', 'label' => 'Umzug'],
    ['type' => 'gardening', 'label' => 'Garten Arbeiten'],
    ['type' => 'electrician', 'label' => 'Elektriker Arbeiten'],
    ['type' => 'painting', 'label' => 'Maler/Gipser'],
];

$offersToUpdate = [];

foreach ($testTypes as $testType) {
    $type = $testType['type'];
    $label = $testType['label'];

    // Finde eine Offerte dieses Typs
    $result = $mysqli->query("
        SELECT id, type, zip, city, price, discounted_price
        FROM offers
        WHERE type = '{$type}' AND price > 10
        LIMIT 1
    ");

    if ($row = $result->fetch_assoc()) {
        echo "✓ {$label}:\n";
        echo "  Offerte #" . $row['id'] . " ({$row['zip']} {$row['city']})\n";
        echo "  Preis: {$row['price']} CHF\n";

        // Berechne neuen Rabattpreis (falls noch nicht vorhanden)
        $newPrice = round($row['price'] * 0.4, 2); // 60% Rabatt für Test

        // Ändere Preis leicht, um E-Mail auszulösen
        $mysqli->query("UPDATE offers SET discounted_price = {$newPrice} + 0.01 WHERE id = {$row['id']}");

        $discount = round(($row['price'] - $newPrice) / $row['price'] * 100);
        echo "  Erwarteter Betreff: \"{$discount}% Rabatt auf Anfrage für {$label} #{$row['id']} {$row['zip']} {$row['city']}\"\n\n";

        $offersToUpdate[] = $row['id'];
    } else {
        echo "✗ Keine Offerte für {$label} gefunden\n\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "Offerten vorbereitet: " . implode(', ', $offersToUpdate) . "\n";
echo str_repeat("=", 70) . "\n\n";

echo "Führe Rabatt-Command aus...\n\n";

// Führe den Command aus
passthru("cd /var/www/html && php spark offers:discount-old 2>&1");

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ Fertig!\n\n";
echo "Prüfe die E-Mails in MailPit:\n";
echo "  → https://mygalaxis.ddev.site:8026\n";
echo "  → oder http://localhost:8025\n\n";
echo "Es sollten mehrere Rabatt-E-Mails mit verschiedenen Betreffs vorhanden sein:\n";
foreach ($testTypes as $testType) {
    echo "  • Rabatt auf Anfrage für {$testType['label']}\n";
}
echo "\nInhalt sollte NICHT enthalten: \"Vielen Dank für Ihre Anfrage!\"\n";

$mysqli->close();
