#!/bin/bash

# Script zum Anpassen der infobip.sender Konfiguration auf allen Servern
# Setzt infobip.sender auf leer (statt "InfoSMS")

set -e

# Server Konfiguration (aus deploy-migration.sh)
SERVERS=(
    "offertenschweiz.ch|famajynu@vsm-devoha.cyon.net|www/my_offertenschweiz_ch|22|yes"
    "renovo24.ch|famajynu@vsm-devoha.cyon.net|www/my_renovo24_ch|22|yes"
    "offertenheld.ch|famajynu@vsm-devoha.cyon.net|www/my_offertenheld_ch|22|yes"
    "offertendeutschland.de|offerq@dedi108.your-server.de|public_html/my_offertendeutschland_de|222|yes"
    "renovoscout24.de|offerq@dedi108.your-server.de|public_html/my_renovoscout24_de|222|yes"
    "offertenheld.de|offerq@dedi108.your-server.de|public_html/my_offertenheld_de|222|yes"
    "offertenaustria.at|offerv@dedi1000.your-server.de|public_html/my_offertenaustria_at|222|yes"
    "offertenheld.at|offerv@dedi1000.your-server.de|public_html/my_offertenheld_at|222|yes"
    "renovo24.at|offerv@dedi1000.your-server.de|public_html/my_renovo24_at|222|yes"
    "verwaltungbox.ch|bajagady@vsm-nysitu.cyon.net|www/verwaltungbox_ch|22|yes"
)

echo "=========================================="
echo "Infobip Sender ID Update Script"
echo "=========================================="
echo ""
echo "Dieses Script setzt 'infobip.sender=' auf leer auf allen Servern"
echo ""
echo "Anzahl Server: ${#SERVERS[@]}"
echo ""
read -p "Fortfahren? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Abgebrochen."
    exit 1
fi

SUCCESS_COUNT=0
FAILED_COUNT=0
FAILED_SERVERS=()

for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_target path port git_pull <<< "$server_config"

    echo ""
    echo "=========================================="
    echo "Server: $server_name"
    echo "=========================================="

    ENV_PATH="$path/.env"

    # Erstelle Backup der .env Datei
    echo "→ Erstelle Backup..."
    if ssh -p "$port" "$ssh_target" "cd $path && cp .env .env.backup-$(date +%Y%m%d-%H%M%S)"; then
        echo "✓ Backup erstellt"
    else
        echo "✗ Backup fehlgeschlagen"
        FAILED_COUNT=$((FAILED_COUNT + 1))
        FAILED_SERVERS+=("$server_name (Backup failed)")
        continue
    fi

    # Prüfe ob infobip.sender existiert
    echo "→ Prüfe aktuelle Konfiguration..."
    CURRENT_VALUE=$(ssh -p "$port" "$ssh_target" "cd $path && grep '^infobip.sender=' .env || echo 'NOT_FOUND'")

    if [[ "$CURRENT_VALUE" == "NOT_FOUND" ]]; then
        echo "⚠ infobip.sender nicht gefunden in .env"
        echo "  Überspringe Server..."
        continue
    fi

    echo "  Aktuell: $CURRENT_VALUE"

    # Update infobip.sender
    echo "→ Aktualisiere infobip.sender..."
    if ssh -p "$port" "$ssh_target" "cd $path && sed -i 's/^infobip.sender=.*/infobip.sender=/' .env"; then
        echo "✓ Update erfolgreich"

        # Verifiziere Änderung
        NEW_VALUE=$(ssh -p "$port" "$ssh_target" "cd $path && grep '^infobip.sender=' .env")
        echo "  Neu: $NEW_VALUE"

        SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
    else
        echo "✗ Update fehlgeschlagen"
        FAILED_COUNT=$((FAILED_COUNT + 1))
        FAILED_SERVERS+=("$server_name (Update failed)")
    fi
done

echo ""
echo "=========================================="
echo "Zusammenfassung"
echo "=========================================="
echo "Erfolgreich: $SUCCESS_COUNT"
echo "Fehlgeschlagen: $FAILED_COUNT"

if [ $FAILED_COUNT -gt 0 ]; then
    echo ""
    echo "Fehlgeschlagene Server:"
    for failed in "${FAILED_SERVERS[@]}"; do
        echo "  - $failed"
    done
fi

echo ""
echo "Fertig!"
