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

<?php if (empty($purchasedOffers)): ?>
    <p class="text-muted"><?= lang('Dashboard.noOffers') ?></p>
<?php else: ?>
    <div class="list-group mb-5">
        <?php foreach ($purchasedOffers as $offer): ?>
            <div class="list-group-item p-3 mb-3 border rounded bg-success bg-opacity-10 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <span class="title fw-bold d-block">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <?= esc($offer['title']) ?>
                        </span>
                        <small class="text-muted"><?= date('d.m.Y', strtotime($offer['created_at'])) ?></small>
                        <br>
                        <a data-bs-toggle="collapse" href="#details-<?= $offer['id'] ?>" role="button" aria-expanded="false" aria-controls="details-<?= $offer['id'] ?>" data-toggle-icon="#toggleIcon-<?= $offer['id'] ?>">
                            <i class="bi bi-chevron-right" id="toggleIcon-<?= $offer['id'] ?>"></i> <?= lang('Offers.showDetails') ?>
                        </a>
                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <div class="small">
                            <?= number_format($offer['price_paid'] ?? $offer['discounted_price'] ?? $offer['price'], 2) ?> CHF
                        </div>
                        <a href="<?= site_url('offers/' . $offer['id']) ?>" class="btn btn-primary btn-sm mt-2"><?= lang('Offers.detailsButton') ?></a>
                    </div>
                </div>

                <div class="collapse mt-3" id="details-<?= $offer['id'] ?>">
                    <div class="card card-body bg-light">
                        <?= view('partials/offer_form_fields_firm', ['offer' => $offer, 'full' => true]) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
        const targetSelector = link.getAttribute('href');
        const iconSelector = link.getAttribute('data-toggle-icon');
        const icon = document.querySelector(iconSelector);
        const target = document.querySelector(targetSelector);

        if (!target || !icon) return;

        target.addEventListener('show.bs.collapse', () => {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-down');
        });

        target.addEventListener('hide.bs.collapse', () => {
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-right');
        });
    });
</script>

<?= $this->endSection() ?>
