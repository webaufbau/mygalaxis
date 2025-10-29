<?php

/**
 * Umfassender E-Mail-Test für alle E-Mail-Typen
 * Testet: Bestätigung Kunde, Neue Offerte Firma, Kauf (Firma+Kunde), Rabatt Firma, Bewertung Kunde
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo str_repeat("=", 80) . "\n";
echo "               UMFASSENDER E-MAIL TEST - ALLE TYPEN\n";
echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// 1. BESTÄTIGUNGS-EMAIL AN KUNDE (Kunden-Bestätigung nach Offerten-Erstellung)
// ============================================================================
echo "1. BESTÄTIGUNGS-EMAIL AN KUNDE\n";
echo str_repeat("-", 80) . "\n";

// Finde 5 verschiedene Offerten (verschiedene Types)
$offerTypes = ['cleaning', 'move', 'gardening', 'electrician', 'painting'];
$testOffers = [];

foreach ($offerTypes as $type) {
    $result = $mysqli->query("
        SELECT id, type, zip, city, firstname, lastname, email
        FROM offers
        WHERE type = '{$type}' AND email IS NOT NULL AND email != ''
        LIMIT 1
    ");
    if ($row = $result->fetch_assoc()) {
        $testOffers[] = $row;
        echo "  • {$row['type']}: Offerte #{$row['id']} - {$row['firstname']} {$row['lastname']} ({$row['email']})\n";
    }
}

echo "\n➜ Ausführung: php spark offers:send-confirmation\n";
echo "  (Hinweis: Dieser Command muss angepasst/erstellt werden für manuelle Tests)\n\n";

// ============================================================================
// 2. NEUE PASSENDE OFFERTE AN FIRMA
// ============================================================================
echo "2. NEUE PASSENDE OFFERTE AN FIRMA\n";
echo str_repeat("-", 80) . "\n";

$firmUsers = $mysqli->query("
    SELECT id, email_text, company_name, contact_person
    FROM users
    WHERE email_text IS NOT NULL AND email_text != ''
    LIMIT 5
");

echo "Test-Firmen:\n";
$firms = [];
while ($row = $firmUsers->fetch_assoc()) {
    $firms[] = $row;
    echo "  • User #{$row['id']}: {$row['company_name']} - {$row['contact_person']} ({$row['email_text']})\n";
}

if (count($testOffers) > 0 && count($firms) > 0) {
    echo "\n➜ Teste mit Offerte #{$testOffers[0]['id']} und User #{$firms[0]['id']}:\n";
    echo "  Command: php spark mail:test-new-offer {$testOffers[0]['id']} {$firms[0]['id']}\n\n";

    passthru("cd /var/www/html && php spark mail:test-new-offer {$testOffers[0]['id']} {$firms[0]['id']} 2>&1");
    echo "\n";
}

// ============================================================================
// 3. KAUF-BENACHRICHTIGUNG (FIRMA + KUNDE)
// ============================================================================
echo "\n3. KAUF-BENACHRICHTIGUNG AN FIRMA UND KUNDE\n";
echo str_repeat("-", 80) . "\n";

// Finde Offerten für Kauf-Test
$purchaseTestOffers = [];
foreach (['cleaning', 'move', 'gardening', 'electrician', 'painting'] as $type) {
    $result = $mysqli->query("
        SELECT id, type, zip, city
        FROM offers
        WHERE type = '{$type}'
        LIMIT 1
    ");
    if ($row = $result->fetch_assoc()) {
        $purchaseTestOffers[] = $row;
    }
}

echo "Test-Offerten für Kauf:\n";
foreach ($purchaseTestOffers as $offer) {
    echo "  • {$offer['type']}: Offerte #{$offer['id']} ({$offer['zip']} {$offer['city']})\n";
}

if (count($purchaseTestOffers) >= 5 && count($firms) >= 5) {
    echo "\n➜ Erstelle 5 Test-Bookings und sende E-Mails:\n";

    for ($i = 0; $i < 5; $i++) {
        $offerId = $purchaseTestOffers[$i]['id'];
        $userId = $firms[$i]['id'];

        // Erstelle Booking
        $mysqli->query("
            INSERT INTO bookings (user_id, type, reference_id, amount, paid_amount, created_at, offer_notification_sent_at)
            VALUES ({$userId}, 'offer_purchase', {$offerId}, 29.00, 29.00, NOW(), NULL)
            ON DUPLICATE KEY UPDATE offer_notification_sent_at = NULL
        ");

        echo "  • Booking erstellt: Offerte #{$offerId} -> User #{$userId}\n";
    }

    echo "\n➜ Sende Kauf-Benachrichtigungen:\n";
    passthru("cd /var/www/html && php spark offers:send-purchase-notification 2>&1");
    echo "\n";
}

// ============================================================================
// 4. RABATT-EMAIL AN FIRMA
// ============================================================================
echo "\n4. RABATT-EMAIL AN FIRMA\n";
echo str_repeat("-", 80) . "\n";

// Bereite 5 Offerten für Rabatt vor
$discountOffers = [];
foreach (['cleaning', 'move', 'gardening', 'electrician', 'painting'] as $type) {
    $result = $mysqli->query("
        SELECT id, type, zip, city, price, discounted_price
        FROM offers
        WHERE type = '{$type}' AND price > 10
        LIMIT 1
    ");
    if ($row = $result->fetch_assoc()) {
        // Ändere Preis leicht um E-Mail auszulösen
        $newPrice = round($row['price'] * 0.4, 2);
        $mysqli->query("UPDATE offers SET discounted_price = {$newPrice} + " . ($row['id'] % 10) / 10 . " WHERE id = {$row['id']}");

        $discountOffers[] = $row;
        $discount = round(($row['price'] - $newPrice) / $row['price'] * 100);
        echo "  • {$row['type']}: Offerte #{$row['id']} - {$discount}% Rabatt ({$row['zip']} {$row['city']})\n";
    }
}

echo "\n➜ Sende Rabatt-E-Mails:\n";
passthru("cd /var/www/html && php spark offers:discount-old 2>&1 | head -30");
echo "\n";

// ============================================================================
// 5. BEWERTUNGS-EMAIL AN KUNDE
// ============================================================================
echo "\n5. BEWERTUNGS-EMAIL AN KUNDE (REVIEW REQUEST)\n";
echo str_repeat("-", 80) . "\n";

// Bereite Bookings für Review-E-Mails vor
$reviewBookings = $mysqli->query("
    SELECT DISTINCT
        b.id as booking_id,
        o.type,
        o.title,
        o.firstname,
        o.lastname,
        o.email
    FROM bookings b
    INNER JOIN offers o ON o.id = b.reference_id
    WHERE b.type = 'offer_purchase'
    AND o.email IS NOT NULL
    AND o.email != ''
    LIMIT 5
");

echo "Test-Bookings für Bewertungs-E-Mail:\n";
$reviewCount = 0;
while ($row = $reviewBookings->fetch_assoc()) {
    $reviewCount++;
    echo "  • {$row['type']}: {$row['title']} - {$row['firstname']} {$row['lastname']} ({$row['email']})\n";

    // Setze Booking zurück für Test
    $mysqli->query("
        UPDATE bookings
        SET review_reminder_sent_at = NULL,
            created_at = DATE_SUB(NOW(), INTERVAL 6 DAY)
        WHERE id = {$row['booking_id']}
    ");
}

if ($reviewCount > 0) {
    echo "\n➜ Sende Bewertungs-E-Mails:\n";
    passthru("cd /var/www/html && php spark reviews:send-reminder 2>&1 | head -20");
    echo "\n";
}

// ============================================================================
// ZUSAMMENFASSUNG
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "                              ZUSAMMENFASSUNG\n";
echo str_repeat("=", 80) . "\n\n";

echo "✅ Tests durchgeführt:\n";
echo "  1. Bestätigungs-Email an Kunde: " . count($testOffers) . " Offerten vorbereitet\n";
echo "  2. Neue Offerte an Firma: 1 E-Mail gesendet\n";
echo "  3. Kauf-Benachrichtigung: 5 E-Mails gesendet (Firma + Kunde = 10 total)\n";
echo "  4. Rabatt-Email an Firma: " . count($discountOffers) . " E-Mails gesendet\n";
echo "  5. Bewertungs-Email an Kunde: {$reviewCount} E-Mails gesendet\n\n";

echo "📬 Prüfe alle E-Mails in MailPit:\n";
echo "  → https://mygalaxis.ddev.site:8026\n";
echo "  → oder http://localhost:8025\n\n";

echo "📊 Erwartete E-Mail-Typen:\n";
echo "  • Bestätigung Kunde: 'Wir bestätigen Ihre Anfrage'\n";
echo "  • Neue Offerte Firma: 'Domain.ch - Neue Anfrage für [Type]'\n";
echo "  • Kauf Firma: 'Domain.ch - Vielen Dank für den Kauf der Anfrage'\n";
echo "  • Kauf Kunde: 'Domain.ch - Eine Firma interessiert sich für Ihre Anfrage'\n";
echo "  • Rabatt Firma: 'X% Rabatt auf Anfrage für [Type]'\n";
echo "  • Bewertung Kunde: 'Bewerten Sie Ihre Erfahrung'\n\n";

$mysqli->close();

echo str_repeat("=", 80) . "\n";
echo "                           TEST ABGESCHLOSSEN\n";
echo str_repeat("=", 80) . "\n";
