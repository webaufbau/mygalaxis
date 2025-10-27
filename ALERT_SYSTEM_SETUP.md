# 🚨 Real-Time Error Alert System

Automatische Benachrichtigungen bei kritischen Fehlern auf deinen CI4-Seiten.

## Features

✅ **Email-Alerts** - Sofort Bescheid wissen bei Errors
✅ **Slack/Webhook-Integration** - Team-Benachrichtigungen
✅ **SMS-Alerts** (optional) - Für kritische Errors (kostet Geld!)
✅ **Severity Levels** - Unterschiedliche Kanäle je nach Fehler-Schwere
✅ **Rate Limiting** - Verhindert Spam bei wiederkehrenden Errors
✅ **Multi-Server Support** - Nutze auf allen deinen CI4-Seiten

---

## Schnellstart

### 1. Config aktivieren

```bash
nano app/Config/AlertWebhooks.php
```

**Minimal-Setup (nur Email):**

```php
public bool $enabled = true;
public string $serverName = 'MyGalaxis Production'; // Oder welche Seite

public bool $emailEnabled = true;
public array $emailRecipients = [
    'deine-email@example.com',
];
public string $emailFrom = 'alerts@yourdomain.com';
```

Das war's! Jetzt bekommst du Emails bei allen kritischen Errors.

---

### 2. Slack/Webhook hinzufügen (optional)

**Slack Webhook erstellen:**
1. Gehe zu https://api.slack.com/messaging/webhooks
2. Create New App → Incoming Webhook
3. Kopiere die Webhook-URL

**In Config eintragen:**

```php
public array $webhooks = [
    [
        'enabled' => true,
        'type' => 'slack',
        'url' => 'https://hooks.slack.com/services/DEINE/WEBHOOK/URL',
    ],
];
```

---

### 3. SMS für kritische Errors (optional)

⚠️ **Kostet Geld pro SMS!** Nur für wirklich kritische Errors.

```php
public bool $smsEnabled = true;
public string $smsProvider = 'twilio'; // oder 'infobip'
public array $smsRecipients = [
    '+41791234567', // Deine Handynummer
];
```

Deine Twilio/Infobip-Config wird automatisch verwendet.

---

## Severity Levels

Das System unterscheidet zwischen 3 Error-Stufen:

### 🔥 CRITICAL (SMS + Email + Slack)
- Database-Errors
- Payment-Errors (Stripe, PayPal)
- Fatal Errors
- Errors in kritischen Dateien (`/Database/`, `/Payment/`, `/Order/`)

### ⚠️ HIGH (Email + Slack)
- Alle 500 Server Errors
- Uncaught Exceptions

### 📝 MEDIUM (nur Slack)
- Warnings
- Deprecation Notices

**Anpassen:**

```php
public array $severityChannels = [
    'critical' => ['sms', 'email', 'slack'],
    'high' => ['email', 'slack'],
    'medium' => ['slack'], // oder [] um zu deaktivieren
];
```

---

## Rate Limiting

Verhindert Spam bei wiederkehrenden Errors:

- **Max 5 Alerts** pro Error-Typ pro 5 Minuten
- Gleicher Error = gleiche Datei + Zeile + Exception-Typ
- Nach 5 Minuten werden wieder Alerts gesendet

Beispiel: Wenn 100x derselbe Database-Error auftritt, bekommst du nur 5 SMS (spart Geld!).

---

## Testing

### Test-Script ausführen:

```bash
php test-alert-system.php
```

Optionen:
1. Webhook-Connectivity testen
2. Trigger 500 Error
3. Trigger Database Error
4. Rate Limiting testen

### Manual Test:

Erstelle einen Test-Endpoint:

```php
// app/Controllers/Test.php
public function triggerError()
{
    throw new \RuntimeException('Test Error Alert!');
}
```

Rufe auf: `https://deine-seite.com/test/triggerError`

Du solltest sofort eine Alert bekommen!

---

## Multi-Server Setup

Kopiere diese 3 Dateien auf alle deine CI4-Seiten:

```
app/Libraries/AlertExceptionHandler.php
app/Config/AlertWebhooks.php
app/Config/Exceptions.php (nur die Änderung in handler())
```

**Wichtig:** Ändere `$serverName` in jeder Config damit du weisst welcher Server den Error hat!

```php
// Server 1
public string $serverName = 'MyGalaxis Production';

// Server 2
public string $serverName = 'Offertenschweiz Production';

// Server 3
public string $serverName = 'AnotherSite Staging';
```

---

## Deployment mit deinem fetch-logs System

### Option A: Manuelle Kopie

```bash
# Von MyGalaxis zu allen anderen Seiten kopieren
for server in server1 server2 server3; do
    rsync -av app/Libraries/AlertExceptionHandler.php $server:/pfad/app/Libraries/
    rsync -av app/Config/AlertWebhooks.php $server:/pfad/app/Config/
done
```

### Option B: Deploy-Script erweitern

Füge zu deinem bestehenden Deploy-Script hinzu:

```bash
#!/bin/bash
# deploy-alert-system.sh

SERVERS=(
    "user@server1:/var/www/site1"
    "user@server2:/var/www/site2"
    "user@server3:/var/www/site3"
)

for server in "${SERVERS[@]}"; do
    echo "Deploying to $server..."
    rsync -av app/Libraries/AlertExceptionHandler.php "$server/app/Libraries/"
    rsync -av app/Config/AlertWebhooks.php "$server/app/Config/"
done
```

---

## Troubleshooting

### ❌ Keine Emails kommen an

1. Check CI4 Email Config: `app/Config/Email.php`
2. Test Email-Versand:
   ```php
   $email = \Config\Services::email();
   $email->setTo('test@example.com');
   $email->setFrom('from@example.com');
   $email->setSubject('Test');
   $email->setMessage('Test');
   $email->send();
   echo $email->printDebugger();
   ```

### ❌ Slack Webhook funktioniert nicht

1. Check Webhook URL (muss mit `https://hooks.slack.com/` starten)
2. Test mit curl:
   ```bash
   curl -X POST YOUR_WEBHOOK_URL \
     -H 'Content-Type: application/json' \
     -d '{"text":"Test from curl"}'
   ```

### ❌ SMS werden nicht gesendet

1. Check ob SMS nur bei CRITICAL errors gesendet werden (absichtlich!)
2. Trigger einen Database-Error zum Testen
3. Check Twilio/Infobip Logs

### ❌ Zu viele Alerts (Spam)

Rate Limiting sollte das verhindern. Falls nicht:

1. Check `writable/cache/alert_rate_limit.json`
2. Erhöhe `MAX_ALERTS_PER_WINDOW` in `AlertExceptionHandler.php:22`
3. Oder erhöhe `RATE_LIMIT_WINDOW` (aktuell 5 Minuten)

---

## Log-Dateien

Alle Alert-Aktivitäten werden geloggt:

```bash
tail -f writable/logs/log-*.log | grep -i "alert\|sms\|webhook"
```

---

## Kosten

**Email:** Kostenlos (nutzt deinen bestehenden SMTP)
**Slack/Webhooks:** Kostenlos
**SMS:**
- Twilio: ~CHF 0.08 pro SMS
- Infobip: ~CHF 0.06 pro SMS

Bei 5 kritischen Errors pro Tag = ~CHF 1/Monat (wenn SMS aktiviert).

---

## Security

- Webhook-URLs und API-Keys niemals in Git committen
- Nutze `.env` für sensitive Daten (CI4 unterstützt das)
- Rate Limiting verhindert Alert-Bombing durch Attacker

---

## Next Steps

1. ✅ System aktivieren und testen
2. ✅ Auf allen Produktions-Servern deployen
3. ✅ ServerName für jeden Server anpassen
4. ✅ SMS-Budget im Auge behalten
5. ✅ Nach 1 Woche: Severity Levels anpassen falls nötig

**Bei Fragen oder Problemen:** Check die Logs oder teste mit `test-alert-system.php`

---

## Example Alert Messages

### Slack/Email Alert:
```
🔥 Critical Error on MyGalaxis Production

Error Type: PDOException
Message: SQLSTATE[HY000]: General error: 2006 MySQL server has gone away

Location: /var/www/app/Models/UserModel.php:42
Request: POST /api/purchase

Time: 2025-10-27 14:32:15
Environment: production
```

### SMS Alert (gekürzt):
```
🔥 [CRITICAL] Error on MyGalaxis Production

SQLSTATE[HY000]: General error: 2006 MySQL server has gone away

File: UserModel.php:42
Time: 2025-10-27 14:32:15
```

---

**Happy Error Hunting! 🔍**
