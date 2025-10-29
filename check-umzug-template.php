<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== E-Mail Template für Umzug (move) ===\n\n";

$result = $mysqli->query("
    SELECT id, offer_type, language, subject, body_template
    FROM email_templates
    WHERE offer_type = 'move' AND language = 'de'
    LIMIT 1
");

if ($row = $result->fetch_assoc()) {
    echo "Template ID: {$row['id']}\n";
    echo "Offer Type: {$row['offer_type']}\n";
    echo "Language: {$row['language']}\n";
    echo "Subject: {$row['subject']}\n\n";
    echo "Body Template:\n";
    echo str_repeat("-", 80) . "\n";
    echo $row['body_template'];
    echo "\n" . str_repeat("-", 80) . "\n";
} else {
    echo "Kein Template für 'move' gefunden.\n";
}
