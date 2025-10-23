<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Finance.title')) ?></h2>

<!-- Guthaben -->
<div class="card mb-4 shadow-sm" style="max-width: 400px;">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span><strong><?= esc(lang('Finance.currentBalance')) ?>:</strong></span>
        <span class="fs-4 fw-bold text-primary"><?= number_format($balance, 2, ".", "'") ?> CHF</span>
    </div>
</div>

<!-- Info: Direktkauf möglich -->
<div class="alert alert-info mb-4" style="max-width: 600px;">
    <i class="bi bi-info-circle"></i>
    <strong>Hinweis:</strong> Anfragen können auch ohne Guthaben-Aufladung direkt von Ihrer gespeicherten Kreditkarte gekauft werden.
    Beim ersten Kauf werden Ihre Kartendaten sicher bei Worldline (Saferpay) gespeichert. Danach erfolgen alle weiteren Käufe automatisch.
</div>

<!-- Guthaben aufladen -->
<form action="<?= site_url('finance/topup') ?>" method="post" class="mb-5" style="max-width: 350px;">
    <?= csrf_field() ?>

    <div class="d-flex align-items-end mb-3" style="gap: 1rem;">
        <div class="flex-grow-1">
            <label for="amount" class="form-label mb-1"><?= esc(lang('Finance.amountCHF')) ?></label>
            <input type="number" step="0.01" min="1" name="amount" id="amount" class="form-control form-control-sm" value="100" required>
        </div>

        <div>
            <button type="submit" class="btn btn-sm btn-primary" style="white-space: nowrap;">
                <i class="bi bi-plus-circle"></i> <?= esc(lang('Finance.topupButton')) ?>
            </button>
        </div>
    </div>

    <div class="form-c mb-3">
        <input type="checkbox" name="accept_agb" id="accept_agb" class="form-radio" value="1" required>
        <label for="accept_agb" class="form-check-label"><?= lang('General.acceptAGB') ?></label>
        <div class="invalid-feedback">
            <?= lang('Auth.acceptAGBRequired') ?>
        </div>
    </div>
</form>


<!-- Filter -->
<form method="get" class="mb-4 d-flex align-items-end flex-wrap" style="gap: 1rem;">
    <!-- Jahr -->
    <div>
        <label for="year" class="form-label mb-0 me-2"><?= esc(lang('Finance.year')) ?>:</label>
        <select id="year" name="year" class="form-select form-select-sm" onchange="this.form.submit();">
            <option value=""><?= esc(lang('Finance.allYears')) ?></option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y['year'] ?>" <?= $currentYear == $y['year'] ? 'selected' : '' ?>>
                    <?= $y['year'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Monat -->
    <div>
        <label for="month" class="form-label mb-0 me-2"><?= esc(lang('Finance.month')) ?>:</label>
        <select id="month" name="month" class="form-select form-select-sm" onchange="this.form.submit();">
            <option value=""><?= esc(lang('Calendar.allMonths')) ?></option>
            <?php
            $months = lang('Calendar.monthNames');
            for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $currentMonth == $m ? 'selected' : '' ?>>
                    <?= esc($months[$m]) ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <!-- Filter anwenden -->
    <button type="submit" class="btn btn-sm btn-primary"><?= esc(lang('Finance.showButton')) ?></button>

    <!-- PDF-Export -->
    <a href="<?= site_url('finance/pdf?year=' . ($currentYear ?? '') . '&month=' . ($currentMonth ?? '')) ?>"
       class="btn btn-sm btn-secondary">
        <i class="bi bi-file-earmark-pdf"></i> <?= esc(lang('Finance.pdfExport')) ?>
    </a>

    <!-- Monatrechnung -->
    <?php if (!empty($currentMonth) && !empty($currentYear)): ?>
        <a href="<?= site_url('finance/monthly-invoice/' . $currentYear . '/' . $currentMonth) ?>"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-text"></i> Monatsrechnung
        </a>
    <?php endif; ?>
</form>

<!-- Tabelle -->
<?php if (empty($bookings)): ?>
    <div class="alert alert-warning"><?= esc(lang('Finance.noBookings')) ?></div>
<?php else: ?>

    <div class="table-responsive" style="overflow-y: auto;">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light position-sticky top-0" style="z-index: 10;">
            <tr>
                <th><?= esc(lang('Finance.date')) ?></th>
                <th><?= esc(lang('Finance.type')) ?></th>
                <th><?= esc(lang('Finance.description')) ?></th>
                <th class="text-end"><?= esc(lang('Finance.cardPayment')) ?></th>
                <th class="text-end"><?= esc(lang('Finance.balanceChange')) ?></th>
                <th><?= esc(lang('Finance.invoice')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $entry): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
                    <td><?= esc(lang('Offers.credit_type.'.$entry['type'])) ?></td>
                    <td><?= esc($entry['description']) ?></td>
                    <!-- Kartenzahlung Spalte -->
                    <td class="text-end">
                        <?php if (!empty($entry['paid_amount']) && $entry['paid_amount'] > 0): ?>
                            <span class="text-primary">
                                <?= number_format($entry['paid_amount'], 2, ".", "'") ?> CHF
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <!-- Guthaben-Änderung Spalte -->
                    <td class="text-end">
                        <?php if ($entry['amount'] != 0): ?>
                            <span class="<?= $entry['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($entry['amount'], 2, ".", "'") ?> CHF
                            </span>
                        <?php else: ?>
                            <span class="text-muted">0.00 CHF</span>
                        <?php endif; ?>
                    </td>
                    <!-- Rechnung -->
                    <td>
                        <?php if ($entry['type'] === 'offer_purchase'): ?>
                            <a href="<?= site_url('finance/invoice/'.$entry['id']) ?>"
                               class="btn btn-sm btn-secondary">
                                <i class="bi bi-file-earmark-pdf"></i> RE<?=strtoupper(siteconfig()->siteCountry);?><?=$entry['id'];?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        <?= $pager->links('default', 'bootstrap5') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
