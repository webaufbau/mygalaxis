# Field Display Rules - Setup & Verwendung

## ✅ Was wurde implementiert?

Ein **Admin-verwaltbares System** für bedingte Feldanzeigen, das an **mehreren Stellen** verwendet wird:

1. ✅ **Email-Templates** - Automatische Verwendung über `[show_all]`
2. ✅ **Firmen-Ansichten** - In `offer_form_fields_firm.php`
3. ✅ **Zukünftig**: PDFs, Export, etc.

---

## 📋 Setup-Schritte

### 1. Datenbank-Migration ausführen

```bash
# Starte die Datenbank (falls nicht läuft)
ddev start

# Führe Migration aus
php spark migrate
```

Dies erstellt die Tabelle `field_display_rules`.

---

### 2. Admin-Zugang

Gehe zu: **`/admin/field-display-rules`**

Hier kannst du:
- ✅ Neue Field Display Rules erstellen
- ✅ Bestehende Rules bearbeiten
- ✅ Rules aktivieren/deaktivieren
- ✅ Rules löschen

---

## 🎯 Wie funktioniert es?

### **System-Ablauf:**

1. **Admin erstellt Rule** über `/admin/field-display-rules/create`
2. **FieldRenderer lädt Rules** aus Datenbank (mit Fallback auf Config)
3. **Rules werden angewendet** in:
   - Email-Templates (über `[show_all]`)
   - Firmen-Ansichten (über `offer_form_fields_firm.php`)
   - Zukünftige Module

---

## 📝 Field Display Rule erstellen

### **Beispiel: Bodenplatten Vorplatz**

#### **Basis-Einstellungen:**
- **Rule-Key**: `bodenplatten_vorplatz_gruppe`
- **Offer-Type**: `gartenbau`
- **Label**: `Bodenplatten: Vorplatz / Garage`

#### **Versteckte Felder** (komma-separiert):
```
bodenplatten_vorplatz, bodenplatten_vorplatz_flaeche, bodenplatten_vorplatz_flaeche_ja
```

#### **Conditions JSON:**
```json
[
  {
    "when": {
      "bodenplatten_vorplatz": "Ja",
      "bodenplatten_vorplatz_flaeche": "Ja"
    },
    "display": "{bodenplatten_vorplatz_flaeche_ja} m²"
  },
  {
    "when": {
      "bodenplatten_vorplatz": "Ja",
      "bodenplatten_vorplatz_flaeche": "Nein"
    },
    "display": "Fläche unbekannt"
  }
]
```

---

## 🔄 Migration von Config zu Datenbank

Die bestehenden Rules in `app/Config/FieldDisplayRules.php` können in die Datenbank migriert werden.

Ein Migrations-Script ist vorhanden: `scripts/migrate_field_rules_to_db.php`

```bash
php spark run:script migrate_field_rules_to_db
```

---

## 📊 Offer-Types

Jede Rule kann einem Offer-Type zugeordnet werden:

| Offer-Type | Beschreibung |
|-----------|--------------|
| `default` | Gilt für alle Branchen |
| `gartenbau` | Nur für Gartenbau-Offerten |
| `umzug` | Nur für Umzug-Offerten |
| `reinigung` | Nur für Reinigungs-Offerten |
| `maler` | Nur für Maler-Offerten |
| `bodenbelag` | Nur für Bodenbelag-Offerten |
| `fensterbau` | Nur für Fensterbau-Offerten |
| `heizung` | Nur für Heizung/Sanitär-Offerten |

---

## 🔧 Verwendung in Code

### **Option A: Automatisch (empfohlen)**

In `offer_form_fields_firm.php` und `EmailTemplateParser` wird der FieldRenderer automatisch verwendet.

Keine Code-Änderungen nötig! ✅

### **Option B: Manuell**

```php
$fieldRenderer = new \App\Services\FieldRenderer();
$fieldRenderer->setData($formFields)
              ->setOfferType('gartenbau')  // Optional: spezifischer Offer-Type
              ->setExcludedFields($excludedFields);

$renderedFields = $fieldRenderer->renderFields('html');
```

---

## 🎨 Beispiel: Vorher vs. Nachher

### **Vorher** (ohne Rule):
```
Vorplatz / Garage: Ja
Fläche bekannt: Ja
Quadratmeter: 25
```

### **Nachher** (mit Rule):
```
Bodenplatten: Vorplatz / Garage: 25 m²
```

Oder wenn Fläche unbekannt:
```
Bodenplatten: Vorplatz / Garage: Fläche unbekannt
```

---

## ⚙️ Technische Details

### **Dateien:**

| Datei | Zweck |
|-------|-------|
| `app/Models/FieldDisplayRuleModel.php` | Datenbank-Model |
| `app/Controllers/Admin/FieldDisplayRules.php` | Admin-Controller |
| `app/Views/admin/field_display_rules/` | Admin-Views |
| `app/Services/FieldRenderer.php` | Zentrale Rendering-Logik |
| `app/Config/FieldDisplayRules.php` | Fallback-Config (optional) |

### **Datenbank-Tabelle:**

```sql
CREATE TABLE field_display_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_key VARCHAR(100),
    offer_type VARCHAR(50) DEFAULT 'default',
    label VARCHAR(255),
    conditions JSON,
    fields_to_hide JSON,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY unique_rule_per_offer_type (rule_key, offer_type)
);
```

---

## 🚀 Fallback-System

Der FieldRenderer hat ein **3-Stufen-Fallback-System**:

1. **Datenbank**: Lädt Rules aus `field_display_rules` Tabelle
2. **Config**: Falls DB leer, lädt aus `app/Config/FieldDisplayRules.php`
3. **Keine Rules**: Felder werden normal angezeigt (wie bisher)

Das bedeutet:
- ✅ Bestehende Installationen funktionieren weiterhin
- ✅ Abwärtskompatibel
- ✅ Graceful Degradation

---

## 🎓 JSON-Format für Conditions

### **Einfache Bedingung:**
```json
[
  {
    "when": {"feld1": "Ja"},
    "display": "Wert vorhanden"
  }
]
```

### **Mehrere Bedingungen (AND-Verknüpfung):**
```json
[
  {
    "when": {
      "feld1": "Ja",
      "feld2": "Ja"
    },
    "display": "{feld3}"
  }
]
```

### **Alternative Bedingungen (OR-Verknüpfung):**
```json
[
  {
    "when": {"feld1": "Ja", "feld2": "Ja"},
    "display": "{feld3} m²"
  },
  {
    "when": {"feld1": "Ja", "feld2": "Nein"},
    "display": "Unbekannt"
  }
]
```

### **Mit mehreren Platzhaltern:**
```json
[
  {
    "when": {"baum_entfernen": "Ja"},
    "display": "{baum_entfernen_baumart} ({baum_entfernen_anzahl} Stück)"
  }
]
```

---

## 🐛 Troubleshooting

### **Problem: Rules werden nicht angezeigt**

**Lösung 1**: Prüfe ob Rule aktiv ist (`is_active = 1`)
**Lösung 2**: Prüfe Offer-Type (muss mit Formular-Type übereinstimmen)
**Lösung 3**: Prüfe JSON-Format in Conditions

### **Problem: Migration schlägt fehl**

**Lösung**: Starte Datenbank mit `ddev start` und versuche erneut

### **Problem: Felder werden doppelt angezeigt**

**Lösung**: Prüfe ob `fields_to_hide` korrekt gesetzt ist

---

## 📚 Weitere Hilfe

- 📄 Siehe `app/Config/FieldDisplayRules.README.md`
- 📝 Beispiel-Rules in `app/Config/FieldDisplayRules.php`
- 🧪 Test-Script: `app/Config/FieldDisplayRules.test.php`

---

## ✅ Checkliste für neue Rule

- [ ] Admin-Login
- [ ] Zu `/admin/field-display-rules` navigieren
- [ ] "Neue Rule erstellen" klicken
- [ ] Rule-Key eingeben (z.B. `meine_gruppe`)
- [ ] Offer-Type wählen
- [ ] Label eingeben
- [ ] Versteckte Felder auflisten (komma-separiert)
- [ ] Conditions JSON eingeben
- [ ] Speichern
- [ ] Testen in Email oder Firmen-Ansicht

---

Viel Erfolg! 🎉
