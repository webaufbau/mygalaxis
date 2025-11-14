<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-trash me-2"></i>Papierkorb</h2>
        <div>
            <span class="badge bg-secondary">Total: <?= count($trashedOffers) ?> gelöschte Anfragen</span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/trash') ?>" id="filterForm">
                <div class="row g-3">
                    <!-- Angebot-Typ -->
                    <div class="col-md-3">
                        <label for="type" class="form-label">Angebot-Typ:</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Alle Typen</option>
                            <?php foreach ($offerTypes as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Plattform Dropdown -->
                    <div class="col-md-3">
                        <label for="platform" class="form-label">Plattform:</label>
                        <select name="platform" id="platform" class="form-select">
                            <option value="">Alle Plattformen</option>
                            <?php foreach ($platforms as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= $filters['platform'] === $key ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Datum von -->
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Gelöscht von:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                               value="<?= esc($filters['date_from']) ?>">
                    </div>

                    <!-- Datum bis -->
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Gelöscht bis:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                               value="<?= esc($filters['date_to']) ?>">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Suche -->
                    <div class="col-md-6">
                        <label for="search" class="form-label">Suche (Name, E-Mail, Firma):</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Suchen..." value="<?= esc($filters['search']) ?>">
                    </div>

                    <!-- Gelöscht von User -->
                    <div class="col-md-6">
                        <label for="deleted_by" class="form-label">Gelöscht von:</label>
                        <select name="deleted_by" id="deleted_by" class="form-select">
                            <option value="">Alle Admins</option>
                            <?php foreach ($adminUsers as $admin): ?>
                                <option value="<?= esc($admin['id']) ?>" <?= $filters['deleted_by'] == $admin['id'] ? 'selected' : '' ?>>
                                    <?= esc($admin['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter anwenden
                        </button>
                        <a href="<?= site_url('admin/trash') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Filter zurücksetzen
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Trash Tabelle -->
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Gelöschte Anfragen</h5>
        </div>
        <div class="card-body">
            <?php if (empty($trashedOffers)): ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>Keine gelöschten Anfragen gefunden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="trash-table" class="table table-bordered table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Typ</th>
                                <th>Kunde</th>
                                <th>E-Mail</th>
                                <th>Stadt</th>
                                <th>Plattform</th>
                                <th>Gelöscht am</th>
                                <th>Gelöscht von</th>
                                <th>Grund</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trashedOffers as $offer): ?>
                                <tr>
                                    <td><strong><?= esc($offer['original_offer_id']) ?></strong></td>
                                    <td>
                                        <?php
                                        // Farb-Badge für Typ
                                        $typeBadgeClass = 'bg-secondary';
                                        switch ($offer['type']) {
                                            case 'move':
                                                $typeBadgeClass = 'bg-info';
                                                break;
                                            case 'cleaning':
                                                $typeBadgeClass = 'bg-success';
                                                break;
                                            case 'move_cleaning':
                                                $typeBadgeClass = 'bg-primary';
                                                break;
                                            case 'plumbing':
                                            case 'electrician':
                                            case 'heating':
                                                $typeBadgeClass = 'bg-warning text-dark';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $typeBadgeClass ?>">
                                            <?= esc($offerTypes[$offer['type']] ?? $offer['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($offer['company'])): ?>
                                            <strong><?= esc($offer['company']) ?></strong><br>
                                        <?php endif; ?>
                                        <small><?= esc($offer['firstname'] ?? '') ?> <?= esc($offer['lastname'] ?? '') ?></small>
                                    </td>
                                    <td><?= esc($offer['email']) ?></td>
                                    <td>
                                        <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Platform color badges (matching other admin pages)
                                        $platformLower = strtolower($offer['platform'] ?? '');
                                        $badgeStyle = 'class="bg-primary"';

                                        if (strpos($platformLower, 'offertenschweiz') !== false ||
                                            strpos($platformLower, 'offertenaustria') !== false ||
                                            strpos($platformLower, 'offertendeutschland') !== false) {
                                            $badgeStyle = 'style="background-color: #E91E63; color: white;"';
                                        } elseif (strpos($platformLower, 'offertenheld') !== false) {
                                            $badgeStyle = 'style="background-color: #6B5B95; color: white;"';
                                        } elseif (strpos($platformLower, 'renovo') !== false) {
                                            $badgeStyle = 'style="background-color: #212529; color: white;"';
                                        } elseif (strpos($platformLower, 'verwaltungbox') !== false) {
                                            $badgeStyle = 'class="bg-primary"';
                                        }
                                        ?>
                                        <span class="badge" <?= $badgeStyle ?>>
                                            <?= esc($platforms[$offer['platform']] ?? $offer['platform']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('d.m.Y H:i', strtotime($offer['deleted_at'])) ?> Uhr</small>
                                    </td>
                                    <td>
                                        <small><?= esc($offer['deleted_by_username']) ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($offer['deletion_reason'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= site_url('admin/trash/view/'.$offer['id']) ?>"
                                               class="btn btn-info" title="Ansehen">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= site_url('admin/trash/restore/'.$offer['id']) ?>"
                                               class="btn btn-success" title="Wiederherstellen"
                                               onclick="return confirm('Möchten Sie diese Anfrage wirklich wiederherstellen?');">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                            <a href="<?= site_url('admin/trash/delete-permanently/'.$offer['id']) ?>"
                                               class="btn btn-danger" title="Endgültig löschen"
                                               onclick="return confirm('ACHTUNG: Diese Anfrage wird ENDGÜLTIG gelöscht und kann nicht wiederhergestellt werden. Fortfahren?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
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
    <?php if (!empty($trashedOffers)): ?>
    $('#trash-table').DataTable({
        order: [[6, 'desc']], // Nach Löschdatum absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        },
        columnDefs: [
            { orderable: false, targets: [9] } // Aktionen-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
