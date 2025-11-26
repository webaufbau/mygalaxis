<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>E-Mail Templates</h1>
    <div>
        <a href="/admin/email-templates/export" class="btn btn-success me-2">
            <i class="bi bi-download"></i> Export
        </a>
        <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import
        </button>
        <a href="/admin/email-field-translations" class="btn btn-warning me-2">
            <i class="bi bi-translate"></i> Feldwerte-Übersetzungen
        </a>
        <a href="/admin/email-templates/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Neues Template
        </a>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/email-templates/import" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Templates importieren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Hinweis:</strong> Export/Import beinhaltet sowohl Templates als auch Feldwerte-Übersetzungen.
                    </div>
                    <div class="mb-3">
                        <label for="import_file" class="form-label">JSON-Datei auswählen</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" accept=".json" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Import-Modus</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_mode" id="mode_insert" value="insert" checked>
                            <label class="form-check-label" for="mode_insert">
                                <strong>Insert:</strong> Nur neue einfügen (vorhandene werden übersprungen)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_mode" id="mode_update" value="update">
                            <label class="form-check-label" for="mode_update">
                                <strong>Update:</strong> Vorhandene überschreiben und neue einfügen
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Importieren</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Info:</strong> Diese Templates werden für die Bestätigungsmails an Kunden verwendet, die über das Formular eine Anfrage stellen.
        Jede Branche kann ein eigenes Template pro Sprache haben.
    </div>

    <?php if (empty($templates)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Noch keine Templates vorhanden.
        </div>
    <?php else: ?>
        <?php
            // Load category types for German translations
            $categoryConfig = config('CategoryOptions');
            $categoryTypes = $categoryConfig->categoryTypes;
            $categoryTypes['default'] = 'Standard (Fallback)';

            // Load subtype labels
            $subtypeConfig = config('OfferSubtypes');
            $subtypeLabels = $subtypeConfig->getSubtypeLabels();
        ?>
        <?php foreach ($templates as $offerType => $typeTemplates): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> <?= esc($categoryTypes[$offerType] ?? ucfirst($offerType)) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sprache</th>
                                    <th>Unterkategorie</th>
                                    <th>Betreff</th>
                                    <th>Status</th>
                                    <th>Notizen</th>
                                    <th>Aktualisiert</th>
                                    <th class="text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($typeTemplates as $template): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= strtoupper(esc($template['language'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($template['subtype'])): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-diagram-3"></i> <?= esc($subtypeLabels[$template['subtype']] ?? $template['subtype']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-asterisk"></i> Alle
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($template['subject']) ?></td>
                                        <td>
                                            <?php if ($template['is_active']): ?>
                                                <span class="badge bg-success">Aktiv</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Inaktiv</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($template['notes'])): ?>
                                                <small class="text-muted"><?= esc(substr($template['notes'], 0, 50)) ?><?= strlen($template['notes']) > 50 ? '...' : '' ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($template['updated_at'])) ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="/admin/email-templates/preview/<?= $template['id'] ?>"
                                                   class="btn btn-info"
                                                   title="Vorschau"
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/admin/email-templates/edit/<?= $template['id'] ?>"
                                                   class="btn btn-warning"
                                                   title="Bearbeiten">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="/admin/email-templates/copy/<?= $template['id'] ?>"
                                                   class="btn btn-secondary"
                                                   title="Kopieren"
                                                   onclick="return confirm('Möchten Sie dieses Template kopieren?')">
                                                    <i class="bi bi-files"></i>
                                                </a>
                                                <?php if ($template['offer_type'] !== 'default' || $template['language'] !== 'de'): ?>
                                                    <button type="button"
                                                            class="btn btn-danger"
                                                            title="Löschen"
                                                            onclick="confirmDelete(<?= $template['id'] ?>, '<?= esc($template['offer_type']) ?> (<?= esc($template['language']) ?>)')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<script>
function confirmDelete(id, name) {
    if (confirm('Möchten Sie das Template "' + name + '" wirklich löschen?')) {
        window.location.href = '/admin/email-templates/delete/' + id;
    }
}
</script>

<style>
.table-responsive {
    border-radius: 0.5rem;
}

.card-header h5 {
    font-weight: 600;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?= $this->endSection() ?>
