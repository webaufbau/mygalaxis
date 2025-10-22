<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<style>
    .stats-card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .badge-status {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .table-actions .btn {
        margin: 0 2px;
    }
</style>

<!-- Header mit Statistiken -->
<div class="row mb-4 mt-4">
    <div class="col">
        <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i>Benutzerverwaltung</h2>
        <p class="text-muted">Übersicht aller registrierten Firmen und Benutzer</p>
    </div>
    <div class="col-auto">
        <a href="/admin/user/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Neue Firma hinzufügen
        </a>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Firmen Total</h6>
                        <h3 class="mb-0"><?= $stats['total_companies'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Aktive Firmen</h6>
                        <h3 class="mb-0"><?= $stats['active_companies'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Ausstehend</h6>
                        <h3 class="mb-0"><?= $stats['pending_companies'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Diesen Monat</h6>
                        <h3 class="mb-0"><?= $stats['this_month'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Alle Status</option>
                    <option value="active" <?= ($filter['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktiv</option>
                    <option value="inactive" <?= ($filter['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inaktiv</option>
                    <option value="pending" <?= ($filter['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Ausstehend</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Kategorie</label>
                <select name="category" class="form-select">
                    <option value="">Alle Kategorien</option>
                    <option value="cleaning" <?= ($filter['category'] ?? '') === 'cleaning' ? 'selected' : '' ?>>Reinigung</option>
                    <option value="move" <?= ($filter['category'] ?? '') === 'move' ? 'selected' : '' ?>>Umzug</option>
                    <option value="painting" <?= ($filter['category'] ?? '') === 'painting' ? 'selected' : '' ?>>Malerarbeiten</option>
                    <option value="gardening" <?= ($filter['category'] ?? '') === 'gardening' ? 'selected' : '' ?>>Gartenpflege</option>
                    <option value="plumbing" <?= ($filter['category'] ?? '') === 'plumbing' ? 'selected' : '' ?>>Sanitär</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">PLZ / Ort</label>
                <input type="text" name="location" class="form-control" placeholder="8000 oder Zürich"
                       value="<?= esc($filter['location'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Suche</label>
                <input type="text" name="search" class="form-control" placeholder="Firmenname..."
                       value="<?= esc($filter['search'] ?? '') ?>">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Filtern
                </button>
                <?php if (!empty(array_filter($filter ?? []))): ?>
                <a href="<?= current_url() ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Filter zurücksetzen
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Firmen Tabelle -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Firmenübersicht</h5>
    </div>
    <div class="card-body">
        <?php if (isset($users) && is_array($users) && count($users) > 0): ?>
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Firma</th>
                        <th>Kontakt</th>
                        <th>Kategorien</th>
                        <th>Regionen</th>
                        <th>Status</th>
                        <th>Abo</th>
                        <th>Registriert</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= esc($user['id']) ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2" style="width: 40px; height: 40px; font-size: 18px;">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= esc($user['company_name'] ?? 'N/A') ?></div>
                                    <small class="text-muted"><?= esc($user['username'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-person me-1 text-muted"></i>
                                <small><?= esc($user['contact_person'] ?? 'N/A') ?></small>
                            </div>
                            <div>
                                <i class="bi bi-envelope me-1 text-muted"></i>
                                <small><?= esc($user['email'] ?? 'N/A') ?></small>
                            </div>
                            <div>
                                <i class="bi bi-telephone me-1 text-muted"></i>
                                <small><?= esc($user['phone'] ?? 'N/A') ?></small>
                            </div>
                        </td>
                        <td>
                            <?php
                            $categories = is_string($user['categories'] ?? '') ? json_decode($user['categories'], true) : ($user['categories'] ?? []);
                            if (!empty($categories)):
                                foreach (array_slice($categories, 0, 2) as $cat):
                            ?>
                                <span class="badge bg-info badge-status me-1"><?= esc($cat) ?></span>
                            <?php
                                endforeach;
                                if (count($categories) > 2):
                            ?>
                                <span class="badge bg-secondary badge-status">+<?= count($categories) - 2 ?></span>
                            <?php
                                endif;
                            else:
                            ?>
                                <span class="text-muted"><small>Keine</small></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $regions = is_string($user['regions'] ?? '') ? json_decode($user['regions'], true) : ($user['regions'] ?? []);
                            if (!empty($regions)):
                                foreach (array_slice($regions, 0, 2) as $reg):
                            ?>
                                <span class="badge bg-light text-dark badge-status me-1"><?= esc($reg) ?></span>
                            <?php
                                endforeach;
                                if (count($regions) > 2):
                            ?>
                                <span class="badge bg-secondary badge-status">+<?= count($regions) - 2 ?></span>
                            <?php
                                endif;
                            else:
                            ?>
                                <span class="text-muted"><small>Keine</small></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($user['active'] ?? 0) == 1): ?>
                                <span class="badge bg-success badge-status">
                                    <i class="bi bi-check-circle me-1"></i>Aktiv
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger badge-status">
                                    <i class="bi bi-x-circle me-1"></i>Inaktiv
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($user['subscription_active'] ?? 0) == 1): ?>
                                <span class="badge bg-primary badge-status">
                                    <i class="bi bi-star-fill me-1"></i>Premium
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary badge-status">Free</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('d.m.Y', strtotime($user['created_at'] ?? 'now')) ?>
                            </small>
                        </td>
                        <td class="text-end table-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/admin/user/edit/<?= $user['id'] ?>"
                                   class="btn btn-outline-primary"
                                   data-bs-toggle="tooltip"
                                   title="Bearbeiten">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/user/view/<?= $user['id'] ?>"
                                   class="btn btn-outline-info"
                                   data-bs-toggle="tooltip"
                                   title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-outline-danger"
                                        data-bs-toggle="tooltip"
                                        title="Löschen"
                                        onclick="if(confirm('Firma <?= esc($user['company_name']) ?> wirklich löschen?')) { window.location.href='/admin/user/delete/<?= $user['id'] ?>'; }">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Keine Firmen gefunden</h5>
            <p class="text-muted">Es wurden keine Firmen mit den aktuellen Filterkriterien gefunden.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        },
        pageLength: 25,
        stateSave: true,
        order: [[7, 'desc']], // Nach Registrierungsdatum sortieren
        columnDefs: [
            { orderable: false, targets: -1 } // Aktionen-Spalte nicht sortierbar
        ],
        responsive: true
    });

    // Tooltips initialisieren
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?= $this->endSection() ?>
