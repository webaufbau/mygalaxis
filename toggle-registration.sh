#!/bin/bash

# Script zum Aktivieren/Deaktivieren der Registrierung auf allen Servern
# Setzt auth.allowRegistration auf true oder false

set -e

# Server Konfiguration
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
)

echo "=========================================="
echo "Registrierung aktivieren/deaktivieren"
echo "=========================================="
echo ""
echo "Was möchtest du tun?"
echo ""
echo "  1) Registrierung AKTIVIEREN   (auth.allowRegistration=true)"
echo "  2) Registrierung DEAKTIVIEREN (auth.allowRegistration=false)"
echo "  3) Status prüfen"
echo ""
read -p "Auswahl (1/2/3): " -n 1 -r CHOICE
echo ""

case $CHOICE in
    1)
        NEW_VALUE="true"
        ACTION="aktivieren"
        ;;
    2)
        NEW_VALUE="false"
        ACTION="deaktivieren"
        ;;
    3)
        echo ""
        echo "Status auf allen Servern:"
        echo ""
        for server_config in "${SERVERS[@]}"; do
            IFS='|' read -r server_name ssh_target path port git_pull <<< "$server_config"
            CURRENT=$(ssh -p "$port" "$ssh_target" "cd $path && grep '^auth.allowRegistration=' .env 2>/dev/null || echo 'NOT_FOUND'" 2>/dev/null)
            if [[ "$CURRENT" == *"true"* ]]; then
                echo "  ✓ $server_name: AKTIVIERT"
            elif [[ "$CURRENT" == *"false"* ]]; then
                echo "  ✗ $server_name: DEAKTIVIERT"
            else
                echo "  ? $server_name: $CURRENT"
            fi
        done
        echo ""
        exit 0
        ;;
    *)
        echo "Ungültige Auswahl. Abgebrochen."
        exit 1
        ;;
esac

echo ""
echo "Registrierung wird auf allen Servern ${ACTION}..."
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
    echo "→ $server_name"

    # Prüfe ob auth.allowRegistration existiert
    CURRENT_VALUE=$(ssh -p "$port" "$ssh_target" "cd $path && grep '^auth.allowRegistration=' .env || echo 'NOT_FOUND'" 2>/dev/null)

    if [[ "$CURRENT_VALUE" == "NOT_FOUND" ]]; then
        # Füge Zeile hinzu wenn sie nicht existiert
        echo "  Füge auth.allowRegistration hinzu..."
        if ssh -p "$port" "$ssh_target" "cd $path && echo 'auth.allowRegistration=$NEW_VALUE' >> .env"; then
            echo "  ✓ Hinzugefügt: auth.allowRegistration=$NEW_VALUE"
            SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
        else
            echo "  ✗ Fehlgeschlagen"
            FAILED_COUNT=$((FAILED_COUNT + 1))
            FAILED_SERVERS+=("$server_name")
        fi
    else
        # Update bestehende Zeile
        if ssh -p "$port" "$ssh_target" "cd $path && sed -i 's/^auth.allowRegistration=.*/auth.allowRegistration=$NEW_VALUE/' .env"; then
            echo "  ✓ auth.allowRegistration=$NEW_VALUE"
            SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
        else
            echo "  ✗ Fehlgeschlagen"
            FAILED_COUNT=$((FAILED_COUNT + 1))
            FAILED_SERVERS+=("$server_name")
        fi
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
echo "Fertig! Registrierung wurde ${ACTION}."
