<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== E-Mail Templates für Gardening ===\n\n";

$result = $mysqli->query("
    SELECT id, offer_type, subtype, language, subject,
           SUBSTRING(field_display_template, 1, 100) as field_preview
    FROM email_templates
    WHERE offer_type = 'gardening' AND language = 'de'
");

if ($result->num_rows === 0) {
    echo "❌ Keine Templates für 'gardening' gefunden!\n\n";

    // Zeige alle verfügbaren offer_types
    echo "Verfügbare offer_types:\n";
    $types = $mysqli->query("SELECT DISTINCT offer_type FROM email_templates ORDER BY offer_type");
    while ($row = $types->fetch_assoc()) {
        echo "  - {$row['offer_type']}\n";
    }
} else {
    while ($row = $result->fetch_assoc()) {
        echo "Template ID: {$row['id']}\n";
        echo "  Type: {$row['offer_type']}\n";
        echo "  Subtype: " . ($row['subtype'] ?? 'NULL') . "\n";
        echo "  Subject: {$row['subject']}\n";
        echo "  Field Display Preview: " . ($row['field_preview'] ? substr($row['field_preview'], 0, 80) . '...' : 'NULL') . "\n\n";
    }
}

// Prüfe Offerte #16
echo "\n=== Offerte #16 Details ===\n";
$result = $mysqli->query("SELECT id, type, form_fields FROM offers WHERE id = 16");
if ($row = $result->fetch_assoc()) {
    echo "Type: {$row['type']}\n";

    $formFields = json_decode($row['form_fields'], true);
    if (isset($formFields['andere_gartenarbeiten'])) {
        echo "Hat 'andere_gartenarbeiten' Feld: Ja\n";
        echo "Möglicher Subtype: garten_andere_gartenarbeiten\n";
    } else {
        echo "Verfügbare Felder:\n";
        foreach (array_keys($formFields) as $key) {
            if (stripos($key, 'garten') !== false) {
                echo "  - {$key}\n";
            }
        }
    }
}
