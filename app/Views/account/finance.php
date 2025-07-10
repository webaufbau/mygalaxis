<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title) ?></h2>

<!-- Guthaben -->
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <strong>Aktuelles Guthaben:</strong>
    <span class="fs-5"><?= number_format($balance, 2) ?> CHF</span>
</div>

<!-- Filter -->
<form method="get" class="row g-3 mb-4">
    <div class="col-auto">
        <select name="year" class="form-select" onchange="this.form.submit()">
            <option value="">Alle Jahre</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y['year'] ?>" <?= $currentYear == $y['year'] ? 'selected' : '' ?>>
                    <?= $y['year'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<!-- Guthaben aufladen -->
<div class="mb-4">
    <a href="/finance/topup" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-plus-circle"></i> Guthaben aufladen
    </a>
</div>

<!-- Tabelle -->
<?php if (empty($bookings)): ?>
    <div class="alert alert-warning">Keine Buchungen gefunden.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
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
                    <td><?= ucfirst($entry['type']) ?></td>
                    <td><?= esc($entry['description']) ?></td>
                    <td class="text-end <?= $entry['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($entry['amount'], 2) ?> CHF
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        <?= $pager->links('default', 'bootstrap') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
