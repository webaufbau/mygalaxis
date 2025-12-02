<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
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

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Basis-Einstellungen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rule_key" class="form-label">
                                    Rule-Key <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="rule_key"
                                       name="rule_key"
                                       value="<?= old('rule_key', $rule['rule_key'] ?? '') ?>"
                                       placeholder="z.B. bodenplatten_vorplatz_gruppe"
                                       required>
                                <small class="form-text text-muted">
                                    Eindeutiger Schlüssel (nur Buchstaben, Zahlen, Unterstriche)
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="offer_type" class="form-label">
                                    Offer-Type / Branche <span class="text-danger">*</span>
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
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="label"
                                   name="label"
                                   value="<?= old('label', $rule['label'] ?? '') ?>"
                                   placeholder="z.B. Bodenplatten: Vorplatz / Garage"
                                   required>
                            <small class="form-text text-muted">
                                Dieser Text wird in Emails und Firmen-Ansichten angezeigt
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">
                                    Sortierung
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="sort_order"
                                       name="sort_order"
                                       value="<?= old('sort_order', $rule['sort_order'] ?? 0) ?>">
                                <small class="form-text text-muted">
                                    Niedrigere Zahl = höhere Priorität
                                </small>
                            </div>

                            <div class="col-md-6 mb-3 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           <?= old('is_active', $rule['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        Rule aktiv
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Versteckte Felder</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="fields_to_hide" class="form-label">
                                Feldnamen (komma-separiert) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="fields_to_hide"
                                   name="fields_to_hide"
                                   value="<?= old('fields_to_hide', isset($rule['fields_to_hide']) ? implode(', ', $rule['fields_to_hide']) : '') ?>"
                                   placeholder="z.B. bodenplatten_vorplatz, bodenplatten_vorplatz_flaeche, bodenplatten_vorplatz_flaeche_ja"
                                   required>
                            <small class="form-text text-muted">
                                Diese Felder werden versteckt und durch die Conditional Group ersetzt
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Bedingungen (JSON)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="conditions_json" class="form-label">
                                Conditions JSON <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control font-monospace"
                                      id="conditions_json"
                                      name="conditions_json"
                                      rows="15"
                                      required><?= old('conditions_json', isset($rule['conditions']) ? json_encode($rule['conditions'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') ?></textarea>
                            <small class="form-text text-muted">
                                JSON-Format für Bedingungen. Siehe Beispiel rechts.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Notizen (optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control"
                                  id="notes"
                                  name="notes"
                                  rows="3"
                                  placeholder="Optionale interne Notizen"><?= old('notes', $rule['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <a href="/admin/field-display-rules" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Abbrechen
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Speichern
                    </button>
                </div>
            </form>
        </div>

        <!-- Hilfe-Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle"></i> Beispiel
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Conditions JSON Format:</h6>
                    <pre class="bg-light p-2 rounded small" style="font-size: 11px;"><code>[
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
]</code></pre>

                    <hr>

                    <h6>Platzhalter:</h6>
                    <p class="small">
                        In <code>display</code> kannst du Platzhalter verwenden:
                        <code>{feldname}</code>
                    </p>

                    <hr>

                    <h6>Ergebnis:</h6>
                    <p class="small">
                        Wenn <code>bodenplatten_vorplatz = "Ja"</code> und
                        <code>bodenplatten_vorplatz_flaeche = "Ja"</code>,
                        wird angezeigt:<br>
                        <strong>"Bodenplatten: Vorplatz / Garage: 25 m²"</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
