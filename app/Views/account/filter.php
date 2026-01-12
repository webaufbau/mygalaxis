<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Filter.title')) ?></h2>
<p class="text-muted mb-4"><?= esc(lang('Filter.subtitle')) ?></p>

<form method="post" action="<?= site_url('/filter/save') ?>" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="row">
        <!-- Branchen -->
        <div class="col-md-6 mb-4">
            <label class="form-label fw-bold"><?= esc(lang('Filter.categories')) ?></label>
            <div class="row">
                <?php foreach ($types as $type_id => $cat): ?>
                    <?php
                    $id = 'cat_' . strtolower(str_replace([' ', '+', '/'], ['_', 'plus', '_'], $cat));
                    $checked = in_array($type_id, $user_filters['filter_categories'] ?? []) ? 'checked' : '';
                    ?>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="<?= esc($type_id) ?>" id="<?= esc($id) ?>" <?= $checked ?>>
                            <label class="form-check-label" for="<?= esc($id) ?>"><?= esc($cat); ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Projekte -->
        <div class="col-md-6 mb-4">
            <label class="form-label fw-bold"><?= esc(lang('Filter.projects')) ?></label>
            <?php if (!empty($projects)): ?>
                <div class="row">
                    <?php foreach ($projects as $project): ?>
                        <?php
                        $projectId = 'proj_' . $project['slug'];
                        $projectChecked = in_array($project['slug'], $user_filters['filter_projects'] ?? []) ? 'checked' : '';
                        $projectColor = $project['color'] ?? '#6c757d';
                        ?>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input p-0" type="checkbox" name="filter_projects[]" value="<?= esc($project['slug']) ?>" id="<?= esc($projectId) ?>" <?= $projectChecked ?>>
                                <label class="form-check-label" for="<?= esc($projectId) ?>">
                                    <?php if ($projectColor !== '#6c757d'): ?>
                                        <span class="badge" style="background-color: <?= esc($projectColor) ?>; width: 10px; height: 10px; padding: 0; margin-right: 4px; display: inline-block; border-radius: 50%;"></span>
                                    <?php endif; ?>
                                    <?= esc($project['name']) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted small"><em>Noch keine Projekte verfügbar.</em></p>
            <?php endif; ?>
        </div>
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

            // Nur den Status setzen, NICHT triggern
            cantonBox.find('input[name="regions[]"]').prop('checked', isChecked);

            saveFilters(); // direkt speichern
        });

// Region Checkbox togglen
        $('input[name="regions[]"]').on('change', function () {
            const cantonBox = $(this).closest('.kanton-box');
            const isChecked = $(this).is(':checked');

            // Kanton nur abwählen, wenn keine Regionen mehr ausgewählt sind
            if (!isChecked) {
                const anyChecked = cantonBox.find('input[name="regions[]"]:checked').length > 0;
                if (!anyChecked) {
                    cantonBox.find('input[name="cantons[]"]').prop('checked', false);
                }
            }

            saveFilters(); // speichern
        });




        // Custom ZIP sofort speichern
        $('#custom_zip').on('input', function () {
            saveFilters();
        });

        // Kategorien sofort speichern
        $('input[name="filter_categories[]"]').on('change', function () {
            saveFilters();
        });

        // Projekte sofort speichern
        $('input[name="filter_projects[]"]').on('change', function () {
            saveFilters();
        });

        // Funktion für AJAX speichern
        function saveFilters() {
            const formData = $('form').serialize();

            $.ajax({
                url: "<?= site_url('/filter/save') ?>",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        console.log('Filter gespeichert');

                        // CSRF-Token aktualisieren
                        $('input[name="<?= csrf_token() ?>"]').val(res.csrf_hash);
                    } else {
                        console.error('Fehler beim Speichern');
                    }
                },
                error: function (xhr) {
                    console.error('AJAX Fehler', xhr.responseText);
                }
            });
        }

    });

</script>

<?= $this->endSection() ?>
