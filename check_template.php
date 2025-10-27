<?php
require __DIR__ . '/vendor/autoload.php';

$db = \Config\Database::connect();
$query = $db->query("SELECT id, offer_type, subtype, field_display_template FROM email_templates WHERE offer_type = 'move' AND subtype IS NULL LIMIT 1");
$result = $query->getRow();

if ($result) {
    echo "ID: " . $result->id . "\n";
    echo "Offer Type: " . $result->offer_type . "\n";
    echo "Subtype: " . ($result->subtype ?? 'NULL') . "\n";
    echo "=== Template ===\n";
    echo $result->field_display_template ?? 'NULL';
    echo "\n";
} else {
    echo "No template found\n";
}