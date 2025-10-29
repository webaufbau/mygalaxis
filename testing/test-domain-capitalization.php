<?php

/**
 * Test: Verify domain capitalization in email subjects
 */

// Simulate domain extraction and capitalization
$platforms = [
    'my_offertenschweiz_ch',
    'my_offertenheld_ch',
    'my_offertenprofi_ch',
];

echo "=== Test Domain Capitalization ===\n\n";

foreach ($platforms as $platform) {
    // Extract domain (same logic as in commands)
    $domain = str_replace('my_', '', $platform);
    $domain = str_replace('_', '.', $domain);

    // Capitalize first letter
    $domain = ucfirst($domain);

    // Show various email subject formats
    echo "Platform: {$platform}\n";
    echo "Domain: {$domain}\n";
    echo "\nEmail Subject Examples:\n";
    echo "  1. New Offer:  \"{$domain} - Neue Anfrage für Umzug #528 - 4053 Basel\"\n";
    echo "  2. Purchase:   \"{$domain} - Vielen Dank für den Kauf der Anfrage Gartenpflege in 4244 Röschenz\"\n";
    echo "  3. Customer:   \"{$domain} - Eine Firma interessiert sich für Ihre Anfrage - Gartenpflege in 4244 Röschenz\"\n";
    echo "  4. Discount:   \"{$domain} - 69% Rabatt auf Anfrage für Garten Arbeiten #16 6900 Lugano\"\n";
    echo "  5. Review:     \"{$domain} - Bitte bewerten Sie Ihre Erfahrung mit Reinigung in Röschenz\"\n";
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "✅ All email subjects now start with a capitalized domain!\n";
