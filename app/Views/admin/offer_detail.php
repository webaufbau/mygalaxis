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

    <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true, 'admin' => true]) ?>

<?php else: ?>
    <p><em>Keine Formulardaten verfügbar.</em></p>
<?php endif; ?>


<?= $this->endSection() ?>
