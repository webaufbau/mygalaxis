<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1>Willkommen</h1>

<br>

<p>Status: <strong><?= $user->active ? 'Aktiv' : 'Inaktiv' ?></strong></p>
<p>Kontostand: <strong><?= number_format($user->account_balance, 2) ?> CHF</strong></p>
<!-- Guthaben aufladen -->
<div class="mb-4">
    <a href="/finance/topup" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-plus-circle"></i> Guthaben aufladen
    </a>
</div>


<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Gekaufte Anfragen</h5>
                <p class="card-text">
                    Anzahl: <strong><?= $totalPurchased ?></strong><br>
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
                    </p>
                </div>
            </div>
        </div>


</div>

<!-- Hinweis auf automatische Käufe -->
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Um keine Anfragen mehr zu verpassen, kannst du in deinem <a href="/profile" class="alert-link">Profil</a> die Option <strong>„automatisch kaufen“</strong> aktivieren.
</div>


<h2>Gekaufte Angebote</h2>

<?php if(empty($bookings)): ?>
    <p>Du hast noch keine Angebote gekauft.</p>
<?php else: ?>
    <ul class="list-group">
        <?php foreach($bookings as $booking): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= esc($booking['description']) ?></strong><br>
                    Kaufdatum: <?= date('d.m.Y', strtotime($booking['created_at'])) ?><br>
                </div>
                <span class="badge bg-primary rounded-pill"><?= number_format($booking['amount'], 2) ?> CHF</span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?= $this->endSection() ?>
