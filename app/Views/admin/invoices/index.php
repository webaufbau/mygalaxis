<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calculator me-2"></i>Finanzen & Buchhaltung</h2>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/invoices') ?>" id="filterForm">
                <div class="row g-3">
                    <!-- Periode von/bis -->
                    <div class="col-md-3">
                        <label for="period_from" class="form-label">Periode von:</label>
                        <input type="month" name="period_from" id="period_from" class="form-control"
                               value="<?= esc($filters['period_from']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="period_to" class="form-label">Periode bis:</label>
                        <input type="month" name="period_to" id="period_to" class="form-control"
                               value="<?= esc($filters['period_to']) ?>">
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

                    <!-- Firmenname Suche -->
                    <div class="col-md-3">
                        <label for="company_name" class="form-label">Firmenname:</label>
                        <input type="text" name="company_name" id="company_name" class="form-control"
                               placeholder="Suchen..." value="<?= esc($filters['company_name']) ?>">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Regionen Multi-Select -->
                    <div class="col-md-6">
                        <label class="form-label">Regionen:</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($allRegions)): ?>
                                <?php foreach ($allRegions as $index => $region): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="regions[]"
                                               value="<?= esc($region['name']) ?>"
                                               id="region_<?= $index ?>"
                                               role="switch"
                                               <?= in_array($region['name'], $filters['regions']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="region_<?= $index ?>">
                                            <?= esc($region['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <small class="text-muted">Keine Regionen verfügbar</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Branchen Multi-Select -->
                    <div class="col-md-6">
                        <label class="form-label">Branchen:</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($allCategories)): ?>
                                <?php foreach ($allCategories as $catKey => $category): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                               value="<?= esc($catKey) ?>"
                                               id="category_<?= esc($catKey) ?>"
                                               role="switch"
                                               <?= in_array($catKey, $filters['categories']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="category_<?= esc($catKey) ?>">
                                            <?= esc($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <small class="text-muted">Keine Kategorien verfügbar</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter anwenden
                        </button>
                        <a href="<?= site_url('admin/invoices') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Filter zurücksetzen
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rechnungen Tabelle -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>Rechnungen
                <?php if (!empty($invoices)): ?>
                    <?php
                    $uniqueCompanies = array_unique(array_column($invoices, 'user_id'));
                    $companyCount = count($uniqueCompanies);
                    ?>
                    <span class="badge bg-light text-dark ms-2">
                        <?= count($invoices) ?> Rechnungen von <?= $companyCount === 1 ? '1 Firma' : $companyCount . ' Firmen' ?>
                    </span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($invoices)): ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>Keine Rechnungen gefunden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="invoices-table" class="table table-bordered table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rechnung-Nr.</th>
                                <th>Firmenname</th>
                                <th>Plattform</th>
                                <th>Periode</th>
                                <th>Käufe</th>
                                <th>Stornierungen</th>
                                <th class="text-end">Netto Betrag</th>
                                <th>Ausgestellt am</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
                                    <td>
                                        <strong><?= esc($invoice['company_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($invoice['email']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        // Bestimme Farbe basierend auf Plattform (wie im Dashboard)
                                        $platformLower = strtolower($invoice['platform']);
                                        $badgeStyle = 'class="bg-primary"'; // Fallback

                                        if (strpos($platformLower, 'offertenschweiz') !== false ||
                                            strpos($platformLower, 'offertenaustria') !== false ||
                                            strpos($platformLower, 'offertendeutschland') !== false) {
                                            // Rosa für Offertenschweiz/Austria/Deutschland
                                            $badgeStyle = 'style="background-color: #E91E63; color: white;"';
                                        } elseif (strpos($platformLower, 'offertenheld') !== false) {
                                            // Lila/Violett für Offertenheld
                                            $badgeStyle = 'style="background-color: #6B5B95; color: white;"';
                                        } elseif (strpos($platformLower, 'renovo') !== false) {
                                            // Schwarz für Renovo
                                            $badgeStyle = 'style="background-color: #212529; color: white;"';
                                        } elseif (strpos($platformLower, 'verwaltungbox') !== false) {
                                            // Blau für Verwaltungbox
                                            $badgeStyle = 'class="bg-primary"';
                                        }
                                        ?>
                                        <span class="badge" <?= $badgeStyle ?>>
                                            <?= esc($platforms[$invoice['platform']] ?? $invoice['platform']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $periodDate = DateTime::createFromFormat('Y-m', $invoice['period']);
                                        echo $periodDate ? $periodDate->format('m/Y') : $invoice['period'];
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= $invoice['purchase_count'] ?? 0 ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($invoice['refund_count'] ?? 0) > 0): ?>
                                            <span class="badge bg-danger"><?= $invoice['refund_count'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php
                                        $amountClass = $invoice['amount'] >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <strong class="<?= $amountClass ?>"><?= number_format($invoice['amount'], 2, ".", "'") ?> <?= esc($invoice['currency']) ?></strong>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($invoice['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= site_url('admin/invoices/download-pdf/'.$invoice['period'].'/'.$invoice['user_id']) ?>"
                                           class="btn btn-sm btn-warning" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
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
    <?php if (!empty($invoices)): ?>
    $('#invoices-table').DataTable({
        order: [[3, 'desc']], // Nach Periode absteigend sortieren
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'
        },
        columnDefs: [
            { orderable: false, targets: [8] } // Aktionen-Spalte nicht sortierbar
        ]
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
