#!/bin/bash

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Fix Heating Keys in category_settings.json${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Server Konfiguration aus deploy-migration.sh
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

fix_keys() {
    local server_name=$1
    local ssh_user_host=$2
    local project_path=$3
    local ssh_port=$4
    local config_file="${project_path}/writable/config/category_settings.json"

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📡 ${server_name}${NC}"
    
    # Prüfen ob Datei existiert
    file_exists=$(ssh -p "${ssh_port}" "${ssh_user_host}" "[ -f ${config_file} ] && echo 'yes' || echo 'no'" 2>/dev/null)
    
    if [ "$file_exists" != "yes" ]; then
        echo -e "${YELLOW}⚠ Keine category_settings.json gefunden${NC}"
        return
    fi
    
    echo -e "${GREEN}✓ Datei gefunden${NC}"
    
    # Prüfen ob alte Keys vorhanden sind
    has_old_keys=$(ssh -p "${ssh_port}" "${ssh_user_host}" "grep -c '\"neue_waermepumpe\"\\|\"neue_oelheizung\"\\|\"neue_erdwaerme\"' ${config_file} 2>/dev/null || echo 0")
    
    if [ "$has_old_keys" = "0" ]; then
        echo -e "${GREEN}✓ Keys bereits korrekt oder nicht vorhanden${NC}"
        return
    fi
    
    echo -e "${YELLOW}→ Ersetze $has_old_keys alte Keys...${NC}"
    
    # Keys ersetzen mit sed
    ssh -p "${ssh_port}" "${ssh_user_host}" "
        cd ${project_path}/writable/config && \
        cp category_settings.json category_settings.json.backup && \
        sed -i 's/\"neue_waermepumpe\"/\"neue_el__waermepumpe\"/g' category_settings.json && \
        sed -i 's/\"neue_oelheizung\"/\"neue___l_heizung\"/g' category_settings.json && \
        sed -i 's/\"neue_erdwaerme\"/\"neue_erdwaermheizung\"/g' category_settings.json
    " 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Keys erfolgreich ersetzt${NC}"
    else
        echo -e "${RED}❌ Fehler beim Ersetzen${NC}"
    fi
}

echo -e "${YELLOW}Prüfe und fixe Keys auf allen Servern...${NC}"
echo ""

for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port <<< "$server_config"
    fix_keys "$server_name" "$ssh_user_host" "$project_path" "$ssh_port"
done

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Fertig!${NC}"
echo -e "${BLUE}========================================${NC}"
