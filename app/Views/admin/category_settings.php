<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Branchen verwalten</h2>
    <div class="d-flex gap-2">
        <a href="/admin/category/export" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i> Export
        </a>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form method="post">
    <?= csrf_field() ?>

    <div class="accordion" id="categoriesAccordion">
        <?php $index = 0; foreach ($categories as $key => $cat): ?>
            <?php $forms = $cat['forms'] ?? []; ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse_<?= esc($key) ?>">
                        <span class="d-flex align-items-center gap-2 w-100">
                            <span class="badge rounded-circle" style="background-color: <?= esc($cat['color'] ?? '#6c757d') ?>; width: 12px; height: 12px;"></span>
                            <strong><?= esc($cat['name']) ?></strong>
                            <code class="text-muted small ms-2"><?= esc($key) ?></code>
                            <?php if (!empty($forms)): ?>
                                <span class="badge bg-success ms-auto me-3"><?= count($forms) ?> Formular<?= count($forms) > 1 ? 'e' : '' ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-auto me-3">Keine Formulare</span>
                            <?php endif; ?>
                        </span>
                    </button>
                </h2>
                <div id="collapse_<?= esc($key) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                     data-bs-parent="#categoriesAccordion">
                    <div class="accordion-body">
                        <input type="hidden" name="categories[<?= esc($key) ?>][name]" value="<?= esc($cat['name']) ?>">

                        <div class="row">
                            <!-- Linke Spalte: Formulare und Einstellungen -->
                            <div class="col-md-6">
                                <!-- Formulare -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-3"><i class="bi bi-file-earmark-text"></i> Formulare</h6>
                                    <p class="small text-muted mb-3">
                                        Definiere hier die Formulare für diese Branche. Jedes Formular erscheint separat im Frontend.
                                    </p>

                                    <div class="forms-container" data-category="<?= esc($key) ?>">
                                        <?php foreach ($forms as $fi => $form): ?>
                                        <div class="form-entry card mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                                <span class="small fw-bold">Formular <?= $fi + 1 ?></span>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-form">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            <div class="card-body py-2">
                                                <!-- DE -->
                                                <div class="row g-2 mb-2">
                                                    <div class="col-4">
                                                        <label class="form-label small">Name (DE) *</label>
                                                        <input type="text"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][name_de]"
                                                               value="<?= esc($form['name_de'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="z.B. Privat-Umzug"
                                                               required>
                                                    </div>
                                                    <div class="col-8">
                                                        <label class="form-label small">Link (DE) *</label>
                                                        <input type="url"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][form_link_de]"
                                                               value="<?= esc($form['form_link_de'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="https://..."
                                                               required>
                                                    </div>
                                                </div>
                                                <!-- EN -->
                                                <div class="row g-2 mb-2">
                                                    <div class="col-4">
                                                        <label class="form-label small">Name (EN)</label>
                                                        <input type="text"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][name_en]"
                                                               value="<?= esc($form['name_en'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="Private Move">
                                                    </div>
                                                    <div class="col-8">
                                                        <label class="form-label small">Link (EN)</label>
                                                        <input type="url"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][form_link_en]"
                                                               value="<?= esc($form['form_link_en'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="https://...">
                                                    </div>
                                                </div>
                                                <!-- FR -->
                                                <div class="row g-2 mb-2">
                                                    <div class="col-4">
                                                        <label class="form-label small">Name (FR)</label>
                                                        <input type="text"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][name_fr]"
                                                               value="<?= esc($form['name_fr'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="Déménagement privé">
                                                    </div>
                                                    <div class="col-8">
                                                        <label class="form-label small">Link (FR)</label>
                                                        <input type="url"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][form_link_fr]"
                                                               value="<?= esc($form['form_link_fr'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="https://...">
                                                    </div>
                                                </div>
                                                <!-- IT -->
                                                <div class="row g-2">
                                                    <div class="col-4">
                                                        <label class="form-label small">Name (IT)</label>
                                                        <input type="text"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][name_it]"
                                                               value="<?= esc($form['name_it'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="Trasloco privato">
                                                    </div>
                                                    <div class="col-8">
                                                        <label class="form-label small">Link (IT)</label>
                                                        <input type="url"
                                                               name="categories[<?= esc($key) ?>][forms][<?= $fi ?>][form_link_it]"
                                                               value="<?= esc($form['form_link_it'] ?? '') ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="https://...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm add-form" data-category="<?= esc($key) ?>">
                                        <i class="bi bi-plus"></i> Formular hinzufügen
                                    </button>
                                </div>

                                <hr>

                                <!-- Weitere Einstellungen -->
                                <h6 class="text-muted mb-3"><i class="bi bi-gear"></i> Einstellungen</h6>

                                <!-- Farbe -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Farbe</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color"
                                               name="categories[<?= esc($key) ?>][color]"
                                               value="<?= esc($cat['color'] ?? '#6c757d') ?>"
                                               class="form-control form-control-color"
                                               style="width: 60px; height: 38px;">
                                        <span class="text-muted small"><?= esc($cat['color'] ?? '#6c757d') ?></span>
                                    </div>
                                </div>

                                <!-- Maximalpreis -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Maximalpreis</label>
                                    <div class="form-check mb-3">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="max_unlimited_<?= esc($key) ?>"
                                               name="categories[<?= esc($key) ?>][max_unlimited]"
                                               value="1"
                                               <?= empty($cat['max']) ? 'checked' : '' ?>
                                               onclick="toggleMaxField('<?= esc($key) ?>')">
                                        <label class="form-check-label ms-1" for="max_unlimited_<?= esc($key) ?>">
                                            Kein Maximum (∞)
                                        </label>
                                    </div>
                                    <div id="max_field_<?= esc($key) ?>" style="<?= empty($cat['max']) ? 'display:none;' : '' ?>">
                                        <input type="number"
                                               name="categories[<?= esc($key) ?>][max]"
                                               value="<?= esc($cat['max'] ?? '') ?>"
                                               min="1"
                                               class="form-control"
                                               style="width: 120px;"
                                               placeholder="CHF">
                                    </div>
                                </div>

                                <hr>

                                <!-- Bewertungs-Email -->
                                <h6 class="text-muted mb-3"><i class="bi bi-envelope"></i> Bewertungs-Emails</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">Erste Email nach</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                   name="categories[<?= esc($key) ?>][review_email_days]"
                                                   value="<?= esc($cat['review_email_days'] ?? 5) ?>"
                                                   min="0"
                                                   class="form-control">
                                            <span class="input-group-text">Tage</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Erinnerung nach</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                   name="categories[<?= esc($key) ?>][review_reminder_days]"
                                                   value="<?= esc($cat['review_reminder_days'] ?? 10) ?>"
                                                   min="0"
                                                   class="form-control">
                                            <span class="input-group-text">Tage</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rechte Spalte: Preise -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3"><i class="bi bi-currency-dollar"></i> Preise (CHF)</h6>

                                <div class="row g-2">
                                    <?php foreach ($cat['options'] as $optKey => $opt): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-truncate" style="max-width: 120px;" title="<?= esc($opt['label']) ?>">
                                                    <?= esc($opt['label']) ?>
                                                </span>
                                                <input type="number"
                                                       name="categories[<?= esc($key) ?>][options][<?= esc($optKey) ?>][price]"
                                                       value="<?= esc($opt['price']) ?>"
                                                       step="0.05" min="0"
                                                       class="form-control">
                                                <input type="hidden"
                                                       name="categories[<?= esc($key) ?>][options][<?= esc($optKey) ?>][label]"
                                                       value="<?= esc($opt['label']) ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php $index++; endforeach; ?>
    </div>

    <hr class="my-4">

    <!-- Rabattregeln -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-percent"></i> Rabattregeln</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm" id="discount-rules-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Stunden</th>
                        <th style="width: 150px;">Rabatt (%)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($discountRules)): ?>
                        <?php foreach ($discountRules as $i => $rule): ?>
                            <tr>
                                <td><input type="number" name="discountRules[<?= $i ?>][hours]" value="<?= esc($rule['hours']) ?>" class="form-control form-control-sm" min="1"></td>
                                <td><input type="number" name="discountRules[<?= $i ?>][discount]" value="<?= esc($rule['discount']) ?>" class="form-control form-control-sm" min="0" max="100"></td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-discount-rule">
                <i class="bi bi-plus"></i> Regel hinzufügen
            </button>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Speichern
        </button>
    </div>
</form>

<!-- Template für neues Formular -->
<template id="form-template">
    <div class="form-entry card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="small fw-bold">Neues Formular</span>
            <button type="button" class="btn btn-outline-danger btn-sm remove-form">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="card-body py-2">
            <!-- DE -->
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Name (DE) *</label>
                    <input type="text" name="" class="form-control form-control-sm form-name-de" placeholder="z.B. Privat-Umzug" required>
                </div>
                <div class="col-8">
                    <label class="form-label small">Link (DE) *</label>
                    <input type="url" name="" class="form-control form-control-sm form-link-de" placeholder="https://..." required>
                </div>
            </div>
            <!-- EN -->
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Name (EN)</label>
                    <input type="text" name="" class="form-control form-control-sm form-name-en" placeholder="Private Move">
                </div>
                <div class="col-8">
                    <label class="form-label small">Link (EN)</label>
                    <input type="url" name="" class="form-control form-control-sm form-link-en" placeholder="https://...">
                </div>
            </div>
            <!-- FR -->
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small">Name (FR)</label>
                    <input type="text" name="" class="form-control form-control-sm form-name-fr" placeholder="Déménagement privé">
                </div>
                <div class="col-8">
                    <label class="form-label small">Link (FR)</label>
                    <input type="url" name="" class="form-control form-control-sm form-link-fr" placeholder="https://...">
                </div>
            </div>
            <!-- IT -->
            <div class="row g-2">
                <div class="col-4">
                    <label class="form-label small">Name (IT)</label>
                    <input type="text" name="" class="form-control form-control-sm form-name-it" placeholder="Trasloco privato">
                </div>
                <div class="col-8">
                    <label class="form-label small">Link (IT)</label>
                    <input type="url" name="" class="form-control form-control-sm form-link-it" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let table = document.getElementById("discount-rules-table").getElementsByTagName("tbody")[0];
    let addBtn = document.getElementById("add-discount-rule");
    const MAX_DISCOUNT_RULES = 3;

    function updateAddButtonState() {
        if (table.rows.length >= MAX_DISCOUNT_RULES) {
            addBtn.disabled = true;
            addBtn.title = 'Maximal ' + MAX_DISCOUNT_RULES + ' Rabattregeln erlaubt';
        } else {
            addBtn.disabled = false;
            addBtn.title = '';
        }
    }

    addBtn.addEventListener("click", function() {
        if (table.rows.length >= MAX_DISCOUNT_RULES) {
            alert('Maximal ' + MAX_DISCOUNT_RULES + ' Rabattregeln sind erlaubt.');
            return;
        }

        let index = table.rows.length;
        let row = table.insertRow();

        row.innerHTML = `
            <td><input type="number" name="discountRules[${index}][hours]" class="form-control form-control-sm" min="1"></td>
            <td><input type="number" name="discountRules[${index}][discount]" class="form-control form-control-sm" min="0" max="100"></td>
            <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
        `;
        updateAddButtonState();
    });

    table.addEventListener("click", function(e) {
        if (e.target && (e.target.classList.contains("remove-row") || e.target.closest(".remove-row"))) {
            e.target.closest("tr").remove();
            updateAddButtonState();
        }
    });

    updateAddButtonState();

    // Formulare hinzufügen
    document.querySelectorAll('.add-form').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            const container = document.querySelector(`.forms-container[data-category="${category}"]`);
            const index = container.querySelectorAll('.form-entry').length;

            const template = document.getElementById('form-template');
            const clone = template.content.cloneNode(true);

            // Namen der Inputs setzen
            clone.querySelector('.form-name-de').name = `categories[${category}][forms][${index}][name_de]`;
            clone.querySelector('.form-name-en').name = `categories[${category}][forms][${index}][name_en]`;
            clone.querySelector('.form-name-fr').name = `categories[${category}][forms][${index}][name_fr]`;
            clone.querySelector('.form-name-it').name = `categories[${category}][forms][${index}][name_it]`;
            clone.querySelector('.form-link-de').name = `categories[${category}][forms][${index}][form_link_de]`;
            clone.querySelector('.form-link-en').name = `categories[${category}][forms][${index}][form_link_en]`;
            clone.querySelector('.form-link-fr').name = `categories[${category}][forms][${index}][form_link_fr]`;
            clone.querySelector('.form-link-it').name = `categories[${category}][forms][${index}][form_link_it]`;

            container.appendChild(clone);
        });
    });

    // Formulare entfernen
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('remove-form') || e.target.closest('.remove-form'))) {
            e.target.closest('.form-entry').remove();
        }
    });
});

function toggleMaxField(key) {
    const cb = document.getElementById('max_unlimited_' + key);
    const field = document.getElementById('max_field_' + key);
    if (cb.checked) {
        field.style.display = 'none';
        field.querySelector('input').value = '';
    } else {
        field.style.display = 'block';
    }
}
</script>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/category/import" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel"><i class="bi bi-upload"></i> Branchen importieren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Achtung:</strong> Der Import überschreibt alle bestehenden Branchen-Einstellungen!
                    </div>

                    <div class="mb-3">
                        <label for="import_file" class="form-label">JSON-Datei auswählen</label>
                        <input type="file"
                               name="import_file"
                               id="import_file"
                               class="form-control"
                               accept=".json,application/json"
                               required>
                        <small class="text-muted">Nur .json Dateien (vorher exportiert)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Importieren
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
