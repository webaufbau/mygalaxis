# CodeIgniter 4 Application Starter

php spark key:generate


## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library


## Sicherheits Konzept
1. Codebasis: 1 Plattform für alle Länder mit Konfigurationsunterschieden

   Sehr sinnvoll, weniger Wartungsaufwand.

   In Git: Code ist identisch, nur die config unterscheidet sich pro Land / Server.

   Deploy über CI/CD: Master-Branch auf alle Server (aktiv und passiv) deployen.

Update der passiven Server

    Pull vom Git-Repository auf passiven Servern ist eine gute Idee, das ist sauber, transparent und nachvollziehbar.

    Alternativ Kopieren vom aktiven Server via SSH o.Ä. ist eher fehleranfällig und unnötig kompliziert.

    Vorteil Git: Versionierung, klare Historie, einfacher Rollback.

2. Daten (Uploads, writable)

   Uploads und writable sind in der Tat nicht in Git, müssen synchronisiert werden.

   Für diese Verzeichnisse empfiehlt sich z.B. rsync (ssh-basiert) in Minuten-Intervallen vom aktiven auf den passiven Server.

   Wichtig ist hier, dass das Synchronisieren atomar erfolgt, d.h. keine halbfertigen Kopien, um inkonsistente Daten zu vermeiden.

3. Datenbank-Synchronisation

Das ist der kritischste Teil:

    Nicht einfach die Datenbank komplett löschen und neu importieren, das ist extrem ressourcenintensiv und führt zu Downtimes.

    Stattdessen sollte man eine Datenbank-Replikation einrichten:

        MySQL/MariaDB bietet z.B. Master-Slave-Replikation an.

        Der aktive Server ist Master, der passive ist Slave.

        Änderungen werden in (nahezu) Echtzeit auf den Slave repliziert.

        Fällt Master aus, kann der Slave promoted werden (z.B. per Hand oder automatisch).

    So hast du quasi eine Live-Synchronisation der DB mit minimaler Latenz.

4. Failover und Aktiv/Passiv

   Das Umschalten des passiven Servers auf aktiv sollte möglichst automatisiert sein, z.B. via:

        Loadbalancer/Reverse Proxy mit Health Checks, der beim Ausfall auf den Passiv-Server schaltet.

        Oder ein Failover-Script, das DNS umschreibt oder die IP-Adressen tauscht.

        Auch "Heartbeat" Software wie keepalived kann das managen.

   Vor dem Umschalten sicherstellen, dass der Passive vollständig aktuell ist.

5. Webhook als Trigger für Updates

   Webhook zum Triggern von Deploys, Rsync und DB Sync (z.B. über Cronjobs oder Script) ist super.

   Z.B. bei Push auf GitHub: Webhook an deine CI/CD Pipeline, die:

        Code auf allen Servern updated,

        Uploads synchronisiert,

        und (wenn nötig) DB-Synchronisation initiiert.




### The secret token to add as a GitHub or GitLab secret, or otherwise as https://www.example.com/?token=secret-token
    # erstelle im root git_deploy_token.php
    define("TOKEN", "");



# Deployment & Synchronisation

## Beispiel: Git Webhook Update PHP (`git_deploy.php.example`)

Im Projekt findest du eine Beispiel-Datei `git_deploy.php.example`. Diese dient als Vorlage für das Update Skript als Webhook welches 
Erstelle einen neuen individuellen Token und passe ihn bei Push im Repos an.
Dies ist ein Webhook Push welcher ein Update macht.

## Beispiel: Synchronisation der Uploads (`sync_uploads.sh.example`)

Im Projekt findest du eine Beispiel-Datei `sync_uploads.sh.example`. Diese dient als Vorlage für ein Skript, mit dem Uploads und andere schreibbare Verzeichnisse vom aktiven auf den passiven Server synchronisiert werden.

### Inhalt von `sync_uploads.sh.example`

```bash
#!/bin/bash
# Beispiel sync_uploads.sh - bitte an deine Umgebung anpassen!

# Quelle (Uploads Ordner)
SRC_DIR="/var/www/mygalaxis/writable/uploads/"

# Ziel Server & Pfad (anpassen!)
TARGET_USER="deployuser"
TARGET_SERVER="passive.mygalaxis.com"
TARGET_DIR="/var/www/mygalaxis/writable/uploads/"

# Synchronisation per rsync
rsync -avz --delete "$SRC_DIR" "$TARGET_USER@$TARGET_SERVER:$TARGET_DIR"
```

Erklärung

    SCRIPT_DIR=$(dirname "$0") sorgt dafür, dass das Skript immer im eigenen Verzeichnis ausgeführt wird, egal von wo aus du es startest.

    git fetch --all lädt alle aktuellen Änderungen vom Git-Remote.

    git reset --hard origin/main setzt den lokalen Stand auf den aktuellen Branch main (passe den Branch bei Bedarf an).

    composer install --no-dev --optimize-autoloader installiert die Produktionsabhängigkeiten effizient.



## Deployment Script: `deploy.sh`

Dieses Skript aktualisiert den Code aus dem Git-Repository und installiert die PHP-Abhängigkeiten via Composer.
Dies ist ein regelmässiges Pull welches direkt alles force pullt, egal ob was angepasst wurde auf dem Server.

### Inhalt von `deploy.sh`

```bash
#!/bin/bash

# Pfad des Scripts ermitteln
SCRIPT_DIR=$(dirname "$0")

# In den Script-Ordner wechseln
cd "$SCRIPT_DIR" || exit 1

git fetch --all
git reset --hard origin/main

# Composer Abhängigkeiten installieren
composer install --no-dev --optimize-autoloader
```

Verwendung

    Lege das Skript deploy.sh im Projektordner ab.

    Automatisches Deployment mit deploy.sh
    
    Nach jedem git pull solltest du auch die Composer-Abhängigkeiten aktualisieren.
    Beispiel deploy.sh

    Mach das Skript ausführbar:

chmod +x deploy.sh

    Führe das Skript manuell aus:

./deploy.sh

    Optional: Automatisiere das Deployment mit einem Cronjob, z.B. alle 5 Minuten:

    */5 * * * * /pfad/zum/projekt/deploy.sh >> /var/log/deploy.log 2>&1

Erklärung

    SCRIPT_DIR=$(dirname "$0") sorgt dafür, dass das Skript immer im eigenen Verzeichnis ausgeführt wird, egal von wo aus du es startest.
    git fetch --all lädt alle aktuellen Änderungen vom Git-Remote.
    git reset --hard origin/main setzt den lokalen Stand auf den aktuellen Branch main (passe den Branch bei Bedarf an).
    composer install --no-dev --optimize-autoloader installiert die Produktionsabhängigkeiten effizient.




# Datenbanksynchronisation per Datenbank Replikation (MySQL / MariaDB)

Voraussetzungen

    MySQL oder MariaDB auf beiden Servern installiert.
    Beide Server können sich per Netzwerk erreichen.

Schritte
a) Master (aktiver Server) konfigurieren

In der MySQL Config /etc/mysql/my.cnf oder /etc/mysql/mariadb.conf.d/50-server.cnf:

```bash
[mysqld]
server-id=1
log_bin=mysql-bin
binlog_do_db=deine_datenbankname
```

    server-id muss auf jedem Server einzigartig sein.
    log_bin aktiviert das Binary-Log (wichtig für Replikation).
    binlog_do_db gibt an, welche Datenbank repliziert wird.

Starte MySQL neu:

sudo systemctl restart mysql

b) MySQL User für Replikation anlegen (auf Master):

```bash
CREATE USER 'repl'@'%' IDENTIFIED BY 'deinPasswort';
GRANT REPLICATION SLAVE ON *.* TO 'repl'@'%';
FLUSH PRIVILEGES;
```

c) Aktuellen Binlog-Status speichern

```bash
FLUSH TABLES WITH READ LOCK;
SHOW MASTER STATUS;
```

Die Ausgabe zeigt:

```bash
File: z.B. mysql-bin.000001

Position: z.B. 154
```

Merke dir diese Werte.

Während der Tabellen-Sperre (Lock) ein Backup der Datenbank machen, z.B.:

```bash
mysqldump -u root -p deine_datenbankname > backup.sql
```

Danach Entsperren:

```bash
UNLOCK TABLES;
```

d) Backup auf den Slave (passiven Server) kopieren und importieren

```bash
scp backup.sql deployuser@passive.example.com:/tmp/
ssh deployuser@passive.example.com "mysql -u root -p deine_datenbankname < /tmp/backup.sql"
```

e) Slave konfigurieren

Auf dem Slave in /etc/mysql/my.cnf:

```bash
[mysqld]
server-id=2
relay_log=relay-log
```

MySQL neu starten:

```bash
sudo systemctl restart mysql
```

f) Slave starten

Im MySQL auf dem Slave:

```bash
    CHANGE MASTER TO
    MASTER_HOST='active.example.com',
    MASTER_USER='repl',
    MASTER_PASSWORD='deinPasswort',
    MASTER_LOG_FILE='mysql-bin.000001',  -- Wert aus SHOW MASTER STATUS
    MASTER_LOG_POS=154;                   -- Wert aus SHOW MASTER STATUS
    
    START SLAVE;
    SHOW SLAVE STATUS\G
```

Wenn Slave_IO_Running und Slave_SQL_Running auf Yes stehen, läuft die Replikation.


# Datenbanksynchronisation per Dump & Restore

Falls Datenbank Replikation (MySQL / MariaDB) nicht verfügbar.
Dieses Skript synchronisiert die Datenbank eines aktiven Servers zu einem passiven Server durch.

- Export (Dump) der Quelldatenbank
- Übertragung des Dumps zum Zielserver
- Import des Dumps in die Zieldatenbank

## Beispiel: Synchronisation der Datenbank (`sync_database.sh.example`)

Im Projekt findest du eine Beispiel-Datei `sync_database.sh.example`. Diese dient als Vorlage für die Synchronisation der Datenbank. Nach dem Kopieren in eine ohne .example Datei müssen die Zugangsdaten angepasst werden.

## Verwendung

1. Passe die Variablen in `sync_database.sh` an deine Umgebung an:

- Quell-Datenbank Zugangsdaten (`SRC_DB_USER`, `SRC_DB_PASS`, ...)
- Zielserver Zugangsdaten (`TARGET_USER`, `TARGET_SERVER`, ...)
- Ziel-Datenbank Zugangsdaten (`TARGET_DB_USER`, `TARGET_DB_PASS`, ...)

2. Stelle sicher, dass auf beiden Servern `mysqldump` und `mysql` verfügbar sind.

3. Mache das Skript ausführbar:

```bash
chmod +x sync_database.sh
```

Teste das Skript manuell:

    ./sync_database.sh

Automatisiere die Ausführung z.B. per Cronjob (alle 15 Minuten):

    */15 * * * * /pfad/zum/script/sync_database.sh >> /var/log/db_sync.log 2>&1

Hinweise

    Die Verbindung zum Zielserver erfolgt per SSH, stelle sicher, dass SSH-Schlüssel eingerichtet sind, um Passwortabfragen zu vermeiden.
    Passwörter können aus Sicherheitsgründen in einer .my.cnf Datei auf beiden Servern hinterlegt werden, um sie nicht im Skript zu speichern.
    Bei großen Datenbanken kann der Dump und Import einige Zeit dauern, die Intervalle entsprechend anpassen.
    Dieses Verfahren ist nicht "Live"-Synchronisation, sondern eine periodische Spiegelung.
    Für Echtzeit-Replikation sind Root-Zugriff und MySQL-Replikation erforderlich, die bei Managed Hosting oft nicht verfügbar sind.


# Datenbanksynchronisation per PT-TABLE-SYNC

   Diese Methode ist bei Hetzner verfügbar. Dabei werden 2 Datenbanken alle Tabellen verglichen dann aktualisiert. 
   
   Bei jedem Aufruf wird das Skript angestossen, ein weiter Anstoss wird erst ausgeführt wenn voriger beendet. 
   /usr/home/offerv/./sync_db.sh --dry.run >> /usr/home/offerv/www_logs/pt-table-sync.log 2>&1 	

## Beispiel: Git Webhook Update (`sync_db.sh.example`)

Im Projekt findest du eine Beispiel-Datei `sync_db.sh.example`. Diese dient als Vorlage für die Synchronisation zweier Datenbank auf gleichen oder unterschiedlichen Servern.
