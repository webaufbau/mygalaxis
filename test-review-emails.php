<?php

/**
 * Test-Script für Bewertungs-E-Mails (Review Reminder)
 * Simuliert 5 Review-Requests an Kunden
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo str_repeat("=", 80) . "\n";
echo "                    TEST BEWERTUNGS-E-MAILS\n";
echo str_repeat("=", 80) . "\n\n";

// Finde 5 gekaufte Offerten (verschiedene Typen)
$offerTypes = ['cleaning', 'move', 'gardening', 'electrician', 'painting'];
$testBookings = [];

echo "Suche gekaufte Offerten für Review-Test...\n\n";

foreach ($offerTypes as $type) {
    $result = $mysqli->query("
        SELECT DISTINCT
            b.id as booking_id,
            b.reference_id as offer_id,
            b.user_id,
            b.created_at,
            b.review_reminder_sent_at,
            o.type,
            o.title,
            o.zip,
            o.city,
            o.email,
            o.firstname,
            o.lastname,
            u.company_name
        FROM bookings b
        INNER JOIN offers o ON o.id = b.reference_id
        INNER JOIN users u ON u.id = b.user_id
        WHERE b.type = 'offer_purchase'
        AND o.type = '{$type}'
        AND o.email IS NOT NULL
        AND o.email != ''
        LIMIT 1
    ");

    if ($row = $result->fetch_assoc()) {
        $testBookings[] = $row;
        echo "  • {$row['type']}: Booking #{$row['booking_id']}\n";
        echo "    Offerte: #{$row['offer_id']} - {$row['title']}\n";
        echo "    Kunde: {$row['firstname']} {$row['lastname']} ({$row['email']})\n";
        echo "    Firma: {$row['company_name']}\n";
        echo "    Ort: {$row['zip']} {$row['city']}\n";
        echo "    Review-Status: " . ($row['review_reminder_sent_at'] ? 'Bereits gesendet' : 'Noch nicht gesendet') . "\n\n";
    }
}

if (empty($testBookings)) {
    echo "❌ Keine gekauften Offerten gefunden.\n";
    echo "Erstellen Sie zuerst Kauf-Bookings mit: php test-all-emails.php\n";
    exit(1);
}

echo str_repeat("-", 80) . "\n";
echo "Gefunden: " . count($testBookings) . " Test-Bookings\n";
echo str_repeat("-", 80) . "\n\n";

// Setze review_reminder_sent_at zurück, damit E-Mails erneut gesendet werden
echo "Bereite Bookings vor (reset review_reminder_sent_at)...\n";
foreach ($testBookings as $booking) {
    // Setze Booking-Datum auf vor 5+ Tagen, damit Review-Command sie findet
    $mysqli->query("
        UPDATE bookings
        SET review_reminder_sent_at = NULL,
            created_at = DATE_SUB(NOW(), INTERVAL 6 DAY)
        WHERE id = {$booking['booking_id']}
    ");
    echo "  ✓ Booking #{$booking['booking_id']} vorbereitet\n";
}

echo "\n" . str_repeat("-", 80) . "\n";
echo "Führe Review-Reminder Command aus...\n";
echo str_repeat("-", 80) . "\n\n";

// Führe Review-Command aus
passthru("cd /var/www/html && php spark reviews:send-reminder 2>&1");

echo "\n" . str_repeat("=", 80) . "\n";
echo "                              ZUSAMMENFASSUNG\n";
echo str_repeat("=", 80) . "\n\n";

echo "✅ Review-E-Mails sollten versendet worden sein für:\n\n";

foreach ($testBookings as $booking) {
    echo "  • {$booking['type']}: {$booking['firstname']} {$booking['lastname']} ({$booking['email']})\n";
    echo "    Offerte: {$booking['title']}\n";
    echo "    Erwarteter Betreff: \"Bewerten Sie Ihre Erfahrung mit {$booking['title']}\"\n\n";
}

echo "📬 Prüfe die Review-E-Mails in MailPit:\n";
echo "  → https://mygalaxis.ddev.site:8026\n";
echo "  → oder http://localhost:8025\n\n";

echo "📝 Review-E-Mail sollte enthalten:\n";
echo "  • Link zur Bewertungsseite (mit access_hash)\n";
echo "  • Firmenname\n";
echo "  • Offerten-Details\n";
echo "  • Aufforderung zur Bewertung\n\n";

echo "🔗 Review-Link Format:\n";
echo "  {backendUrl}/offer/interested/{access_hash}\n\n";

$mysqli->close();

echo str_repeat("=", 80) . "\n";
echo "                           TEST ABGESCHLOSSEN\n";
echo str_repeat("=", 80) . "\n";
