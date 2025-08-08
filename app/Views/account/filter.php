<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Filter.title')) ?></h2>

<form method="post" action="<?= site_url('/filter/save') ?>" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="mb-4">
        <label class="form-label"><?= esc(lang('Filter.categories')) ?></label>
        <?php foreach ($types as $type_id => $cat): ?>
            <?php
            $id = 'cat_' . strtolower(str_replace([' ', '+'], ['_', 'plus'], $cat));
            $checked = in_array($type_id, $user_filters['filter_categories'] ?? []) ? 'checked' : '';
            ?>
            <div class="form-check">
                <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="<?= esc($type_id) ?>" id="<?= esc($id) ?>" <?= $checked ?>>
                <label class="form-check-label" for="<?= esc($id) ?>"><?= esc(lang('Filter.' . $cat)); ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mb-4">
        <label class="form-label"><?= esc(lang('Filter.cantonsRegions')) ?></label>

        <div class="d-flex flex-wrap gap-4">
            <?php foreach ($cantons as $cantonName => $canton): ?>
                <div class="kanton-box border rounded p-3" style="min-width: 250px; flex: 1 1 250px;">
                    <div class="form-check mb-2">
                        <input
                                class="form-check-input p-0"
                                type="checkbox"
                                name="cantons[]"
                                id="canton-<?= esc($canton['code']) ?>"
                                value="<?= esc($cantonName) ?>"
                            <?= in_array($cantonName, $user_filters['filter_cantons'] ?? []) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label fw-bold" for="canton-<?= esc($canton['code']) ?>">
                            <?= esc($cantonName) ?> (<?= esc($canton['code']) ?>)
                        </label>
                    </div>

                    <ul class="list-unstyled ms-2 mt-2 mb-0">
                        <?php foreach ($canton['regions'] as $regionName => $region): ?>
                            <li>
                                <div class="form-check">
                                    <input
                                            class="form-check-input p-0"
                                            type="checkbox"
                                            name="regions[]"
                                            id="region-<?= md5($cantonName . $regionName) ?>"
                                            value="<?= esc($regionName) ?>"
                                        <?= in_array($regionName, $user_filters['filter_regions'] ?? []) ? 'checked' : '' ?>
                                    >
                                    <label
                                            class="form-check-label"
                                            for="region-<?= md5($cantonName . $regionName) ?>"
                                            data-bs-toggle="tooltip"
                                            title="<?= esc($region['communities']) ?>"
                                            style="text-decoration: underline; cursor: help;"
                                    >
                                        <?= esc($regionName) ?>
                                    </label>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mb-4">
        <label for="custom_zip" class="form-label"><?= esc(lang('Filter.customZip')) ?></label>
        <input type="text" name="custom_zip" id="custom_zip" class="form-control" placeholder="<?= esc(lang('Filter.customZipPlaceholder')) ?>" value="<?= esc($user_filters['filter_custom_zip'] ?? '') ?>">
    </div>

    <button type="submit" class="btn btn-primary"><?= esc(lang('Filter.saveButton')) ?></button>
</form>

<script>
    $(document).ready(function () {
        // Tooltips aktivieren
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, {
                placement: 'bottom'
            });
        });

        // Kanton Checkbox togglen
        $('input[name="cantons[]"]').on('change', function () {
            const cantonBox = $(this).closest('.kanton-box');
            const isChecked = $(this).is(':checked');
            cantonBox.find('input[name="regions[]"]').prop('checked', isChecked);
        });

        // Region Checkbox togglen
        $('input[name="regions[]"]').on('change', function () {
            const cantonBox = $(this).closest('.kanton-box');
            const isChecked = $(this).is(':checked');
            if (!isChecked) {
                cantonBox.find('input[name="cantons[]"]').prop('checked', false);
            }
        });
    });
</script>

<?= $this->endSection() ?>
