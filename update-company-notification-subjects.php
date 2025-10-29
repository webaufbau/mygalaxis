<?php

/**
 * Aktualisiert die Betreffs von company_notification Templates
 * Von: "Neue passende Offerte #ID"
 * Zu: "{site_domain} - Neue Anfrage für {offer_type_name} #{offer_id} - {offer_zip} {offer_city}"
 */

$mysqli = new mysqli('db', 'db', 'db', 'db');
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error . "\n");
}

echo "=== Aktualisiere Company Notification Template Subjects ===\n\n";

// Finde alle company_notification Templates
$result = $mysqli->query("
    SELECT id, offer_type, language, subject
    FROM email_templates
    WHERE offer_type LIKE 'company_notification%'
    ORDER BY offer_type, language
");

if (!$result || $result->num_rows === 0) {
    echo "Keine company_notification Templates gefunden.\n";
    exit(0);
}

$newSubject = "{site_domain} - Neue Anfrage für {offer_type_name} #{offer_id} - {offer_zip} {offer_city}";

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $offerType = $row['offer_type'];
    $language = $row['language'];
    $oldSubject = $row['subject'];

    echo "Template ID {$id} ({$offerType}, {$language}):\n";
    echo "  Alt: {$oldSubject}\n";
    echo "  Neu: {$newSubject}\n";

    // Update subject
    $stmt = $mysqli->prepare("UPDATE email_templates SET subject = ? WHERE id = ?");
    $stmt->bind_param("si", $newSubject, $id);

    if ($stmt->execute()) {
        echo "  ✓ Aktualisiert\n\n";
    } else {
        echo "  ✗ Fehler: " . $mysqli->error . "\n\n";
    }
}

$mysqli->close();

echo "=== Fertig ===\n";
