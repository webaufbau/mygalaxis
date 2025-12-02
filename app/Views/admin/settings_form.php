<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php
use Config\App;

$appConfig = new App();
$supportedLocales = $appConfig->supportedLocales;
$currentLocale = service('request')->getLocale();
?>

<h2 class="mb-4"><i class="bi bi-gear"></i> Einstellungen</h2>

<form method="post" action="<?= current_url() ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php if (!empty($fieldGroups)): ?>
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <?php $firstGroup = true; ?>
            <?php foreach ($fieldGroups as $groupKey => $group): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $firstGroup ? 'active' : '' ?>"
                            id="tab-<?= $groupKey ?>-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-<?= $groupKey ?>"
                            type="button"
                            role="tab"
                            aria-controls="tab-<?= $groupKey ?>"
                            aria-selected="<?= $firstGroup ? 'true' : 'false' ?>">
                        <i class="bi <?= $group['icon'] ?? 'bi-folder' ?>"></i> <?= esc($group['label']) ?>
                    </button>
                </li>
                <?php $firstGroup = false; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabsContent">
            <?php $firstGroup = true; ?>
            <?php foreach ($fieldGroups as $groupKey => $group): ?>
                <div class="tab-pane fade <?= $firstGroup ? 'show active' : '' ?>"
                     id="tab-<?= $groupKey ?>"
                     role="tabpanel"
                     aria-labelledby="tab-<?= $groupKey ?>-tab">

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi <?= $group['icon'] ?? 'bi-folder' ?>"></i> <?= esc($group['label']) ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($group['fields'] as $fieldName): ?>
                                <?php if (!isset($fields[$fieldName])) continue; ?>
                                <?php $meta = $fields[$fieldName]; ?>

                                <div class="mb-3">
                                    <label for="<?= esc($fieldName) ?>" class="form-label">
                                        <strong><?= esc($meta['label']) ?></strong>
                                    </label>

                                    <?php if ($meta['type'] === 'file'): ?>
                                        <?php if ($values->$fieldName !== '' && $values->$fieldName !== null): ?>
                                            <div class="mb-2">
                                                <img src="<?= esc($values->$fieldName) ?>" alt="<?= esc($meta['label']) ?>" style="max-height: 80px;">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control">

                                    <?php elseif ($meta['multilang'] ?? false): ?>
                                        <div>
                                            <ul class="nav nav-tabs nav-tabs-sm" role="tablist">
                                                <?php foreach ($supportedLocales as $index => $lang): ?>
                                                    <li class="nav-item" role="presentation">
                                                        <button
                                                            class="nav-link <?= $lang === $currentLocale ? 'active' : '' ?>"
                                                            id="<?= esc($fieldName.'-'.$lang.'-tab') ?>"
                                                            data-bs-toggle="tab"
                                                            data-bs-target="#<?= esc($fieldName.'_'.$lang) ?>"
                                                            type="button" role="tab"
                                                            aria-controls="<?= esc($fieldName.'_'.$lang) ?>"
                                                            aria-selected="<?= $lang === $currentLocale ? 'true' : 'false' ?>">
                                                            <?= esc(strtoupper($lang)) ?>
                                                        </button>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="tab-content border border-top-0 p-2">
                                                <?php foreach ($supportedLocales as $lang): ?>
                                                    <div
                                                        class="tab-pane fade <?= $lang === $currentLocale ? 'show active' : '' ?>"
                                                        id="<?= esc($fieldName.'_'.$lang) ?>"
                                                        role="tabpanel"
                                                        aria-labelledby="<?= esc($fieldName.'-'.$lang.'-tab') ?>">
                                                        <input
                                                            type="<?= esc($meta['type']) ?>"
                                                            name="<?= esc($fieldName) ?>[<?= esc($lang) ?>]"
                                                            value="<?= esc($values->get($fieldName, $lang) ?? '') ?>"
                                                            class="form-control"
                                                        >
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                    <?php elseif ($meta['type'] === 'checkbox'): ?>
                                        <div class="form-check form-switch ps-0">
                                            <input type="hidden" name="<?= esc($fieldName) ?>" value="0">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" value="1" <?= $values->$fieldName ? 'checked' : '' ?> class="form-check-input ms-0" role="switch" style="width: 3em; height: 1.5em;">
                                                <label class="form-check-label" for="<?= esc($fieldName) ?>">
                                                    <?= $values->$fieldName ? '<span class="badge bg-success">Aktiviert</span>' : '<span class="badge bg-secondary">Deaktiviert</span>' ?>
                                                </label>
                                            </div>
                                            <?php if (isset($meta['help'])): ?>
                                                <small class="form-text text-muted d-block mt-2"><?= esc($meta['help']) ?></small>
                                            <?php endif; ?>
                                        </div>

                                    <?php elseif ($meta['type'] === 'textarea'): ?>
                                        <textarea name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control" rows="3"><?= esc($values->$fieldName) ?></textarea>

                                    <?php elseif ($meta['type'] === 'dropdown'): ?>
                                        <select name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-select">
                                            <?php foreach ($meta['options'] as $key => $label): ?>
                                                <option value="<?= esc($key) ?>" <?= ($values->$fieldName === $key) ? 'selected' : '' ?>>
                                                    <?= esc($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php elseif ($meta['type'] === 'color'): ?>
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="color" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control form-control-color" value="<?= esc($values->$fieldName ?: '#000000') ?>">
                                            <input type="text" class="form-control" value="<?= esc($values->$fieldName) ?>" readonly style="max-width: 100px;">
                                        </div>

                                    <?php else: ?>
                                        <input
                                            type="<?= esc($meta['type']) ?>"
                                            name="<?= esc($fieldName) ?>"
                                            id="<?= esc($fieldName) ?>"
                                            class="form-control"
                                            value="<?= esc($values->$fieldName) ?>"
                                            <?= isset($meta['placeholder']) ? 'placeholder="'.esc($meta['placeholder']).'"' : '' ?>
                                        >
                                    <?php endif; ?>

                                    <?php if (isset($meta['help']) && $meta['type'] !== 'checkbox'): ?>
                                        <small class="form-text text-muted"><?= esc($meta['help']) ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php $firstGroup = false; ?>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- Fallback: Alle Felder ohne Gruppierung -->
        <?php foreach ($fields as $fieldName => $meta): ?>
            <div class="mb-3">
                <label for="<?= esc($fieldName) ?>"><?= esc($meta['label']) ?></label>

                <?php if ($meta['type'] === 'file'): ?>
                    <?php if ($values->$fieldName !== ''): ?>
                        <div class="mb-2">
                            <img src="<?= esc($values->$fieldName) ?>" alt="<?= esc($meta['label']) ?>" style="max-height: 80px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control">

                <?php elseif ($meta['type'] === 'checkbox'): ?>
                    <div class="form-check form-switch ps-0">
                        <input type="hidden" name="<?= esc($fieldName) ?>" value="0">
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" value="1" <?= $values->$fieldName ? 'checked' : '' ?> class="form-check-input ms-0" role="switch" style="width: 3em; height: 1.5em;">
                            <label class="form-check-label" for="<?= esc($fieldName) ?>">
                                <?= $values->$fieldName ? '<span class="badge bg-success">Aktiviert</span>' : '<span class="badge bg-secondary">Deaktiviert</span>' ?>
                            </label>
                        </div>
                    </div>

                <?php elseif ($meta['type'] === 'textarea'): ?>
                    <textarea name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control"><?= esc($values->$fieldName) ?></textarea>

                <?php else: ?>
                    <input
                        type="<?= esc($meta['type']) ?>"
                        name="<?= esc($fieldName) ?>"
                        id="<?= esc($fieldName) ?>"
                        class="form-control"
                        value="<?= esc($values->$fieldName) ?>"
                    >
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg"></i> Speichern
        </button>
    </div>
</form>

<script>
// Update badge when switch is toggled
document.querySelectorAll('.form-switch input[type="checkbox"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const label = this.closest('.form-switch').querySelector('.form-check-label');
        if (label) {
            if (this.checked) {
                label.innerHTML = '<span class="badge bg-success">Aktiviert</span>';
            } else {
                label.innerHTML = '<span class="badge bg-secondary">Deaktiviert</span>';
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
