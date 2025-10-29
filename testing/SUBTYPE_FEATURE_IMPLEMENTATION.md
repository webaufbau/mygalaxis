# Subtype Feature Implementation

## Übersicht

Das Subtype-Feature ermöglicht es, spezifische E-Mail Templates für Unterkategorien von Branchen zu erstellen.

**Beispiel:**
- Branche: **Umzug** (move)
  - Subtype: `umzug_privat`
  - Subtype: `umzug_firma`

Früher gab es nur ein Template für "Umzug", jetzt können separate Templates für "Umzug Privat" und "Umzug Firma" erstellt werden.

## Implementierte Änderungen

### 1. Datenbankstruktur

**Migration:** `2025-10-23-104630_AddSubtypeToEmailTemplates.php`

```sql
ALTER TABLE email_templates
ADD COLUMN subtype VARCHAR(100) NULL
COMMENT 'Optional subtype for specific variations. NULL = applies to all subtypes';

CREATE INDEX idx_offer_type_subtype ON email_templates(offer_type, subtype);
```

### 2. Zentrale Subtype-Konfiguration

**Neue Datei:** `app/Config/OfferSubtypes.php`

- Definiert Mapping von Subtypes zu Branchen
- Bietet Helper-Methoden:
  - `getSubtypesForType(string $offerType): array`
  - `getTypeForSubtype(string $subtype): ?string`
  - `hasSubtypes(string $offerType): bool`
  - `getSubtypeLabels(): array`

**Aktuell definierte Subtypes:**

| Branche     | Subtypes                                                                                                                |
|-------------|-------------------------------------------------------------------------------------------------------------------------|
| Umzug       | `umzug_privat`, `umzug_firma`                                                                                          |
| Reinigung   | `reinigung_wohnung`, `reinigung_haus`, `reinigung_gewerbe`, `reinigung_andere`, `reinigung_nur_fenster`, `reinigung_fassaden`, `reinigung_hauswartung` |
| Maler       | `maler_wohnung`, `maler_haus`, `maler_gewerbe`, `maler_andere`                                                        |
| Garten      | `garten_neue_gartenanlage`, `garten_garten_umgestalten`, `garten_allgemeine_gartenpflege`, `garten_andere_gartenarbeiten` |
| Einzelgewerke | Keine Subtypes (direktes Mapping)                                                                                     |

### 3. OfferModel Erweiterungen

**Datei:** `app/Models/OfferModel.php`

- **`detectType()`**: Nutzt jetzt zentrale Config statt hardc oded Mapping
- **`detectSubtype()`**: Neue Methode zum Extrahieren des Subtypes aus Form-Feldern

```php
public function detectSubtype(array $fields): ?string
```

### 4. EmailTemplateModel Erweiterungen

**Datei:** `app/Models/EmailTemplateModel.php`

- **`allowedFields`**: Erweitert um `'subtype'`
- **`getTemplateForOffer()`**: Erweitert um `$subtype` Parameter

**Template-Auswahl-Logik (Priorität):**

1. Template mit matching `offer_type` **UND** `subtype`
2. Template mit matching `offer_type` und `subtype = NULL` (gilt für alle)
3. Fallback auf `offer_type = 'default'`

### 5. E-Mail-Versand

**Datei:** `app/Helpers/email_template_helper.php`

```php
// Detect subtype from form fields
$offerModel = new \App\Models\OfferModel();
$subtype = $offerModel->detectSubtype($data);

// Load template with subtype
$template = $templateModel->getTemplateForOffer($offerType, $language, $subtype);
```

### 6. Admin-Interface

**Datei:** `app/Views/admin/email_templates/form.php`

- Neues Dropdown-Feld "Unterkategorie"
- Dynamische Befüllung via JavaScript basierend auf gewählter Branche
- Zeigt nur relevante Subtypes für die gewählte Branche
- Versteckt sich, wenn Branche keine Subtypes hat

**Screenshot:**
```
Branche: [Umzug ▼]
Unterkategorie: [Alle (Standard) ▼]
                 [Umzug Privat]
                 [Umzug Firma]
```

## Verwendung im Admin

### Template erstellen

1. **Admin** → **E-Mail Templates** → **Neues Template**
2. **Branche** wählen (z.B. "Umzug")
3. **Unterkategorie** erscheint automatisch:
   - **"Alle (Standard)"**: Template gilt für alle Umzug-Typen
   - **"Umzug Privat"**: Template nur für private Umzüge
   - **"Umzug Firma"**: Template nur für Firmen-Umzüge
4. Template-Inhalt anpassen
5. Speichern

### Beispiel-Szenarien

**Szenario 1: Generisches Template**
- Branche: Umzug
- Unterkategorie: Alle (Standard)
- → Wird für ALLE Umzug-Anfragen verwendet (privat UND firma)

**Szenario 2: Spezifische Templates**
- Template A: Branche: Umzug, Unterkategorie: Umzug Privat
- Template B: Branche: Umzug, Unterkategorie: Umzug Firma
- → Private Umzüge bekommen Template A
- → Firmen-Umzüge bekommen Template B

**Szenario 3: Mixed**
- Template A: Branche: Umzug, Unterkategorie: Umzug Privat
- Template B: Branche: Umzug, Unterkategorie: Alle (Standard)
- → Private Umzüge bekommen Template A (spezifisch)
- → Firmen-Umzüge bekommen Template B (generisch)

## Logging

Das System loggt welches Template verwendet wird:

```
INFO - Verwende E-Mail Template ID 12 (Template Subtype: umzug_privat)
       für Offer Type: move, Subtype: umzug_privat, Language: de
```

## Neue Subtypes hinzufügen

Um neue Subtypes hinzuzufügen, editieren Sie `app/Config/OfferSubtypes.php`:

```php
public array $subtypeToTypeMapping = [
    // Bestehende...

    // Neue Unterkategorie
    'neue_unterkategorie' => 'branche',
];

public function getSubtypeLabels(): array
{
    return [
        // Bestehende...

        // Neue Label
        'neue_unterkategorie' => 'Neue Unterkategorie',
    ];
}
```

## Deployment

```bash
# 1. Code deployen
git pull origin main

# 2. Migration ausführen
php spark migrate

# 3. Testen
# - Template erstellen mit Subtype
# - Test-Offerte erfassen
# - Prüfen ob korrektes Template verwendet wird
```

## Troubleshooting

### Template wird nicht gefunden

**Problem:** "Kein E-Mail Template gefunden für Offer Type: move, Subtype: umzug_privat"

**Lösung:**
1. Prüfe ob Template existiert für diese Kombination
2. Prüfe ob Template aktiv ist (`is_active = 1`)
3. Falls kein spezifisches Template: Erstelle generisches Template mit "Alle (Standard)"

### Falsches Template wird verwendet

**Prüfe Log:**
```
Verwende E-Mail Template ID X (Template Subtype: Y) für Offer Type: Z, Subtype: W
```

**Priorität beachten:**
1. Spezifisch (offer_type + subtype)
2. Generisch (offer_type + subtype NULL)
3. Default (offer_type = 'default')

### Subtype-Dropdown zeigt nichts

**Ursachen:**
- JavaScript-Fehler (Browser Console prüfen)
- Branche hat keine Subtypes → Dropdown versteckt sich automatisch
- Config nicht geladen → `config('OfferSubtypes')` prüfen
