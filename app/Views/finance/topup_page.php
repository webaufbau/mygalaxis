<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Finance.topupTitle')) ?></h2>

<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle"></i> <?= esc(lang('Finance.insufficientBalance')) ?>
</div>

<!-- Guthaben-Info -->
<div class="card mb-4 shadow-sm" style="max-width: 500px;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span><?= esc(lang('Finance.currentBalance')) ?>:</span>
            <span class="fs-5 fw-bold text-primary"><?= number_format($currentBalance, 2, ".", "'") ?> CHF</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span><?= esc(lang('Finance.requiredAmount')) ?>:</span>
            <span class="fs-5 fw-bold text-danger"><?= number_format($requiredAmount, 2, ".", "'") ?> CHF</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <span><strong><?= esc(lang('Finance.missingAmount')) ?>:</strong></span>
            <span class="fs-4 fw-bold text-warning"><?= number_format($missingAmount, 2, ".", "'") ?> CHF</span>
        </div>
    </div>
</div>

<!-- Guthaben aufladen -->
<form action="<?= site_url('finance/topup') ?>" method="post" class="mb-5" style="max-width: 500px;">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="amount" class="form-label"><?= esc(lang('Finance.topupAmount')) ?></label>
        <input
            type="number"
            step="0.01"
            min="<?= number_format($missingAmount, 2, '.', '') ?>"
            name="amount"
            id="amount"
            class="form-control"
            value="<?= number_format(ceil($missingAmount), 2, '.', '') ?>"
            required
        >
        <div class="form-text">
            <?= sprintf(lang('Finance.minimumTopupAmount'), number_format($missingAmount, 2, ".", "'")) ?>
        </div>
    </div>

    <div class="mb-3">

            <input type="checkbox" name="accept_agb" id="accept_agb" class="form-radio" value="1" required>
            <label for="accept_agb" class="form-check-label ms-1"><?= lang('General.acceptAGB') ?></label>
            <div class="invalid-feedback">
                <?= lang('Auth.acceptAGBRequired') ?>
            </div>

    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-credit-card"></i> <?= esc(lang('Finance.topupNow')) ?>
        </button>
        <a href="<?= site_url('offers') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> <?= esc(lang('Finance.backToOffers')) ?>
        </a>
    </div>
</form>

<?= $this->endSection() ?>
