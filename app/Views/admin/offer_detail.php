<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
// Typ-Namen für Überschrift (wie in E-Mail-Betreffs)
$typeMapping = [
    'move'              => 'Umzug',
    'cleaning'          => 'Reinigung',
    'move_cleaning'     => 'Umzug + Reinigung',
    'painting'          => 'Maler/Gipser',
    'painter'           => 'Maler/Gipser',
    'gardening'         => 'Garten Arbeiten',
    'gardener'          => 'Garten Arbeiten',
    'electrician'       => 'Elektriker Arbeiten',
    'plumbing'          => 'Sanitär Arbeiten',
    'heating'           => 'Heizung Arbeiten',
    'tiling'            => 'Platten Arbeiten',
    'flooring'          => 'Boden Arbeiten',
    'furniture_assembly'=> 'Möbelaufbau',
    'other'             => 'Sonstiges',
];
$typeName = $typeMapping[$offer['type']] ?? ucfirst(str_replace('_', ' ', $offer['type']));
?>
<h2 class="mb-4"><?= esc($typeName) ?> <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?> ID <?= esc($offer['id']) ?> Anfrage</h2>

<!-- Angebotsinformationen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Angebotsdaten</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td><?= esc($offer['id']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Datum/Uhrzeit:</strong></td>
                        <td><?= \CodeIgniter\I18n\Time::parse($offer['created_at'])->setTimezone(app_timezone())->format('d.m.Y - H:i') ?> Uhr</td>
                    </tr>
                    <tr>
                        <td><strong>Plattform:</strong></td>
                        <td>
                            <?php
                            if (!empty($offer['platform'])) {
                                $platform = $offer['platform'];
                                $platform = str_replace('my_', '', $platform);
                                $platform = str_replace('_', '.', $platform);
                                $platform = ucfirst($platform);
                                echo '<span class="badge bg-primary">' . esc($platform) . '</span>';
                            } else {
                                echo '<span class="badge bg-secondary">-</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Typ:</strong></td>
                        <td><?= esc(lang('Offers.type.' . $offer['type'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Kundendaten</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?= esc($offer['firstname'] . ' ' . $offer['lastname']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ort:</strong></td>
                        <td><?= esc($offer['zip']) ?> <?= esc($offer['city']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Verkauft:</strong></td>
                        <td><?= $purchaseCount ?> / <?= \App\Models\OfferModel::MAX_PURCHASES ?> mal</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Preisinformationen Card -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Preisinformationen</h5>
    </div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <tr>
                <td style="width: 250px;"><strong>Aktueller Preis (DB):</strong></td>
                <td><strong><?= esc($offer['price']) ?> CHF</strong></td>
                <td class="text-muted">(gespeicherter Wert)</td>
            </tr>
            <tr>
                <td><strong>Berechneter Basispreis:</strong></td>
                <td><strong><?= esc($calculatedPrice) ?> CHF</strong></td>
                <td class="text-muted">(nach aktuellen Regeln)</td>
            </tr>
            <?php if ($discountedPrice < $calculatedPrice): ?>
            <tr class="table-success">
                <td><strong>Rabattpreis:</strong></td>
                <td><strong class="text-success"><?= esc($discountedPrice) ?> CHF</strong></td>
                <td class="text-success">(<?= $discountPercent ?>% Rabatt aktiv)</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if ($calculatedPrice == 0 && !empty($priceDebugInfo)): ?>
    <p style="background-color: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin-bottom: 20px;">
        ❌ <strong>Preis ist 0 CHF - Diagnose:</strong>
        <ul style="margin: 10px 0 0 0;">
            <?php foreach ($priceDebugInfo as $info): ?>
                <li><?= esc($info) ?></li>
            <?php endforeach; ?>
        </ul>
    </p>
<?php elseif ($offer['price'] != $calculatedPrice): ?>
    <p style="background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
        ⚠️ <strong>Hinweis:</strong> Der gespeicherte Preis (<?= esc($offer['price']) ?> CHF) weicht vom berechneten Preis (<?= esc($calculatedPrice) ?> CHF) ab.
        Führe <code>php spark offers:recalculate-price <?= esc($offer['id']) ?></code> aus, um den Preis zu aktualisieren.
    </p>
<?php endif; ?>

<?php if (!empty($purchases)): ?>
<h4>Käufer (<?= count($purchases) ?>):</h4>
<table class="table table-striped table-sm mb-4">
    <thead>
        <tr>
            <th>Firma / Kontaktperson</th>
            <th>Username</th>
            <th>Bezahlter Preis</th>
            <th>Gekauft am</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($purchases as $purchase): ?>
        <tr>
            <td><?= esc($purchase['contact_person'] ?? 'N/A') ?></td>
            <td>
                <a href="<?= site_url('admin/user/' . $purchase['user_id']) ?>">
                    <?= esc($purchase['username']) ?>
                </a>
            </td>
            <td><?= number_format(abs($purchase['paid_amount'] ?? $purchase['amount']), 2) ?> <?= currency() ?></td>
            <td><?= \CodeIgniter\I18n\Time::parse($purchase['created_at'])->setTimezone(app_timezone())->format('d.m.Y - H:i') ?> Uhr</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Aufklappbare Berechnung -->
<details style="margin-top: 20px; margin-bottom: 30px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
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
            <li><strong>Angebot erstellt:</strong> <?= \CodeIgniter\I18n\Time::parse($offer['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?> Uhr</li>
            <li><strong>Alter des Angebots:</strong> <?= floor($hoursDiff / 24) ?> Tage, <?= $hoursDiff % 24 ?> Stunden (<?= $hoursDiff ?> Stunden total)</li>
            <li><strong>Anzahl Verkäufe:</strong> <?= $purchaseCount ?></li>
            <?php if ($purchaseCount < \App\Models\OfferModel::MAX_PURCHASES): ?>
                <?php if ($discountedPrice < $calculatedPrice): ?>
                    <li style="color: green;"><strong>✓ Rabatt aktiv:</strong> <?= $discountPercent ?>% (Preis: <?= $calculatedPrice ?> CHF → <?= $discountedPrice ?> CHF)</li>
                <?php else: ?>
                    <li><strong>Kein Rabatt:</strong> Angebot ist noch nicht alt genug (< 72 Stunden)</li>
                <?php endif; ?>
            <?php else: ?>
                <li><strong>Kein Rabatt:</strong> Maximale Verkäufe (4) erreicht</li>
            <?php endif; ?>
        </ul>

        <?php if (!empty($priceComponents)): ?>
            <h5>Preisberechnung im Detail:</h5>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Feld</th>
                        <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Wert</th>
                        <th style="padding: 8px; text-align: right; border: 1px solid #ddd;">Preis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($priceComponents as $component): ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;"><strong><?= esc($component['label']) ?></strong></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($component['value']) ?></td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #ddd;"><?= esc($component['price']) ?> CHF</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php if (!empty($maxPriceCapInfo)): ?>
                        <tr style="background-color: #fff3cd;">
                            <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Zwischensumme:</td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #ddd; text-decoration: line-through; color: #666;"><?= esc($maxPriceCapInfo['before_cap']) ?> CHF</td>
                        </tr>
                        <tr style="background-color: #fff3cd;">
                            <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">
                                <strong>⚠️ Maximaler Preis für diese Kategorie:</strong>
                            </td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #ddd; font-weight: bold; color: #856404;"><?= esc($maxPriceCapInfo['cap']) ?> CHF</td>
                        </tr>
                        <tr style="background-color: #e8f4f8; font-weight: bold;">
                            <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Endpreis (nach Cap):</td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #ddd;"><?= esc($calculatedPrice) ?> CHF</td>
                        </tr>
                    <?php else: ?>
                        <tr style="background-color: #e8f4f8; font-weight: bold;">
                            <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Summe:</td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #ddd;"><?= esc($calculatedPrice) ?> CHF</td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        <?php else: ?>
            <h5>Relevante Formularfelder für Preisberechnung:</h5>
            <ul>
                <?php if (isset($formFields['art_objekt'])): ?>
                    <li><strong>Art Objekt:</strong> <?= esc($formFields['art_objekt']) ?></li>
                <?php endif; ?>
                <?php if (isset($formFields['arbeiten_sanitaer'])): ?>
                    <li><strong>Arbeiten Sanitär:</strong> <?= is_array($formFields['arbeiten_sanitaer']) ? implode(', ', $formFields['arbeiten_sanitaer']) : esc($formFields['arbeiten_sanitaer']) ?></li>
                <?php endif; ?>
                <?php if (isset($formFields['auszug_zimmer'])): ?>
                    <li><strong>Auszug Zimmer:</strong> <?= esc($formFields['auszug_zimmer']) ?></li>
                <?php endif; ?>
                <?php if (isset($formFields['auszug_arbeitsplatz_firma'])): ?>
                    <li><strong>Arbeitsplätze:</strong> <?= esc($formFields['auszug_arbeitsplatz_firma']) ?></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</details>

<?php
$formFields = json_decode($offer['form_fields'], true);

// Liste von Keys, die du **nicht anzeigen** möchtest:
$excludeKeys = ['uuid', 'file_upload', '__submission', 'service_url'];
?>

<?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true, 'admin' => true]) ?>

<?php if (empty($formFields)): ?>
    <div class="card">
        <div class="card-body">
            <p class="mb-0"><em>Keine Formulardaten verfügbar.</em></p>
        </div>
    </div>
<?php endif; ?>


<?= $this->endSection() ?>
