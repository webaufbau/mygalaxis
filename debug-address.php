<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');
$result = $mysqli->query('SELECT form_fields FROM offers WHERE id = 447');
$row = $result->fetch_assoc();
$fields = json_decode($row['form_fields'], true);

echo "=== Adress-relevante Felder ===\n";
foreach ($fields as $key => $value) {
    if (stripos($key, 'address') !== false || stripos($key, 'addr') !== false ||
        stripos($key, 'strasse') !== false || stripos($key, 'street') !== false ||
        stripos($key, 'line') !== false) {
        echo $key . ' = ' . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
}

// Prüfe auch verschachtelte Felder
echo "\n=== Alle Felder mit Array-Werten ===\n";
foreach ($fields as $key => $value) {
    if (is_array($value)) {
        echo "\n$key:\n";
        foreach ($value as $subKey => $subValue) {
            if (stripos($subKey, 'address') !== false || stripos($subKey, 'strasse') !== false) {
                echo "  $subKey = " . (is_array($subValue) ? json_encode($subValue) : $subValue) . "\n";
            }
        }
    }
}
