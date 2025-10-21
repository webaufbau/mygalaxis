#!/bin/bash

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Banner
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Migration Deployment Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Server Konfiguration
# Format: "server_name|ssh_user@host|pfad|port|git_pull"
SERVERS=(
    "offertenschweiz.ch|famajynu@vsm-devoha.cyon.net|www/my_offertenschweiz_ch|22|yes"
    "offertenheld.de|offerq@dedi108.your-server.de|public_html/my_offertenheld_de|222|yes"
    "offertenheld.at|offerv@dedi1000.your-server.de|public_html/my_offertenheld_at|222|yes"
    "verwaltungbox.ch|bajagady@vsm-nysitu.cyon.net|www/verwaltungbox_ch|22|yes"
)

# Funktion zum Ausführen der Migration auf einem Server
run_migration() {
    local server_name=$1
    local ssh_user_host=$2
    local project_path=$3
    local ssh_port=$4
    local need_git_pull=$5

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📡 Verbinde mit: ${server_name}${NC}"
    echo -e "${BLUE}   Server: ${ssh_user_host}:${ssh_port}${NC}"
    echo -e "${BLUE}   Pfad: ${project_path}${NC}"
    if [ "$need_git_pull" = "yes" ]; then
        echo -e "${BLUE}   Git Pull: Ja${NC}"
    fi
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # SSH Befehl zusammenbauen
    local command="cd ${project_path}"

    if [ "$need_git_pull" = "yes" ]; then
        echo -e "${BLUE}🔄 Git Pull wird ausgeführt...${NC}"
        command="${command} && git pull"
    fi

    command="${command} && php spark migrate"

    # SSH Befehl ausführen
    ssh -p "${ssh_port}" "${ssh_user_host}" "${command}"

    local exit_code=$?

    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✅ Migration erfolgreich auf ${server_name}${NC}"
    else
        echo -e "${RED}❌ Fehler bei Migration auf ${server_name} (Exit Code: ${exit_code})${NC}"
    fi

    echo ""
}

# Bestätigung einholen
echo -e "${YELLOW}Folgende Server werden aktualisiert:${NC}"
echo -e "  1. offertenschweiz.ch (famajynu@vsm-devoha.cyon.net)"
echo -e "  2. offertenheld.de (offerq@dedi108.your-server.de)"
echo -e "  3. offertenheld.at (offerv@dedi1000.your-server.de)"
echo -e "  4. verwaltungbox.ch (bajagady@vsm-nysitu.cyon.net) ${BLUE}[mit Git Pull]${NC}"
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

# Migrations auf allen Servern ausführen
for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port need_git_pull <<< "$server_config"
    run_migration "$server_name" "$ssh_user_host" "$project_path" "$ssh_port" "$need_git_pull"
done

# Zusammenfassung
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Deployment abgeschlossen!${NC}"
echo -e "${BLUE}========================================${NC}"
