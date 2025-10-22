<?php
/**
 * Test-Script für FieldDisplayRules
 *
 * Führe aus mit: php spark shell
 * Dann: include('app/Config/FieldDisplayRules.test.php');
 */

// Test-Daten
$testData = [
    // Bodenplatten Vorplatz - Fall 1: Fläche bekannt
    'bodenplatten_vorplatz' => 'Ja',
    'bodenplatten_vorplatz_flaeche' => 'Ja',
    'bodenplatten_vorplatz_flaeche_ja' => '25',

    // Bodenplatten Sitzplatz - Fall 2: Fläche unbekannt
    'bodenplatten_sitzplatz' => 'Ja',
    'bodenplatten_sitzplatz_flaeche' => 'Nein',

    // Normales Feld (ohne Conditional Group)
    'anzahl_zimmer' => '4.5',
    'wohnflaeche' => '120',

    // Test "Nein"-Wert (sollte nicht angezeigt werden)
    'keller' => 'Nein',
];

echo "\n=== FieldRenderer Test ===\n\n";

// Initialisiere FieldRenderer
$fieldRenderer = new \App\Services\FieldRenderer();
$fieldRenderer->setData($testData);

// Rendere Felder
$renderedFields = $fieldRenderer->renderFields('html');

echo "Anzahl gerenderte Felder: " . count($renderedFields) . "\n\n";

foreach ($renderedFields as $field) {
    echo "- " . $field['label'] . ": " . $field['display'] . "\n";
}

echo "\n=== Erwartetes Ergebnis ===\n";
echo "- Bodenplatten: Vorplatz / Garage: 25 m²\n";
echo "- Bodenplatten: Sitzplatz: Fläche unbekannt\n";
echo "- Anzahl Zimmer: 4.5\n";
echo "- Wohnflaeche: 120\n";
echo "\nHINWEIS: 'keller: Nein' sollte NICHT erscheinen\n";

echo "\n=== Test ohne FieldDisplayRules ===\n";
echo "Wenn FieldDisplayRules.php nicht existiert, sollten alle Felder normal angezeigt werden:\n";
echo "- bodenplatten_vorplatz: Ja\n";
echo "- bodenplatten_vorplatz_flaeche: Ja\n";
echo "- bodenplatten_vorplatz_flaeche_ja: 25\n";
echo "- etc.\n";

echo "\n=== Test abgeschlossen ===\n\n";
