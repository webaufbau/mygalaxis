<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
use Config\App;

$appConfig = new App();
$supportedLocales = $appConfig->supportedLocales;
$currentLocale = service('request')->getLocale();
?>

<form method="post" action="<?= current_url() ?>">
    <?= csrf_field() ?>

    <?php foreach ($fields as $fieldName => $meta): ?>
        <div class="mb-3">
            <label for="<?= esc($fieldName) ?>"><?= esc($meta['label']) ?></label>

            <?php if ($meta['type'] === 'file'): ?>
                <?php if (!empty($values->$fieldName)): ?>
                    <div class="mb-2">
                        <img src="<?= esc($values->$fieldName) ?>" alt="<?= esc($meta['label']) ?>" style="max-height: 80px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control">

            <?php elseif ($meta['multilang'] ?? false): ?>
                <div>
                    <label><?= esc($meta['label']) ?></label>
                    <ul class="nav nav-tabs" role="tablist">
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
                    <div class="tab-content">
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
                <input type="checkbox" name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" value="1" <?= $values->$fieldName ? 'checked' : '' ?> >
            <?php elseif ($meta['type'] === 'textarea'): ?>
                <textarea name="<?= esc($fieldName) ?>" id="<?= esc($fieldName) ?>" class="form-control"><?= esc($values->$fieldName) ?></textarea>
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
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary">Speichern</button>
</form>


<?= $this->endSection() ?>
