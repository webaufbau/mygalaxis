<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1>Willkommen</h1>

<br>

<p>Status: <strong><?= $user->active ? 'Aktiv' : 'Inaktiv' ?></strong></p>
<p>Kontostand: <strong><?= number_format($user->account_balance, 2) ?> CHF</strong></p>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Gekaufte Anfragen</h5>
                <p class="card-text">
                    Anzahl: <strong><?= $totalPurchased ?></strong><br>
                    Gesamtbetrag: <strong><?= number_format($totalSpent, 2) ?> CHF</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <h5 class="card-title">Verpasste Anfragen</h5>
                <p class="card-text">
                    Anzahl: <strong><?= $totalMissed ?></strong><br>
                    Verpasster Wert: <strong><?= number_format($totalMissedCHF, 2) ?> CHF</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Hinweis auf automatische Käufe -->
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Um keine Anfragen mehr zu verpassen, kannst du in deinem <a href="/account/profile" class="alert-link">Profil</a> die Option <strong>„automatisch kaufen“</strong> aktivieren.
</div>


<!-- Guthaben aufladen -->
<div class="mb-4">
    <a href="/finance/topup" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-plus-circle"></i> Guthaben aufladen
    </a>
</div>

<h2>Gekaufte Angebote</h2>

<?php if(empty($purchasedOffers)): ?>
    <p>Du hast noch keine Angebote gekauft.</p>
<?php else: ?>
    <ul class="list-group">
        <?php foreach($purchasedOffers as $offer): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= esc($offer['form_name']) ?></strong><br>
                    Kaufdatum: <?= date('d.m.Y', strtotime($offer['created_at'])) ?><br>
                    Status:
                    <?php
                    switch($offer['status']) {
                        case 'pending': echo 'In Bearbeitung'; break;
                        case 'paid': echo 'Bezahlt'; break;
                        case 'cancelled': echo 'Storniert'; break;
                        case 'refunded': echo 'Erstattet'; break;
                        default: echo 'Unbekannt';
                    }
                    ?>
                </div>
                <span class="badge bg-primary rounded-pill"><?= number_format($offer['price_paid'], 2) ?> CHF</span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?= $this->endSection() ?>
