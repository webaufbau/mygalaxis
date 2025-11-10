#!/bin/bash

# ============================================================================
# MyGalaxis Database Sync Script
# ============================================================================
# Erstellt einen Dump auf dem Server und importiert ihn in die lokale DDEV-DB
# ============================================================================

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Server Konfiguration - offertenschweiz.ch als Standard
SSH_USER="famajynu"
SSH_HOST="vsm-devoha.cyon.net"
SSH_PORT="22"
SSH_CONNECTION="${SSH_USER}@${SSH_HOST}"
PROJECT_PATH="www/my_offertenschweiz_ch"

# Lokale Konfiguration
LOCAL_DUMP_DIR="writable/dumps"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DUMP_FILENAME="mygalaxis_dump_${TIMESTAMP}.sql"
DUMP_FILENAME_GZ="${DUMP_FILENAME}.gz"

# Banner
clear
echo -e "${CYAN}╔════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║   MyGalaxis Database Sync Script       ║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📡 Server: ${SSH_CONNECTION}:${SSH_PORT}${NC}"
echo -e "${BLUE}📁 Pfad: ${PROJECT_PATH}${NC}"
echo ""

# ============================================================================
# Prüfe ob DDEV läuft
# ============================================================================
echo -e "${BLUE}🔍 Prüfe DDEV Status...${NC}"
if ! ddev describe >/dev/null 2>&1; then
    echo -e "${RED}✗ DDEV ist nicht gestartet!${NC}"
    echo -e "${YELLOW}Starte DDEV jetzt...${NC}"
    ddev start
    if [ $? -ne 0 ]; then
        echo -e "${RED}✗ DDEV konnte nicht gestartet werden!${NC}"
        exit 1
    fi
fi
echo -e "${GREEN}✓ DDEV läuft${NC}"
echo ""

# ============================================================================
# Prüfe SSH-Verbindung
# ============================================================================
echo -e "${BLUE}🔐 Teste SSH-Verbindung...${NC}"
if ! ssh -p "${SSH_PORT}" -o ConnectTimeout=5 "${SSH_CONNECTION}" "echo 'OK'" >/dev/null 2>&1; then
    echo -e "${RED}✗ SSH-Verbindung fehlgeschlagen!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ SSH-Verbindung erfolgreich${NC}"
echo ""

# ============================================================================
# Lokales Dump-Verzeichnis erstellen
# ============================================================================
mkdir -p "$LOCAL_DUMP_DIR"

# ============================================================================
# Datenbank-Credentials vom Server holen
# ============================================================================
echo -e "${BLUE}🔍 Hole Datenbank-Credentials vom Server...${NC}"

# Lese .env Datei vom Server und extrahiere DB-Credentials
DB_CREDENTIALS=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "cd ${PROJECT_PATH} && cat .env | grep -E '^database\.default\.' | grep -v '^#'" 2>&1)

if [ -z "$DB_CREDENTIALS" ]; then
    echo -e "${RED}✗ Konnte keine Datenbank-Credentials finden!${NC}"
    echo -e "${YELLOW}Bitte prüfe, ob die .env Datei auf dem Server existiert.${NC}"
    exit 1
fi

echo -e "${CYAN}Debug: Gefundene Credentials:${NC}"
echo "$DB_CREDENTIALS"
echo ""

# Extrahiere einzelne Werte mit verbessertem Parsing
DB_HOST=$(echo "$DB_CREDENTIALS" | grep "^database\.default\.hostname" | cut -d'=' -f2- | tr -d ' ' | tr -d '\r')
DB_NAME=$(echo "$DB_CREDENTIALS" | grep "^database\.default\.database[^a-z]" | cut -d'=' -f2- | tr -d ' ' | tr -d '\r')
DB_USER=$(echo "$DB_CREDENTIALS" | grep "^database\.default\.username" | cut -d'=' -f2- | tr -d ' ' | tr -d '\r')
DB_PASS=$(echo "$DB_CREDENTIALS" | grep "^database\.default\.password" | cut -d'=' -f2- | tr -d ' ' | tr -d '\r')
DB_PORT=$(echo "$DB_CREDENTIALS" | grep "^database\.default\.port" | cut -d'=' -f2- | tr -d ' ' | tr -d '\r')

# Validierung
if [ -z "$DB_HOST" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo -e "${RED}✗ Unvollständige Datenbank-Credentials!${NC}"
    echo -e "${YELLOW}Gefunden:${NC}"
    echo -e "  Host: '${DB_HOST}'"
    echo -e "  Database: '${DB_NAME}'"
    echo -e "  User: '${DB_USER}'"
    echo -e "  Password: '$(if [ -n "$DB_PASS" ]; then echo "***"; else echo "LEER"; fi)'"
    echo -e "  Port: '${DB_PORT}'"
    exit 1
fi

echo -e "${GREEN}✓ Credentials erfolgreich ausgelesen${NC}"
echo -e "${CYAN}  Host: ${DB_HOST}${NC}"
echo -e "${CYAN}  Database: ${DB_NAME}${NC}"
echo -e "${CYAN}  User: ${DB_USER}${NC}"
echo -e "${CYAN}  Port: ${DB_PORT:-3306}${NC}"
echo ""

# ============================================================================
# Dump auf dem Server erstellen
# ============================================================================
echo -e "${BLUE}📦 Erstelle Dump auf dem Server...${NC}"
echo -e "${YELLOW}   Dies kann einige Minuten dauern...${NC}"

# Erstelle temporären Dump-Pfad auf dem Server
REMOTE_DUMP_PATH_SQL="mygalaxis_temp_dump_${TIMESTAMP}.sql"
REMOTE_DUMP_PATH_GZ="mygalaxis_temp_dump_${TIMESTAMP}.sql.gz"

# Debug-Informationen
echo -e "${CYAN}Debug: Dump wird erstellt mit folgenden Parametern:${NC}"
echo -e "${CYAN}  Host: ${DB_HOST}${NC}"
echo -e "${CYAN}  User: ${DB_USER}${NC}"
echo -e "${CYAN}  Database: ${DB_NAME}${NC}"
echo -e "${CYAN}  Port: ${DB_PORT:-3306}${NC}"
echo ""

# mysqldump auf dem Server ausführen
# Cyon verwendet diese Syntax: mysqldump datenbankname -u user -p'password' > datei.sql
# KEIN -h Parameter nötig bei Cyon (verwendet automatisch localhost)
echo -e "${BLUE}Step 1: Erstelle SQL Dump...${NC}"
echo -e "${CYAN}Befehl: mysqldump '${DB_NAME}' -u '${DB_USER}' -p'***' > ${REMOTE_DUMP_PATH_SQL}${NC}"
DUMP_OUTPUT=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "
    mysqldump '${DB_NAME}' -u '${DB_USER}' -p'${DB_PASS}' > ${REMOTE_DUMP_PATH_SQL} 2>&1
    echo \$?
" 2>&1)

DUMP_EXIT_CODE=$(echo "$DUMP_OUTPUT" | tail -1)

if [ "$DUMP_EXIT_CODE" != "0" ]; then
    echo -e "${RED}✗ mysqldump fehlgeschlagen!${NC}"
    echo -e "${YELLOW}Exit Code: ${DUMP_EXIT_CODE}${NC}"
    echo -e "${YELLOW}Fehlerausgabe:${NC}"
    echo "$DUMP_OUTPUT"

    # Versuche herauszufinden, was schiefgelaufen ist
    echo ""
    echo -e "${YELLOW}Teste Datenbankverbindung...${NC}"
    TEST_OUTPUT=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "
        mysql -u '${DB_USER}' -p'${DB_PASS}' -e 'SHOW DATABASES;' 2>&1
    ")
    echo "$TEST_OUTPUT"
    exit 1
fi

# Prüfe SQL-Dump-Größe
SQL_SIZE_BYTES=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "stat -c%s ${REMOTE_DUMP_PATH_SQL} 2>/dev/null || stat -f%z ${REMOTE_DUMP_PATH_SQL} 2>/dev/null")
if [ "$SQL_SIZE_BYTES" -lt 1000 ]; then
    echo -e "${RED}✗ SQL-Dump ist zu klein (${SQL_SIZE_BYTES} Bytes)!${NC}"
    echo -e "${YELLOW}Dump-Inhalt (erste 50 Zeilen):${NC}"
    ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "head -50 ${REMOTE_DUMP_PATH_SQL}"
    exit 1
fi

echo -e "${GREEN}✓ SQL Dump erstellt ($(numfmt --to=iec ${SQL_SIZE_BYTES} 2>/dev/null || echo ${SQL_SIZE_BYTES} Bytes))${NC}"

# Komprimiere den Dump
echo -e "${BLUE}Step 2: Komprimiere Dump...${NC}"
ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "gzip -c ${REMOTE_DUMP_PATH_SQL} > ${REMOTE_DUMP_PATH_GZ} && rm ${REMOTE_DUMP_PATH_SQL}"

# Prüfe komprimierte Dateigröße
REMOTE_SIZE=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "ls -lh ${REMOTE_DUMP_PATH_GZ} 2>/dev/null | awk '{print \$5}'")
REMOTE_SIZE_BYTES=$(ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "stat -c%s ${REMOTE_DUMP_PATH_GZ} 2>/dev/null || stat -f%z ${REMOTE_DUMP_PATH_GZ} 2>/dev/null")

if [ -n "$REMOTE_SIZE" ] && [ "$REMOTE_SIZE_BYTES" -gt 100 ]; then
    echo -e "${GREEN}✓ Dump komprimiert (${REMOTE_SIZE})${NC}"
    REMOTE_DUMP_PATH="${REMOTE_DUMP_PATH_GZ}"
else
    echo -e "${RED}✗ Komprimierung fehlgeschlagen!${NC}"
    exit 1
fi
echo ""

# ============================================================================
# Dump herunterladen
# ============================================================================
echo -e "${BLUE}📥 Lade Dump herunter...${NC}"

scp -P "${SSH_PORT}" "${SSH_CONNECTION}:${REMOTE_DUMP_PATH}" "${LOCAL_DUMP_DIR}/${DUMP_FILENAME_GZ}" >/dev/null 2>&1

if [ -f "${LOCAL_DUMP_DIR}/${DUMP_FILENAME_GZ}" ]; then
    LOCAL_SIZE=$(ls -lh "${LOCAL_DUMP_DIR}/${DUMP_FILENAME_GZ}" | awk '{print $5}')
    echo -e "${GREEN}✓ Dump heruntergeladen (${LOCAL_SIZE})${NC}"

    # Entpacke den Dump
    echo -e "${BLUE}📦 Entpacke Dump...${NC}"
    gunzip "${LOCAL_DUMP_DIR}/${DUMP_FILENAME_GZ}"

    if [ -f "${LOCAL_DUMP_DIR}/${DUMP_FILENAME}" ]; then
        UNPACKED_SIZE=$(ls -lh "${LOCAL_DUMP_DIR}/${DUMP_FILENAME}" | awk '{print $5}')
        echo -e "${GREEN}✓ Dump entpackt (${UNPACKED_SIZE})${NC}"
    else
        echo -e "${RED}✗ Fehler beim Entpacken!${NC}"
        exit 1
    fi
else
    echo -e "${RED}✗ Download fehlgeschlagen!${NC}"
    exit 1
fi
echo ""

# ============================================================================
# Temporären Dump auf dem Server löschen
# ============================================================================
echo -e "${BLUE}🗑️  Räume Server auf...${NC}"
ssh -p "${SSH_PORT}" "${SSH_CONNECTION}" "rm -f ${REMOTE_DUMP_PATH}"
echo -e "${GREEN}✓ Temporäre Datei gelöscht${NC}"
echo ""

# ============================================================================
# Dump in DDEV importieren
# ============================================================================
echo -e "${BLUE}📥 Importiere Dump in DDEV...${NC}"
echo -e "${YELLOW}   Dies kann einige Minuten dauern...${NC}"

# Backup-Warnung
echo -e "${YELLOW}⚠  WARNUNG: Die lokale Datenbank wird überschrieben!${NC}"
read -p "Möchtest du fortfahren? (j/n): " confirm

if [[ ! $confirm =~ ^[jJyY]$ ]]; then
    echo -e "${RED}Import abgebrochen.${NC}"
    exit 0
fi

# Import mit DDEV
ddev import-db --file="${LOCAL_DUMP_DIR}/${DUMP_FILENAME}"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Datenbank erfolgreich importiert!${NC}"
else
    echo -e "${RED}✗ Import fehlgeschlagen!${NC}"
    echo -e "${YELLOW}Dump-Datei wurde gespeichert: ${LOCAL_DUMP_DIR}/${DUMP_FILENAME}${NC}"
    echo -e "${YELLOW}Du kannst den Import manuell versuchen mit:${NC}"
    echo -e "${CYAN}  ddev import-db --file=${LOCAL_DUMP_DIR}/${DUMP_FILENAME}${NC}"
    exit 1
fi
echo ""

# ============================================================================
# Zusammenfassung
# ============================================================================
echo -e "${CYAN}╔════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║   SYNC ERFOLGREICH ABGESCHLOSSEN       ║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✨ Datenbank wurde erfolgreich synchronisiert!${NC}"
echo -e "${BLUE}📂 Dump gespeichert: ${LOCAL_DUMP_DIR}/${DUMP_FILENAME}${NC}"
echo ""
echo -e "${YELLOW}💡 Tipp:${NC}"
echo -e "   • Lokale URL: https://mygalaxis.ddev.site/"
echo -e "   • Alte Dumps löschen: rm ${LOCAL_DUMP_DIR}/mygalaxis_dump_*.sql"
echo ""

# Optional: Alte Dumps aufräumen (älter als 7 Tage)
OLD_DUMPS=$(find "${LOCAL_DUMP_DIR}" -name "mygalaxis_dump_*.sql" -mtime +7 2>/dev/null | wc -l | tr -d ' ')
if [ "$OLD_DUMPS" -gt 0 ]; then
    echo -e "${BLUE}🗑️  ${OLD_DUMPS} alte Dump(s) gefunden (älter als 7 Tage)${NC}"
    read -p "Möchtest du diese löschen? (j/n): " cleanup
    if [[ $cleanup =~ ^[jJyY]$ ]]; then
        find "${LOCAL_DUMP_DIR}" -name "mygalaxis_dump_*.sql" -mtime +7 -delete
        echo -e "${GREEN}✓ Alte Dumps gelöscht${NC}"
    fi
fi

echo ""
echo -e "${GREEN}👍 Fertig!${NC}"
