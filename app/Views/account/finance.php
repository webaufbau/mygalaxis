<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title) ?></h2>

<!-- Guthaben -->
<div class="card mb-4 shadow-sm" style="max-width: 400px;">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span><strong>Aktuelles Guthaben:</strong></span>
        <span class="fs-4 fw-bold text-primary"><?= number_format($balance, 2, ".", "'") ?> CHF</span>
    </div>
</div>

<!-- Guthaben aufladen -->
<form action="<?= site_url('finance/topup') ?>" method="post" class="mb-5 d-flex flex-wrap align-items-end" style="gap: 1rem; max-width: 350px;">
    <?= csrf_field() ?>

    <div class="flex-grow-1">
        <label for="amount" class="form-label mb-1">Betrag (CHF)</label>
        <input type="number" step="0.01" min="20" name="amount" id="amount" class="form-control form-control-sm" value="100" required>
    </div>

    <div>
        <button type="submit" class="btn btn-sm btn-primary" style="white-space: nowrap;">
            <i class="bi bi-plus-circle"></i> Guthaben aufladen
        </button>
    </div>
</form>

<!-- Tabelle -->
<?php if (empty($bookings)): ?>
    <div class="alert alert-warning">Keine Buchungen gefunden.</div>
<?php else: ?>

    <!-- Filter -->
    <form method="get" class="mb-4 d-flex align-items-center" style="gap: 1rem; max-width: 250px;">
        <label for="year" class="form-label mb-0 me-2">Jahr:</label>
        <select id="year" name="year" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Alle Jahre</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y['year'] ?>" <?= $currentYear == $y['year'] ? 'selected' : '' ?>>
                    <?= $y['year'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="table-responsive" style="overflow-y: auto;">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light position-sticky top-0" style="z-index: 10;">
            <tr>
                <th>Datum</th>
                <th>Typ</th>
                <th>Beschreibung</th>
                <th class="text-end">Betrag</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $entry): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
                    <td><?= Lang('Offers.credit_type.'.$entry['type']) ?></td>
                    <td><?= esc($entry['description']) ?></td>
                    <td class="text-end <?= $entry['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($entry['amount'], 2, ".", "'") ?> CHF
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
