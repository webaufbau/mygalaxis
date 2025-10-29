# E-Mail Template Felder - Dokumentation

## Übersicht

Seit der Implementierung der separaten Feld-Extraktion stehen in E-Mail-Templates zusätzlich zu den normalen `form_fields` auch bereits extrahierte und aufbereitete Felder zur Verfügung.

Diese Felder sind über die Variable `$fields` verfügbar und bieten direkten Zugriff auf wichtige Daten ohne komplexe Array-Zugriffe.

## Zugriffsmethoden

### 1. PHP-Zugriff (in .php Templates)
```php
<?= esc($fields['city']) ?>
```

### 2. Shortcode-Zugriff (in E-Mail-Templates)
```
{field:city}
```

### 3. Verschachtelte Arrays mit Punkt-Notation
Für verschachtelte Arrays (wie Adressen) kann die Punkt-Notation verwendet werden:

**Shortcode:**
```
{field:einzug_adresse.city}
{field:auszug_adresse_firma.address_line_1}
```

**PHP:**
```php
<?= esc($offer['data']['einzug_adresse']['city']) ?>
```

**Mit Konditional:**
```
[if field:einzug_adresse.city]
    <p>Einzugsort: {field:einzug_adresse.city}</p>
[/if]
```

### 4. Text-Filter (Pipe-Syntax)
Mit der Pipe-Syntax ("|") können Feldwerte formatiert werden. Filter werden direkt hinter dem Feldnamen mit einem "|" angegeben:

#### Verfügbare Filter:

**Datum formatieren:**
```
{field:move_date|date:d.m.Y}
```
Ausgabe: 31.10.2025

**Erster Buchstabe groß:**
```
{field:city|ucfirst}
```
Ausgabe: "zürich" → "Zürich"

**Alles klein schreiben:**
```
{field:nachname|strtolower}
```
Ausgabe: "MÜLLER" → "müller"

**Alles groß schreiben:**
```
{field:city|strtoupper}
```
Ausgabe: "Zürich" → "ZÜRICH"
⚠️ **Hinweis:** Das deutsche "ß" bleibt als "ß" erhalten (wird nicht zu "SS")

**Jeden Wort-Anfang groß:**
```
{field:street|ucwords}
```
Ausgabe: "hauptstrasse 42" → "Hauptstrasse 42"
⚠️ **Hinweis:** Das deutsche "ß" bleibt als "ß" erhalten

**Text ersetzen:**
```
{field:company|replace:GmbH,AG}
```
Ausgabe: "Musterfirma GmbH" → "Musterfirma AG"

#### Mehrere Filter kombinieren:
Filter können **nicht** kombiniert werden. Verwenden Sie nur einen Filter pro Feld.

#### Beispiele aus der Praxis:

**Ort immer groß schreiben:**
```
<p>Einsatzort: {field:city|strtoupper}</p>
```

**Name schön formatiert:**
```
<p>Sehr geehrte/r {field:vorname|ucfirst} {field:nachname|ucwords},</p>
```

**Datum im deutschen Format:**
```
<p>Umzugsdatum: {field:move_date|date:d.m.Y}</p>
```

## Verfügbare Felder

### Grundlegende Felder (alle Offerten)

| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['city']` | Ort der Offerte | "Zürich" |
| `$fields['zip']` | Postleitzahl | "8001" |
| `$fields['country']` | Land | "Switzerland" |

### Normale Adressen (Standard-Offerten)

Für Offerten mit einem `address` Feld (z.B. Reinigung, Garten, etc.):

| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['address_street']` | Strassenname | "Schöne Fluh" |
| `$fields['address_number']` | Hausnummer | "4" |
| `$fields['address_zip']` | PLZ | "4244" |
| `$fields['address_city']` | Ort | "Röschenz" |

### Umzug-Adressen (Privat & Firma)

Für Umzug-Offerten werden die Auszug- und Einzug-Adressen automatisch aufgebrochen:

**Auszug-Adresse:**
| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['auszug_street']` | Strassenname | "Postgasse" |
| `$fields['auszug_number']` | Hausnummer | "8b" |
| `$fields['auszug_zip']` | PLZ | "8001" |
| `$fields['auszug_city']` | Ort | "Zürich" |

**Einzug-Adresse:**
| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['einzug_street']` | Strassenname | "Marktusgasse" |
| `$fields['einzug_number']` | Hausnummer | "44" |
| `$fields['einzug_zip']` | PLZ | "8001" |
| `$fields['einzug_city']` | Ort | "Zürich" |

### Umzug-spezifische Felder (aus `offers_move` Tabelle)

Für `type = 'move'`:

| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['from_city']` | Auszugsort | "Zürich" |
| `$fields['to_city']` | Einzugsort | "Zürich" |
| `$fields['from_object_type']` | Objekttyp Auszug | "Wohnung" |
| `$fields['to_object_type']` | Objekttyp Einzug | "Wohnung" |
| `$fields['from_room_count']` | Zimmer Auszug | 3 |
| `$fields['to_room_count']` | Zimmer Einzug | 1 |
| `$fields['move_date']` | Umzugsdatum | "2025-10-31" |
| `$fields['customer_type']` | Kundentyp | "private" |

### Umzug-Reinigung-Kombi (aus `offers_move_cleaning` Tabelle)

Für `type = 'move_cleaning'`:

| Feld | Beschreibung | Beispiel |
|------|-------------|----------|
| `$fields['from_city']` | Auszugsort | "Basel" |
| `$fields['to_city']` | Einzugsort | "Zürich" |
| `$fields['address_city']` | Reinigungsort | "Basel" |
| `$fields['from_object_type']` | Objekttyp Auszug | "Wohnung" |
| `$fields['to_object_type']` | Objekttyp Einzug | "Haus" |
| `$fields['from_room_count']` | Zimmer Auszug | 3 |
| `$fields['to_room_count']` | Zimmer Einzug | 5 |
| `$fields['cleaning_type']` | Reinigungsart | "Endreinigung" |
| `$fields['move_date']` | Umzugsdatum | "2025-11-15" |
| `$fields['customer_type']` | Kundentyp | "private" |

### Weitere wichtige Felder

Diese Felder werden direkt aus `form_fields` extrahiert:

| Feld | Beschreibung |
|------|-------------|
| `$fields['vorname']` | Vorname |
| `$fields['nachname']` | Nachname |
| `$fields['email']` | E-Mail |
| `$fields['phone']` / `$fields['telefon']` | Telefon |
| `$fields['mobile']` / `$fields['handy']` | Mobile |
| `$fields['company']` / `$fields['firma']` | Firma |
| `$fields['datetime_1']` | Datum/Zeit 1 |
| `$fields['work_start_date']` | Arbeitsbeginn |
| `$fields['erreichbar']` | Erreichbarkeit |
| `$fields['details_hinweise']` | Details/Hinweise |
| `$fields['sonstige_hinweise']` | Sonstige Hinweise |

## Verwendungsbeispiele

### Beispiel 1: Nur Ort anzeigen (statt voller Adresse)

**Vorher (zeigt volle Adresse):**
```php
<?php if (!empty($offer['data']['address'])): ?>
    <p><strong>Adresse:</strong>
        <?= esc($offer['data']['address']['address_line_1']) ?>
        <?= esc($offer['data']['address']['address_line_2']) ?>,
        <?= esc($offer['data']['address']['zip']) ?>
        <?= esc($offer['data']['address']['city']) ?>
    </p>
<?php endif; ?>
```

**Nachher (zeigt nur Ort):**
```php
<?php if (!empty($fields['address_city'])): ?>
    <p><strong>Ort:</strong> <?= esc($fields['address_city']) ?></p>
<?php endif; ?>
```

### Beispiel 2: Umzug - Nur Auszugs- und Einzugsorte anzeigen

```php
<?php if (!empty($fields['from_city']) && !empty($fields['to_city'])): ?>
    <p><strong>Auszugsort:</strong> <?= esc($fields['from_city']) ?></p>
    <p><strong>Einzugsort:</strong> <?= esc($fields['to_city']) ?></p>
<?php endif; ?>
```

### Beispiel 3: Kombinierte Anzeige mit Fallback

```php
<!-- Zeige Ort, mit Fallback auf allgemeines city-Feld -->
<p><strong>Ort:</strong>
    <?= esc($fields['address_city'] ?? $fields['city'] ?? 'Nicht angegeben') ?>
</p>
```

### Beispiel 4: Umzug mit Details

```php
<?php if ($offer['type'] === 'move'): ?>
    <div class="move-details">
        <h3>Umzugsdetails</h3>
        <div class="row">
            <div class="col-md-6">
                <h4>Auszug</h4>
                <p><strong>Ort:</strong> <?= esc($fields['from_city'] ?? '-') ?></p>
                <p><strong>Objekttyp:</strong> <?= esc($fields['from_object_type'] ?? '-') ?></p>
                <p><strong>Zimmer:</strong> <?= esc($fields['from_room_count'] ?? '-') ?></p>
            </div>
            <div class="col-md-6">
                <h4>Einzug</h4>
                <p><strong>Ort:</strong> <?= esc($fields['to_city'] ?? '-') ?></p>
                <p><strong>Objekttyp:</strong> <?= esc($fields['to_object_type'] ?? '-') ?></p>
                <p><strong>Zimmer:</strong> <?= esc($fields['to_room_count'] ?? '-') ?></p>
            </div>
        </div>
        <?php if (!empty($fields['move_date'])): ?>
            <p><strong>Umzugsdatum:</strong>
                <?= date('d.m.Y', strtotime($fields['move_date'])) ?>
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
```

## Migration bestehender Templates

### Schritt 1: Identifiziere Adress-Anzeigen

Suche nach Code wie:
- `$offer['data']['address']`
- `$offer['data']['auszug_adresse']`
- `$offer['data']['einzug_adresse']`

### Schritt 2: Ersetze mit neuen Feldern

Ersetze komplexe Array-Zugriffe durch einfache Feld-Zugriffe:

```php
<!-- Alt -->
<?= esc($offer['data']['auszug_adresse']['city']) ?>

<!-- Neu -->
<?= esc($fields['auszug_city']) ?>
```

### Schritt 3: Teste mit verschiedenen Offerten-Typen

Teste das Template mit:
- Normalen Offerten (Reinigung, Garten, etc.)
- Umzug-Offerten (Privat & Firma)
- Umzug-Reinigung-Kombis

## Hinweise

1. **Fallbacks verwenden**: Da nicht alle Felder in allen Offerten vorhanden sind, immer Fallbacks verwenden:
   ```php
   <?= esc($fields['city'] ?? 'Nicht angegeben') ?>
   ```

2. **Null-Checks**: Prüfe immer, ob ein Feld existiert, bevor du es anzeigst:
   ```php
   <?php if (!empty($fields['from_city'])): ?>
       <p>Auszugsort: <?= esc($fields['from_city']) ?></p>
   <?php endif; ?>
   ```

3. **Originalfelder bleiben verfügbar**: Die originalen `$offer['data']` Felder bleiben weiterhin verfügbar, wenn spezielle Zugriffe nötig sind.

4. **Datenschutz**: Orte sind bereits in der Datenbank gespeichert und werden für Filterung verwendet. Die vollen Adressen (Strasse, Hausnummer) sollten nur nach Kauf angezeigt werden.

## Implementation Details

Die Extraktion erfolgt in:
- **Datei**: `app/Libraries/OfferNotificationSender.php`
- **Methode**: `extractFieldsForTemplate()`
- **Aufruf**: Automatisch bei jedem E-Mail-Versand

Die Felder werden als `$fields` Variable an alle E-Mail-Templates übergeben und sind zusätzlich zu den bestehenden Variablen verfügbar.
