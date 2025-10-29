<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');
$result = $mysqli->query('SELECT id, platform, type, zip, city FROM offers WHERE id = 528');
if ($row = $result->fetch_assoc()) {
    echo "Offerte #528:\n";
    echo "  Platform: " . ($row['platform'] ?? 'NULL') . "\n";
    echo "  Type: " . $row['type'] . "\n";
    echo "  Location: " . $row['zip'] . " " . $row['city'] . "\n";
} else {
    echo "Offerte #528 nicht gefunden\n";
}
