<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people me-2"></i>Weiterempfehlungen</h2>
        <div>
            <a href="<?= site_url('admin/referrals/manual-credit') ?>" class="btn btn-success">
                <i class="bi bi-cash-coin me-1"></i>Manuelle Gutschrift
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/referrals') ?>" id="filterForm">
                <div class="row g-3">
                    <!-- Status -->
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Alle Status</option>
                            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Ausstehend</option>
                            <option value="credited" <?= $filters['status'] === 'credited' ? 'selected' : '' ?>>Gutgeschrieben</option>
                            <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Abgelehnt</option>
                        </select>
                    </div>

                    <!-- Vermittler -->
                    <div class="col-md-3">
                        <label for="referrer_id" class="form-label">Vermittler (Firma):</label>
                        <select name="referrer_id" id="referrer_id" class="form-select">
                            <option value="">Alle Vermittler</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= esc($company['id']) ?>" <?= $filters['referrer_id'] == $company['id'] ? 'selected' : '' ?>>
                                    <?= esc($company['company_name']) ?: esc($company['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Datum von -->
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Datum von:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                               value="<?= esc($filters['date_from']) ?>">
                    </div>

                    <!-- Datum bis -->
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Datum bis:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                               value="<?= esc($filters['date_to']) ?>">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Suche -->
                    <div class="col-md-12">
                        <label for="search" class="form-label">Suche (Firma, E-Mail):</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Suchen..." value="<?= esc($filters['search']) ?>">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter anwenden
                        </button>
                        <a href="<?= site_url('admin/referrals') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Filter zurücksetzen
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Referrals Tabelle -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Weiterempfehlungen (<?= count($referrals) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($referrals)): ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>Keine Weiterempfehlungen gefunden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="referrals-table" class="table table-bordered table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Vermittler</th>
                                <th>Neue Firma</th>
                                <th>E-Mail</th>
                                <th>IP-Adresse</th>
                                <th>Status</th>
                                <th>Betrag</th>
                                <th>Datum</th>
                                <th>Gutgeschrieben von</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $referral): ?>
                                <tr>
                                    <td><strong><?= esc($referral['id']) ?></strong></td>
                                    <td>
                                        <strong><?= esc($referral['referrer_company']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($referral['referrer_username']) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($referral['referred_company_name'])): ?>
                                            <strong><?= esc($referral['referred_company_name']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($referral['referred_email']) ?></td>
                                    <td>
                                        <small><code><?= esc($referral['ip_address'] ?? '-') ?></code></small>
                                        <?php if (!empty($referral['ip_warning'])): ?>
                                            <br>
                                            <span class="badge bg-danger" title="Verdächtig: Gleiche IP wie Vermittler">
                                                <i class="bi bi-exclamation-triangle"></i> IP-Match!
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadge = match($referral['status']) {
                                            'pending' => 'bg-warning text-dark',
                                            'credited' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $statusText = match($referral['status']) {
                                            'pending' => 'Ausstehend',
                                            'credited' => 'Gutgeschrieben',
                                            'rejected' => 'Abgelehnt',
                                            default => $referral['status']
                                        };
                                        ?>
                                        <span class="badge <?= $statusBadge ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= number_format($referral['credit_amount'], 2, '.', "'") ?> CHF</strong>
                                    </td>
                                    <td>
                                        <small><?= date('d.m.Y H:i', strtotime($referral['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($referral['credited_by_username'])): ?>
                                            <small><?= esc($referral['credited_by_username']) ?></small>
                                            <br>
                                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($referral['credited_at'])) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($referral['status'] === 'pending'): ?>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- Gutschrift geben Button mit Modal -->
                                                <button type="button" class="btn btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#creditModal<?= $referral['id'] ?>"
                                                        title="Gutschrift geben">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>

                                                <!-- Ablehnen Button mit Modal -->
                                                <button type="button" class="btn btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal<?= $referral['id'] ?>"
                                                        title="Ablehnen">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>

                                            <!-- Credit Modal -->
                                            <div class="modal fade" id="creditModal<?= $referral['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="post" action="<?= site_url('admin/referrals/give-credit/'.$referral['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Gutschrift geben</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Gutschrift geben für:</p>
                                                                <p><strong><?= esc($referral['referrer_company']) ?></strong></p>
                                                                <p>Neue Firma: <strong><?= esc($referral['referred_email']) ?></strong></p>

                                                                <div class="mb-3">
                                                                    <label for="amount<?= $referral['id'] ?>" class="form-label">Betrag (CHF):</label>
                                                                    <input type="number" step="0.01" name="amount"
                                                                           id="amount<?= $referral['id'] ?>"
                                                                           class="form-control"
                                                                           value="50.00" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="note<?= $referral['id'] ?>" class="form-label">Notiz:</label>
                                                                    <textarea name="note" id="note<?= $referral['id'] ?>"
                                                                              class="form-control" rows="3">Weiterempfehlungs-Gutschrift genehmigt</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                                                <button type="submit" class="btn btn-success">Gutschrift geben</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?= $referral['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="post" action="<?= site_url('admin/referrals/reject/'.$referral['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Weiterempfehlung ablehnen</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Möchten Sie diese Weiterempfehlung wirklich ablehnen?</p>
                                                                <p><strong><?= esc($referral['referrer_company']) ?></strong> → <?= esc($referral['referred_email']) ?></p>

                                                                <div class="mb-3">
                                                                    <label for="reject_note<?= $referral['id'] ?>" class="form-label">Grund (optional):</label>
                                                                    <textarea name="note" id="reject_note<?= $referral['id'] ?>"
                                                                              class="form-control" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                                                <button type="submit" class="btn btn-danger">Ablehnen</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?= $referral['status'] === 'credited' ? 'Abgeschlossen' : 'Abgelehnt' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Manuelle Gutschriften -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Manuelle Gutschriften (<?= count($manualCredits) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($manualCredits)): ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>Keine manuellen Gutschriften vorhanden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="manual-credits-table" class="table table-bordered table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Firma</th>
                                <th>E-Mail</th>
                                <th>Betrag</th>
                                <th>Beschreibung</th>
                                <th>Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manualCredits as $credit): ?>
                                <tr>
                                    <td><strong>#<?= esc($credit['id']) ?></strong></td>
                                    <td>
                                        <strong><?= esc($credit['company_name']) ?: '-' ?></strong>
                                    </td>
                                    <td><?= esc($credit['user_email']) ?></td>
                                    <td class="text-end">
                                        <strong class="text-success">+<?= number_format($credit['amount'], 2, '.', "'") ?> CHF</strong>
                                    </td>
                                    <td>
                                        <small><?= esc($credit['description']) ?: 'Manuelle Gutschrift durch Admin' ?></small>
                                    </td>
                                    <td>
                                        <small><?= date('d.m.Y H:i', strtotime($credit['created_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    <?php if (!empty($referrals)): ?>
    $('#referrals-table').DataTable({
        order: [[7, 'desc']], // Nach Datum absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        },
        columnDefs: [
            { orderable: false, targets: [9] } // Aktionen-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>

    <?php if (!empty($manualCredits)): ?>
    $('#manual-credits-table').DataTable({
        order: [[5, 'desc']], // Nach Datum absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        }
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
