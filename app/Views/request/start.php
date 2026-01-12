<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container py-4">
    <h2 class="mb-3">Offerte anfordern</h2>
    <p class="text-muted mb-4">
        Falls mehrere Dienstleistungen benötigt werden, kannst du unbegrenzt auswählen
        und wir leiten es den entsprechenden Firmen/Branchen weiter.
    </p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/request/submit') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>

        <!-- Branchen und Projekte -->
        <div class="row mb-4">
            <!-- Branchen -->
            <div class="col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Branchen</h5>
                <div class="row">
                    <?php foreach ($categories as $key => $cat): ?>
                        <?php
                        $checked = ($initial === $key) ? 'checked' : '';
                        $color = $cat['color'] ?? '#6c757d';
                        $hasFormLink = !empty($cat['form_link']);
                        ?>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="categories[]"
                                       value="<?= esc($key) ?>"
                                       id="cat_<?= esc($key) ?>"
                                       <?= $checked ?>
                                       <?= !$hasFormLink ? 'disabled' : '' ?>>
                                <label class="form-check-label <?= !$hasFormLink ? 'text-muted' : '' ?>" for="cat_<?= esc($key) ?>">
                                    <span class="badge rounded-circle me-1" style="background-color: <?= esc($color) ?>; width: 10px; height: 10px; display: inline-block;"></span>
                                    <?= esc($cat['name']) ?>
                                    <?php if (!$hasFormLink): ?>
                                        <small class="text-danger">(kein Formular)</small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Projekte -->
            <div class="col-md-6 mb-4">
                <h5 class="fw-bold mb-3">Projekte</h5>
                <?php if (!empty($projects)): ?>
                    <div class="row">
                        <?php foreach ($projects as $project): ?>
                            <div class="col-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="projects[]"
                                           value="<?= esc($project['slug']) ?>"
                                           id="proj_<?= esc($project['slug']) ?>">
                                    <label class="form-check-label" for="proj_<?= esc($project['slug']) ?>">
                                        <?= esc($project['name']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Keine Projekte verfügbar.</em></p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-4">

        <!-- Arbeitsbeginn -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3">Wann sollen die Arbeiten beginnen?</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="work_start_date" class="form-label">Gewünschter Starttermin</label>
                    <input type="date"
                           class="form-control"
                           id="work_start_date"
                           name="work_start_date"
                           value="<?= esc(old('work_start_date')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="flexibility" class="form-label">Flexibilität</label>
                    <select class="form-select" id="flexibility" name="flexibility">
                        <option value="flexible" <?= old('flexibility') === 'flexible' ? 'selected' : '' ?>>Flexibel</option>
                        <option value="1_week" <?= old('flexibility') === '1_week' ? 'selected' : '' ?>>Innerhalb 1 Woche</option>
                        <option value="2_weeks" <?= old('flexibility') === '2_weeks' ? 'selected' : '' ?>>Innerhalb 2 Wochen</option>
                        <option value="1_month" <?= old('flexibility') === '1_month' ? 'selected' : '' ?>>Innerhalb 1 Monat</option>
                        <option value="exact" <?= old('flexibility') === 'exact' ? 'selected' : '' ?>>Genau an diesem Datum</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Kontaktdaten -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3">Kontaktdaten</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstname" class="form-label">Vorname *</label>
                    <input type="text"
                           class="form-control"
                           id="firstname"
                           name="firstname"
                           value="<?= esc(old('firstname')) ?>"
                           required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastname" class="form-label">Nachname *</label>
                    <input type="text"
                           class="form-control"
                           id="lastname"
                           name="lastname"
                           value="<?= esc(old('lastname')) ?>"
                           required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-Mail *</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="<?= esc(old('email')) ?>"
                           required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefon *</label>
                    <input type="tel"
                           class="form-control"
                           id="phone"
                           name="phone"
                           value="<?= esc(old('phone')) ?>"
                           required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="street" class="form-label">Strasse / Nr.</label>
                    <input type="text"
                           class="form-control"
                           id="street"
                           name="street"
                           value="<?= esc(old('street')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="zip" class="form-label">PLZ</label>
                    <input type="text"
                           class="form-control"
                           id="zip"
                           name="zip"
                           value="<?= esc(old('zip')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="city" class="form-label">Ort</label>
                    <input type="text"
                           class="form-control"
                           id="city"
                           name="city"
                           value="<?= esc(old('city')) ?>">
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">* Pflichtfelder</span>
            <button type="submit" class="btn btn-primary btn-lg">
                Weiter <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
