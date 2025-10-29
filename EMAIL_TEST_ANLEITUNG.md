# E-Mail Test Anleitung

Alle E-Mail-Typen können lokal mit MailPit getestet werden.

## 📬 MailPit öffnen
- **URL**: https://mygalaxis.ddev.site:8026
- **Alternative**: http://localhost:8025

---

## 🚀 Schnelltest - Alle E-Mails auf einmal

```bash
ddev exec php test-all-emails.php
```

Dieser Script testet automatisch:
- ✅ **Neue Offerte an Firma** (1 E-Mail)
- ✅ **Kauf an Firma** (5 E-Mails)
- ✅ **Kauf an Kunde** (5 E-Mails)
- ✅ **Rabatt an Firma** (33 E-Mails - 5 Offerten an mehrere Firmen)

**Total: ~44 Test-E-Mails**

---

## 📧 Einzelne E-Mail-Typen testen

### 1. Neue passende Offerte an Firma

**Command:**
```bash
php spark mail:test-new-offer [offer_id] [user_id]
```

**Beispiel:**
```bash
ddev exec php spark mail:test-new-offer 447 11
```

**Erwarteter Betreff:**
```
{domain}.ch - Neue Anfrage für {Type} #{ID} - {PLZ} {Stadt}
```

**Beispiele:**
- `offertenschweiz.ch - Neue Anfrage für Reinigung #453 - 4244 Röschenz`
- `offertenschweiz.ch - Neue Anfrage für Umzug #451 - 4153 Kanton Reinach`
- `offertenschweiz.ch - Neue Anfrage für Garten Arbeiten #447 - 4244 Röschenz`

---

### 2. Kauf-Benachrichtigung (Firma + Kunde)

**Command:**
```bash
php spark offers:send-purchase-notification
```

**Test-Script:**
```bash
ddev exec php test-purchase-email.php
```

**Erwartete Betreffs:**

**An Firma:**
```
{domain}.ch - Vielen Dank für den Kauf der Anfrage {Type} in {PLZ} {Stadt}
```

**An Kunde:**
```
{domain}.ch - Eine Firma interessiert sich für Ihre Anfrage - {Type} in {PLZ} {Stadt}
```

**Beispiele:**
- Firma: `offertenschweiz.ch - Vielen Dank für den Kauf der Anfrage Gartenpflege in 4244 Röschenz`
- Kunde: `offertenschweiz.ch - Eine Firma interessiert sich für Ihre Anfrage - Gartenpflege in 4244 Röschenz`

---

### 3. Rabatt-E-Mail an Firma

**Command:**
```bash
php spark offers:discount-old
```

**Test-Script:**
```bash
ddev exec php test-various-discounts.php
```

**Erwarteter Betreff:**
```
{X}% Rabatt auf Anfrage für {Type} #{ID} {PLZ} {Stadt}
```

**Beispiele:**
- `69% Rabatt auf Anfrage für Reinigung #453 4244 Röschenz`
- `59% Rabatt auf Anfrage für Umzug #451 4153 Kanton Reinach`
- `70% Rabatt auf Anfrage für Garten Arbeiten #16 6900 Lugano`

**Inhalt:**
- ✅ Formatierte Anfragedetails aus `field_display_template`
- ✅ Preisänderungs-Box mit Rabatt
- ❌ KEIN "Vielen Dank für Ihre Anfrage!" Text
- ❌ KEINE Kunden-Bestätigungstexte

---

### 4. Bestätigungs-E-Mail an Kunde

**Hinweis:** Diese E-Mail wird automatisch beim Erstellen einer neuen Offerte gesendet.

**Manuelle Tests:**
1. Erstellen Sie eine neue Offerte über das Frontend
2. Oder verwenden Sie die Test-Offerten aus `test-remaining-emails.php`

**Test-Offerten:**
- Cleaning: Offerte #1 (marvin.zappe@gmx.net)
- Move: Offerte #4 (soeren.kleve@gmx.net)
- Gardening: Offerte #16 (berger-dominik@gmx.net)
- Electrician: Offerte #13 (paul.kartheiser@gmx.net)
- Painting: Offerte #2 (alina.christensen@gmx.ch)

**Erwarteter Betreff:**
```
{site_domain} - Wir bestätigen Ihre {Type}-Anfrage
```

---

### 5. Bewertungs-E-Mail an Kunde

**Command:**
```bash
php spark reviews:send-reminder
```

**Test-Script:**
```bash
ddev exec php test-review-emails.php
```

**Erwarteter Betreff:**
```
{domain}.ch - Bitte bewerten Sie Ihre Erfahrung mit {Offerten-Titel}
```

**Beispiele:**
- `offertenschweiz.ch - Bitte bewerten Sie Ihre Erfahrung mit Reinigung in Röschenz`
- `offertenschweiz.ch - Bitte bewerten Sie Ihre Erfahrung mit Umzug von Basel 1 Zi`
- `offertenschweiz.ch - Bitte bewerten Sie Ihre Erfahrung mit Gartenpflege in Basel`

**Inhalt:**
- ✅ Link zur Bewertungsseite mit `access_hash`
- ✅ Firmenname
- ✅ Offerten-Details
- ✅ Aufforderung zur Bewertung

**Review-Link Format:**
```
{backendUrl}/offer/interested/{access_hash}
```

---

## 📊 Typ-Namen in E-Mail-Betreffs

Die Typ-Namen werden automatisch korrekt formatiert:

| Offer Type | E-Mail Betreff |
|------------|----------------|
| `move` | Umzug |
| `cleaning` | Reinigung |
| `painting` / `painter` | Maler/Gipser |
| `gardening` / `gardener` | Garten Arbeiten |
| `electrician` | Elektriker Arbeiten |
| `plumbing` | Sanitär Arbeiten |
| `heating` | Heizung Arbeiten |
| `tiling` | Platten Arbeiten |
| `flooring` | Boden Arbeiten |
| `furniture_assembly` | Möbelaufbau |
| `move_cleaning` | Umzug + Reinigung |

---

## 🛠️ Verfügbare Test-Scripts

### Alle E-Mails testen
```bash
ddev exec php test-all-emails.php
```

### Verschiedene Rabatt-E-Mails
```bash
ddev exec php test-various-discounts.php
```

### Kauf-Benachrichtigungen
```bash
ddev exec php test-purchase-email.php
```

### Gartenpflege Rabatt
```bash
ddev exec php test-gardening-discount.php
```

### Bewertungs-E-Mails
```bash
ddev exec php test-review-emails.php
```

### Bestätigung & Bewertung Info
```bash
ddev exec php test-remaining-emails.php
```

---

## ✅ Checkliste - Was wurde getestet

Nach dem Ausführen von `test-all-emails.php`:

- [x] Neue Offerte an Firma
- [x] Kauf an Firma (5 verschiedene Types)
- [x] Kauf an Kunde (5 verschiedene Types)
- [x] Rabatt an Firma (5 verschiedene Types)
- [x] Bewertung an Kunde (5+ E-Mails)
- [ ] Bestätigung an Kunde (manuell über Frontend)

---

## 📝 Notizen

### OPcache leeren
Falls Änderungen nicht sichtbar sind:
```bash
ddev exec php -r "opcache_reset(); echo 'OPcache geleert';"
```

### MailPit leeren
Alle Test-E-Mails löschen über das MailPit UI:
- → https://mygalaxis.ddev.site:8026
- Button: "Delete all messages"

### E-Mail-Templates bearbeiten
E-Mail-Templates in der Datenbank:
```sql
SELECT id, offer_type, subtype, language, subject
FROM email_templates
ORDER BY offer_type, language;
```

---

## 🎯 Erwartete Ergebnisse

Nach dem Test sollten in MailPit folgende E-Mails sichtbar sein:

1. **Neue Offerte Firma**: Formatierte Details, kein "Vielen Dank"
2. **Kauf Firma**: Vollständige Kundendaten inkl. Adresse
3. **Kauf Kunde**: Interessenten-Link, Firmendaten
4. **Rabatt Firma**: Formatierte Details aus Template, Preisänderung
5. **Bestätigung Kunde**: "Wir bestätigen Ihre Anfrage"
6. **Bewertung Kunde**: Review-Link mit access_hash

---

**Letzte Aktualisierung:** 29.10.2025
