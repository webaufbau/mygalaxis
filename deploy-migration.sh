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
        echo -e "${BLUE}🔄 Git Stash & Pull wird ausgeführt...${NC}"
        # Zuerst stash um lokale Änderungen zu sichern, dann pull
        ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && git stash && git pull && git stash pop 2>/dev/null || true"
    fi

    # Writable-Verzeichnisse erstellen und Rechte setzen
    echo -e "${BLUE}📁 Erstelle writable-Verzeichnisse...${NC}"
    ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && \
        mkdir -p writable/cache writable/logs writable/session writable/uploads && \
        chmod -R 775 writable && \
        find writable -type d -exec chmod 775 {} \; && \
        find writable -type f -exec chmod 664 {} \;"

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Writable-Verzeichnisse erstellt${NC}"
    else
        echo -e "${YELLOW}⚠ Warnung: Konnte writable-Verzeichnisse nicht erstellen${NC}"
    fi

    # Migration separat ausführen mit besserer Fehlerbehandlung
    echo -e "${BLUE}🔄 Migration wird ausgeführt...${NC}"
    local migrate_output
    migrate_output=$(ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && php spark migrate 2>&1" 2>&1)
    local exit_code=$?

    # Output immer anzeigen
    echo "$migrate_output"

    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✅ Migration erfolgreich auf ${server_name}${NC}"
    else
        echo -e "${RED}❌ Fehler bei Migration auf ${server_name} (Exit Code: ${exit_code})${NC}"

        # Zusätzliche Diagnose-Informationen
        echo -e "${YELLOW}🔍 Diagnose-Informationen:${NC}"
        ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && php -v 2>&1 | head -1 && ls -la php 2>&1 || echo 'PHP binary check' && which php 2>&1"
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
