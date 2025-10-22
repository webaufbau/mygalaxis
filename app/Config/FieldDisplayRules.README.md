# Field Display Rules System

## Übersicht

Das **Field Display Rules System** ermöglicht die zentrale Verwaltung von bedingten Feldanzeigen. Die Regeln werden sowohl in **Email-Templates** als auch in **Firmen-Ansichten** verwendet.

## Vorteile

✅ **Einmal definieren, überall nutzen**: Regeln gelten für Emails UND Firmen-Views
✅ **Keine Code-Duplikation**: Zentrale Logik im `FieldRenderer`
✅ **Intelligente Darstellung**: Bedingte Felder werden automatisch gruppiert
✅ **Einfach erweiterbar**: Neue Regeln in `FieldDisplayRules.php` hinzufügen

## Verwendung

### In Email-Templates

Verwende einfach `[show_all]` im Email-Template. Die conditional groups werden automatisch angewendet:

```html
<h3>Ihre Angaben</h3>
<ul>
[show_all exclude="email,phone,terms"]
</ul>
```

**Ergebnis:**
```
- Bodenplatten: Vorplatz / Garage: 25 m²
- Bodenplatten: Sitzplatz: Fläche unbekannt
- Andere Felder...
```

### In Firmen-Ansichten

Die Datei `app/Views/partials/offer_form_fields_firm.php` verwendet automatisch den `FieldRenderer`.

```php
$fieldRenderer = new \App\Services\FieldRenderer();
$fieldRenderer->setData($formFields)
              ->setExcludedFields($excludedFields);

$renderedFields = $fieldRenderer->renderFields('html');
```

## Struktur einer Conditional Group

In `app/Config/FieldDisplayRules.php`:

```php
'bodenplatten_vorplatz_gruppe' => [
    'type' => 'conditional_group',
    'label' => 'Bodenplatten: Vorplatz / Garage',
    'conditions' => [
        [
            // Wenn beide Bedingungen erfüllt sind
            'when' => [
                'bodenplatten_vorplatz' => 'Ja',
                'bodenplatten_vorplatz_flaeche' => 'Ja'
            ],
            // Zeige diesen Text (mit Platzhaltern)
            'display' => '{bodenplatten_vorplatz_flaeche_ja} m²',
        ],
        [
            // Alternative Bedingung
            'when' => [
                'bodenplatten_vorplatz' => 'Ja',
                'bodenplatten_vorplatz_flaeche' => 'Nein'
            ],
            'display' => 'Fläche unbekannt',
        ],
    ],
    // Diese Felder werden ausgeblendet (nicht einzeln angezeigt)
    'fields_to_hide' => [
        'bodenplatten_vorplatz',
        'bodenplatten_vorplatz_flaeche',
        'bodenplatten_vorplatz_flaeche_ja'
    ],
]
```

## Neue Conditional Group hinzufügen

### Schritt 1: Regel definieren

Füge eine neue Methode in `FieldDisplayRules.php` hinzu:

```php
private function meineNeueRegel(): array
{
    return [
        'type' => 'conditional_group',
        'label' => 'Mein Label',
        'conditions' => [
            [
                'when' => ['feld1' => 'Ja', 'feld2' => 'Ja'],
                'display' => '{feld3} m²',
            ],
            [
                'when' => ['feld1' => 'Ja', 'feld2' => 'Nein'],
                'display' => 'Unbekannt',
            ],
        ],
        'fields_to_hide' => ['feld1', 'feld2', 'feld3'],
    ];
}
```

### Schritt 2: Regel registrieren

Füge die Regel in `getRules()` hinzu:

```php
public function getRules(): array
{
    return [
        // Bestehende Regeln...
        'meine_neue_gruppe' => $this->meineNeueRegel(),
    ];
}
```

### Schritt 3: Felder aus Ausschlussliste entfernen

**Wichtig**: Felder, die über Conditional Groups gehandhabt werden, dürfen NICHT in `FormFieldOptions::$excludedFieldsAlways` sein!

## Beispiel: Vorplatz / Garage

### Formular-Felder:
- `bodenplatten_vorplatz` → Ja/Nein
- `bodenplatten_vorplatz_flaeche` → Ja/Nein
- `bodenplatten_vorplatz_flaeche_ja` → Zahl (z.B. "25")

### Logik:

| bodenplatten_vorplatz | bodenplatten_vorplatz_flaeche | Anzeige |
|----------------------|------------------------------|---------|
| Ja | Ja | "25 m²" |
| Ja | Nein | "Fläche unbekannt" |
| Nein | - | *(nichts)* |

### Ausgabe in Email/Firmen-View:

```
Bodenplatten: Vorplatz / Garage: 25 m²
```

Statt:
```
Vorplatz / Garage: Ja
Fläche bekannt: Ja
Quadratmeter: 25
```

## Technische Details

### Dateien

| Datei | Zweck |
|-------|-------|
| `app/Services/FieldRenderer.php` | Zentrale Rendering-Logik |
| `app/Config/FieldDisplayRules.php` | Regel-Definitionen |
| `app/Services/EmailTemplateParser.php` | Verwendet FieldRenderer für Emails |
| `app/Views/partials/offer_form_fields_firm.php` | Verwendet FieldRenderer für Firmen-View |

### Workflow

1. **Daten laden**: `$fieldRenderer->setData($formFields)`
2. **Ausschlüsse setzen**: `$fieldRenderer->setExcludedFields($excludedFields)`
3. **Rendern**: `$renderedFields = $fieldRenderer->renderFields()`
4. **Ausgabe**: HTML-Tabelle oder Email-Liste

### Features

- ✅ **Automatische Datumsformatierung** (dd/mm/YYYY → dd.mm.YYYY)
- ✅ **JSON-Array-Handling** (z.B. Multiple-Choice-Felder)
- ✅ **File-Upload-Anzeige** (Bilder und Links)
- ✅ **Bilder für erklärende Felder** (z.B. Fensterarten)
- ✅ **"Nein"-Werte werden übersprungen**
- ✅ **Leere Felder werden übersprungen**

## Zukunft: Admin-UI (optional)

Für noch mehr Flexibilität könnte man die Rules auch über die Admin-Oberfläche bearbeitbar machen:

1. Datenbank-Tabelle `field_display_rules` erstellen
2. Admin-Controller für CRUD-Operationen
3. `FieldDisplayRules::getRules()` erweitern um Datenbank-Abfrage
4. Fallback auf PHP-Config wenn keine DB-Rules existieren

## Fragen?

Bei Fragen oder Problemen:
1. Prüfe `app/Config/FieldDisplayRules.php` für bestehende Regeln
2. Prüfe `app/Config/FormFieldOptions.php` dass Felder NICHT ausgeschlossen sind
3. Teste mit `ddev logs` ob Fehler auftreten
