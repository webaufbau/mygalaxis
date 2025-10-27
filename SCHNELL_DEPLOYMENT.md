# 🚀 Schnell-Deployment - Alert System mit .env

## ✅ Was du brauchst:

**Nur 3 Dateien + .env Settings!**

---

## 📦 Schritt 1: Files auf Server kopieren

Kopiere diese **3 Dateien** auf JEDEN deiner CI4-Server:

```
app/Libraries/AlertExceptionHandler.php
app/Config/AlertWebhooks.php
app/Config/Exceptions.php
```

**Via rsync/scp/FTP - egal wie, Hauptsache die 3 Files sind da.**

---

## ⚙️ Schritt 2: Settings zur .env hinzufügen

Öffne auf **jedem Server** die `.env` Datei:

```bash
nano .env
```

Füge am Ende hinzu (oder kopiere aus `.env.alerts.example`):

```bash
#--------------------------------------------------------------------
# ALERT SYSTEM
#--------------------------------------------------------------------

alert.enabled=true
alert.serverName="MyGalaxis Production"  # ⚠️ Für jeden Server ändern!
alert.emailEnabled=true
alert.emailRecipients="logs@webaufbau.com"

# SMS (optional)
alert.smsEnabled=false
alert.smsProvider="infobip"
# alert.smsRecipients="+41791234567"
alert.maxSmsPerHour=10
alert.maxSmsPerDay=50
```

**WICHTIG:** Ändere `alert.serverName` auf jedem Server:
- Server 1: `"MyGalaxis Production"`
- Server 2: `"Offertenschweiz Production"`
- Server 3: `"TestSite Staging"`

---

## 🧪 Schritt 3: Testen

Erstelle einen kurzen Test-Endpoint:

```php
// app/Controllers/Test.php (temporär!)
public function alert()
{
    throw new \RuntimeException('🧪 Production Alert Test');
}
```

Route hinzufügen:
```php
$routes->get('test-alert-xyz', 'Test::alert');
```

Aufrufen:
```
https://your-site.com/test-alert-xyz
```

✅ Check Email → Alert sollte ankommen mit richtigem Server-Namen!

❌ Danach Test-Endpoint wieder entfernen!

---

## 📧 Email-Check:

Der Email-Betreff zeigt dir welcher Server:

```
[MyGalaxis Production] KRITISCHER FEHLER: RuntimeException
[Offertenschweiz Production] FEHLER: DatabaseException
```

---

## 🎯 Multi-Server Deployment:

### Vorteil mit .env:

Die **3 PHP-Files bleiben überall gleich** - nur die .env ändert sich!

**Das bedeutet:**
✅ Einmal die 3 Files deployen
✅ Auf jedem Server nur die .env anpassen
✅ Updates? → Nur die 3 Files neu kopieren, .env bleibt!

### Quick-Deploy für alle Server:

```bash
#!/bin/bash
# deploy-alerts.sh

SERVERS=(
    "user@server1.com:/var/www/mygalaxis"
    "user@server2.com:/var/www/offertenschweiz"
    "user@server3.com:/var/www/testsite"
)

for server in "${SERVERS[@]}"; do
    echo "📦 Deploying to $server..."
    rsync -av app/Libraries/AlertExceptionHandler.php "$server/app/Libraries/"
    rsync -av app/Config/AlertWebhooks.php "$server/app/Config/"
    rsync -av app/Config/Exceptions.php "$server/app/Config/"
    echo "✅ $server"
done

echo ""
echo "⚠️  WICHTIG: Jetzt auf jedem Server die .env anpassen!"
echo "    1. Alert-Settings hinzufügen"
echo "    2. alert.serverName für jeden Server ändern"
```

---

## 🔧 .env Settings Übersicht:

### Minimal (nur Email):

```env
alert.enabled=true
alert.serverName="MyServer Production"
alert.emailEnabled=true
alert.emailRecipients="logs@webaufbau.com"
```

### Mit mehreren Email-Empfängern:

```env
alert.emailRecipients="logs@webaufbau.com,admin@example.com,dev@example.com"
```

### Mit SMS (optional):

```env
alert.smsEnabled=true
alert.smsProvider="infobip"
alert.smsRecipients="+41791234567"
```

### Mit mehreren SMS-Empfängern:

```env
alert.smsRecipients="+41791234567,+41799999999"
```

---

## ❌ KEIN Cronjob nötig!

Das System läuft **automatisch** bei jedem Error!

---

## 📋 Checklist pro Server:

- [ ] 3 Files kopiert
- [ ] .env geöffnet
- [ ] Alert-Settings hinzugefügt
- [ ] `alert.serverName` angepasst
- [ ] `alert.enabled=true` gesetzt
- [ ] Email-Empfänger eingetragen
- [ ] Gespeichert
- [ ] Test-Error ausgelöst
- [ ] Email empfangen ✅
- [ ] Test-Endpoint entfernt

---

## 🎯 Das war's!

**Zusammenfassung:**
1. 3 Files kopieren
2. .env anpassen (serverName!)
3. Testen
4. Fertig!

Das Alert-System meldet sich ab jetzt **sofort** bei kritischen Errors!

---

## 📖 Mehr Details:

- `PRODUCTION_DEPLOYMENT.md` - Ausführliche Anleitung
- `ALERT_SYSTEM_SETUP.md` - Alle Features & Troubleshooting
- `.env.alerts.example` - Template für .env Settings
