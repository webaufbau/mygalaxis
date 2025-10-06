<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1 class="mb-4"><?= esc(sprintf(lang('Dashboard.welcomeUser'), $user->contact_person ?? lang('Dashboard.user'))) ?></h1>


<p><?= lang('Dashboard.status') ?>:
    <?php if ($user->active): ?>
        <span class="badge bg-success"><?= lang('Dashboard.active') ?></span>
    <?php else: ?>
        <span class="badge bg-secondary"><?= lang('Dashboard.inactive') ?></span>
    <?php endif; ?>
</p>

<?php if (empty($bookings)): ?>
    <!-- Anleitung für neue Benutzer -->
    <div class="alert alert-primary mt-4" role="alert">
        <h4 class="alert-heading">
            <i class="bi bi-info-circle me-2"></i><?= esc(sprintf(lang('Dashboard.welcomeHeading'), siteconfig()->name)) ?>
        </h4>
        <p class="mb-3"><?= lang('Dashboard.welcomeText') ?></p>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-funnel text-primary me-2"></i><?= lang('Dashboard.step1Title') ?>
                        </h5>
                        <p class="card-text"><?= lang('Dashboard.step1Text') ?></p>
                        <a href="<?= site_url('filter') ?>" class="btn btn-primary">
                            <i class="bi bi-gear me-1"></i><?= lang('Dashboard.step1Button') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-inbox text-success me-2"></i><?= lang('Dashboard.step2Title') ?>
                        </h5>
                        <p class="card-text"><?= lang('Dashboard.step2Text') ?></p>
                        <a href="<?= site_url('offers') ?>" class="btn btn-success">
                            <i class="bi bi-search me-1"></i><?= lang('Dashboard.step2Button') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3">
        <p class="mb-0">
            <i class="bi bi-lightbulb text-warning me-2"></i>
            <?= lang('Dashboard.welcomeTip') ?>
        </p>
    </div>
<?php endif; ?>

<!-- Hinweis auf automatische Käufe, falls gewünscht
<div class="alert alert-info mt-4 d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-info-circle fs-4"></i>
    <div>
        <?= lang('Dashboard.automaticPurchaseInfo') ?>
        <a href="/profile" class="alert-link"><?= lang('Dashboard.profile') ?></a>
        <?= lang('Dashboard.automaticPurchaseOption') ?>
    </div>
</div>
-->

<h2 class="mt-5 mb-3"><?= lang('Dashboard.purchasedOffers') ?></h2>

<?php if (empty($bookings)): ?>
    <p class="text-muted"><?= lang('Dashboard.noOffers') ?></p>
<?php else: ?>
    <ul class="list-group mb-5">
        <?php foreach ($bookings as $booking): ?>
            <li class="list-group-item p-2">
                <a href="<?= site_url('/offers/mine#detailsview-' . $booking['reference_id']); ?>" class="d-flex justify-content-between align-items-center text-decoration-none w-100">
                    <div>
                        <strong><?= esc($booking['description']) ?></strong><br>
                        <small class="text-muted"><?= lang('Dashboard.purchaseDate') ?>: <?= date('d.m.Y', strtotime($booking['created_at'])) ?></small>
                    </div>
                    <span class="badge bg-primary rounded-pill"><?= number_format($booking['amount'], 2) ?> CHF</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?= $this->endSection() ?>
