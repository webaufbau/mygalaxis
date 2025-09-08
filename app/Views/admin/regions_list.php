<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Kantone / Regionen</h1>

<p>Diese Übersicht zeigt, welche Kantone und Regionen den Firmen in deren Firmenaccount angezeigt werden. Die angezeigten Regionen sind abhängig vom Land, welches unter den <a href="<?=site_url('/admin/settings');?>">Einstellungen</a> "Welches Land bei Registrierungen setzen?" festgelegt wurde. Firmen können anhand dieser Auswahl ihre Offerten filtern. Die Filterung wirkt sich sowohl auf die Website als auch auf die automatischen E-Mails mit neuen Offerten aus.</p>

<p>Fahren Sie mit der Maus über einen Bezirk, um die enthaltenen Orte zu sehen.</p>

    <div class="mb-4">
        <label class="form-label"><?= esc(lang('Filter.cantonsRegions')) ?></label>

        <div class="d-flex flex-wrap gap-4">
            <?php foreach ($cantons as $cantonName => $canton): ?>
                <div class="kanton-box border rounded p-3" style="min-width: 250px; flex: 1 1 250px;">
                    <div class="form-check mb-2">

                        <label class="form-check-label fw-bold" for="canton-<?= esc($canton['code']) ?>">
                            <?= esc($cantonName) ?> (<?= esc($canton['code']) ?>)
                        </label>
                    </div>

                    <ul class="list-unstyled ms-2 mt-2 mb-0">
                        <?php foreach ($canton['regions'] as $regionName => $region): ?>
                            <li>
                                <div class="form-check">

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

<?= $this->endSection() ?>
