#!/bin/bash

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Fix Zero Price Offers on All Servers${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Server Konfiguration
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
)

fix_prices() {
    local server_name=$1
    local ssh_user_host=$2
    local project_path=$3
    local ssh_port=$4

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📡 ${server_name}${NC}"
    
    # Spark Befehl ausführen
    ssh -p "${ssh_port}" "${ssh_user_host}" "cd ${project_path} && php spark offers:fix-zero-prices 2>&1"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Fertig${NC}"
    else
        echo -e "${RED}❌ Fehler${NC}"
    fi
    echo ""
}

for server_config in "${SERVERS[@]}"; do
    IFS='|' read -r server_name ssh_user_host project_path ssh_port <<< "$server_config"
    fix_prices "$server_name" "$ssh_user_host" "$project_path" "$ssh_port"
done

echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✨ Alle Server verarbeitet!${NC}"
echo -e "${BLUE}========================================${NC}"
