<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1 class="mb-4">Willkommen, <?= esc($user->contact_person ?? 'Nutzer') ?>!</h1>

<p>Status:
    <?php if ($user->active): ?>
        <span class="badge bg-success">Aktiv</span>
    <?php else: ?>
        <span class="badge bg-secondary">Inaktiv</span>
    <?php endif; ?>
</p>

<!-- Hinweis auf automatische Käufe
<div class="alert alert-info mt-4 d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-info-circle fs-4"></i>
    <div>
        Um keine Anfragen mehr zu verpassen, kannst du in deinem
        <a href="/profile" class="alert-link">Profil</a> die Option <strong>„automatisch kaufen“</strong> aktivieren.
    </div>
</div>
 -->

<h2 class="mt-5 mb-3">Gekaufte Angebote</h2>

<?php if(empty($bookings)): ?>
    <p class="text-muted">Du hast noch keine Angebote gekauft.</p>
<?php else: ?>
    <ul class="list-group mb-5">

        <?php foreach($bookings as $booking): ?>
            <li class="list-group-item p-2"><a href="<?=site_url('/offers/mine#detailsview-' . $booking['reference_id']);?>" class="d-flex justify-content-between align-items-center text-decoration-none w-100">
                <div>
                    <strong><?= esc($booking['description']) ?></strong><br>
                    <small class="text-muted">Kaufdatum: <?= date('d.m.Y', strtotime($booking['created_at'])) ?></small>
                </div>
                <span class="badge bg-primary rounded-pill"><?= number_format($booking['amount'], 2) ?> CHF</span></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?= $this->endSection() ?>
