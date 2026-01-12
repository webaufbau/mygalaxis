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
            <div class="col-lg-5 mb-4">
                <h5 class="fw-bold mb-3">Branchen</h5>
                <?php foreach ($categories as $key => $cat): ?>
                    <?php
                    $checked = ($initial === $key) ? 'checked' : '';
                    $color = $cat['color'] ?? '#6c757d';
                    $hasFormLink = !empty($cat['form_link']);
                    ?>
                    <?php if ($hasFormLink): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input"
                               type="checkbox"
                               name="categories[]"
                               value="<?= esc($key) ?>"
                               id="cat_<?= esc($key) ?>"
                               <?= $checked ?>
                               style="width: 1.2em; height: 1.2em;">
                        <label class="form-check-label ms-2" for="cat_<?= esc($key) ?>">
                            <span class="d-inline-block rounded-circle me-1" style="background-color: <?= esc($color) ?>; width: 12px; height: 12px;"></span>
                            <?= esc($cat['name']) ?>
                        </label>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Projekte -->
            <div class="col-lg-7 mb-4">
                <h5 class="fw-bold mb-3">Projekte</h5>
                <?php if (!empty($projects)): ?>
                    <div class="row">
                        <?php foreach ($projects as $project): ?>
                            <?php
                            // Prüfen ob Projekt eine Branche hat und diese ein Formular hat
                            $categoryType = $project['category_type'] ?? null;
                            $hasValidBranch = $categoryType && isset($categories[$categoryType]) && !empty($categories[$categoryType]['form_link']);
                            ?>
                            <?php if ($hasValidBranch): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
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
                // Prüfen ob es Projekte ohne gültige Branche gibt
                $invalidProjects = array_filter($projects, function($p) use ($categories) {
                    $ct = $p['category_type'] ?? null;
                    return !$ct || !isset($categories[$ct]) || empty($categories[$ct]['form_link']);
                });
                if (!empty($invalidProjects)):
                ?>
                <div class="mt-3 p-2 bg-light rounded small text-muted">
                    <strong><?= count($invalidProjects) ?> Projekte</strong> sind ausgeblendet (keine Branche mit Formular zugewiesen)
                </div>
                <?php endif; ?>
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
