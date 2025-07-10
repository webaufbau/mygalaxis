<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4">Filter einstellen</h2>
<form method="post" action="/filter/save" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="mb-4">
        <label for="filter_address" class="form-label">Adresse / Ort</label>
        <input type="text" name="filter_address" id="filter_address" class="form-control">
    </div>

    <div class="mb-4">
        <label class="form-label">Kategorien</label>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="Umzug" id="cat_umzug">
            <label class="form-check-label" for="cat_umzug">Umzug</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="Umzug+Reinigung" id="cat_umzug_reinigung">
            <label class="form-check-label" for="cat_umzug_reinigung">Umzug + Reinigung</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="Reinigung" id="cat_reinigung">
            <label class="form-check-label" for="cat_reinigung">Reinigung</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="Maler" id="cat_maler">
            <label class="form-check-label" for="cat_maler">Maler</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_categories[]" value="Gärtner" id="cat_gaertner">
            <label class="form-check-label" for="cat_gaertner">Gärtner</label>
        </div>
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
        $(document).ready(function() {
            $('[title]').tooltip({'placement':'bottom'});

        });

    </script>


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

    <div class="mb-4">
        <label class="form-label">Services</label>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_absences[]" value="Hausrat einpacken" id="service1">
            <label class="form-check-label" for="service1">Hausrat einpacken</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_absences[]" value="Hausrat anpacken" id="service2">
            <label class="form-check-label" for="service2">Hausrat anpacken</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_absences[]" value="Möbel Aufbau" id="service3">
            <label class="form-check-label" for="service3">Möbel Aufbau</label>
        </div>
        <div class="form-check">
            <input class="form-check-input p-0" type="checkbox" name="filter_absences[]" value="Lampen demontieren" id="service4">
            <label class="form-check-label" for="service4">Lampen demontieren</label>
        </div>
    </div>

    <div class="mb-4">
        <label for="min_rooms" class="form-label">Objekte ab X Zimmer</label>
        <input type="number" name="min_rooms" id="min_rooms" class="form-control" min="1">
    </div>

    <div class="mb-4">
        <label for="custom_zip" class="form-label">Individuelle PLZ</label>
        <input type="text" name="custom_zip" id="custom_zip" class="form-control" placeholder="z.B. 3000, 3012">
    </div>

    <button type="submit" class="btn btn-primary">Filter speichern</button>
</form>

<?= $this->endSection() ?>
