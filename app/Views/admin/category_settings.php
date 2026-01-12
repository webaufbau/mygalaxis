<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Branchen verwalten</h2>
</div>

<form method="post">
    <?= csrf_field() ?>

    <div class="accordion" id="categoriesAccordion">
        <?php $index = 0; foreach ($categories as $key => $cat): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse_<?= esc($key) ?>">
                        <span class="d-flex align-items-center gap-2 w-100">
                            <span class="badge rounded-circle" style="background-color: <?= esc($cat['color'] ?? '#6c757d') ?>; width: 12px; height: 12px;"></span>
                            <strong><?= esc($cat['name']) ?></strong>
                            <code class="text-muted small ms-2"><?= esc($key) ?></code>
                            <?php if (!empty($cat['form_link'])): ?>
                                <i class="bi bi-link-45deg text-success ms-auto me-3" title="Formular-Link gesetzt"></i>
                            <?php else: ?>
                                <i class="bi bi-link-45deg text-muted ms-auto me-3" title="Kein Formular-Link"></i>
                            <?php endif; ?>
                        </span>
                    </button>
                </h2>
                <div id="collapse_<?= esc($key) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                     data-bs-parent="#categoriesAccordion">
                    <div class="accordion-body">
                        <input type="hidden" name="categories[<?= esc($key) ?>][name]" value="<?= esc($cat['name']) ?>">

                        <div class="row">
                            <!-- Linke Spalte: Formular, Farbe, Max -->
                            <div class="col-md-5">
                                <h6 class="text-muted mb-3"><i class="bi bi-gear"></i> Einstellungen</h6>

                                <!-- Formular-Link -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Formular-Link</label>
                                    <input type="url"
                                           name="categories[<?= esc($key) ?>][form_link]"
                                           value="<?= esc($cat['form_link'] ?? '') ?>"
                                           class="form-control"
                                           placeholder="https://...">
                                    <small class="text-muted">Link zum Offerten-Formular für diese Branche</small>
                                </div>

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
                                        <label class="form-check-label" for="max_unlimited_<?= esc($key) ?>">
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
                            <div class="col-md-7">
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

<?= $this->endSection() ?>
