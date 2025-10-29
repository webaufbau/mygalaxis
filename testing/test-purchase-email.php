<?php

/**
 * Test Script für Kauf-Benachrichtigungs-E-Mails
 * Simuliert einen Offerten-Kauf und sendet Test-E-Mails an Firma und Kunde
 */

// Direkt über spark command
echo "=== Test Kauf-Benachrichtigungs-E-Mails ===\n\n";
echo "Dieses Script simuliert einen Offerten-Kauf und sendet E-Mails an:\n";
echo "1. Firma (Käufer der Offerte)\n";
echo "2. Kunde (Auftraggeber)\n\n";

// Parameter
$offerId = 447; // Gartenpflege Offerte
$companyUserId = 11; // WebAufbau Vincent Kilchherr

echo "Verwende:\n";
echo "  Offerte ID: {$offerId}\n";
echo "  Firma User ID: {$companyUserId}\n\n";

// Erstelle temporären Booking-Eintrag
echo "Schritt 1: Verbinde mit Datenbank...\n";

$mysqli = new mysqli('db', 'db', 'db', 'db');
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error . "\n");
}
echo "✓ Datenbankverbindung erfolgreich\n\n";

// Prüfe ob Offerte existiert
$result = $mysqli->query("SELECT id, type, zip, city, form_fields FROM offers WHERE id = {$offerId}");
if (!$result || $result->num_rows === 0) {
    die("ERROR: Offerte #{$offerId} nicht gefunden!\n");
}
$offer = $result->fetch_assoc();

// Dekodiere form_fields um Kundendaten zu erhalten
$formFields = json_decode($offer['form_fields'], true) ?? [];
$customerEmail = $formFields['email'] ?? $formFields['e-mail'] ?? $formFields['e_mail'] ?? 'test@example.com';
$customerFirstname = $formFields['vorname'] ?? $formFields['firstname'] ?? 'Test';
$customerLastname = $formFields['nachname'] ?? $formFields['lastname'] ?? 'Kunde';

echo "✓ Offerte #{$offer['id']} gefunden: {$offer['type']}, {$offer['zip']} {$offer['city']}\n";
echo "  Kunde: {$customerFirstname} {$customerLastname} ({$customerEmail})\n\n";

// Prüfe ob Firma existiert
$result = $mysqli->query("SELECT id, email_text, contact_person, company_name FROM users WHERE id = {$companyUserId}");
if (!$result || $result->num_rows === 0) {
    die("ERROR: Firma User ID {$companyUserId} nicht gefunden!\n");
}
$company = $result->fetch_assoc();
echo "✓ Firma gefunden: {$company['company_name']} - {$company['contact_person']} ({$company['email_text']})\n\n";

// Prüfe ob bereits ein Booking existiert (für Cleanup später)
$result = $mysqli->query("SELECT id FROM bookings WHERE type = 'offer_purchase' AND reference_id = {$offerId} AND user_id = {$companyUserId} AND offer_notification_sent_at IS NULL LIMIT 1");
$existingBooking = $result->fetch_assoc();

if ($existingBooking) {
    echo "ℹ Bestehendes Test-Booking gefunden (ID: {$existingBooking['id']}), wird wiederverwendet\n\n";
    $bookingId = $existingBooking['id'];
} else {
    // Erstelle neuen Booking-Eintrag
    echo "Schritt 2: Erstelle temporären Booking-Eintrag...\n";
    $stmt = $mysqli->prepare("INSERT INTO bookings (user_id, type, reference_id, amount, paid_amount, created_at, offer_notification_sent_at) VALUES (?, 'offer_purchase', ?, 29.00, 29.00, NOW(), NULL)");
    $stmt->bind_param("ii", $companyUserId, $offerId);

    if (!$stmt->execute()) {
        die("ERROR: Booking konnte nicht erstellt werden: " . $mysqli->error . "\n");
    }

    $bookingId = $mysqli->insert_id;
    echo "✓ Booking-Eintrag erstellt (ID: {$bookingId})\n\n";
}

$mysqli->close();

echo "Schritt 3: Führe Benachrichtigungs-Command aus...\n";
echo str_repeat("-", 60) . "\n\n";

// Führe den Command aus
$output = [];
$returnCode = 0;
exec("cd /var/www/html && php spark offers:send-purchase-notification 2>&1", $output, $returnCode);

// Zeige Output
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\n" . str_repeat("-", 60) . "\n";

if ($returnCode === 0) {
    echo "✅ E-Mails erfolgreich versendet!\n\n";
    echo "Prüfe die E-Mails in MailPit:\n";
    echo "  → https://mygalaxis.ddev.site:8026\n";
    echo "  → oder http://localhost:8025\n\n";
    echo "Es sollten 2 E-Mails vorhanden sein:\n";
    echo "  1. E-Mail an Firma: {$company['email_text']}\n";
    echo "  2. E-Mail an Kunde: {$customerEmail}\n";
} else {
    echo "❌ Fehler beim Ausführen des Commands (Exit Code: {$returnCode})\n";
}

echo "\n=== Test abgeschlossen ===\n";
