# Deployment: Firmen-Benachrichtigung

## Änderungen

Die Firmen-Benachrichtigung wurde implementiert und wird jetzt **automatisch** nach dem Versand der Bestätigungsmail an den Offertensteller ausgelöst.

### Geänderte Dateien:
1. `app/Helpers/email_template_helper.php`
   - Firmen werden nach Versand der Einzelofferte benachrichtigt
   - Firmen werden nach Versand der gruppierten Offerten benachrichtigt

2. `app/Controllers/Verification.php`
   - Firmen-Benachrichtigung auch für alte Fallback-Methode (wenn kein Template gefunden wird)
   - Sowohl für einzelne als auch gruppierte E-Mails

3. `app/Config/FormFieldOptions.php`
   - Fluent Forms Felder (fluentformnonce, input_type) hinzugefügt zur excludedFieldsAlways Liste

4. `app/Views/emails/review_reminder.php`
   - Doppelter Footer entfernt

5. `app/Views/emails/price_update.php`
   - Doppelter Footer entfernt

6. `app/Views/emails/daily_offer_suggestions.php`
   - Doppelter Footer entfernt

7. `app/Controllers/Dashboard.php`
   - Delete-Funktion korrigiert (inGroup statt in_array)

## Deployment Schritte

### 1. Code auf Server deployen
```bash
ssh offerv@dedi1000.your-server.de
cd /pfad/zum/projekt
git pull origin main
```

### 2. Fehlende Benachrichtigungen nachsenden (einmalig)
Nach dem Deployment einmal ausführen, um alle bisherigen verifizierten Offerten nachträglich zu benachrichtigen:

```bash
php spark offers:send-missing-company-notifications
```

### 3. Optional: Cronjob als Fallback einrichten
Als zusätzliche Sicherheit kann ein Cronjob eingerichtet werden, der alle 10 Minuten prüft, ob es Offerten gibt, die noch nicht benachrichtigt wurden:

```bash
crontab -e
```

Folgende Zeile hinzufügen:
```
*/10 * * * * cd /pfad/zum/projekt && php spark offers:send-missing-company-notifications >> /pfad/zum/projekt/writable/logs/company-notifications.log 2>&1
```

**Hinweis:** Der Cronjob ist optional, da die Benachrichtigung jetzt automatisch ausgelöst wird. Er dient nur als zusätzlicher Fallback.

### 4. Tägliche Offerten-E-Mails (bereits vorhanden)
Der bestehende Cronjob für tägliche neue Offerten läuft weiter wie gehabt:
```
0 8 * * * cd /pfad/zum/projekt && php spark offers:send-daily-new-offers
```

## Wie es funktioniert

### Ablauf nach Verifizierung:
1. ✅ Kunde verifiziert Telefonnummer/E-Mail
2. ✅ Offerte wird in DB als `verified = 1` markiert
3. ✅ Bestätigungsmail geht an den Offertensteller
4. ✅ `confirmation_sent_at` wird gesetzt
5. ✅ **AUTOMATISCH:** Firmen-Benachrichtigung wird ausgelöst
6. ✅ `companies_notified_at` wird gesetzt
7. ✅ E-Mails gehen an alle passenden Firmen (basierend auf Filtern)

### Logging
Die Firmen-Benachrichtigungen werden geloggt:
```
INFO - Firmen-Benachrichtigung versendet: 3 Firma(n) benachrichtigt für Angebot ID 123
INFO - Firmen-Benachrichtigung für Offer ID 123: 3 E-Mails versendet
```

## Testen

Nach dem Deployment eine Test-Offerte erstellen und verifizieren:
1. Offerte über Frontend erfassen
2. Telefonnummer/E-Mail verifizieren
3. Prüfen ob:
   - Bestätigungsmail ankommt
   - Firmen-E-Mails ankommen (bei Test-Firmen)
   - In Logs "Firmen-Benachrichtigung versendet" erscheint
   - In DB `companies_notified_at` gesetzt ist

## Troubleshooting

### Firmen erhalten keine E-Mails
1. Prüfe Logs: `tail -f writable/logs/log-YYYY-MM-DD.log | grep "Firmen-Benachrichtigung"`
2. Prüfe DB: `SELECT companies_notified_at FROM offers WHERE id = XXX;`
3. Falls `companies_notified_at = NULL`: Command manuell ausführen
4. Prüfe Firmen-Filter: Passen PLZ, Kategorie, Sprache?

### Command schlägt fehl
1. Prüfe Dateiberechtigungen
2. Prüfe PHP-Version und Extensions
3. Prüfe Datenbankverbindung
