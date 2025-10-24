<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
// Übersetzungen mit Fallbacks
$backLabel = lang('Offers.backToList');
if (str_starts_with($backLabel, 'Offers.')) $backLabel = 'Zurück zur Übersicht';

$zipLabel = lang('Offers.labels.zip');
if (str_starts_with($zipLabel, 'Offers.')) $zipLabel = 'PLZ';

$cityLabel = lang('Offers.labels.city');
if (str_starts_with($cityLabel, 'Offers.')) $cityLabel = 'Ort';

$typeLabel = lang('Offers.labels.type');
if (str_starts_with($typeLabel, 'Offers.')) $typeLabel = 'Kategorie';

$typeValue = lang('Offers.type.' . $offer['type']);
if (str_starts_with($typeValue, 'Offers.')) {
    // Fallback: Ersten Buchstaben groß, Rest klein, Unterstriche durch Leerzeichen ersetzen
    $typeValue = ucfirst(strtolower(str_replace(['_', '-'], ' ', $offer['type'])));
}

$detailsLabel = lang('Offers.details');
if (str_starts_with($detailsLabel, 'Offers.')) $detailsLabel = 'Details';

$purchasedLabel = lang('Offers.purchased_on');
if (str_starts_with($purchasedLabel, 'Offers.')) $purchasedLabel = 'Gekauft am';

$buyButtonLabel = lang('Offers.buyButton');
if (str_starts_with($buyButtonLabel, 'Offers.')) $buyButtonLabel = 'Kaufen';

$statusSoldLabel = lang('Offers.statusSold');
if (str_starts_with($statusSoldLabel, 'Offers.')) $statusSoldLabel = 'Verkauft';

$statusOutOfStockLabel = lang('Offers.statusOutOfStock');
if (str_starts_with($statusOutOfStockLabel, 'Offers.')) $statusOutOfStockLabel = 'Nicht verfügbar';

$priceNotAvailableLabel = lang('Offers.priceNotAvailable');
if (str_starts_with($priceNotAvailableLabel, 'Offers.')) $priceNotAvailableLabel = 'Preis nicht verfügbar';

$status = $offer['status'] ?? 'available';
$createdDate = new DateTime($offer['created_at']);
$now = new DateTime();
$diffDays = $now->diff($createdDate)->days;

$displayPrice = $offer['price'];
$priceWasDiscounted = false;
if ($offer['discounted_price'] > 0) {
    $displayPrice = $offer['discounted_price'];
    $priceWasDiscounted = true;
}

// Kundeninfos extrahieren wenn gekauft
$customerInfo = [];
if ($isPurchased) {
    $formFields = json_decode($offer['form_fields'] ?? '', true) ?? [];
    $contactKeys = [
        'vorname' => 'Vorname',
        'firstname' => 'Vorname',
        'first_name' => 'Vorname',
        'nachname' => 'Nachname',
        'lastname' => 'Nachname',
        'last_name' => 'Nachname',
        'surname' => 'Nachname',
        'email' => 'E-Mail',
        'e_mail' => 'E-Mail',
        'email_address' => 'E-Mail',
        'mail' => 'E-Mail',
        'e_mail_adresse' => 'E-Mail',
        'telefon' => 'Telefon',
        'telefonnummer' => 'Telefon',
        'phone' => 'Telefon',
        'telephone' => 'Telefon',
        'phone_number' => 'Telefon',
        'tel' => 'Telefon'
    ];

    foreach ($formFields as $key => $value) {
        $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
        if (isset($contactKeys[$normalizedKey]) && !empty($value)) {
            $label = $contactKeys[$normalizedKey];
            if (!isset($customerInfo[$label])) {
                $customerInfo[$label] = $value;
            }
        }
    }
}
?>

<div class="mb-3">
    <a href="<?= site_url('/offers') ?>" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> <?= $backLabel ?>
    </a>
</div>

<h2 class="my-4"><?= esc($offer['title']) ?></h2>

<?php if ($isPurchased && !empty($customerInfo)): ?>
    <!-- Kundeninformationen prominent anzeigen wenn gekauft -->
    <div class="card mb-4 border-success">
        <div class="card-header bg-success bg-opacity-10">
            <h4 class="mb-0"><i class="bi bi-person-circle text-success"></i> Kundeninformationen</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?php foreach ($customerInfo as $label => $value): ?>
                        <p class="mb-2">
                            <strong><?= esc($label) ?>:</strong>
                            <?php if ($label === 'E-Mail'): ?>
                                <a href="mailto:<?= esc($value) ?>"><?= esc($value) ?></a>
                            <?php elseif ($label === 'Telefon'): ?>
                                <a href="tel:<?= esc($value) ?>"><?= esc($value) ?></a>
                            <?php else: ?>
                                <?= esc($value) ?>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong><?= $zipLabel ?>:</strong> <?= esc($offer['zip']) ?><br>
                        <strong><?= $cityLabel ?>:</strong> <?= esc($offer['city']) ?><br>
                        <strong><?= $typeLabel ?>:</strong> <?= $typeValue ?><br>
                    </p>
                    <p class="text-muted mb-0">
                        <small><?= $purchasedLabel ?>: <?= date('d.m.Y', strtotime($offer['purchased_at'])) ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Standard-Ansicht für nicht gekaufte Angebote -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p>
                        <strong><?= $zipLabel ?>:</strong> <?= esc($offer['zip']) ?><br>
                        <strong><?= $cityLabel ?>:</strong> <?= esc($offer['city']) ?><br>
                        <strong><?= $typeLabel ?>:</strong> <?= $typeValue ?><br>
                        <small class="text-muted"><?= date('d.m.Y', strtotime($offer['created_at'])) ?></small>
                    </p>
                </div>

                <div class="col-md-4 text-end d-flex flex-column justify-content-center align-items-end">
                    <div class="mb-3">
                        <?php if ($priceWasDiscounted): ?>
                            <span class="text-decoration-line-through text-muted d-block"><?= number_format($offer['price'], 2) ?> <?= currency() ?></span>
                            <span class="h4 mb-0"><?= number_format($displayPrice, 2) ?> <?= currency() ?></span>
                        <?php else: ?>
                            <span class="h4 mb-0"><?= number_format($displayPrice, 2) ?> <?= currency() ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isPurchased && $status === 'available' && $displayPrice > 0): ?>
                        <a href="<?= site_url('offers/buy/' . $offer['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-cart"></i> <?= $buyButtonLabel ?>
                        </a>
                    <?php elseif ($status === 'sold'): ?>
                        <button type="button" class="btn btn-success disabled"><?= $statusSoldLabel ?></button>
                    <?php elseif ($status === 'out_of_stock'): ?>
                        <button type="button" class="btn btn-danger disabled"><?= $statusOutOfStockLabel ?></button>
                    <?php elseif ($displayPrice <= 0): ?>
                        <button type="button" class="btn btn-secondary disabled"><?= $priceNotAvailableLabel ?></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><?= $detailsLabel ?></h4>
    </div>
    <div class="card-body">
        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => $isPurchased]) ?>
    </div>
</div>

<?= $this->endSection() ?>
