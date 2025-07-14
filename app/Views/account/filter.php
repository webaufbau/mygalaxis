<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4">Filter einstellen</h2>
<form method="post" action="/filter/save" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="mb-4">
        <label class="form-label">Kategorien</label>
        <?php foreach ($types as $type_id=>$cat): ?>
            <?php
            $id = 'cat_' . strtolower(str_replace([' ', '+'], ['_', 'plus'], $cat));
            $checked = in_array($type_id, $user_filters['filter_categories'] ?? []) ? 'checked' : '';
            ?>
            <div class="form-check">
                <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="<?= esc($type_id) ?>" id="<?= esc($id) ?>" <?= $checked ?>>
                <label class="form-check-label" for="<?= esc($id) ?>"><?= esc($cat) ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mb-4">
        <label class="form-label">Kantone / Regionen</label>

        <!-- Flexbox-Container für Kantone -->
        <div class="d-flex flex-wrap gap-4">
            <?php foreach ($cantons as $cantonName => $canton): ?>
                <div class="kanton-box border rounded p-3" style="min-width: 250px; flex: 1 1 250px;">
                    <!-- Kanton Checkbox -->
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

                    <!-- Regionen als Unterliste -->
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

    <script>
        $(document).ready(function () {
            // Tooltips aktivieren
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el, {
                    placement: 'bottom'
                });
            });

            // Wenn ein Kanton aktiviert/deaktiviert wird
            $('input[name="cantons[]"]').on('change', function () {
                const cantonBox = $(this).closest('.kanton-box');
                const isChecked = $(this).is(':checked');

                // Alle Regionen in dieser Box aktivieren/deaktivieren
                cantonBox.find('input[name="regions[]"]').prop('checked', isChecked);
            });

            // Wenn eine Region abgewählt wird, Kanton sofort abwählen
            $('input[name="regions[]"]').on('change', function () {
                const cantonBox = $(this).closest('.kanton-box');
                const isChecked = $(this).is(':checked');

                if (!isChecked) {
                    // Eine Region wurde deaktiviert → Kanton auch deaktivieren
                    cantonBox.find('input[name="cantons[]"]').prop('checked', false);
                }
            });
        });
    </script>





<!--
    <div class="mb-4">
        <label class="form-label">Sprachen</label>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_languages[]" value="Deutsch" id="lang_de">
            <label class="form-check-label" for="lang_de">Deutsch</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_languages[]" value="Englisch" id="lang_en">
            <label class="form-check-label" for="lang_en">Englisch</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_languages[]" value="Französisch" id="lang_fr">
            <label class="form-check-label" for="lang_fr">Französisch</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_languages[]" value="Italienisch" id="lang_it">
            <label class="form-check-label" for="lang_it">Italienisch</label>
        </div>
    </div>
-->

    <div class="mb-4">
        <label for="custom_zip" class="form-label">Individuelle PLZ</label>
        <input type="text" name="custom_zip" id="custom_zip" class="form-control" placeholder="z.B. 3000, 3012" value="<?php echo $user_filters['filter_custom_zip']; ?>" >
    </div>

    <button type="submit" class="btn btn-primary">Filter speichern</button>
</form>

<?= $this->endSection() ?>
