<?php

/**
 * Test: Rabatt E-Mail Betreff mit neuem Preis
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== Test Rabatt E-Mail Betreff ===\n\n";

// Hole verschiedene Offerten mit Rabatten
$offers = $mysqli->query("
    SELECT id, type, zip, city, price, discounted_price
    FROM offers
    WHERE discounted_price IS NOT NULL AND discounted_price > 0 AND price > 0
    ORDER BY id DESC
    LIMIT 5
");

echo "Beispiel-Betreffs mit Rabatt und neuem Preis:\n\n";

while ($offer = $offers->fetch_assoc()) {
    // Simuliere Domain-Extraktion
    $domain = "Offertenschweiz.ch";

    // Typ-Mapping (gleich wie im Code)
    $typeMapping = [
        'move'              => 'Umzug',
        'cleaning'          => 'Reinigung',
        'move_cleaning'     => 'Umzug + Reinigung',
        'painting'          => 'Maler/Gipser',
        'painter'           => 'Maler/Gipser',
        'gardening'         => 'Garten Arbeiten',
        'gardener'          => 'Garten Arbeiten',
        'electrician'       => 'Elektriker Arbeiten',
        'plumbing'          => 'Sanitär Arbeiten',
        'heating'           => 'Heizung Arbeiten',
        'tiling'            => 'Platten Arbeiten',
        'flooring'          => 'Boden Arbeiten',
        'furniture_assembly'=> 'Möbelaufbau',
        'other'             => 'Sonstiges',
    ];

    $type = $typeMapping[$offer['type']] ?? ucfirst(str_replace('_', ' ', $offer['type']));

    // Rabatt berechnen
    $discount = round(($offer['price'] - $offer['discounted_price']) / $offer['price'] * 100);

    // Neuer Preis formatieren
    $newPriceFormatted = number_format($offer['discounted_price'], 2, '.', '\'');

    // Neuer Betreff mit Rabatt und neuem Preis
    $subject = "{$domain} - {$discount}% Rabatt / Neuer Preis Fr. {$newPriceFormatted} auf Anfrage für {$type} #{$offer['id']} {$offer['zip']} {$offer['city']}";

    echo "  ✅ {$subject}\n";
}

echo "\n";
echo "Format: {Domain}.ch - {X}% Rabatt / Neuer Preis Fr. {Preis} auf Anfrage für {Type} #{ID} {PLZ} {Stadt}\n";
echo "\n";

$mysqli->close();
