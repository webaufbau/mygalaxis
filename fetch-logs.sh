#!/bin/bash

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Banner
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Log Fetch Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Anzahl der letzten Log-Dateien die geholt werden sollen (Standard: 3)
LOG_COUNT=${1:-3}

# Lokales Verzeichnis für gesammelte Logs
LOCAL_LOG_DIR="writable/logs"
mkdir -p "$LOCAL_LOG_DIR"

echo -e "${BLUE}📥 Hole die letzten ${LOG_COUNT} Log-Dateien von allen Servern${NC}"
echo -e "${BLUE}📂 Speicherort: ${LOCAL_LOG_DIR}${NC}"
echo ""

# Server Konfiguration (gleiche wie in deploy-migration.sh)
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

# Funktion zum Holen der Logs von einem Server
fetch_logs() {
    local server_name=$1
    local ssh_user_host=$2
    local project_path=$3
    local ssh_port=$4

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📡 Verbinde mit: ${server_name}${NC}"
    echo -e "${BLUE}   Server: ${ssh_user_host}:${ssh_port}${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # Server-Slug für Datei-Präfix
    local server_slug=$(echo "$server_name" | sed 's/\./_/g')

    # Hole Liste der neuesten Log-Dateien vom Server
    echo -e "${BLUE}🔍 Suche Log-Dateien...${NC}"
    local log_files=$(ssh -p "${ssh_port}" "${ssh_user_host}" \
        "cd ${project_path}/writable/logs 2>/dev/null && ls -t log-*.log 2>/dev/null | head -n ${LOG_COUNT}" 2>&1)

    if [ -z "$log_files" ]; then
        echo -e "${YELLOW}⚠ Keine Log-Dateien gefunden auf ${server_name}${NC}"
        echo ""
        return
    fi

    # Zähle gefundene Log-Dateien
    local log_count=$(echo "$log_files" | wc -l | tr -d ' ')
    echo -e "${GREEN}✓ ${log_count} Log-Datei(en) gefunden${NC}"

    # Lade jede Log-Datei herunter
    while IFS= read -r log_file; do
        if [ -n "$log_file" ]; then
            echo -e "${BLUE}  📥 Lade: ${log_file}${NC}"

            # SCP mit Umbenennung (Server-Präfix hinzufügen)
            local local_filename="${server_slug}_${log_file}"

            scp -P "${ssh_port}" \
                "${ssh_user_host}:${project_path}/writable/logs/${log_file}" \
                "${LOCAL_LOG_DIR}/${local_filename}" 2>/dev/null

            if [ $? -eq 0 ]; then
                # Prüfe Dateigröße
                local filesize=$(stat -f%z "${LOCAL_LOG_DIR}/${local_filename}" 2>/dev/null || stat -c%s "${LOCAL_LOG_DIR}/${local_filename}" 2>/dev/null)
                if [ "$filesize" -gt 0 ]; then
                    local size_kb=$((filesize / 1024))
                    echo -e "${GREEN}     ✓ Gespeichert (${size_kb} KB): ${local_filename}${NC}"
                else
                    echo -e "${YELLOW}     ⚠ Datei ist leer${NC}"
                fi
            else
                echo -e "${RED}     ❌ Fehler beim Download${NC}"
            fi
        fi
    done <<< "$log_files"

    echo ""
}

# Bestätigung einholen
echo -e "${YELLOW}Logs werden von folgenden Servern geholt:${NC}"
for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port <<< "$server_config"
    echo -e "  • ${server_name}"
done
echo ""

# Prüfe ob --yes oder -y Parameter übergeben wurde
if [[ "$2" != "-y" && "$2" != "--yes" ]]; then
    read -p "Möchtest du fortfahren? (j/n): " confirm
    if [[ ! $confirm =~ ^[jJyY]$ ]]; then
        echo -e "${RED}Abgebrochen.${NC}"
        exit 0
    fi
else
    echo -e "${GREEN}Auto-Bestätigung aktiviert (-y/--yes)${NC}"
fi

echo ""

# Logs von allen Servern holen
for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port <<< "$server_config"
    fetch_logs "$server_name" "$ssh_user_host" "$project_path" "$ssh_port"
done

# Zusammenfassung
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Log-Download abgeschlossen!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${GREEN}📂 Logs gespeichert in: ${LOCAL_LOG_DIR}${NC}"
echo ""

# Optional: Übersicht der heruntergeladenen Dateien
echo -e "${BLUE}📊 Übersicht der heruntergeladenen Logs:${NC}"
echo -e "${YELLOW}Alle Logs:${NC}"
ls -lht "${LOCAL_LOG_DIR}"/*_log-*.log 2>/dev/null | head -20 | awk '{printf "  %s (%s)\n", $9, $5}'

echo ""
echo -e "${BLUE}💡 Tipp: Um mehr/weniger Logs zu holen, verwende:${NC}"
echo -e "${BLUE}   ./fetch-logs.sh 5     # holt die letzten 5 Log-Dateien${NC}"
echo -e "${BLUE}   ./fetch-logs.sh 10 -y # holt 10 Logs ohne Bestätigung${NC}"
