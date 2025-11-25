# Konzept: Projekte vs. Branchen

## Ausgangslage

**Aktuell:** Firmen registrieren sich für Branchen (Elektriker, Maurer, Gärtner, etc.)

**Problem:** Endkunden wissen oft nicht, welche Branche sie brauchen.
- "Ich will einen Pool" → Braucht: Maurer + Elektriker + Gärtner?
- "Ich will einen Balkon" → Braucht: Schreiner + Maurer + Metallbauer?
- "Lampe installieren" → Elektriker (klar), aber nicht jedem Laien offensichtlich

---

## Variante A: Projekte als "Splitter" (Empfehlung)

### Konzept
- Fluentforms enthält **Projekt-Formulare** (Pool, Balkon, Küche, etc.)
- Kunde füllt EIN Formular aus mit Basisdaten + projektspezifischen Fragen
- Webhook erstellt **mehrere Branchen-Aufträge** im System
- Firmen registrieren sich weiterhin **nur für Branchen**

### Ablauf
```
Kunde füllt "Pool bauen" aus
         ↓
    Webhook splittet
         ↓
┌────────┼────────┐
↓        ↓        ↓
Auftrag  Auftrag  Auftrag
Maurer   Elektr.  Gärtner
```

### Vorteile
- **Keine Systemänderung** an mygalaxis nötig
- Firmen-Registrierung bleibt einfach (nur Branchen)
- Flexibel: Projekt-Zuordnung zu Branchen kann in Fluentforms angepasst werden
- Kunde bekommt automatisch alle relevanten Offerten

### Nachteile
- Kunde erhält mehrere separate Kontaktaufnahmen
- Koordination zwischen Gewerken liegt beim Kunden
- Mapping "Projekt → Branchen" muss gepflegt werden

### Umsetzungsaufwand
- **Fluentforms:** Neue Formulare anlegen
- **Webhook-Logik:** Erweitern um Multi-Auftrag-Erstellung
- **mygalaxis:** Minimal (evtl. Feld "aus Projekt: Pool" zur Info)

---

## Variante A - Technische Details

### Wo wird das Mapping definiert?

Es gibt **3 Optionen** wo festgelegt wird, welches Projekt in welche Branchen aufgeteilt wird:

#### Option A1: Mapping in Fluentforms (Hidden Fields)

**Wie:** Jedes Projekt-Formular hat ein Hidden Field mit den Ziel-Kategorien

```
Formular "Pool bauen"
├── Sichtbare Felder: Name, Email, PLZ, Poolgrösse, ...
└── Hidden Field: target_categories = "12,45,78"  (IDs: Maurer, Elektriker, Gärtner)
```

**Webhook empfängt:**
```json
{
  "project_name": "Pool bauen",
  "target_categories": "12,45,78",
  "name": "Max Muster",
  "email": "max@example.com",
  "plz": "8000",
  "details": "Pool 4x8m, beheizt"
}
```

**Vorteile:**
- Mapping ist pro Formular konfigurierbar
- Keine Code-Änderung für neue Projekte
- Kunde (ihr) kann selbst anpassen

**Nachteile:**
- Muss bei jedem neuen Formular manuell gesetzt werden
- Kategorie-IDs können sich ändern → Fehleranfällig

---

#### Option A2: Mapping in Datenbank (Empfohlen)

**Wie:** Einfache Tabelle in mygalaxis definiert das Mapping

```sql
CREATE TABLE project_mappings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_slug VARCHAR(50) UNIQUE,      -- z.B. "pool", "balkon", "kueche"
    project_name VARCHAR(100),            -- z.B. "Pool bauen"
    category_ids JSON,                    -- z.B. [12, 45, 78]
    is_active TINYINT DEFAULT 1,
    created_at DATETIME
);

-- Beispieldaten:
INSERT INTO project_mappings (project_slug, project_name, category_ids) VALUES
('pool', 'Pool bauen', '[12, 45, 78]'),
('balkon', 'Balkon/Terrasse', '[12, 33]'),
('kueche', 'Küche einbauen', '[22, 45, 55]'),
('bad', 'Badezimmer renovieren', '[12, 33, 45]');
```

**Webhook empfängt:**
```json
{
  "project_slug": "pool",
  "name": "Max Muster",
  ...
}
```

**API schaut in DB nach:** `SELECT category_ids FROM project_mappings WHERE project_slug = 'pool'`

**Vorteile:**
- Zentrale Verwaltung
- Admin kann Mapping anpassen ohne Fluentforms zu ändern
- Kategorie-Namen statt IDs möglich
- Später erweiterbar (z.B. Preise pro Projekt)

**Nachteile:**
- Braucht Admin-UI zum Verwalten (oder direkt in DB)
- Kleine DB-Änderung nötig

---

#### Option A3: Mapping fix im Code

**Wie:** PHP-Array im WebhookController

```php
// In WebhookController.php
private $projectMappings = [
    'pool' => [
        'name' => 'Pool bauen',
        'categories' => [12, 45, 78]  // Maurer, Elektriker, Gärtner
    ],
    'balkon' => [
        'name' => 'Balkon/Terrasse',
        'categories' => [12, 33]
    ],
    'kueche' => [
        'name' => 'Küche einbauen',
        'categories' => [22, 45, 55]
    ],
];
```

**Vorteile:**
- Einfachste Umsetzung
- Keine DB-Änderung

**Nachteile:**
- Jede Änderung braucht Deployment
- Nicht vom Kunden selbst anpassbar

---

### Empfehlung: Option A2 (Datenbank)

Mit einfachem Admin-Interface:

```
Admin → Einstellungen → Projekt-Mappings
┌─────────────────────────────────────────────────────────┐
│ Projekt-Mappings                          [+ Neu]      │
├──────────┬─────────────────┬────────────────┬──────────┤
│ Slug     │ Name            │ Branchen       │ Aktionen │
├──────────┼─────────────────┼────────────────┼──────────┤
│ pool     │ Pool bauen      │ Maurer,        │ [✏][🗑] │
│          │                 │ Elektriker,    │          │
│          │                 │ Gärtner        │          │
├──────────┼─────────────────┼────────────────┼──────────┤
│ balkon   │ Balkon/Terrasse │ Maurer,        │ [✏][🗑] │
│          │                 │ Plattenleger   │          │
└──────────┴─────────────────┴────────────────┴──────────┘
```

---

### Webhook-Flow im Detail

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. FLUENTFORMS                                                  │
│    Kunde füllt "Pool bauen" aus                                 │
│    → Sendet Webhook mit project_slug: "pool"                    │
└─────────────────────────┬───────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. WEBHOOK-ENDPOINT (mygalaxis)                                 │
│    POST /api/webhook/project                                    │
│                                                                 │
│    a) Empfängt: { project_slug: "pool", name, email, plz, ... } │
│    b) Schlägt Mapping nach: pool → [12, 45, 78]                 │
│    c) Erstellt 3 Aufträge mit gleichen Basisdaten               │
└─────────────────────────┬───────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. AUFTRÄGE IN DATENBANK                                        │
│                                                                 │
│    offers Tabelle:                                              │
│    ┌────┬────────────┬──────────────┬─────────────────────────┐ │
│    │ ID │ category   │ project_ref  │ description             │ │
│    ├────┼────────────┼──────────────┼─────────────────────────┤ │
│    │ 99 │ 12 (Maurer)│ pool_abc123  │ Pool 4x8m, Basisdaten...│ │
│    │100 │ 45 (Elekt.)│ pool_abc123  │ Pool 4x8m, Basisdaten...│ │
│    │101 │ 78 (Gärtn.)│ pool_abc123  │ Pool 4x8m, Basisdaten...│ │
│    └────┴────────────┴──────────────┴─────────────────────────┘ │
│                                                                 │
│    project_ref verknüpft zusammengehörige Aufträge              │
└─────────────────────────────────────────────────────────────────┘
```

---

### Neues Feld: project_ref

Um zusammengehörige Aufträge zu verknüpfen:

```sql
ALTER TABLE offers ADD COLUMN project_ref VARCHAR(50) NULL;
ALTER TABLE offers ADD COLUMN project_name VARCHAR(100) NULL;

-- Beispiel:
-- Alle Pool-Aufträge von Max haben project_ref = "pool_abc123"
-- So kann man später auswerten: "Wie viele Pool-Projekte hatten wir?"
```

**Optional in Auftrag-Ansicht:**
```
┌─────────────────────────────────────────────┐
│ Auftrag #99 - Maurerarbeiten                │
├─────────────────────────────────────────────┤
│ Teil von Projekt: Pool bauen                │
│ Weitere Aufträge: #100 (Elektr.), #101 (G.) │
└─────────────────────────────────────────────┘
```

---

### Zusammenfassung Variante A

| Komponente       | Änderung                                      |
|------------------|-----------------------------------------------|
| Fluentforms      | Neue Formulare mit `project_slug` Hidden Field|
| Datenbank        | +1 Tabelle `project_mappings`                 |
|                  | +2 Felder in `offers` (project_ref, project_name) |
| WebhookController| Neuer Endpoint `/api/webhook/project`         |
| Admin-UI         | Kleine Seite für Mapping-Verwaltung           |
| Firmen-Seite     | Keine Änderung                                |
| Registrierung    | Keine Änderung                                |

**Geschätzter Aufwand:** 1-2 Tage

---

## Variante B: Projekte als eigene Kategorie

### Konzept
- Projekte existieren als **eigenständige Entität** neben Branchen
- Firmen können sich für **Branchen ODER Projekte** registrieren
- "Generalunternehmer" registriert sich z.B. für "Pool komplett"
- Spezialist registriert sich weiterhin nur für "Elektriker"

### Ablauf
```
Kunde füllt "Pool bauen" aus
         ↓
    System prüft:
         ↓
┌────────────────────────┐
│ Gibt es Firmen für     │
│ Projekt "Pool"?        │
├────────┬───────────────┤
│ JA     │ NEIN          │
↓        ↓               │
Projekt- Splitte in      │
Auftrag  Branchen-       │
         Aufträge        │
└────────────────────────┘
```

### Vorteile
- Generalunternehmer können "Alles aus einer Hand" anbieten
- Kunde hat EINEN Ansprechpartner
- Premium-Produkt möglich (höherer Preis für Projekt-Leads)

### Nachteile
- **Erheblicher Systemumbau** (Datenbank, Filter, Registrierung)
- Firmen müssen verstehen: "Registriere ich mich für Projekt oder Branche?"
- Komplexere Verwaltung

### Umsetzungsaufwand
- **Datenbank:** Neue Tabellen (projects, user_projects)
- **Filter-System:** Erweitern um Projekt-Auswahl
- **Registrierung:** Zweistufig oder Tabs
- **Matching-Logik:** Komplett neu
- **Admin-Bereich:** Projekt-Verwaltung

---

## Variante C: Hybrid (Projekte mit Fallback)

### Konzept
- Projekte existieren im System
- Firmen können sich **zusätzlich** für Projekte registrieren
- Wenn keine Projekt-Firma verfügbar → automatischer Split in Branchen

### Vorteile
- Best of both worlds
- Schrittweise einführbar

### Nachteile
- Höchste Komplexität
- Schwer zu erklären

---

## Empfehlung

### Kurzfristig: Variante A
- Schnell umsetzbar
- Kein Risiko für bestehendes System
- Testet, ob "Projekte" überhaupt nachgefragt werden

### Mittelfristig: Variante B evaluieren
- Wenn Projekt-Anfragen gut laufen
- Wenn Generalunternehmer Interesse zeigen
- Als Premium-Feature positionieren

---

## Offene Fragen für Besprechung

1. **Welche Projekte sollen initial angeboten werden?**
   - Pool, Balkon, Küche, Bad, Wintergarten, ...?

2. **Wer definiert das Mapping Projekt → Branchen?**
   - Fixes Mapping oder pro Anfrage manuell?

3. **Soll der Kunde wissen, dass sein "Pool-Projekt" gesplittet wird?**
   - Oder erwartet er einen Generalunternehmer?

4. **Preismodell bei Variante A:**
   - Zählt ein Pool-Split als 1 Lead oder 3 Leads für den Kunden?
   - Firmen zahlen normal pro Branchen-Auftrag?

5. **Wie mit regionaler Verfügbarkeit umgehen?**
   - Was wenn für Pool in Region X kein Gärtner verfügbar?

---

## Technische Details (nur bei Variante B relevant)

### Datenbankänderungen
```sql
-- Neue Tabelle: Projekte
CREATE TABLE projects (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    icon VARCHAR(50),
    is_active TINYINT DEFAULT 1
);

-- Projekt-Branchen-Zuordnung
CREATE TABLE project_categories (
    project_id INT,
    category_id INT,
    is_required TINYINT DEFAULT 1
);

-- Firmen-Projekt-Registrierung
CREATE TABLE user_projects (
    user_id INT,
    project_id INT,
    created_at DATETIME
);
```

### Betroffene Module
- UserController (Registrierung)
- FilterController (Projekt-Filter)
- OfferController (Projekt-Aufträge)
- Admin: Neue Projekt-Verwaltung
