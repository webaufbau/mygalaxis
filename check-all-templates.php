<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== Alle E-Mail Templates ===\n\n";

$result = $mysqli->query("
    SELECT id, offer_type, subtype, language, subject
    FROM email_templates
    ORDER BY offer_type, language
");

while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}\n";
    echo "  Type: {$row['offer_type']}\n";
    echo "  Subtype: " . ($row['subtype'] ?? 'NULL') . "\n";
    echo "  Language: {$row['language']}\n";
    echo "  Subject: {$row['subject']}\n\n";
}
