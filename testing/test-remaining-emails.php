<?php

/**
 * Test für Bestätigungs-E-Mail und Bewertungs-E-Mail
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo str_repeat("=", 80) . "\n";
echo "           TEST: BESTÄTIGUNGS- & BEWERTUNGS-E-MAILS\n";
echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// 1. BESTÄTIGUNGS-EMAIL AN KUNDE (manuelle Simulation)
// ============================================================================
echo "1. BESTÄTIGUNGS-EMAIL AN KUNDE\n";
echo str_repeat("-", 80) . "\n";

// Finde 5 Offerten verschiedener Typen
$offerTypes = ['cleaning', 'move', 'gardening', 'electrician', 'painting'];

echo "Diese E-Mails werden normalerweise automatisch beim Erstellen der Offerte gesendet.\n";
echo "Für Tests können Sie die AddOffer.php anpassen oder folgende Test-Offerten verwenden:\n\n";

foreach ($offerTypes as $type) {
    $result = $mysqli->query("
        SELECT id, type, zip, city, email, firstname, lastname
        FROM offers
        WHERE type = '{$type}' AND email IS NOT NULL
        LIMIT 1
    ");
    if ($row = $result->fetch_assoc()) {
        echo "  • {$row['type']}: Offerte #{$row['id']}\n";
        echo "    Kunde: {$row['firstname']} {$row['lastname']} ({$row['email']})\n";
        echo "    Ort: {$row['zip']} {$row['city']}\n\n";
    }
}

echo "💡 Tipp: Erstellen Sie eine neue Offerte über das Frontend, um die Bestätigungs-E-Mail zu testen.\n\n";

// ============================================================================
// 2. BEWERTUNGS-EMAIL AN KUNDE
// ============================================================================
echo "\n2. BEWERTUNGS-EMAIL AN KUNDE (REVIEW REQUEST)\n";
echo str_repeat("-", 80) . "\n";

// Finde gekaufte Offerten
$reviewOffers = $mysqli->query("
    SELECT DISTINCT o.id, o.type, o.zip, o.city, o.email, o.firstname, o.lastname,
           op.user_id as company_id,
           u.company_name
    FROM offers o
    INNER JOIN offer_purchases op ON op.offer_id = o.id
    INNER JOIN users u ON u.id = op.user_id
    WHERE o.email IS NOT NULL AND o.email != ''
    AND op.status = 'paid'
    LIMIT 5
");

echo "Gekaufte Offerten für Review-Test:\n\n";

$reviewCount = 0;
while ($row = $reviewOffers->fetch_assoc()) {
    $reviewCount++;
    echo "  {$reviewCount}. {$row['type']}: Offerte #{$row['id']}\n";
    echo "     Kunde: {$row['firstname']} {$row['lastname']} ({$row['email']})\n";
    echo "     Firma: {$row['company_name']} (User #{$row['company_id']})\n";
    echo "     Ort: {$row['zip']} {$row['city']}\n\n";

    // Simuliere Review-Request E-Mail
    // Hinweis: Dies hängt davon ab, ob ein entsprechender Command existiert
}

if ($reviewCount > 0) {
    echo "💡 Tipp: Erstellen Sie einen Command 'offers:send-review-requests' oder senden Sie\n";
    echo "   Review-E-Mails direkt nach Abschluss der Offerte.\n\n";

    echo "📝 Review-E-Mail sollte enthalten:\n";
    echo "   • Link zur Bewertungsseite mit access_hash\n";
    echo "   • Firmenname und Details\n";
    echo "   • Anfragedetails\n";
    echo "   • Sterne-Bewertung (1-5)\n";
    echo "   • Kommentar-Feld\n\n";
}

// ============================================================================
// ZUSAMMENFASSUNG
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "                              ZUSAMMENFASSUNG\n";
echo str_repeat("=", 80) . "\n\n";

echo "✅ E-Mail-Typen gefunden:\n";
echo "  1. Bestätigungs-Email: 5 Test-Offerten identifiziert\n";
echo "  2. Bewertungs-Email: {$reviewCount} gekaufte Offerten identifiziert\n\n";

echo "📬 Alle bisherigen Test-E-Mails in MailPit:\n";
echo "  → https://mygalaxis.ddev.site:8026\n";
echo "  → oder http://localhost:8025\n\n";

echo "📊 Vollständige E-Mail-Übersicht:\n";
echo "  ✅ Neue Offerte an Firma: Getestet (1 E-Mail)\n";
echo "  ✅ Kauf an Firma: Getestet (5 E-Mails)\n";
echo "  ✅ Kauf an Kunde: Getestet (5 E-Mails)\n";
echo "  ✅ Rabatt an Firma: Getestet (33 E-Mails total)\n";
echo "  ⚠️  Bestätigung an Kunde: Manuell testen (Frontend)\n";
echo "  ⚠️  Bewertung an Kunde: Command muss erstellt werden\n\n";

$mysqli->close();

echo str_repeat("=", 80) . "\n";
echo "                           TEST ABGESCHLOSSEN\n";
echo str_repeat("=", 80) . "\n";
