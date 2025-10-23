#!/bin/bash

echo "=== Fehleranalyse aller heruntergeladenen Logs ==="
echo ""

# Temporäre Datei für alle Fehler
TEMP_FILE="/tmp/all_errors.txt"
> "$TEMP_FILE"

# Sammle alle Fehler
grep -h "ERROR\|CRITICAL" writable/logs/*_log-2025-10-*.log 2>/dev/null > "$TEMP_FILE"

TOTAL_ERRORS=$(wc -l < "$TEMP_FILE" | tr -d ' ')
echo "Gesamt Fehler gefunden: $TOTAL_ERRORS"
echo ""

# Cache-Fehler zählen
CACHE_ERRORS=$(grep -c "Cache unable to write" "$TEMP_FILE" 2>/dev/null || echo "0")
echo "❌ Cache-Schreibfehler: $CACHE_ERRORS"

# Andere Fehler
OTHER_ERRORS=$((TOTAL_ERRORS - CACHE_ERRORS))
echo "❌ Andere Fehler: $OTHER_ERRORS"
echo ""

# Nach Seiten gruppieren
echo "=== Fehler nach Server ==="
for server in offertenschweiz_ch renovo24_ch offertenheld_ch offertendeutschland_de renovoscout24_de offertenheld_de offertenaustria_at offertenheld_at renovo24_at verwaltungbox_ch; do
    count=$(grep -h "ERROR\|CRITICAL" writable/logs/${server}_log-2025-10-*.log 2>/dev/null | wc -l | tr -d ' ')
    if [ "$count" -gt 0 ]; then
        echo "  $server: $count Fehler"
    fi
done
echo ""

# Nicht-Cache Fehler anzeigen
echo "=== Andere Fehler (keine Cache-Fehler) ==="
grep -h "ERROR\|CRITICAL" writable/logs/*_log-2025-10-*.log 2>/dev/null | grep -v "Cache unable to write" | head -30
echo ""

# Letzte 10 unique Fehler (ohne Cache)
echo "=== Letzte 10 unique Fehlertypen ==="
grep -h "ERROR\|CRITICAL" writable/logs/*_log-2025-10-*.log 2>/dev/null | \
    grep -v "Cache unable to write" | \
    sed 's/.*--> //' | \
    sort | uniq | tail -10

echo ""
echo "✓ Analyse abgeschlossen"
