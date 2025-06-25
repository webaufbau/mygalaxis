<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h2>Angebotsdetails #<?= esc($offer['id']) ?></h2>

<p><strong>Typ:</strong> <?= esc(lang('Offers.type.' . $offer['type'])) ?></p>
<p><strong>Status:</strong> <?= esc(lang('Offers.status.' . $offer['status'])) ?></p>
<p><strong>Name:</strong> <?= esc($offer['firstname'] . ' ' . $offer['lastname']) ?></p>
<p><strong>Ort:</strong> <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?></p>

<h4>Formulardaten:</h4>


<?php
$formFields = json_decode($offer['form_fields'], true);

// Liste von Keys, die du **nicht anzeigen** möchtest:
$excludeKeys = ['uuid', 'file_upload', '__submission', 'service_url'];

if (!empty($formFields)):
    ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Feld</th>
            <th>Wert</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($formFields as $key => $value): ?>
            <?php if (in_array($key, $excludeKeys)) continue; ?>

            <tr>
                <td><?= esc(ucfirst(str_replace('_', ' ', $key))) ?></td>
                <td>
                    <?php if (is_array($value)): ?>
                        <ul class="mb-0">
                            <?php foreach ($value as $sub): ?>
                                <li>
                                    <?php if (filter_var($sub, FILTER_VALIDATE_URL)): ?>
                                        <a href="<?= esc($sub) ?>" target="_blank"><?= esc(basename($sub)) ?></a>
                                    <?php else: ?>
                                        <?= esc($sub) ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?= esc($value) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p><em>Keine Formulardaten verfügbar.</em></p>
<?php endif; ?>


<?= $this->endSection() ?>
