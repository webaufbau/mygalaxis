<?php

$mysqli = new mysqli('db', 'db', 'db', 'db');

echo "=== Test Gartenpflege Rabatt-E-Mail ===\n\n";

// Ändere Rabattpreis für Offerte #16 (Gartenpflege)
$mysqli->query("UPDATE offers SET discounted_price = discounted_price + 0.10 WHERE id = 16");
echo "✓ Rabattpreis für Offerte #16 (Gartenpflege) geändert\n\n";

echo "Führe Rabatt-Command aus...\n";
echo str_repeat("-", 60) . "\n";

passthru("cd /var/www/html && php spark offers:discount-old 2>&1");

echo "\n" . str_repeat("-", 60) . "\n";
echo "✅ Fertig!\n\n";
echo "Prüfe die Gartenpflege-E-Mail in MailPit:\n";
echo "  → https://mygalaxis.ddev.site:8026\n\n";
echo "Erwartung:\n";
echo "  • Betreff: \"X% Rabatt auf Anfrage für Garten Arbeiten #16 6900 Lugano\"\n";
echo "  • Inhalt: FORMATIERTE Details mit \"Informationen zum Projekt\"\n";
echo "  • NICHT: Rohfelder wie \"Fluentform 27 Fluentformnonce\"\n";
