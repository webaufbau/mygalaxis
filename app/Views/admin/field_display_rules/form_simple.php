<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= esc($title) ?></h1>
                <a href="/admin/field-display-rules" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Zurück
                </a>
            </div>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Fehler:</strong>
                    <ul class="mb-0">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="ruleForm">
                <?= csrf_field() ?>

                <!-- Verstecktes JSON-Feld (wird automatisch gefüllt) -->
                <input type="hidden" id="conditions_json" name="conditions_json" value="">

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Basis-Einstellungen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rule_key" class="form-label">
                                    Rule-Key <span class="text-danger">*</span>
                                    <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="Eindeutiger Schlüssel, z.B. bodenplatten_vorplatz_gruppe"></i>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="rule_key"
                                       name="rule_key"
                                       value="<?= old('rule_key', $rule['rule_key'] ?? '') ?>"
                                       placeholder="z.B. bodenplatten_vorplatz_gruppe"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="offer_type" class="form-label">
                                    Branche <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="offer_type" name="offer_type" required>
                                    <?php
                                    $currentValue = old('offer_type', $rule['offer_type'] ?? 'default');
                                    foreach ($offerTypes as $key => $label):
                                    ?>
                                        <option value="<?= esc($key) ?>" <?= $currentValue === $key ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="label" class="form-label">
                                Anzeige-Label <span class="text-danger">*</span>
                                <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="Dieser Text wird in Emails und Firmen-Ansichten angezeigt"></i>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="label"
                                   name="label"
                                   value="<?= old('label', $rule['label'] ?? '') ?>"
                                   placeholder="z.B. Bodenplatten: Vorplatz / Garage"
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sortierung</label>
                                <input type="number"
                                       class="form-control"
                                       id="sort_order"
                                       name="sort_order"
                                       value="<?= old('sort_order', $rule['sort_order'] ?? 0) ?>">
                                <small class="text-muted">0 = ganz oben</small>
                            </div>

                            <div class="col-md-6 mb-3 d-flex align-items-center">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           <?= old('is_active', $rule['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Rule aktiv</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-eye-slash"></i> Welche Felder sollen versteckt werden?</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Diese Felder werden <strong>nicht einzeln angezeigt</strong>, sondern durch die Rule ersetzt.
                        </div>

                        <div class="mb-3">
                            <label for="fields_to_hide" class="form-label">
                                Feldnamen <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="fields_to_hide"
                                      name="fields_to_hide"
                                      rows="3"
                                      placeholder="Ein Feldname pro Zeile, z.B.:&#10;bodenplatten_vorplatz&#10;bodenplatten_vorplatz_flaeche&#10;bodenplatten_vorplatz_flaeche_ja"
                                      required><?= old('fields_to_hide', isset($rule['fields_to_hide']) ? implode("\n", $rule['fields_to_hide']) : '') ?></textarea>
                            <small class="text-muted">Ein Feldname pro Zeile</small>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Bedingungen: Wann wird was angezeigt?</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Beispiel:</strong> "Wenn Vorplatz=Ja UND Fläche bekannt=Ja, dann zeige: '25 m²'"
                        </div>

                        <div id="conditions-container">
                            <!-- Bedingungen werden hier dynamisch hinzugefügt -->
                        </div>

                        <button type="button" class="btn btn-outline-success" onclick="addCondition()">
                            <i class="bi bi-plus-lg"></i> Neue Bedingung hinzufügen
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-sticky"></i> Notizen (optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control"
                                  id="notes"
                                  name="notes"
                                  rows="2"
                                  placeholder="Optionale interne Notizen"><?= old('notes', $rule['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <a href="/admin/field-display-rules" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Abbrechen
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.condition-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f8f9fa;
}

.condition-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.when-field {
    background: #fff;
    border: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.display-field {
    background: #d1e7dd;
    border: 2px solid #0f5132;
    padding: 15px;
    border-radius: 5px;
}
</style>

<script>
let conditionCounter = 0;

// Lade bestehende Bedingungen beim Start
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($rule['conditions']) && !empty($rule['conditions'])): ?>
        const existingConditions = <?= json_encode($rule['conditions']) ?>;
        existingConditions.forEach(condition => {
            addCondition(condition);
        });
    <?php else: ?>
        // Erste Bedingung automatisch hinzufügen
        addCondition();
    <?php endif; ?>
});

function addCondition(existingData = null) {
    conditionCounter++;
    const container = document.getElementById('conditions-container');

    const conditionDiv = document.createElement('div');
    conditionDiv.className = 'condition-card';
    conditionDiv.id = 'condition-' + conditionCounter;

    let whenFieldsHtml = '';
    if (existingData && existingData.when) {
        let whenIndex = 0;
        for (const [field, value] of Object.entries(existingData.when)) {
            whenFieldsHtml += createWhenField(conditionCounter, whenIndex, field, value);
            whenIndex++;
        }
    } else {
        whenFieldsHtml = createWhenField(conditionCounter, 0, '', '');
    }

    conditionDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="bi bi-check-circle"></i> Bedingung #${conditionCounter}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeCondition(${conditionCounter})">
                <i class="bi bi-trash"></i> Entfernen
            </button>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>WENN folgende Bedingungen erfüllt sind:</strong></label>
            <div id="when-fields-${conditionCounter}">
                ${whenFieldsHtml}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addWhenField(${conditionCounter})">
                <i class="bi bi-plus"></i> Weitere Bedingung (UND)
            </button>
        </div>

        <div class="display-field">
            <label class="form-label"><strong>DANN zeige folgenden Text:</strong></label>
            <input type="text"
                   class="form-control"
                   id="display-${conditionCounter}"
                   placeholder="z.B. {bodenplatten_vorplatz_flaeche_ja} m²"
                   value="${existingData && existingData.display ? existingData.display : ''}"
                   required>
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Verwende {feldname} um Werte einzufügen. Beispiel: {bodenplatten_vorplatz_flaeche_ja} m²
            </small>
        </div>
    `;

    container.appendChild(conditionDiv);
}

function createWhenField(conditionId, whenIndex, fieldName = '', fieldValue = '') {
    return `
        <div class="when-field" id="when-${conditionId}-${whenIndex}">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <label class="form-label small">Feldname</label>
                    <input type="text"
                           class="form-control when-field-name"
                           data-condition="${conditionId}"
                           placeholder="z.B. bodenplatten_vorplatz"
                           value="${fieldName}"
                           required>
                </div>
                <div class="col-md-1 mb-2 d-flex align-items-end justify-content-center">
                    <span class="badge bg-primary">=</span>
                </div>
                <div class="col-md-5 mb-2">
                    <label class="form-label small">Wert</label>
                    <input type="text"
                           class="form-control when-field-value"
                           data-condition="${conditionId}"
                           placeholder="z.B. Ja"
                           value="${fieldValue}"
                           required>
                </div>
                <div class="col-md-1 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeWhenField(${conditionId}, ${whenIndex})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function addWhenField(conditionId) {
    const container = document.getElementById('when-fields-' + conditionId);
    const whenIndex = container.children.length;

    const div = document.createElement('div');
    div.innerHTML = createWhenField(conditionId, whenIndex);
    container.appendChild(div.firstElementChild);
}

function removeWhenField(conditionId, whenIndex) {
    const field = document.getElementById(`when-${conditionId}-${whenIndex}`);
    if (field) {
        field.remove();
    }
}

function removeCondition(conditionId) {
    const condition = document.getElementById('condition-' + conditionId);
    if (condition) {
        if (confirm('Möchten Sie diese Bedingung wirklich entfernen?')) {
            condition.remove();
        }
    }
}

// Beim Absenden: Sammle alle Bedingungen und baue JSON
document.getElementById('ruleForm').addEventListener('submit', function(e) {
    const conditions = [];
    const conditionCards = document.querySelectorAll('.condition-card');

    conditionCards.forEach((card, index) => {
        const conditionId = card.id.replace('condition-', '');

        // Sammle "when"-Bedingungen
        const when = {};
        const whenFields = card.querySelectorAll('.when-field');
        whenFields.forEach(whenField => {
            const fieldName = whenField.querySelector('.when-field-name').value.trim();
            const fieldValue = whenField.querySelector('.when-field-value').value.trim();
            if (fieldName && fieldValue) {
                when[fieldName] = fieldValue;
            }
        });

        // Hole "display"-Text
        const display = document.getElementById('display-' + conditionId).value.trim();

        if (Object.keys(when).length > 0 && display) {
            conditions.push({
                when: when,
                display: display
            });
        }
    });

    // Setze JSON in verstecktes Feld
    document.getElementById('conditions_json').value = JSON.stringify(conditions);

    // Validierung
    if (conditions.length === 0) {
        e.preventDefault();
        alert('Bitte fügen Sie mindestens eine Bedingung hinzu!');
        return false;
    }
});

// Tooltips aktivieren
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<?= $this->endSection() ?>
