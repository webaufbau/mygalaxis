<?php

/**
 * Test Script für Rabatt-E-Mails
 * Simuliert Rabatt-Benachrichtigungen an Firmen
 */

echo "=== Test Rabatt-E-Mails ===\n\n";

// Parameter
$offerId = 451; // Umzug Offerte
$companyUserId = 82; // Blumer Rapid Umzug

echo "Verwende:\n";
echo "  Offerte ID: {$offerId}\n";
echo "  Firma User ID: {$companyUserId}\n\n";

// Verbinde mit Datenbank
$mysqli = new mysqli('db', 'db', 'db', 'db');
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error . "\n");
}

// Prüfe ob Offerte existiert
$result = $mysqli->query("SELECT id, type, zip, city, price, discounted_price FROM offers WHERE id = {$offerId}");
if (!$result || $result->num_rows === 0) {
    die("ERROR: Offerte #{$offerId} nicht gefunden!\n");
}
$offer = $result->fetch_assoc();

echo "✓ Offerte #{$offer['id']} gefunden: {$offer['type']}, {$offer['zip']} {$offer['city']}\n";
echo "  Preis: {$offer['price']} CHF\n";
echo "  Rabattpreis: " . ($offer['discounted_price'] ?? 'NULL') . " CHF\n\n";

// Prüfe ob Firma existiert
$result = $mysqli->query("SELECT id, email_text, contact_person, company_name FROM users WHERE id = {$companyUserId}");
if (!$result || $result->num_rows === 0) {
    die("ERROR: Firma User ID {$companyUserId} nicht gefunden!\n");
}
$company = $result->fetch_assoc();
echo "✓ Firma gefunden: {$company['company_name']} - {$company['contact_person']} ({$company['email_text']})\n\n";

// Setze Rabattpreis falls nicht vorhanden
if (empty($offer['discounted_price']) || $offer['discounted_price'] >= $offer['price']) {
    $newPrice = round($offer['price'] * 0.3, 2); // 70% Rabatt
    $mysqli->query("UPDATE offers SET discounted_price = {$newPrice}, last_price_update_sent = NULL WHERE id = {$offerId}");
    echo "Rabattpreis gesetzt: {$newPrice} CHF (ca. 70% Rabatt)\n\n";
    $offer['discounted_price'] = $newPrice;
}

$discount = round(($offer['price'] - $offer['discounted_price']) / $offer['price'] * 100);
echo "Erwarteter Betreff: \"{$discount}% Rabatt auf Anfrage für Umzug #{$offerId} {$offer['zip']} {$offer['city']}\"\n";
echo "Inhalt sollte NICHT enthalten: \"Vielen Dank für Ihre Anfrage!\"\n\n";

$mysqli->close();

echo "Führe Rabatt-Command aus...\n";
echo str_repeat("-", 60) . "\n\n";

// Führe den Command aus
$output = [];
$returnCode = 0;
exec("cd /var/www/html && php spark offers:discount-old 2>&1", $output, $returnCode);

// Zeige Output
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\n" . str_repeat("-", 60) . "\n";

if ($returnCode === 0) {
    echo "✅ Command erfolgreich ausgeführt!\n\n";
    echo "Prüfe die E-Mails in MailPit:\n";
    echo "  → https://mygalaxis.ddev.site:8026\n";
    echo "  → oder http://localhost:8025\n\n";
    echo "Es sollte eine Rabatt-E-Mail an {$company['email_text']} vorhanden sein.\n";
} else {
    echo "❌ Fehler beim Ausführen des Commands (Exit Code: {$returnCode})\n";
}

echo "\n=== Test abgeschlossen ===\n";
