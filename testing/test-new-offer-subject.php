<?php

/**
 * Test: Neue Offerte E-Mail Betreff mit Preis
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== Test Neue Offerte E-Mail Betreff ===\n\n";

// Hole verschiedene Offerten mit unterschiedlichen Preisen
$offers = $mysqli->query("
    SELECT id, type, zip, city, price, discounted_price
    FROM offers
    WHERE price > 0
    ORDER BY id DESC
    LIMIT 5
");

echo "Beispiel-Betreffs mit Preis:\n\n";

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

    // Preis formatieren (discounted_price hat Vorrang)
    $price = !empty($offer['discounted_price']) ? $offer['discounted_price'] : $offer['price'];
    $priceFormatted = number_format($price, 2, '.', '\'');

    // Neuer Betreff mit Preis
    $subject = "{$domain} - Neue Anfrage Preis Fr. {$priceFormatted} für {$type} ID {$offer['id']} - {$offer['zip']} {$offer['city']}";

    echo "  ✅ {$subject}\n";
}

echo "\n";
echo "Format: {Domain}.ch - Neue Anfrage Preis Fr. {Preis} für {Type} #{ID} - {PLZ} {Stadt}\n";
echo "\n";

$mysqli->close();
