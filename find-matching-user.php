<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "Suche nach User mit Platform 'my_offertenschweiz_ch'...\n\n";

$result = $mysqli->query("
    SELECT id, email_text, company_name, contact_person, platform
    FROM users
    WHERE platform = 'my_offertenschweiz_ch'
    LIMIT 5
");

if ($result->num_rows === 0) {
    echo "Keine User mit dieser Platform gefunden.\n";
    echo "\nVerfügbare Platforms:\n";

    $platforms = $mysqli->query("SELECT DISTINCT platform FROM users WHERE platform IS NOT NULL");
    while ($row = $platforms->fetch_assoc()) {
        echo "  - " . $row['platform'] . "\n";
    }
} else {
    echo "Gefundene User:\n";
    while ($row = $result->fetch_assoc()) {
        echo "\nUser ID: " . $row['id'] . "\n";
        echo "  Email: " . $row['email_text'] . "\n";
        echo "  Firma: " . $row['company_name'] . "\n";
        echo "  Kontakt: " . $row['contact_person'] . "\n";
        echo "  Platform: " . $row['platform'] . "\n";
    }
}
