# Konzept: Projekte vs. Branchen

## Ausgangslage

**Aktuell:** Firmen registrieren sich für Branchen (Elektriker, Maurer, Gärtner, etc.)

**Problem:** Endkunden wissen oft nicht, welche Branche sie brauchen.
- "Ich will einen Pool" → Braucht: Maurer + Elektriker + Gärtner?
- "Ich will einen Balkon" → Braucht: Schreiner + Maurer + Metallbauer?
- "Lampe installieren" → Elektriker (klar), aber nicht jedem Laien offensichtlich

---

## Entscheidung: Variante A mit Admin-UI

**Gewählt:** Variante A (Projekte als "Splitter") mit Option A2 (Datenbank-Mapping + Admin-UI)

**Grund:**
- Auftraggeber muss selbst bestimmen können, welche Branchen zu welchem Projekt gehören
- Keine Code-Änderungen nötig wenn neue Projekte hinzukommen
- Flexibel anpassbar

---

## Übersicht: Branchen vs. Projekte

| | Branche | Projekt |
|---|---------|---------|
| **Beispiel** | Elektriker, Maurer, Sanitär | Pool bauen, Bad sanieren |
| **Formular** | 1 Formular = 1 Offer | 1 Formular = mehrere Offers |
| **Hidden Field** | `type = "electrician"` | `project_slug = "pool"` |
| **Webhook-URL** | `/fluentform/webhook` | `/fluentform/project` |
| **Firma registriert für** | Eine Branche | - (Firmen sehen nur Branchen-Offers) |
| **Mapping** | Fix im Code (CategoryOptions.php) | Admin-UI (Datenbank) |

---

## Bestehende Branchen (in CategoryOptions.php)

| Type-Slug | Name | Status |
|-----------|------|--------|
| `move` | Umzug | Aktiv |
| `move_cleaning` | Umzug + Reinigung | Aktiv |
| `cleaning` | Reinigung | Aktiv |
| `painting` | Maler | Aktiv |
| `gardening` | Gartenpflege | Aktiv |
| `plumbing` | Sanitär | Aktiv |
| `electrician` | Elektriker | Aktiv |
| `flooring` | Boden | Aktiv |
| `heating` | Heizung | Aktiv |
| `tiling` | Platten | Aktiv |

---

## Neue Branchen (Januar 2026)

| Type-Slug | Name | Formular-Felder |
|-----------|------|-----------------|
| `mason` | Baumeister/Maurer | Neubau, Reparatur, Abbrucharbeiten, Betonplatten/Wände, Wände erstellen, Betontreppe, Preisplanung/Leitung, Aushubbarbeiten, Verputzarbeiten, Renovierung, Umbau/Sanierung |
| `carpenter` | Schreiner/Zimmermann | Wand/Decke, Schränke nach Mass, Treppen/Geländer, Kücheneinbau, Dachausbau, Balkon/Terrasse, Fassade, Carport, Dachlatterne/Gaube, Fenster/Türen, Regale nach Mass, Paneele/Täfer, Möbel nach Mass, Sauna, Dachfenster/Gartenhaus |
| `roofer_sheet_metal` | Spengler | Dach/Ablaufrinnen, Blitzschutz, Steildach, Flachdach, Pultdach, Laternen/Gauben Verkleidung, Schneefangsysteme, Flachdach Verdichtung, Dach Seiten-Abschlüsse |
| `locksmith` | Schlosser/Metallbauer | Treppen/Geländer, Balkone/Überdachungen, Garagentor, Allg. Unterstand, Wintergarten, Carport, Windschutz/Blickschutz, Nur Geländer, Türen/Fenster, Fahrrad-Unterstand |
| `kitchen_builder` | Küchenbauer | Alte Küche entfernen, Neue Küche einbauen, Einzelne Geräte umbauen, Küchengeräte ersetzen, Dampfabzug ersetzen, Backofen ersetzen, Müllschrank ersetzen, Steamer ersetzen, Kochfeld/Herd ersetzen, Geschirrspüler ersetzen |
| `roofer` | Dachdecker | Steildach (Ziegel, Holz/Schiefer), Pultdach, Flachdach (Metall/Blech), Dachdämmung innen/aussen |
| `stair_builder` | Treppenbauer | Einbau kompl. Treppe (Holz, Metall/Glas, Stein/Glas), Nur Geländer (kompl. Metall), Innentreppe, Aussentreppe, Beton |
| `scaffolding` | Gerüst | Anzahl Fassaden, Fassaden Breite, Anzahl Stockwerke |
| `windows_doors` | Fenster/Türen | Material (Holz, Kunststoff, Metall/Alu, Kombiniert), Verglasung (Doppel, Dreifach), Fenstertyp/Grösse |
| `architect` | Architekt | Baupläne, Baubewilligung, Handwerkerbeschreibung, Bauberatung, Bauplanung, Bauführung |

---

## Ablauf: Projekt-Formular

```
Kunde füllt "Pool bauen" aus
         ↓
Webhook: POST /fluentform/project
         ↓
CI4 schlägt in DB nach: pool → ["mason", "electrician", "gardening"]
         ↓
CI4 erstellt 3 Offers (gleiche Kontaktdaten, verschiedene types)
         ↓
3 Firmen (je Branche) bekommen die Anfrage
```

---

# Teil 1: Anleitung für Formularersteller (Fluent Forms)

## Gemeinsame Basis-Felder (in JEDEM Formular)

Diese Felder müssen in jedem Formular vorhanden sein:

| Feldname | Typ | Pflicht | Beschreibung |
|----------|-----|---------|--------------|
| `names` | Text | Ja | Vorname |
| `nachname` | Text | Ja | Nachname |
| `email` | Email | Ja | E-Mail-Adresse |
| `phone` | Tel | Ja | Telefonnummer |
| `erreichbar` | Select | Nein | Erreichbarkeit |
| `address[address_line_1]` | Text | Ja | Strasse + Nr. |
| `address[zip]` | Text | Ja | PLZ |
| `address[city]` | Text | Ja | Ort |
| `uuid` | Hidden | Ja | Wird automatisch generiert |
| `additional_service` | Hidden | Ja | "Ja" oder "Nein" |
| `service_url` | Hidden | Nein | URL für nächstes Formular |

## Gemeinsame Felder für Bau-/Handwerker-Formulare

| Feldname | Typ | Optionen |
|----------|-----|----------|
| `object_type` | Select | Wohnung, Gewerbe, Haus, Mehrfamilienhaus, Andere |
| `project_type` | Checkbox | Anbau, Neubau, Renovation/Umbau, Sanierung |
| `rooms_known` | Radio | Ja / Nein |
| `rooms_count` | Number | (nur wenn rooms_known = Ja) |
| `area_known` | Radio | Ja / Nein |
| `area_sqm` | Number | (nur wenn area_known = Ja) |
| `flexibility` | Select | 1-2 Tage, 1-2 Wochen, 1 Monat, Flexibel |

---

## Branchen-Formular erstellen

**Für ein normales Branchen-Formular (1 Formular = 1 Offer):**

### Hidden Fields:
```
type = "electrician"  (oder anderer Branchen-Slug)
```

### Webhook-Konfiguration:
```
URL: https://my.[domain].ch/fluentform/webhook
Methode: POST
Format: x-www-form-urlencoded
```

### Beispiel: Elektriker-Formular
```
Hidden Fields:
- type = "electrician"
- uuid = {unique_id}
- additional_service = "Nein"

Sichtbare Felder:
- Kontaktdaten (names, nachname, email, phone, address)
- object_type (Wohnung, Haus, etc.)
- Arbeiten (Checkboxen gemäss CategoryOptions.php)
```

---

## Projekt-Formular erstellen

**Für ein Projekt-Formular (1 Formular = mehrere Offers):**

### Hidden Fields:
```
project_slug = "pool"  (oder anderer Projekt-Slug aus Admin)
```

**WICHTIG:** Kein `type` Hidden Field! Der Type wird aus dem Mapping ermittelt.

### Webhook-Konfiguration:
```
URL: https://my.[domain].ch/fluentform/project
Methode: POST
Format: x-www-form-urlencoded
```

### Beispiel: Pool-Formular
```
Hidden Fields:
- project_slug = "pool"
- uuid = {unique_id}
- additional_service = "Nein"

Sichtbare Felder:
- Kontaktdaten (names, nachname, email, phone, address)
- pool_size (Grösse in m²)
- pool_heated (Ja/Nein)
- pool_cover (Ja/Nein)
- pool_material (Beton, Folie, etc.)
- additional_info (Freitext)
```

---

## Übersicht: Unterschied Branchen vs. Projekt

| Aspekt | Branchen-Formular | Projekt-Formular |
|--------|-------------------|------------------|
| Hidden Field | `type = "electrician"` | `project_slug = "pool"` |
| Webhook-URL | `/fluentform/webhook` | `/fluentform/project` |
| Ergebnis | 1 Offer mit type="electrician" | Mehrere Offers (je nach Mapping) |
| Mapping | Fix im Code | Admin-UI (Datenbank) |

---

# Teil 2: CI4 Umsetzung

## Datenbank-Änderungen

### Neue Tabelle: project_mappings

```sql
CREATE TABLE project_mappings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_slug VARCHAR(50) UNIQUE NOT NULL,
    project_name VARCHAR(100) NOT NULL,
    category_types JSON NOT NULL,
    description TEXT NULL,
    is_active TINYINT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index
ALTER TABLE project_mappings ADD INDEX idx_slug_active (project_slug, is_active);

-- Beispieldaten
INSERT INTO project_mappings (project_slug, project_name, category_types, description) VALUES
('pool', 'Pool bauen', '["mason", "electrician", "gardening"]', 'Kompletter Poolbau inkl. Technik und Umgebung'),
('balkon', 'Balkon/Terrasse', '["mason", "carpenter", "roofer_sheet_metal"]', 'Balkon- oder Terrassenbau'),
('bad_sanierung', 'Bad sanieren', '["plumbing", "electrician", "tiling"]', 'Komplette Badsanierung'),
('kueche_komplett', 'Küche komplett', '["kitchen_builder", "electrician", "plumbing"]', 'Neue Küche mit Anschlüssen');
```

### Neue Felder in offers Tabelle

```sql
ALTER TABLE offers ADD COLUMN project_ref VARCHAR(50) NULL;
ALTER TABLE offers ADD COLUMN project_slug VARCHAR(50) NULL;
ALTER TABLE offers ADD COLUMN project_name VARCHAR(100) NULL;

-- Index für Gruppierung
ALTER TABLE offers ADD INDEX idx_project_ref (project_ref);
```

---

## Model: ProjectMappingModel.php

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class ProjectMappingModel extends Model
{
    protected $table = 'project_mappings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'project_slug',
        'project_name',
        'category_types',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'project_slug' => 'required|alpha_dash|max_length[50]|is_unique[project_mappings.project_slug,id,{id}]',
        'project_name' => 'required|max_length[100]',
        'category_types' => 'required',
    ];

    /**
     * Alle aktiven Projekte holen
     */
    public function getActiveProjects(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('project_name', 'ASC')
                    ->findAll();
    }

    /**
     * Projekt mit aufgelösten Branchen-Namen holen
     */
    public function getProjectWithCategoryNames(int $id): ?array
    {
        $project = $this->find($id);
        if (!$project) return null;

        $categoryTypes = json_decode($project['category_types'], true) ?? [];
        $categoryConfig = config('CategoryOptions');

        $categoryNames = [];
        foreach ($categoryTypes as $type) {
            $categoryNames[] = $categoryConfig->categoryTypes[$type] ?? $type;
        }

        $project['category_names'] = $categoryNames;
        return $project;
    }
}
```

---

## Controller: FluentForm.php - Neue Methode

```php
/**
 * Projekt-Webhook: Erstellt mehrere Offers basierend auf project_slug
 */
public function project()
{
    log_message('debug', '[Project Webhook] Called');

    $data = $this->request->getPost();
    $data = trim_recursive($data);

    log_message('debug', '[Project Webhook] POST: ' . print_r($data, true));

    $projectSlug = $data['project_slug'] ?? null;

    if (!$projectSlug) {
        log_message('error', '[Project Webhook] project_slug fehlt');
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'error' => 'project_slug required'
        ]);
    }

    // Mapping aus DB laden
    $mappingModel = new \App\Models\ProjectMappingModel();
    $mapping = $mappingModel->where('project_slug', $projectSlug)
                            ->where('is_active', 1)
                            ->first();

    if (!$mapping) {
        log_message('error', "[Project Webhook] Kein aktives Mapping für: $projectSlug");
        return $this->response->setStatusCode(404)->setJSON([
            'success' => false,
            'error' => "Project '$projectSlug' not found or inactive"
        ]);
    }

    $categoryTypes = json_decode($mapping['category_types'], true);

    if (empty($categoryTypes)) {
        log_message('error', "[Project Webhook] Keine Branchen für Projekt: $projectSlug");
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'error' => 'No categories mapped for this project'
        ]);
    }

    // Eindeutige project_ref generieren (verknüpft alle Offers)
    $projectRef = $projectSlug . '_' . bin2hex(random_bytes(6));
    $uuid = $data['uuid'] ?? bin2hex(random_bytes(8));

    $createdOffers = [];
    $offerModel = new \App\Models\OfferModel();

    // Headers und Referer speichern
    $headers = array_map(fn($h) => (string)$h->getValueLine(), $this->request->headers());
    $referer = $this->request->getServer('HTTP_REFERER');

    // Platform ermitteln
    $host = $_SERVER['HTTP_HOST'] ?? $headers['Host'] ?? 'unknown';
    $parts = explode('.', $host);
    $domain = count($parts) > 2 ? $parts[count($parts)-2] . '.' . $parts[count($parts)-1] : $host;
    $platform = 'my_' . str_replace(['.', '-'], '_', $domain);

    // Für jeden category_type einen Offer erstellen
    foreach ($categoryTypes as $type) {
        // Basis-Daten enrichen
        $enriched = $offerModel->enrichDataFromFormFields($data, ['uuid' => $uuid]);

        // Preis berechnen
        $priceCalculator = new \App\Libraries\OfferPriceCalculator();
        $price = $priceCalculator->calculatePrice($type, $type, $data, []);

        // Fallback auf CategoryManager
        if ($price === 0) {
            $categoryManager = new \App\Libraries\CategoryManager();
            $categories = $categoryManager->getAll();
            $price = $categories[$type]['price'] ?? 0;
        }

        $insertData = array_merge($enriched, [
            'type' => $type,
            'original_type' => $type,
            'form_fields' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers' => json_encode($headers, JSON_UNESCAPED_UNICODE),
            'referer' => $referer,
            'uuid' => $uuid,
            'status' => 'new',
            'price' => $price,
            'buyers' => 0,
            'bought_by' => json_encode([]),
            'platform' => $platform,
            'country' => siteconfig()->siteCountry ?? null,
            'project_ref' => $projectRef,
            'project_slug' => $mapping['project_slug'],
            'project_name' => $mapping['project_name'],
        ]);

        if (!$offerModel->insert($insertData)) {
            log_message('error', "[Project Webhook] Insert failed for type $type: " . print_r($offerModel->errors(), true));
            continue;
        }

        $offerId = $offerModel->getInsertID();

        // Titel generieren
        $savedOffer = $offerModel->find($offerId);
        if ($savedOffer) {
            $titleGenerator = new \App\Libraries\OfferTitleGenerator();
            $title = $titleGenerator->generateTitle($savedOffer);
            $offerModel->update($offerId, ['title' => $title]);
        }

        $createdOffers[] = [
            'id' => $offerId,
            'type' => $type
        ];

        log_message('info', "[Project Webhook] Offer erstellt: #$offerId, Type: $type");
    }

    log_message('info', "[Project Webhook] Projekt '$projectSlug' -> " . count($createdOffers) . " Offers erstellt (Ref: $projectRef)");

    return $this->response->setJSON([
        'success' => true,
        'project_ref' => $projectRef,
        'project_slug' => $projectSlug,
        'offers_created' => count($createdOffers),
        'offers' => $createdOffers
    ]);
}
```

---

## Route hinzufügen

In `app/Config/Routes.php`:

```php
// Projekt-Webhook
$routes->post('fluentform/project', 'FluentForm::project');
```

---

## Admin-Controller: ProjectMapping.php

```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProjectMappingModel;

class ProjectMapping extends BaseController
{
    protected ProjectMappingModel $model;

    public function __construct()
    {
        $this->model = new ProjectMappingModel();
    }

    /**
     * Liste aller Projekt-Mappings
     */
    public function index()
    {
        $mappings = $this->model->orderBy('sort_order')->orderBy('project_name')->findAll();

        // Branchen-Namen auflösen
        $categoryConfig = config('CategoryOptions');
        foreach ($mappings as &$mapping) {
            $types = json_decode($mapping['category_types'], true) ?? [];
            $mapping['category_names'] = array_map(
                fn($t) => $categoryConfig->categoryTypes[$t] ?? $t,
                $types
            );
        }

        return view('admin/project_mappings/index', [
            'mappings' => $mappings,
            'title' => 'Projekt-Mappings'
        ]);
    }

    /**
     * Formular: Neues Mapping
     */
    public function new()
    {
        $categoryConfig = config('CategoryOptions');

        return view('admin/project_mappings/form', [
            'mapping' => null,
            'categories' => $categoryConfig->categoryTypes,
            'title' => 'Neues Projekt-Mapping'
        ]);
    }

    /**
     * Formular: Mapping bearbeiten
     */
    public function edit(int $id)
    {
        $mapping = $this->model->find($id);
        if (!$mapping) {
            return redirect()->to('/admin/project-mappings')->with('error', 'Mapping nicht gefunden');
        }

        $categoryConfig = config('CategoryOptions');
        $mapping['category_types_array'] = json_decode($mapping['category_types'], true) ?? [];

        return view('admin/project_mappings/form', [
            'mapping' => $mapping,
            'categories' => $categoryConfig->categoryTypes,
            'title' => 'Projekt-Mapping bearbeiten'
        ]);
    }

    /**
     * Speichern (Create/Update)
     */
    public function save(int $id = null)
    {
        $data = [
            'project_slug' => url_title($this->request->getPost('project_slug'), '-', true),
            'project_name' => $this->request->getPost('project_name'),
            'category_types' => json_encode($this->request->getPost('category_types') ?? []),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ];

        if ($id) {
            $data['id'] = $id;
        }

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('/admin/project-mappings')->with('success', 'Mapping gespeichert');
    }

    /**
     * Löschen
     */
    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to('/admin/project-mappings')->with('success', 'Mapping gelöscht');
    }

    /**
     * Toggle Aktiv-Status
     */
    public function toggle(int $id)
    {
        $mapping = $this->model->find($id);
        if ($mapping) {
            $this->model->update($id, ['is_active' => $mapping['is_active'] ? 0 : 1]);
        }
        return redirect()->to('/admin/project-mappings');
    }
}
```

---

## Admin-View: project_mappings/index.php

```php
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Projekt-Mappings</h1>
        <a href="/admin/project-mappings/new" class="btn btn-primary">+ Neues Projekt</a>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php endif ?>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-4">
                Hier definieren Sie, welche Branchen zu einem Projekt gehören.
                Wenn ein Kunde ein Projekt-Formular ausfüllt, werden automatisch
                Anfragen für alle zugeordneten Branchen erstellt.
            </p>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Slug</th>
                        <th>Name</th>
                        <th>Branchen</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mappings as $mapping): ?>
                    <tr>
                        <td><code><?= esc($mapping['project_slug']) ?></code></td>
                        <td><?= esc($mapping['project_name']) ?></td>
                        <td>
                            <?php foreach ($mapping['category_names'] as $name): ?>
                                <span class="badge bg-secondary"><?= esc($name) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td>
                            <?php if ($mapping['is_active']): ?>
                                <span class="badge bg-success">Aktiv</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inaktiv</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <a href="/admin/project-mappings/edit/<?= $mapping['id'] ?>"
                               class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                            <a href="/admin/project-mappings/toggle/<?= $mapping['id'] ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <?= $mapping['is_active'] ? 'Deaktivieren' : 'Aktivieren' ?>
                            </a>
                            <a href="/admin/project-mappings/delete/<?= $mapping['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Wirklich löschen?')">Löschen</a>
                        </td>
                    </tr>
                    <?php endforeach ?>

                    <?php if (empty($mappings)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Noch keine Projekt-Mappings vorhanden.
                        </td>
                    </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Anleitung für Formularersteller</h5>
        </div>
        <div class="card-body">
            <p>Bei Projekt-Formularen in Fluent Forms:</p>
            <ol>
                <li>Hidden Field <code>project_slug</code> hinzufügen (z.B. <code>pool</code>)</li>
                <li><strong>Kein</strong> <code>type</code> Hidden Field verwenden</li>
                <li>Webhook-URL: <code>https://my.[domain].ch/fluentform/project</code></li>
            </ol>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
```

---

## Admin-View: project_mappings/form.php

```php
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1><?= $title ?></h1>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form method="post" action="/admin/project-mappings/save<?= $mapping ? '/' . $mapping['id'] : '' ?>">
        <?= csrf_field() ?>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Projekt-Slug *</label>
                            <input type="text" name="project_slug" class="form-control"
                                   value="<?= old('project_slug', $mapping['project_slug'] ?? '') ?>"
                                   placeholder="z.B. pool, bad_sanierung"
                                   pattern="[a-z0-9_-]+"
                                   title="Nur Kleinbuchstaben, Zahlen, Unterstriche und Bindestriche"
                                   required>
                            <small class="text-muted">
                                Wird im Formular als Hidden Field verwendet. Keine Leerzeichen oder Sonderzeichen.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Projekt-Name *</label>
                            <input type="text" name="project_name" class="form-control"
                                   value="<?= old('project_name', $mapping['project_name'] ?? '') ?>"
                                   placeholder="z.B. Pool bauen"
                                   required>
                            <small class="text-muted">
                                Wird in der Anfrage-Ansicht angezeigt.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Zugeordnete Branchen *</label>
                    <div class="row">
                        <?php
                        $selectedTypes = $mapping['category_types_array'] ?? [];
                        foreach ($categories as $slug => $name):
                        ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="checkbox" name="category_types[]"
                                       value="<?= $slug ?>"
                                       id="cat_<?= $slug ?>"
                                       class="form-check-input"
                                       <?= in_array($slug, $selectedTypes) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat_<?= $slug ?>">
                                    <?= esc($name) ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach ?>
                    </div>
                    <small class="text-muted">
                        Für jede ausgewählte Branche wird eine separate Anfrage erstellt.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Beschreibung</label>
                    <textarea name="description" class="form-control" rows="2"
                              placeholder="Optionale Beschreibung für interne Zwecke"
                    ><?= old('description', $mapping['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Sortierung</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="<?= old('sort_order', $mapping['sort_order'] ?? 0) ?>"
                                   min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" value="1"
                                       class="form-check-input" id="is_active"
                                       <?= old('is_active', $mapping['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Aktiv</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Speichern</button>
                <a href="/admin/project-mappings" class="btn btn-outline-secondary">Abbrechen</a>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
```

---

## Admin-Routes hinzufügen

In `app/Config/Routes.php`:

```php
// Admin: Projekt-Mappings
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('project-mappings', 'Admin\ProjectMapping::index');
    $routes->get('project-mappings/new', 'Admin\ProjectMapping::new');
    $routes->get('project-mappings/edit/(:num)', 'Admin\ProjectMapping::edit/$1');
    $routes->post('project-mappings/save', 'Admin\ProjectMapping::save');
    $routes->post('project-mappings/save/(:num)', 'Admin\ProjectMapping::save/$1');
    $routes->get('project-mappings/delete/(:num)', 'Admin\ProjectMapping::delete/$1');
    $routes->get('project-mappings/toggle/(:num)', 'Admin\ProjectMapping::toggle/$1');
});
```

---

# Teil 3: Zusammenfassung

## Umsetzungsschritte

| # | Aufgabe | Wer | Status |
|---|---------|-----|--------|
| 1 | DB-Migration erstellen (project_mappings + offers Felder) | Entwickler | Offen |
| 2 | ProjectMappingModel erstellen | Entwickler | Offen |
| 3 | FluentForm::project() Methode hinzufügen | Entwickler | Offen |
| 4 | Admin-Controller + Views erstellen | Entwickler | Offen |
| 5 | Routes hinzufügen | Entwickler | Offen |
| 6 | Projekt-Mappings im Admin anlegen | Auftraggeber | Offen |
| 7 | Projekt-Formulare in Fluent Forms erstellen | Formularersteller | Offen |

## Checkliste für Formularersteller

### Bei Branchen-Formularen:
- [ ] Hidden Field `type` mit Branchen-Slug (z.B. `electrician`)
- [ ] Webhook-URL: `/fluentform/webhook`
- [ ] Alle Basis-Felder vorhanden

### Bei Projekt-Formularen:
- [ ] Hidden Field `project_slug` (z.B. `pool`)
- [ ] **Kein** `type` Hidden Field
- [ ] Webhook-URL: `/fluentform/project`
- [ ] Alle Basis-Felder vorhanden
- [ ] Projekt-spezifische Felder nach Bedarf

## Offene Fragen

1. **Preismodell:** Wie wird der Preis bei Projekt-Anfragen berechnet?
   - Option A: Summe aller Branchen-Preise
   - Option B: Eigener Projekt-Preis
   - Option C: Preis pro resultierende Anfrage (wie bisher)

2. **Verifikation:** Muss der Kunde bei Projekt-Anfragen nur einmal verifizieren?

3. **Anzeige:** Soll in der Anfrage-Ansicht angezeigt werden "Teil von Projekt: Pool bauen"?
