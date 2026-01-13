<?= $this->extend('layout/minimal') ?>
<?= $this->section('content') ?>

<?php
// Header-Hintergrundfarbe: Branchenfarbe wenn initial vorhanden, sonst Standard aus SiteConfig
$headerBgColor = $initialCategoryColor ?? ($siteConfig->headerBackgroundColor ?? '#6c757d');
// Logo-URL in Variable speichern (empty() funktioniert nicht mit Magic Getters)
$logoUrl = $siteConfig->logoUrl;
$logoHeight = $siteConfig->logoHeightPixel ?? '60';

// Sprachwechsel-URLs
$currentLocale = service('request')->getLocale();
$currentUri = current_url();
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Sprachen mit Flaggen-URLs
$languages = [
    'de' => ['name' => 'Deutsch', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/de.svg'],
    'en' => ['name' => 'English', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.svg'],
    'fr' => ['name' => 'Français', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.svg'],
    'it' => ['name' => 'Italiano', 'flag' => 'https://offertenschweiz.ch/wp-content/plugins/sitepress-multilingual-cms/res/flags/it.svg'],
];
?>

<!-- Header mit Logo, Flaggen und Branchenfarbe -->
<header class="py-3 mb-4" style="background-color: <?= esc($headerBgColor) ?>;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-center gap-4">
            <!-- Flaggen (Desktop) -->
            <div class="d-none d-md-flex gap-2">
                <?php foreach ($languages as $code => $langInfo): ?>
                <?php
                    $langUrl = site_url($code . '/request/start');
                    if ($queryString) $langUrl .= '?' . $queryString;
                ?>
                <a href="<?= esc($langUrl) ?>" title="<?= esc($langInfo['name']) ?>" class="<?= $code === $currentLocale ? 'opacity-100' : 'opacity-75' ?>">
                    <img src="<?= esc($langInfo['flag']) ?>" alt="<?= esc($langInfo['name']) ?>" width="24" height="16" style="border-radius: 2px;">
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Logo -->
            <?php if ($logoUrl): ?>
            <a href="<?= esc($siteConfig->frontendUrl) ?>">
                <img src="<?= esc($logoUrl) ?>"
                     alt="<?= esc($siteConfig->name) ?>"
                     style="max-height: <?= esc($logoHeight) ?>px; max-width: 100%;">
            </a>
            <?php else: ?>
            <a href="<?= esc($siteConfig->frontendUrl) ?>" class="text-white text-decoration-none fs-4 fw-bold">
                <?= esc($siteConfig->name) ?>
            </a>
            <?php endif; ?>

            <!-- Dropdown (Mobile) -->
            <div class="d-md-none">
                <select onchange="location.href=this.value;" class="form-select form-select-sm" style="width: auto; background-color: transparent; color: white; border-color: rgba(255,255,255,0.3);">
                    <?php foreach ($languages as $code => $langInfo): ?>
                    <?php
                        $langUrl = site_url($code . '/request/start');
                        if ($queryString) $langUrl .= '?' . $queryString;
                    ?>
                    <option value="<?= esc($langUrl) ?>" <?= $code === $currentLocale ? 'selected' : '' ?>><?= strtoupper($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</header>

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
        <?php if ($initial): ?>
        <input type="hidden" name="initial" value="<?= esc($initial) ?>">
        <?php endif; ?>

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
