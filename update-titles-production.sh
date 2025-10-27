#!/bin/bash

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Banner
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Update Offer Titles (One-Time)${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Server Konfiguration
# Format: "server_name|ssh_user@host|pfad|port"
SERVERS=(
    "offertenschweiz.ch|famajynu@vsm-devoha.cyon.net|www/my_offertenschweiz_ch|22"
    "renovo24.ch|famajynu@vsm-devoha.cyon.net|www/my_renovo24_ch|22"
    "offertenheld.ch|famajynu@vsm-devoha.cyon.net|www/my_offertenheld_ch|22"
    "offertendeutschland.de|offerq@dedi108.your-server.de|public_html/my_offertendeutschland_de|222"
    "renovoscout24.de|offerq@dedi108.your-server.de|public_html/my_renovoscout24_de|222"
    "offertenheld.de|offerq@dedi108.your-server.de|public_html/my_offertenheld_de|222"
    "offertenaustria.at|offerv@dedi1000.your-server.de|public_html/my_offertenaustria_at|222"
    "offertenheld.at|offerv@dedi1000.your-server.de|public_html/my_offertenheld_at|222"
    "renovo24.at|offerv@dedi1000.your-server.de|public_html/my_renovo24_at|222"
    "verwaltungbox.ch|bajagady@vsm-nysitu.cyon.net|www/verwaltungbox_ch|22"
)

# Funktion zum Ausführen des Commands auf einem Server
update_titles() {
    local server_name=$1
    local ssh_user_host=$2
    local project_path=$3
    local ssh_port=$4

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📡 Verbinde mit: ${server_name}${NC}"
    echo -e "${BLUE}   Server: ${ssh_user_host}:${ssh_port}${NC}"
    echo -e "${BLUE}   Pfad: ${project_path}${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # Command ausführen
    echo -e "${BLUE}🔄 Update Offer Titles wird ausgeführt...${NC}"
    local output
    output=$(ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && php spark offers:update-titles 2>&1" 2>&1)
    local exit_code=$?

    # Output immer anzeigen
    echo "$output"

    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✅ Titel erfolgreich aktualisiert auf ${server_name}${NC}"
    else
        echo -e "${RED}❌ Fehler beim Aktualisieren auf ${server_name} (Exit Code: ${exit_code})${NC}"
    fi

    echo ""
}

# Bestätigung einholen
echo -e "${YELLOW}Dieser Command aktualisiert alle Angebots-Titel auf folgenden Servern:${NC}"
for i in "${!SERVERS[@]}"; do
    IFS='|' read -r server_name _ _ _ <<< "${SERVERS[$i]}"
    echo -e "  $((i+1)). ${server_name}"
done
echo ""
echo -e "${RED}WICHTIG: Dies ist ein einmaliger Command!${NC}"
echo -e "${YELLOW}Er entfernt die Details in Klammern aus allen Angebots-Titeln.${NC}"
echo ""

# Prüfe ob --yes oder -y Parameter übergeben wurde
if [[ "$1" != "-y" && "$1" != "--yes" ]]; then
    read -p "Möchtest du fortfahren? (j/n): " confirm
    if [[ ! $confirm =~ ^[jJyY]$ ]]; then
        echo -e "${RED}Abgebrochen.${NC}"
        exit 0
    fi
else
    echo -e "${GREEN}Auto-Bestätigung aktiviert (-y/--yes)${NC}"
fi

echo ""

# Command auf allen Servern ausführen
for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port <<< "$server_config"
    update_titles "$server_name" "$ssh_user_host" "$project_path" "$ssh_port"
done

# Zusammenfassung
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Update abgeschlossen!${NC}"
echo -e "${BLUE}========================================${NC}"
