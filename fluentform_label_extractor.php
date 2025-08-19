<?php

/**
 * Rekursive Suche nach name => label in Fluent Form Export
 */
function extractLabelsFromFields(array $fields): array
{
    $labels = [];

    foreach ($fields as $field) {
        $attributes = $field['attributes'] ?? [];
        $settings   = $field['settings'] ?? [];

        // Falls name + label vorhanden → speichern
        if (!empty($attributes['name']) && !empty($settings['label'])) {
            $fieldName = $attributes['name'];
            $labelText = html_entity_decode($settings['label'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $labels[$fieldName] = $labelText;
        }

        // Prüfen ob Spalten vorhanden sind
        if (isset($field['columns']) && is_array($field['columns'])) {
            foreach ($field['columns'] as $column) {
                if (isset($column['fields'])) {
                    $labels = array_merge($labels, extractLabelsFromFields($column['fields']));
                }
            }
        }

        // Falls weitere verschachtelte Felder existieren
        if (isset($field['fields']) && is_array($field['fields'])) {
            $labels = array_merge($labels, extractLabelsFromFields($field['fields']));
        }
    }

    return $labels;
}

// Ordner mit JSON-Dateien
$folder = __DIR__ . '/exports';
$allLabels = [];

foreach (glob($folder . '/*.json') as $file) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);

    // Wichtiger Einstiegspunkt: form_fields → fields
    if (isset($data[0]['form_fields']['fields']) && is_array($data[0]['form_fields']['fields'])) {
        $labels = extractLabelsFromFields($data[0]['form_fields']['fields']);
        $allLabels = array_merge($allLabels, $labels);
    }
}

// PHP-Sprachdatei schreiben
$outputFile = __DIR__ . '/labels.php';
file_put_contents(
    $outputFile,
    "<?php\n\nreturn ['labels' => " . var_export($allLabels, true) . "];\n"
);

echo "Fertig! " . count($allLabels) . " Labels wurden extrahiert.\n";
