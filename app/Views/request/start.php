<?= $this->extend('layout/minimal') ?>
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

        <!-- Formulare und Projekte -->
        <div class="row mb-4">
            <!-- Formulare (nach Branche gruppiert) -->
            <div class="col-lg-6 mb-4">
                <h5 class="fw-bold mb-3">Dienstleistungen</h5>

                <?php
                // Formulare nach Branche gruppieren
                $groupedForms = [];
                foreach ($forms as $form) {
                    $catKey = $form['category_key'];
                    if (!isset($groupedForms[$catKey])) {
                        $groupedForms[$catKey] = [
                            'name' => $form['category_name'],
                            'color' => $form['category_color'],
                            'forms' => [],
                        ];
                    }
                    $groupedForms[$catKey]['forms'][] = $form;
                }
                ?>

                <?php if (!empty($groupedForms)): ?>
                    <?php
                    // Alle Formulare in eine flache Liste
                    $allForms = [];
                    foreach ($groupedForms as $catKey => $group) {
                        foreach ($group['forms'] as $form) {
                            $allForms[] = $form;
                        }
                    }
                    $total = count($allForms);
                    $half = ceil($total / 2);
                    ?>
                    <div class="row">
                        <div class="col-6">
                            <?php for ($i = 0; $i < $half; $i++): ?>
                                <?php
                                $form = $allForms[$i];
                                $isInitial = ($initial === $form['form_id']);
                                ?>
                                <div class="form-check mb-2">
                                    <?php if ($isInitial): ?>
                                    <input type="hidden" name="forms[]" value="<?= esc($form['form_id']) ?>">
                                    <?php endif; ?>
                                    <input class="form-check-input"
                                           type="checkbox"
                                           <?= $isInitial ? '' : 'name="forms[]"' ?>
                                           value="<?= esc($form['form_id']) ?>"
                                           id="form_<?= esc($form['form_id']) ?>"
                                           <?= $isInitial ? 'checked disabled' : '' ?>
                                           style="width: 1.2em; height: 1.2em;">
                                    <label class="form-check-label ms-2" for="form_<?= esc($form['form_id']) ?>">
                                        <?= esc($form['name']) ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="col-6">
                            <?php for ($i = $half; $i < $total; $i++): ?>
                                <?php
                                $form = $allForms[$i];
                                $isInitial = ($initial === $form['form_id']);
                                ?>
                                <div class="form-check mb-2">
                                    <?php if ($isInitial): ?>
                                    <input type="hidden" name="forms[]" value="<?= esc($form['form_id']) ?>">
                                    <?php endif; ?>
                                    <input class="form-check-input"
                                           type="checkbox"
                                           <?= $isInitial ? '' : 'name="forms[]"' ?>
                                           value="<?= esc($form['form_id']) ?>"
                                           id="form_<?= esc($form['form_id']) ?>"
                                           <?= $isInitial ? 'checked disabled' : '' ?>
                                           style="width: 1.2em; height: 1.2em;">
                                    <label class="form-check-label ms-2" for="form_<?= esc($form['form_id']) ?>">
                                        <?= esc($form['name']) ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Keine Formulare verfügbar.</em></p>
                <?php endif; ?>
            </div>

            <!-- Projekte -->
            <div class="col-lg-6 mb-4">
                <h5 class="fw-bold mb-3">Projekte</h5>
                <?php if (!empty($projects)): ?>
                    <div class="row">
                        <?php foreach ($projects as $project): ?>
                            <?php
                            // Prüfen ob Projekt ein gültiges Formular hat
                            $hasValidForm = !empty($project['form_id']);
                            $form = $hasValidForm ? $categoryManager->getFormById($project['form_id'], $lang) : null;
                            $hasValidForm = $form !== null;
                            ?>
                            <?php if ($hasValidForm): ?>
                            <div class="col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="projects[]"
                                           value="<?= esc($project['slug']) ?>"
                                           id="proj_<?= esc($project['slug']) ?>"
                                           style="width: 1.2em; height: 1.2em;">
                                    <label class="form-check-label ms-2" for="proj_<?= esc($project['slug']) ?>">
                                        <?= esc($project['name']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Keine Projekte verfügbar.</em></p>
                <?php endif; ?>

                <?php
                // Prüfen ob es Projekte ohne gültiges Formular gibt (nur für Admins anzeigen)
                $isAdmin = session()->get('isLoggedIn') && isset(session()->get('user')['role']) && session()->get('user')['role'] === 'admin';
                if ($isAdmin):
                    $invalidProjects = array_filter($projects, function($p) use ($categoryManager, $lang) {
                        if (empty($p['form_id'])) return true;
                        $form = $categoryManager->getFormById($p['form_id'], $lang);
                        return $form === null;
                    });
                    if (!empty($invalidProjects)):
                ?>
                <div class="mt-3 p-2 bg-light rounded small text-muted">
                    <strong><?= count($invalidProjects) ?> Projekte</strong> sind ausgeblendet (kein Formular zugewiesen)
                </div>
                <?php endif; endif; ?>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">
                Weiter <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
