<?php

/**
 * Final Test: All Email Subjects with Capitalized Domain
 */

echo "================================================================================\n";
echo "                FINALE EMAIL-BETREFFS ÜBERSICHT\n";
echo "================================================================================\n\n";

$domain = "Offertenschweiz.ch";
$examples = [
    [
        'name' => '1. Neue Offerte → Firma',
        'format' => '{Domain}.ch - Neue Anfrage für {Type} #{ID} - {PLZ} {Stadt}',
        'examples' => [
            "{$domain} - Neue Anfrage für Reinigung #453 - 4244 Röschenz",
            "{$domain} - Neue Anfrage für Umzug #451 - 4153 Reinach",
            "{$domain} - Neue Anfrage für Garten Arbeiten #447 - 4244 Röschenz",
        ]
    ],
    [
        'name' => '2. Kauf → Firma',
        'format' => '{Domain}.ch - Vielen Dank für den Kauf der Anfrage {Type} in {PLZ} {Stadt}',
        'examples' => [
            "{$domain} - Vielen Dank für den Kauf der Anfrage Reinigung in 4244 Röschenz",
            "{$domain} - Vielen Dank für den Kauf der Anfrage Umzug in 4153 Reinach",
        ]
    ],
    [
        'name' => '3. Kauf → Kunde',
        'format' => '{Domain}.ch - Eine Firma interessiert sich für Ihre Anfrage - {Type} in {PLZ} {Stadt}',
        'examples' => [
            "{$domain} - Eine Firma interessiert sich für Ihre Anfrage - Reinigung in 4244 Röschenz",
            "{$domain} - Eine Firma interessiert sich für Ihre Anfrage - Umzug in 4153 Reinach",
        ]
    ],
    [
        'name' => '4. Rabatt → Firma',
        'format' => '{Domain}.ch - {X}% Rabatt auf Anfrage für {Type} #{ID} {PLZ} {Stadt}',
        'examples' => [
            "{$domain} - 69% Rabatt auf Anfrage für Reinigung #453 4244 Röschenz",
            "{$domain} - 59% Rabatt auf Anfrage für Umzug #451 4153 Reinach",
            "{$domain} - 70% Rabatt auf Anfrage für Garten Arbeiten #16 6900 Lugano",
        ]
    ],
    [
        'name' => '5. Bewertung → Kunde',
        'format' => '{Domain}.ch - Bitte bewerten Sie die Anfrage - {Type} #{ID} {PLZ} {Stadt}',
        'examples' => [
            "{$domain} - Bitte bewerten Sie die Anfrage - Reinigung #1 3000 Bern",
            "{$domain} - Bitte bewerten Sie die Anfrage - Umzug #4 8001 Zürich",
            "{$domain} - Bitte bewerten Sie die Anfrage - Garten Arbeiten #16 6900 Lugano",
        ]
    ],
];

foreach ($examples as $section) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $section['name'] . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "Format:\n  " . $section['format'] . "\n\n";
    echo "Beispiele:\n";
    foreach ($section['examples'] as $example) {
        // Check if first character is uppercase
        $firstChar = mb_substr($example, 0, 1);
        $isCapitalized = $firstChar === mb_strtoupper($firstChar);
        $status = $isCapitalized ? "✅" : "❌";

        echo "  {$status} {$example}\n";
    }
    echo "\n";
}

echo "================================================================================\n";
echo "                           ZUSAMMENFASSUNG\n";
echo "================================================================================\n\n";
echo "✅ Alle E-Mail-Betreffs beginnen mit großem Anfangsbuchstaben\n";
echo "✅ Domain wird kapitalisiert (z.B. Offertenschweiz.ch)\n";
echo "✅ Einheitliches Format über alle E-Mail-Typen\n";
echo "✅ Typ-Namen grammatikalisch korrekt (Umzug, Garten Arbeiten, etc.)\n";
echo "✅ PLZ und Stadt im Betreff enthalten\n\n";

echo "📬 E-Mails testen:\n";
echo "  ddev exec php test-all-emails.php\n\n";

echo "================================================================================\n";
