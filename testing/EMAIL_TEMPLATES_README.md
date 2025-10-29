# Email Templates System - Dokumentation

## Übersicht

Das Email Templates System ermöglicht es, branchenspezifische Bestätigungsmails für Offerten-Anfragen zu erstellen und zu verwalten. Nicht-Programmierer können über eine Admin-Oberfläche Templates mit Shortcodes erstellen und bearbeiten.

## Installation

### 1. Migration ausführen

```bash
php spark migrate
```

Dies erstellt die `email_templates` Tabelle und fügt drei Default-Templates ein:
- **umzug** (DE) - Beispiel für Umzugsanfragen
- **reinigung** (DE) - Beispiel für Reinigungsanfragen
- **default** (DE) - Fallback für alle anderen Branchen

### 2. Admin-Zugang

Die Email Templates sind erreichbar unter:
```
https://ihre-domain.ch/admin/email-templates
```

**Voraussetzung:** Sie müssen als Admin oder Superadmin eingeloggt sein.

## Features

### ✅ Was das System kann:

- **Branchenspezifische Templates:** Jede Branche (Offer Type) kann eigene Templates haben
- **Mehrsprachig:** Templates für DE, FR, IT, EN
- **Shortcodes:** Dynamische Platzhalter für Daten
- **Bedingungen:** Zeige Inhalte nur wenn bestimmte Bedingungen erfüllt sind
- **Fallback:** Wenn kein Template existiert, wird die alte Methode verwendet
- **Vorschau:** Live-Vorschau mit Testdaten
- **Aktivierung/Deaktivierung:** Templates können ein-/ausgeschaltet werden

## Template-Verwaltung

### Neues Template erstellen

1. Gehe zu `/admin/email-templates`
2. Klicke auf "Neues Template"
3. Fülle die Felder aus:
   - **Offer Type:** z.B. `umzug`, `reinigung`, `maler`, `heizung`, etc.
   - **Sprache:** DE, FR, IT oder EN
   - **Betreff:** E-Mail Betreff (kann Shortcodes enthalten)
   - **Template HTML:** Der Email-Inhalt mit Shortcodes
   - **Status:** Aktiv/Inaktiv
   - **Notizen:** Optionale interne Bemerkungen

### Template bearbeiten

1. Gehe zu `/admin/email-templates`
2. Klicke auf das Edit-Symbol (Stift) bei dem gewünschten Template
3. Bearbeite die Felder
4. Speichern

### Template löschen

1. Gehe zu `/admin/email-templates`
2. Klicke auf das Löschen-Symbol (Mülleimer)
3. Bestätige die Löschung

**Hinweis:** Das Default-Template (DE) kann nicht gelöscht werden.

### Vorschau anzeigen

1. Gehe zu `/admin/email-templates`
2. Klicke auf das Auge-Symbol
3. Du siehst eine Live-Vorschau mit Testdaten

## Shortcodes

### Basis Shortcodes

#### Feldwerte ausgeben
```
{field:vorname}
{field:nachname}
{field:email}
{field:phone}
```

**Beispiel:**
```html
<p>Hallo {field:vorname} {field:nachname},</p>
```

**Output:**
```html
<p>Hallo Max Mustermann,</p>
```

---

#### Site-Informationen
```
{site_name}  - Name der Website (aus Config)
{site_url}   - URL der Website
```

**Beispiel:**
```html
<p>Vielen Dank für Ihre Anfrage bei {site_name}.</p>
```

**Output:**
```html
<p>Vielen Dank für Ihre Anfrage bei Offertenschweiz.</p>
```

---

### Datum Formatierung

```
{field:umzugsdatum|date:d.m.Y}
```

**Verfügbare Formate:**
- `d.m.Y` → 15.12.2025
- `d/m/Y` → 15/12/2025
- `Y-m-d` → 2025-12-15
- `d.m.y` → 15.12.25

**Beispiel:**
```html
<p>Ihr Umzugstermin: {field:umzugsdatum|date:d.m.Y}</p>
```

**Output:**
```html
<p>Ihr Umzugstermin: 15.12.2025</p>
```

---

### Bedingungen (Conditionals)

#### Einfache Bedingung (Feld existiert)
```
[if field:umzugsdatum]
  <p>Ihr Termin: {field:umzugsdatum}</p>
[/if]
```

**Wird nur angezeigt wenn:** Das Feld `umzugsdatum` existiert und nicht leer ist.

---

#### Bedingung mit Vergleich
```
[if field:anzahl_zimmer > 3]
  <p>Sie haben eine große Wohnung!</p>
[/if]
```

**Verfügbare Operatoren:**
- `>` Größer als
- `<` Kleiner als
- `>=` Größer oder gleich
- `<=` Kleiner oder gleich
- `==` Gleich
- `!=` Ungleich

**Beispiele:**
```
[if field:qm >= 100]
  <p>Ihre Wohnung ist mindestens 100m²</p>
[/if]

[if field:object_type == Wohnung]
  <p>Sie ziehen aus einer Wohnung aus.</p>
[/if]

[if field:cleaning_type != nein]
  <p>Sie haben Reinigung gewünscht.</p>
[/if]
```

---

### Feldanzeige

#### Einzelnes Feld mit eigenem Label
```
[show_field name="qm" label="Quadratmeter"]
```

**Output:**
```html
<li><strong>Quadratmeter:</strong> 85</li>
```

---

#### Alle Felder anzeigen (außer ausgeschlossene)
```
[show_all exclude="email,phone,terms"]
```

**Output:**
```html
<li><strong>Anzahl Zimmer:</strong> 4</li>
<li><strong>Quadratmeter:</strong> 85</li>
<li><strong>Umzugsdatum:</strong> 15.12.2025</li>
...
```

**Standard-Ausschluss-Felder** (werden automatisch nicht angezeigt):
```
terms_n_condition, terms_and_conditions, terms,
type, lang, language, csrf_test_name, submit,
form_token, __submission, __fluent_form_embded_post_id,
_wp_http_referer, form_name, uuid, service_url,
uuid_value, verified_method, utm_source, utm_medium,
utm_campaign, utm_term, utm_content, referrer,
skip_kontakt, skip_reinigung_umzug
```

---

## Vollständiges Beispiel-Template

### Betreff:
```
Ihre Umzugsanfrage bei {site_name}
```

### Body Template:
```html
<h2>Vielen Dank für Ihre Anfrage!</h2>

<p>Hallo {field:vorname} {field:nachname},</p>

<div class="highlight">
    <p>Vielen Dank für Ihre Umzugsanfrage über {site_name}.</p>
    <p>Wir leiten Ihre Anfrage an passende Umzugsfirmen in Ihrer Region weiter.</p>
    <p>Sie erhalten in Kürze unverbindliche Offerten direkt per E-Mail oder Telefon.</p>
</div>

<h3>So funktioniert es</h3>
<ul>
    <li>Passende Firmen erhalten Ihre Anfrage</li>
    <li>Sie werden innerhalb von 48 Stunden kontaktiert</li>
    <li>Sie vergleichen bis zu 5 kostenlose Offerten</li>
    <li>Sie wählen das beste Angebot aus</li>
</ul>

<p><strong>Wichtig:</strong> Die Offerten sind komplett kostenlos und unverbindlich.</p>

[if field:umzugsdatum]
<p><strong>Gewünschter Umzugstermin:</strong> {field:umzugsdatum|date:d.m.Y}</p>
[/if]

[if field:anzahl_zimmer > 3]
<p><strong>Hinweis:</strong> Bei Wohnungen mit mehr als 3 Zimmern empfehlen wir eine frühzeitige Planung.</p>
[/if]

<h3>Zusammenfassung Ihrer Angaben</h3>
<ul>
[show_all exclude="terms_n_condition,terms_and_conditions,terms,type,lang,language,csrf_test_name,submit,form_token,__submission,__fluent_form_embded_post_id,_wp_http_referer,form_name,uuid,service_url,uuid_value,verified_method,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer,vorname,nachname,email,phone,skip_kontakt,skip_reinigung_umzug"]
</ul>
```

---

## Wie funktioniert das System?

### Ablauf beim Versand:

1. **Kunde füllt Formular aus** → Daten werden gespeichert
2. **Verifizierung** → SMS/Email Bestätigung
3. **Template-Suche:**
   - System sucht Template für `offer_type` + `language` (z.B. `umzug` + `de`)
   - Falls nicht gefunden: Suche nach `default` + `language`
   - Falls nicht gefunden: **Fallback zur alten Methode**
4. **Template-Parsing:**
   - Shortcodes werden durch echte Daten ersetzt
   - Bedingungen werden ausgewertet
   - Felder werden formatiert
5. **Email versenden** → Kunde erhält personalisierte Bestätigungsmail

### Fallback-Mechanismus

Wenn **kein Template** für die Branche/Sprache existiert:
→ System verwendet die **alte Email-Methode** (View: `emails/offer_notification.php`)

**Vorteil:** Das System funktioniert auch ohne Templates! Neue Branchen können schrittweise hinzugefügt werden.

---

## Häufige Anwendungsfälle

### 1. Branchenspezifische Informationen anzeigen

**Umzug:**
```html
[if field:umzugsdatum]
<p>Ihr Umzug ist geplant für: {field:umzugsdatum|date:d.m.Y}</p>
[/if]

[if field:anzahl_zimmer]
<p>Anzahl Zimmer: {field:anzahl_zimmer}</p>
[/if]
```

**Reinigung:**
```html
[if field:cleaning_type]
<p>Art der Reinigung: {field:cleaning_type}</p>
[/if]

[if field:qm]
<p>Zu reinigende Fläche: {field:qm} m²</p>
[/if]
```

---

### 2. Zusatzleistungen nur bei Bedarf anzeigen

```html
[if field:additional_service != Nein]
<div class="highlight">
    <h4>Ihre gewünschten Zusatzleistungen:</h4>
    <p>{field:additional_service}</p>
</div>
[/if]
```

---

### 3. Personalisierte Ansprache je nach Wohnungstyp

```html
[if field:object_type == Haus]
<p>Der Umzug aus einem Haus erfordert besondere Planung. Unsere Partner sind darauf spezialisiert.</p>
[/if]

[if field:object_type == Wohnung]
<p>Bei Wohnungsumzügen können wir Ihnen besonders schnell passende Offerten vermitteln.</p>
[/if]
```

---

### 4. Warnungen bei bestimmten Bedingungen

```html
[if field:umzugsdatum]
[if field:anzahl_zimmer >= 5]
<div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;">
    <strong>Wichtig:</strong> Bei großen Wohnungen (5+ Zimmer) empfehlen wir eine Vorlaufzeit von mindestens 4 Wochen.
</div>
[/if]
[/if]
```

---

## Technische Details

### Datenbank-Struktur

**Tabelle:** `email_templates`

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| id | INT | Primary Key |
| offer_type | VARCHAR(50) | Branche (umzug, reinigung, etc.) |
| language | VARCHAR(5) | Sprache (de, fr, it, en) |
| subject | VARCHAR(255) | Email-Betreff |
| body_template | TEXT | HTML Template mit Shortcodes |
| is_active | TINYINT | Status (1=aktiv, 0=inaktiv) |
| notes | TEXT | Interne Notizen |
| created_at | DATETIME | Erstellungsdatum |
| updated_at | DATETIME | Letzte Änderung |

---

### Verfügbare Dateien

**Migration:**
- `app/Database/Migrations/20251022025449_CreateEmailTemplatesTable.php`

**Model:**
- `app/Models/EmailTemplateModel.php`

**Service:**
- `app/Services/EmailTemplateParser.php`

**Controller:**
- `app/Controllers/Admin/EmailTemplates.php`

**Views:**
- `app/Views/admin/email_templates/index.php` (Übersicht)
- `app/Views/admin/email_templates/form.php` (Erstellen/Bearbeiten)
- `app/Views/admin/email_templates/preview.php` (Vorschau)

**Helper:**
- `app/Helpers/email_template_helper.php`

**Routes:**
- Definiert in `app/Config/Routes.php` (Admin-Gruppe)

---

## Tipps & Best Practices

### ✅ DO's:

1. **Immer Fallback-Text verwenden:**
   ```html
   [if field:umzugsdatum]
     Ihr Termin: {field:umzugsdatum}
   [/if]
   ```
   Falls das Feld leer ist, wird nichts angezeigt (kein Fehler).

2. **Templates testen:**
   Nutze die Vorschau-Funktion mit Testdaten bevor du aktivierst.

3. **Klare Benennungen:**
   - `umzug` statt `umzug_service`
   - `reinigung` statt `cleaning123`

4. **Notizen nutzen:**
   Schreibe in die Notizen wann/warum du Änderungen gemacht hast.

### ❌ DON'Ts:

1. **Nicht vergessen zu speichern:**
   Änderungen werden erst nach Klick auf "Speichern" übernommen.

2. **Templates nicht ohne Test aktivieren:**
   Immer erst Vorschau ansehen!

3. **Default-Template nicht löschen:**
   Das System braucht mindestens ein Fallback-Template.

---

## Troubleshooting

### Problem: Email wird mit alter Methode versendet

**Lösung:**
1. Prüfe ob ein Template für `offer_type` + `language` existiert
2. Prüfe ob Template auf "Aktiv" gesetzt ist
3. Schaue ins Log (`writable/logs/`) für Fehlermeldungen

---

### Problem: Shortcodes werden nicht ersetzt

**Lösung:**
1. Syntax prüfen: `{field:name}` (nicht `{field: name}` oder `{ field:name }`)
2. Feldname muss exakt übereinstimmen (case-sensitive)
3. Prüfe ob Feld in `$excludedFields` ist

---

### Problem: Bedingungen funktionieren nicht

**Lösung:**
1. Syntax prüfen: `[if field:name]...[/if]` (schließendes `/if` nicht vergessen)
2. Bei Vergleichen: Leerzeichen um Operator: `[if field:zimmer > 3]`
3. String-Vergleiche: `[if field:type == Wohnung]` (ohne Anführungszeichen)

---

## Support

Bei Fragen oder Problemen:
1. Schaue in die Logs: `writable/logs/log-YYYY-MM-DD.log`
2. Prüfe ob Migration ausgeführt wurde: `php spark migrate:status`
3. Kontaktiere den Entwickler

---

## Version History

**v1.0.0 - 2025-10-22**
- Initiale Version
- Support für DE, FR, IT, EN
- Shortcodes: field, site_name, site_url, date
- Bedingungen: if/endif mit Operatoren
- Feldanzeige: show_field, show_all
- Fallback zur alten Methode
