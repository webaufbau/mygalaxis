# 🚀 Production Deployment - Alert System

## ✅ Wie es funktioniert:

**Event-basiert = KEIN Cronjob nötig!**

Das Alert-System ist direkt in den **Exception Handler** integriert:
- Tritt ein Error auf → CI4 fängt Exception
- `app/Config/Exceptions.php` ruft `AlertExceptionHandler::handleException()`
- Alerts werden **sofort** gesendet (0-2 Sekunden)

**Unterschied zu fetch-logs.sh:**
- ❌ fetch-logs.sh = Pull (Cronjob holt alle 5 Min Logs)
- ✅ Alert System = Push (Error meldet sich sofort)

---

## 📦 Deployment Checklist:

### 1️⃣ Files hochladen

Lade diese **3 Dateien** hoch:

```bash
# Via rsync
rsync -av app/Libraries/AlertExceptionHandler.php user@production:/var/www/app/Libraries/
rsync -av app/Config/AlertWebhooks.php user@production:/var/www/app/Config/
rsync -av app/Config/Exceptions.php user@production:/var/www/app/Config/

# Oder via SFTP/FTP
app/Libraries/AlertExceptionHandler.php
app/Config/AlertWebhooks.php
app/Config/Exceptions.php
```

### 2️⃣ Config anpassen

Editiere auf dem Production Server:

```bash
nano /var/www/app/Config/AlertWebhooks.php
```

**Minimal-Setup (nur Email):**

```php
<?php

public bool $enabled = true;
public string $serverName = 'MyGalaxis Production'; // ⚠️ WICHTIG: "Production" statt "Development"!

// Email
public bool $emailEnabled = true;
public array $emailRecipients = [
    'logs@webaufbau.com',
    // 'backup@yourdomain.com',
];
public string $emailFrom = ''; // Nutzt automatisch .env email.fromEmail

// SMS (optional aktivieren)
public bool $smsEnabled = false; // true für SMS-Alerts
public string $smsProvider = 'twilio'; // oder 'infobip'
public array $smsRecipients = [
    // '+41791234567',
];
```

### 3️⃣ Permissions checken

```bash
# Cache-Verzeichnis muss beschreibbar sein
chmod -R 775 writable/cache
chown -R www-data:www-data writable/cache
```

### 4️⃣ Testen

Erstelle einen temporären Test-Endpoint:

```php
// app/Controllers/AlertTest.php
<?php
namespace App\Controllers;

class AlertTest extends BaseController
{
    public function test()
    {
        throw new \RuntimeException('🧪 Production Test Alert');
    }
}
```

Route hinzufügen:
```php
// app/Config/Routes.php
$routes->get('alert-test-xyz123', 'AlertTest::test'); // Zufällige URL
```

Aufrufen:
```bash
curl https://your-production-site.com/alert-test-xyz123
```

✅ Check Email-Postfach → Alert sollte ankommen!

❌ Nach Test: Test-Controller und Route wieder entfernen!

---

## 🔧 KEIN Cronjob nötig!

Das System läuft **automatisch** bei jedem Request der einen Error produziert.

**Dein bestehendes fetch-logs.sh System kannst du parallel weiter nutzen:**
- fetch-logs.sh → Manuelle Übersicht aller Logs
- Alert System → Sofort-Benachrichtigung bei kritischen Errors

---

## 📱 SMS aktivieren (optional):

Wenn du SMS-Alerts willst:

```php
// In app/Config/AlertWebhooks.php
public bool $smsEnabled = true;
public string $smsProvider = 'twilio'; // oder 'infobip'
public array $smsRecipients = [
    '+41791234567', // Deine Handynummer
];

// Rate Limits (Standard = ok)
public int $maxSmsPerHour = 10;   // ~CHF 0.80/Stunde
public int $maxSmsPerDay = 50;    // ~CHF 4/Tag
```

**SMS werden nur bei CRITICAL Errors gesendet:**
- Database Errors
- Payment Errors
- Fatal System Errors

**Kosten:** ~CHF 0.08 pro SMS (Twilio) / ~CHF 0.06 (Infobip)

---

## 🌐 Multi-Server Setup:

Wenn du mehrere Server hast:

### Server 1 (MyGalaxis Production):
```php
public string $serverName = 'MyGalaxis Production';
```

### Server 2 (Offertenschweiz Production):
```php
public string $serverName = 'Offertenschweiz Production';
```

### Server 3 (AnotherSite Staging):
```php
public string $serverName = 'AnotherSite Staging';
```

**Gleiche Email-Empfänger für alle Server:**
```php
public array $emailRecipients = ['logs@webaufbau.com'];
```

→ Du siehst im Email-Betreff welcher Server den Error hatte!

---

## 📊 Monitoring & Logs:

### Log-Überwachung:

```bash
# Live-Monitoring der Alerts
tail -f /var/www/writable/logs/log-*.log | grep "\[AlertSystem\]"
```

### Wichtige Log-Messages:

✅ **Erfolg:**
```
[AlertSystem] Kritischer Fehler erkannt, sende Alerts
[AlertSystem] Sende Email-Alert an: logs@webaufbau.com
[AlertSystem] Email-Alert erfolgreich gesendet
```

⚠️ **Rate Limiting:**
```
[AlertSystem] Alert-Limit erreicht für Error: [hash]
[AlertSystem] SMS Stunden-Limit erreicht: 10/10
```

❌ **Fehler:**
```
[AlertSystem] Email-Versand fehlgeschlagen: [Details]
[AlertSystem] SMS-Versand fehlgeschlagen: [Details]
```

### Cache-Files (automatisch erstellt):

```bash
writable/cache/alert_rate_limit.json  # Per-Error Rate Limiting
writable/cache/sms_rate_limit.json    # SMS Rate Limiting
```

Diese werden automatisch bereinigt (alte Entries gelöscht).

---

## 🔍 Troubleshooting:

### Problem: Keine Emails kommen an

**1. Check CI4 Email Config:**
```bash
nano /var/www/app/Config/Email.php
# Oder in .env
```

**2. Test Email-Versand manuell:**
```php
$email = \Config\Services::email();
$email->setTo('test@example.com');
$email->setSubject('Test');
$email->setMessage('Test');
if (!$email->send()) {
    echo $email->printDebugger();
}
```

**3. Check Logs:**
```bash
grep "Email-Versand fehlgeschlagen" writable/logs/log-*.log
```

### Problem: Alert System funktioniert nicht

**1. Check ob System aktiviert:**
```bash
grep "enabled = true" app/Config/AlertWebhooks.php
```

**2. Check Logs:**
```bash
tail -100 writable/logs/log-*.log | grep AlertSystem
```

**3. Check Permissions:**
```bash
ls -la writable/cache/
# Sollte beschreibbar sein für www-data
```

---

## 🎯 Quick Deploy Script:

Erstelle ein Deploy-Script:

```bash
#!/bin/bash
# deploy-alert-system.sh

SERVER="user@your-production-server.com"
REMOTE_PATH="/var/www/html"

echo "📦 Deploying Alert System..."

# Upload files
rsync -av app/Libraries/AlertExceptionHandler.php $SERVER:$REMOTE_PATH/app/Libraries/
rsync -av app/Config/AlertWebhooks.php $SERVER:$REMOTE_PATH/app/Config/
rsync -av app/Config/Exceptions.php $SERVER:$REMOTE_PATH/app/Config/

# Set permissions
ssh $SERVER "chmod -R 775 $REMOTE_PATH/writable/cache && chown -R www-data:www-data $REMOTE_PATH/writable/cache"

echo "✅ Deployment complete!"
echo "⚠️  Don't forget to edit AlertWebhooks.php on the server!"
echo "   Change serverName to 'Production'"
```

Ausführen:
```bash
chmod +x deploy-alert-system.sh
./deploy-alert-system.sh
```

---

## 📋 Post-Deployment Checklist:

Nach dem Upload:

- [ ] Files hochgeladen
- [ ] `serverName` auf "Production" geändert
- [ ] Email-Empfänger konfiguriert
- [ ] `enabled = true` gesetzt
- [ ] Permissions gecheckt (writable/cache)
- [ ] Test-Alert ausgelöst
- [ ] Email empfangen
- [ ] Test-Controller wieder entfernt
- [ ] Logs überwacht (ersten Tag)
- [ ] SMS aktiviert (optional)
- [ ] Rate Limits angepasst (falls nötig)

---

## ⚙️ Performance Impact:

**Minimal!**

- Nur bei Errors aktiv (nicht bei normalen Requests)
- Asynchroner Versand (blockiert Response nicht)
- Rate Limiting verhindert Overload
- Cache-Files sind klein (~1-5 KB)

**Geschätzte Last:**
- 0.01-0.05 Sekunden pro Error
- Negligible für Production

---

## 🔐 Security:

✅ Alert-System läuft nur bei Exceptions
✅ Kein öffentlicher Endpoint
✅ Sensitive Daten werden nicht geloggt (siehe `$sensitiveDataInTrace`)
✅ Rate Limiting verhindert DoS durch Alert-Spam
✅ Webhook-URLs in Config (nicht in Git committen!)

**Tipp:** Nutze `.env` für Webhook-URLs auf Production:

```php
// app/Config/AlertWebhooks.php
public array $webhooks = [
    [
        'enabled' => true,
        'type' => 'slack',
        'url' => env('SLACK_WEBHOOK_URL'),
    ],
];
```

---

## 🎉 Das war's!

**Zusammenfassung:**
1. ✅ 3 Files hochladen
2. ✅ Config anpassen (serverName!)
3. ✅ Testen
4. ❌ **KEIN Cronjob nötig**

Das Alert-System läuft ab jetzt **automatisch** und meldet sich **sofort** bei kritischen Errors!

Bei Fragen: Siehe `ALERT_SYSTEM_SETUP.md` für Details.
