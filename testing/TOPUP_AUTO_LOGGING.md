# Auto-Topup Logging Dokumentation

## Problem
User Drago (#31) musste bei 3 Guthaben-Aufladungen jedes Mal verifizieren, obwohl es nur beim ersten Mal nötig sein sollte.

## Lösung
Detailliertes Logging wurde hinzugefügt, um zu verstehen, warum die automatische Abbuchung nicht funktioniert.

## Was wird geloggt?

### 1. Start der Auto-Topup Versuche
```
[TOPUP AUTO] Versuche automatische Abbuchung für User #31, Betrag: CHF 20
```

### 2. Gespeicherte Zahlungsmethode gefunden (oder nicht)
**Fall A: Keine Zahlungsmethode gefunden**
```
[TOPUP AUTO] Keine gespeicherte Zahlungsmethode für User #31 - Weiterleitung zu Saferpay
```
→ **Grund:** User hat noch keinen Alias gespeichert

**Fall B: Zahlungsmethode gefunden**
```
[TOPUP AUTO] Gefundene Zahlungsmethode für User #31: Alias a5f0586d0056072d2d0dd133b60fd66b, Karte: xxxx xxxx xxxx 6789, Platform: my_renovo24_ch
```
→ **Platform zeigt:** In welchem Log-Ordner du suchen musst!

### 3. Alias-ID Validierung
**Fall A: Alias-ID fehlt**
```
[TOPUP AUTO] Alias-ID fehlt in provider_data für User #31 - Weiterleitung zu Saferpay
```
→ **Grund:** Der provider_data JSON ist korrupt oder unvollständig

**Fall B: Alias-ID vorhanden**
```
[TOPUP AUTO] Starte authorizeWithAlias für User #31, Alias: a5f0586d0056072d2d0dd133b60fd66b, Betrag: 2000 Rappen, Refno: topup_auto_673810ab54321
```

### 4. Saferpay Authorization Response
```
[TOPUP AUTO] authorizeWithAlias Response für User #31: {"Transaction":{"Id":"...",...},"PaymentMeans":{...}}
```
→ **Enthält:** Die komplette Saferpay Antwort mit allen Details

### 5. Authorization Status Check
**Fall A: Authorization fehlgeschlagen**
```
[TOPUP AUTO] Authorization fehlgeschlagen für User #31, Status: DECLINED - Weiterleitung zu Saferpay. Full Response: {...}
```
→ **Mögliche Status:** DECLINED, FAILED, CANCELED, etc.
→ **Grund:** Karte abgelaufen, 3D Secure fehlgeschlagen, Alias ungültig, etc.

**Fall B: Authorization erfolgreich**
```
[TOPUP AUTO] Starte Capture für User #31, Transaction ID: A7pbSn4vr6v6tKAhAQSS6jYYd4Eb
```

### 6. Capture Response
```
[TOPUP AUTO] Capture Response für User #31: {"Status":"CAPTURED",...}
```

### 7. Capture Status Check
**Fall A: Capture fehlgeschlagen**
```
[TOPUP AUTO] Capture fehlgeschlagen für User #31, Transaction #A7pbSn4vr6v6tKAhAQSS6jYYd4Eb, Status: PENDING
```
→ **Grund:** Transaktion kann nicht captured werden

**Fall B: Capture erfolgreich**
```
[TOPUP AUTO] ✓ Erfolgreich abgeschlossen für User #31, Betrag: CHF 20, Alias: a5f0586d0056072d2d0dd133b60fd66b
```

### 8. Exceptions
```
[TOPUP AUTO] ✗ Exception für User #31: cURL error 28: Operation timed out
Stacktrace: ...
```
→ **Grund:** Netzwerkfehler, Saferpay nicht erreichbar, etc.

## Wo finde ich die Logs?

Die Logs werden in das **platform-spezifische** Log-Verzeichnis geschrieben:

### Lokal (Development)
```
/Users/vince/Sites/mygalaxis/writable/logs/log-2025-10-22.log
```

### Server (Production)
Je nach Platform (wird im Log angezeigt):

**Offertenschweiz:**
```
/home/famajynu/public_html/my_offertenschweiz_ch/writable/logs/log-2025-10-22.log
```

**Renovo24:**
```
/home/famajynu/public_html/my_renovo24_ch/writable/logs/log-2025-10-22.log
```

**Offertenheld:**
```
/home/famajynu/public_html/my_offertenheld_ch/writable/logs/log-2025-10-22.log
```

## Wie suche ich nach den Logs?

```bash
# SSH zum Server
ssh famajynu@server

# Suche nach TOPUP AUTO Logs für User #31
grep "\[TOPUP AUTO\].*User #31" /home/famajynu/public_html/my_*/writable/logs/log-$(date +%Y-%m-%d).log

# Oder für ein bestimmtes Datum
grep "\[TOPUP AUTO\].*User #31" /home/famajynu/public_html/my_*/writable/logs/log-2025-10-21.log
```

## Typische Fehlerszenarien

### Szenario 1: Kein Alias gespeichert
**Log:**
```
[TOPUP AUTO] Versuche automatische Abbuchung für User #31, Betrag: CHF 20
[TOPUP AUTO] Keine gespeicherte Zahlungsmethode für User #31 - Weiterleitung zu Saferpay
```
**Lösung:** User muss einmal manuell zahlen, dann wird Alias gespeichert

### Szenario 2: Alias abgelaufen
**Log:**
```
[TOPUP AUTO] Gefundene Zahlungsmethode für User #31: Alias abc123, Karte: xxxx xxxx xxxx 6789, Platform: my_renovo24_ch
[TOPUP AUTO] Starte authorizeWithAlias...
[TOPUP AUTO] authorizeWithAlias Response: {"Transaction":{"Status":"DECLINED"},"Alias":{"Status":"EXPIRED"}}
[TOPUP AUTO] Authorization fehlgeschlagen für User #31, Status: DECLINED - Weiterleitung zu Saferpay
```
**Lösung:** User muss alte Zahlungsmethode löschen und neue hinzufügen

### Szenario 3: 3D Secure erforderlich
**Log:**
```
[TOPUP AUTO] authorizeWithAlias Response: {"Transaction":{"Status":"PENDING"},"Redirect":{"Url":"https://..."}}
[TOPUP AUTO] Authorization fehlgeschlagen für User #31, Status: PENDING - Weiterleitung zu Saferpay
```
**Lösung:** authorizeWithAlias kann keine 3D Secure durchführen → User muss manuell zahlen

### Szenario 4: Karte abgelaufen
**Log:**
```
[TOPUP AUTO] authorizeWithAlias Response: {"Error":{"Message":"CARD_EXPIRED"}}
[TOPUP AUTO] Authorization fehlgeschlagen für User #31, Status: unbekannt - Weiterleitung zu Saferpay
```
**Lösung:** User muss neue Karte hinterlegen

## Platform-Spalte in user_payment_methods

Nach der Migration wird die `platform` Spalte zur `user_payment_methods` Tabelle hinzugefügt:

```sql
ALTER TABLE user_payment_methods
ADD platform VARCHAR(100) NULL
COMMENT 'Platform where payment method was created (my_offertenschweiz_ch, my_offertenheld_ch, my_renovo24_ch)'
AFTER payment_method_code;
```

### Anzeige in der View
Die Platform wird in der Zahlungsmethoden-Übersicht angezeigt:

| Zahlungsmethode | Details | Platform | Aktion |
|-----------------|---------|----------|--------|
| saferpay | VISA xxxx xxxx xxxx 6789 | **Renovo24** | Löschen |
| saferpay | VISA xxxx xxxx xxxx 6789 | **Offertenschweiz** | Löschen |

## Nach dem Deployment

1. **Migration ausführen:**
```bash
cd /home/famajynu/public_html/my_offertenschweiz_ch
php spark migrate
```

2. **User bitten, erneut zu testen:**
   - Guthaben aufladen
   - Bei Problemen: Logs prüfen

3. **Logs analysieren:**
```bash
tail -f /home/famajynu/public_html/my_renovo24_ch/writable/logs/log-$(date +%Y-%m-%d).log | grep "TOPUP AUTO"
```

## Checkliste für Debugging

- [ ] Migration ausgeführt? (`php spark migrate`)
- [ ] User hat gespeicherte Zahlungsmethode? (Check `user_payment_methods` Tabelle)
- [ ] Alias-ID vorhanden? (Check `provider_data` JSON)
- [ ] Platform korrekt gespeichert?
- [ ] Logs enthalten `[TOPUP AUTO]` Einträge?
- [ ] Welcher Status wird von Saferpay zurückgegeben?
- [ ] Gibt es eine Exception?

## Erwartetes Verhalten

### Erste Zahlung (kein Alias)
1. User klickt auf "Guthaben aufladen"
2. Log: `[TOPUP AUTO] Keine gespeicherte Zahlungsmethode`
3. User wird zu Saferpay weitergeleitet
4. User gibt Karte ein + 3D Secure
5. Alias wird gespeichert mit Platform
6. Guthaben wird gutgeschrieben

### Zweite Zahlung (mit Alias)
1. User klickt auf "Guthaben aufladen"
2. Log: `[TOPUP AUTO] Gefundene Zahlungsmethode... Platform: my_renovo24_ch`
3. Log: `[TOPUP AUTO] Starte authorizeWithAlias...`
4. Log: `[TOPUP AUTO] ✓ Erfolgreich abgeschlossen`
5. **KEINE Weiterleitung zu Saferpay**
6. Guthaben wird sofort gutgeschrieben
7. User bleibt auf Finance-Seite

## Warum hat es bei User #31 nicht funktioniert?

**Mögliche Gründe (werden durch Logs geklärt):**

1. ❌ authorizeWithAlias() funktioniert nicht (Saferpay API Problem)
2. ❌ Alias wurde nicht korrekt gespeichert
3. ❌ 3D Secure ist bei jeder Transaktion erforderlich
4. ❌ Karte/Alias ist abgelaufen
5. ❌ Exception beim Authorization/Capture

**Nach dem Logging wissen wir genau, was passiert ist!**
