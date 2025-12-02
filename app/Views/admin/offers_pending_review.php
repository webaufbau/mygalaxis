<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-inbox-fill text-warning"></i>
        Anfragen zur Prüfung
        <?php if ($totalPending > 0): ?>
            <span class="badge bg-warning text-dark"><?= $totalPending ?></span>
        <?php endif; ?>
    </h2>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($pendingOffers)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <strong>Alles erledigt!</strong> Keine Anfragen zur Prüfung vorhanden.
    </div>
<?php else: ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Typ</th>
                            <th>Kunde</th>
                            <th>Ort</th>
                            <th>Preis</th>
                            <th style="width: 100px;">Alter</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingOffers as $offer): ?>
                            <tr class="<?= $offer['is_test'] ? 'table-warning' : '' ?>"
                                style="cursor: pointer;"
                                onclick="window.location='/admin/offers/edit/<?= $offer['id'] ?>'">
                                <td>
                                    <strong>#<?= $offer['id'] ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= esc($offer['type_display']) ?></span>
                                </td>
                                <td>
                                    <div><?= esc($offer['customer_name'] ?: '-') ?></div>
                                    <small class="text-muted"><?= esc($offer['customer_email']) ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-muted"></i>
                                    <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        CHF <?= number_format($offer['custom_price'] ?? $offer['discounted_price'] ?? $offer['price'], 0, '.', "'") ?>
                                    </strong>
                                    <?php if ($offer['custom_price']): ?>
                                        <small class="text-muted">(angepasst)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= esc($offer['age']) ?></span>
                                </td>
                                <td>
                                    <?php if ($offer['is_test']): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-flask"></i> Test
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($offer['edited_at'])): ?>
                                        <span class="badge bg-info" title="Bearbeitet am <?= date('d.m.Y H:i', strtotime($offer['edited_at'])) ?>">
                                            <i class="bi bi-pencil"></i> Bearbeitet
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <?= $pager->links('default', 'bootstrap5') ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?= $this->endSection() ?>
