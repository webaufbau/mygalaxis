<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h2>Angebotsdetails #<?= esc($offer['id']) ?></h2>

<p><strong>Typ:</strong> <?= esc(lang('Offers.type.' . $offer['type'])) ?></p>
<p><strong>Status:</strong> <?= esc(lang('Offers.status.' . $offer['status'])) ?></p>
<p><strong>Name:</strong> <?= esc($offer['firstname'] . ' ' . $offer['lastname']) ?></p>
<p><strong>Ort:</strong> <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?></p>

<h4>Preise:</h4>
<table style="border-collapse: collapse; margin-bottom: 20px;">
    <tr>
        <td style="padding: 5px;"><strong>Aktueller Preis (DB):</strong></td>
        <td style="padding: 5px;"><?= esc($offer['price']) ?> CHF</td>
        <td style="padding: 5px; color: #666; font-size: 0.9em;">(gespeicherter Wert)</td>
    </tr>
    <tr>
        <td style="padding: 5px;"><strong>Berechneter Basispreis:</strong></td>
        <td style="padding: 5px;"><?= esc($calculatedPrice) ?> CHF</td>
        <td style="padding: 5px; color: #666; font-size: 0.9em;">(nach aktuellen Regeln)</td>
    </tr>
    <?php if ($discountedPrice < $calculatedPrice): ?>
    <tr>
        <td style="padding: 5px;"><strong>Rabattpreis:</strong></td>
        <td style="padding: 5px; color: green; font-weight: bold;"><?= esc($discountedPrice) ?> CHF</td>
        <td style="padding: 5px; color: green; font-size: 0.9em;">(<?= $discountPercent ?>% Rabatt aktiv)</td>
    </tr>
    <?php endif; ?>
</table>

<?php if ($offer['price'] != $calculatedPrice): ?>
    <p style="background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
        ⚠️ <strong>Hinweis:</strong> Der gespeicherte Preis (<?= esc($offer['price']) ?> CHF) weicht vom berechneten Preis (<?= esc($calculatedPrice) ?> CHF) ab.
        Führe <code>php spark offers:recalculate-price <?= esc($offer['id']) ?></code> aus, um den Preis zu aktualisieren.
    </p>
<?php endif; ?>

<!-- Aufklappbare Berechnung -->
<details style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
    <summary style="cursor: pointer; font-weight: bold; padding: 10px; background-color: #f5f5f5;">🔍 Berechnungsdetails anzeigen</summary>
    <div style="padding: 10px; margin-top: 10px;">
        <h5>Berechnungsgrundlage:</h5>
        <ul>
            <li><strong>Typ:</strong> <?= esc($calculationDetails['type']) ?></li>
            <li><strong>Original-Typ:</strong> <?= esc($calculationDetails['original_type']) ?></li>
            <?php if ($offer['type'] === 'cleaning'): ?>
                <?php if (isset($calculationDetails['base'])): ?>
                    <li><strong>Basis:</strong> <?= esc($calculationDetails['base']) ?></li>
                <?php else: ?>
                    <li><strong>Wohnungsgröße:</strong> <?= esc($calculationDetails['wohnung_groesse'] ?? 'N/A') ?></li>
                    <li><strong>Anzahl Zimmer:</strong> <?= esc($calculationDetails['komplett_anzahlzimmer'] ?? 'N/A') ?></li>
                    <li><strong>Wiederkehrend:</strong> <?= esc($calculationDetails['wiederkehrend'] ?? 'N/A') ?></li>
                    <li><strong>Fensterreinigung:</strong> <?= esc($calculationDetails['fensterreinigung'] ?? 'N/A') ?></li>
                    <li><strong>Aussenfassade:</strong> <?= esc($calculationDetails['aussenfassade'] ?? 'N/A') ?></li>
                <?php endif; ?>
            <?php elseif (in_array($offer['type'], ['move', 'move_cleaning'])): ?>
                <li><strong>Auszug Zimmer:</strong> <?= esc($calculationDetails['auszug_zimmer'] ?? 'N/A') ?></li>
                <li><strong>Arbeitsplätze (Firma):</strong> <?= esc($calculationDetails['auszug_arbeitsplatz_firma'] ?? 'N/A') ?></li>
                <li><strong>Fläche (Firma):</strong> <?= esc($calculationDetails['auszug_flaeche_firma'] ?? 'N/A') ?></li>
            <?php endif; ?>
        </ul>

        <h5>Rabatt-Berechnung:</h5>
        <ul>
            <li><strong>Angebot erstellt:</strong> <?= date('d.m.Y H:i', strtotime($offer['created_at'])) ?> Uhr</li>
            <li><strong>Alter des Angebots:</strong> <?= floor($hoursDiff / 24) ?> Tage, <?= $hoursDiff % 24 ?> Stunden (<?= $hoursDiff ?> Stunden total)</li>
            <li><strong>Anzahl Verkäufe:</strong> <?= $purchaseCount ?></li>
            <?php if ($purchaseCount < 4): ?>
                <?php if ($discountedPrice < $calculatedPrice): ?>
                    <li style="color: green;"><strong>✓ Rabatt aktiv:</strong> <?= $discountPercent ?>% (Preis: <?= $calculatedPrice ?> CHF → <?= $discountedPrice ?> CHF)</li>
                <?php else: ?>
                    <li><strong>Kein Rabatt:</strong> Angebot ist noch nicht alt genug (< 72 Stunden)</li>
                <?php endif; ?>
            <?php else: ?>
                <li><strong>Kein Rabatt:</strong> Maximale Verkäufe (4) erreicht</li>
            <?php endif; ?>
        </ul>
    </div>
</details>

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
