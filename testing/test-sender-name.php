<?php

// Test der getEmailFromName Funktion

// Simuliere die Funktion
function getEmailFromName($siteConfig): string
{
    $domain = '';

    // Versuche name zu bekommen
    if (!empty($siteConfig->name)) {
        // Use first part of name (e.g. "Offertenschweiz AG" -> "Offertenschweiz")
        $domain = explode(' ', $siteConfig->name)[0];
    }

    // Fallback: Wenn name leer ist, verwende email domain
    if (empty($domain) && !empty($siteConfig->email)) {
        // Extrahiere Domain aus E-Mail (z.B. info@offertenschweiz.ch -> offertenschweiz)
        $emailParts = explode('@', $siteConfig->email);
        if (count($emailParts) === 2) {
            $domainParts = explode('.', $emailParts[1]);
            if (count($domainParts) >= 2) {
                $domain = $domainParts[0];
            }
        }
    }

    $domainExtension = $siteConfig->domain_extension ?? '.ch';
    return ucfirst($domain) . $domainExtension;
}

// Test-Fälle
$testCases = [
    (object)['name' => 'Offertenschweiz', 'domain_extension' => '.ch', 'email' => 'info@offertenschweiz.ch'],
    (object)['name' => 'Offertenheld', 'domain_extension' => '.ch', 'email' => 'info@offertenheld.ch'],
    (object)['name' => 'Offertenschweiz AG', 'domain_extension' => '.ch', 'email' => 'info@offertenschweiz.ch'],
    (object)['name' => 'MyDomain', 'domain_extension' => '.de', 'email' => 'info@mydomain.de'],
    (object)['name' => '', 'domain_extension' => '.ch', 'email' => 'info@offertenschweiz.ch'], // LEER
    (object)['domain_extension' => '.ch', 'email' => 'info@offertenheld.ch'], // NULL
];

echo "=== Test: E-Mail Absendernamen ===\n\n";

foreach ($testCases as $config) {
    $result = getEmailFromName($config);
    echo "Config Name: '{$config->name}'\n";
    echo "Extension: '{$config->domain_extension}'\n";
    echo "➜ Ergebnis: '{$result}'\n\n";
}

echo "=== Test abgeschlossen ===\n";
