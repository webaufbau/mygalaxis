<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-arrow-down-up me-2"></i>Import / Export</h4>
</div>

<div class="row">
    <!-- Export Section -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/import-export/export') ?>">
                    <?= csrf_field() ?>

                    <p class="text-muted mb-3">Wählen Sie die Tabellen aus, die exportiert werden sollen:</p>

                    <div class="mb-3">
                        <?php foreach ($tables as $key => $config): ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tables[]" value="<?= esc($key) ?>" id="export_<?= esc($key) ?>">
                                <label class="form-check-label" for="export_<?= esc($key) ?>">
                                    <?= esc($config['label']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Format</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="format" value="csv" id="format_csv" checked>
                            <label class="btn btn-outline-primary" for="format_csv">CSV <small>(Excel)</small></label>
                            <input type="radio" class="btn-check" name="format" value="json" id="format_json">
                            <label class="btn btn-outline-primary" for="format_json">JSON</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Exportieren
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="selectAllExport">
                            Alle auswählen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Import</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/import-export/import') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Achtung:</strong> Import fügt neue Einträge hinzu. Bestehende Daten werden nicht überschrieben.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Zieltabelle</label>
                        <select name="import_table" class="form-select" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($tables as $key => $config): ?>
                                <option value="<?= esc($key) ?>"><?= esc($config['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Datei</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv,.json" required>
                        <div class="form-text">Erlaubte Formate: CSV, JSON</div>
                    </div>

                    <button type="submit" class="btn btn-warning" onclick="return confirm('Daten wirklich importieren?')">
                        <i class="bi bi-upload me-1"></i> Importieren
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Hinweise</h5>
    </div>
    <div class="card-body">
        <h6>Export</h6>
        <ul>
            <li>Bei Auswahl mehrerer Tabellen wird eine ZIP-Datei erstellt</li>
            <li>CSV-Dateien sind UTF-8 kodiert und mit Semikolon getrennt (für Excel)</li>
            <li>JSON eignet sich für Backups und Datenübertragung zwischen Systemen</li>
        </ul>

        <h6>Import</h6>
        <ul>
            <li>CSV-Dateien müssen Semikolon als Trennzeichen verwenden</li>
            <li>Die erste Zeile muss die Spaltennamen enthalten</li>
            <li>ID und Zeitstempel (created_at, updated_at) werden beim Import ignoriert</li>
            <li>Ungültige Zeilen werden übersprungen und als Fehler gemeldet</li>
        </ul>
    </div>
</div>

<script>
document.getElementById('selectAllExport').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="tables[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    this.textContent = allChecked ? 'Alle auswählen' : 'Alle abwählen';
});
</script>

<?= $this->endSection() ?>
