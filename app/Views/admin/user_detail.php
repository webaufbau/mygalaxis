<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php
// Typ-Namen Mapping
$typeMapping = [
    'move'              => 'Umzug',
    'cleaning'          => 'Reinigung',
    'move_cleaning'     => 'Umzug + Reinigung',
    'painting'          => 'Maler/Gipser',
    'painter'           => 'Maler/Gipser',
    'gardening'         => 'Garten Arbeiten',
    'gardener'          => 'Garten Arbeiten',
    'electrician'       => 'Elektriker Arbeiten',
    'plumbing'          => 'Sanitär Arbeiten',
    'heating'           => 'Heizung Arbeiten',
    'tiling'            => 'Platten Arbeiten',
    'flooring'          => 'Boden Arbeiten',
    'furniture_assembly'=> 'Möbelaufbau',
    'other'             => 'Sonstiges',
];

// Plattform-Namen Mapping
$platformMapping = [
    'my_offertenheld_ch'     => 'Offertenheld.ch',
    'my_offertenschweiz_ch'  => 'Offertenschweiz.ch',
    'my_renovo24_ch'         => 'Renovo24.ch',
];

// Plattform-Farben wie im Dashboard
$platformName = $platformMapping[$user->platform ?? ''] ?? ($user->platform ?? '-');
$platformLower = strtolower($user->platform ?? '');

$badgeStyle = 'class="badge bg-secondary"'; // Fallback
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
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">
            <?= esc($user->company_name ?? 'Benutzer') ?> - Details
            <?php if ($user->is_blocked): ?>
                <span class="badge bg-danger ms-2"><i class="bi bi-ban"></i> BLOCKIERT</span>
            <?php endif; ?>
            <?php if ($user->is_test): ?>
                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-flask"></i> TESTFIRMA</span>
            <?php endif; ?>
        </h2>
    </div>
    <div>
        <?php if ($user->is_test): ?>
            <a href="<?= site_url('admin/user/toggle-test/' . $user->id) ?>" class="btn btn-outline-warning" onclick="return confirm('Testfirma-Status entfernen? Die Firma erhält dann keine Testanfragen mehr.');">
                <i class="bi bi-flask"></i> Test-Status entfernen
            </a>
        <?php else: ?>
            <a href="<?= site_url('admin/user/toggle-test/' . $user->id) ?>" class="btn btn-warning" onclick="return confirm('Als Testfirma markieren? Die Firma erhält dann ALLE Anfragen (Test + Normal).');">
                <i class="bi bi-flask"></i> Als Testfirma
            </a>
        <?php endif; ?>
        <?php if ($user->is_blocked): ?>
            <a href="<?= site_url('admin/user/toggle-block/' . $user->id) ?>" class="btn btn-success" onclick="return confirm('Firma wirklich deblockieren?');">
                <i class="bi bi-unlock"></i> Deblockieren
            </a>
        <?php else: ?>
            <a href="<?= site_url('admin/user/toggle-block/' . $user->id) ?>" class="btn btn-danger" onclick="return confirm('Firma wirklich blockieren? Die Firma kann sich nicht mehr einloggen und erhält keine Anfragen mehr.');">
                <i class="bi bi-ban"></i> Blockieren
            </a>
        <?php endif; ?>
        <a href="<?= site_url('admin/user/form/' . $user->id . '?model=user') ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-pencil"></i> Bearbeiten
        </a>
        <a href="<?= site_url('admin/user') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Zurück
        </a>
    </div>
</div>

<!-- Tab Navigation -->
<ul class="nav nav-tabs mb-4" id="userDetailTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab" aria-controls="company" aria-selected="true">
            <i class="bi bi-building"></i> Firmendaten
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab" aria-controls="offers" aria-selected="false">
            <i class="bi bi-cart-check"></i> Anfragen <?php if (!empty($purchases)): ?>(<?= count($purchases) ?>)<?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="false">
            <i class="bi bi-tags"></i> Branchen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="regions-tab" data-bs-toggle="tab" data-bs-target="#regions" type="button" role="tab" aria-controls="regions" aria-selected="false">
            <i class="bi bi-geo-alt"></i> Regionen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#finance" type="button" role="tab" aria-controls="finance" aria-selected="false">
            <i class="bi bi-wallet2"></i> Finanzen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
            <i class="bi bi-star"></i> Bewertungen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="agenda-tab" data-bs-toggle="tab" data-bs-target="#agenda" type="button" role="tab" aria-controls="agenda" aria-selected="false">
            <i class="bi bi-calendar3"></i> Agenda
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">
            <i class="bi bi-sticky"></i> Notizen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab" aria-controls="invoices" aria-selected="false">
            <i class="bi bi-receipt"></i> Monatsrechnungen <?php if (!empty($invoices)): ?>(<?= count($invoices) ?>)<?php endif; ?>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="userDetailTabsContent">

    <!-- Tab 1: Firmendaten -->
    <div class="tab-pane fade show active" id="company" role="tabpanel" aria-labelledby="company-tab">

        <!-- Benutzerinformationen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Firmendaten</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td style="width: 200px;"><strong>Firmenname:</strong></td>
                        <td><?= esc($user->company_name ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Kontaktperson:</strong></td>
                        <td><?= esc($user->contact_person ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td><strong>UID:</strong></td>
                        <td><?= esc($user->company_uid ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Adresse:</strong></td>
                        <td>
                            <?= esc($user->company_street ?? '-') ?><br>
                            <?= esc($user->company_zip ?? '') ?> <?= esc($user->company_city ?? '') ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Website:</strong></td>
                        <td><?= esc($user->company_website ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td><strong>E-Mail:</strong></td>
                        <td><?= esc($user->company_email ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telefon:</strong></td>
                        <td><?= esc($user->company_phone ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Account-Daten</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td style="width: 200px;"><strong>ID:</strong></td>
                        <td><?= esc($user->id) ?></td>
                    </tr>
                    <tr>
                        <td><strong>E-Mail (Login):</strong></td>
                        <td><?= esc($user->email) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Plattform:</strong></td>
                        <td>
                            <span class="badge" <?= $badgeStyle ?>><?= esc($platformName) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Aktiv:</strong></td>
                        <td>
                            <?php if ($user->active): ?>
                                <span class="badge bg-success">Ja</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Nein</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Blockiert:</strong></td>
                        <td>
                            <?php if ($user->is_blocked): ?>
                                <span class="badge bg-danger"><i class="bi bi-ban"></i> Ja - Blockiert</span>
                            <?php else: ?>
                                <span class="badge bg-success">Nein</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Testfirma:</strong></td>
                        <td>
                            <?php if ($user->is_test): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-flask"></i> Ja - Testfirma</span>
                                <br><small class="text-muted">Erhält alle Anfragen (Test + Normal)</small>
                            <?php else: ?>
                                <span class="badge bg-success">Nein</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Erstellt am:</strong></td>
                        <td><?= \CodeIgniter\I18n\Time::parse($user->created_at)->setTimezone(app_timezone())->format('d.m.Y - H:i') ?> Uhr</td>
                    </tr>
                    <tr>
                        <td><strong>Letzter Login:</strong></td>
                        <td><?= $user->last_active ? \CodeIgniter\I18n\Time::parse($user->last_active)->setTimezone(app_timezone())->format('d.m.Y - H:i') . ' Uhr' : '-' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Automatischer Kauf:</strong></td>
                        <td>
                            <?php if ($user->auto_purchase): ?>
                                <span class="badge bg-success">Aktiviert</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Deaktiviert</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

        <!-- Kontostand -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Kontostand</h5>
            </div>
            <div class="card-body">
                <h3 class="mb-0">
                    <?php if ($balance >= 0): ?>
                        <span class="text-success"><?= number_format($balance, 2) ?> CHF</span>
                    <?php else: ?>
                        <span class="text-danger"><?= number_format($balance, 2) ?> CHF</span>
                    <?php endif; ?>
                </h3>
            </div>
        </div>

    </div>
    <!-- Ende Tab 1: Firmendaten -->

    <!-- Tab 2: Anfragen (Gekaufte Angebote) -->
    <div class="tab-pane fade" id="offers" role="tabpanel" aria-labelledby="offers-tab">
<?php if (!empty($purchases)): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-cart-check"></i> Gekaufte Angebote (<?= count($purchases) ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm" id="purchasesTable">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Angebot</th>
                        <th>Plattform</th>
                        <th>Typ</th>
                        <th>Ort</th>
                        <th class="text-end">Preis</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <?php if (!empty($purchase['offer'])): ?>
                            <?php
                            $offer = $purchase['offer'];
                            $typeName = $typeMapping[$offer['type']] ?? ucfirst(str_replace('_', ' ', $offer['type']));
                            $offerTitle = $typeName . ' ' . $offer['zip'] . ' ' . $offer['city'] . ' ID ' . $offer['id'] . ' Anfrage';

                            // Plattform-Farben
                            $platformLower = strtolower($offer['platform'] ?? '');
                            $badgeStyle = 'class="badge bg-secondary"';
                            if (strpos($platformLower, 'offertenschweiz') !== false ||
                                strpos($platformLower, 'offertenaustria') !== false ||
                                strpos($platformLower, 'offertendeutschland') !== false) {
                                $badgeStyle = 'style="background-color: #E91E63; color: white;"';
                            } elseif (strpos($platformLower, 'offertenheld') !== false) {
                                $badgeStyle = 'style="background-color: #6B5B95; color: white;"';
                            } elseif (strpos($platformLower, 'renovo') !== false) {
                                $badgeStyle = 'style="background-color: #212529; color: white;"';
                            }

                            $platformDisplay = $offer['platform'] ?? '';
                            $platformDisplay = str_replace('my_', '', $platformDisplay);
                            $platformDisplay = str_replace('_', '.', $platformDisplay);
                            $platformDisplay = ucfirst($platformDisplay);
                            ?>
                            <tr>
                                <td><?= \CodeIgniter\I18n\Time::parse($purchase['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?></td>
                                <td>
                                    <strong><?= esc($offerTitle) ?></strong><br>
                                    <small class="text-muted"><?= esc($offer['firstname']) ?> <?= esc($offer['lastname']) ?></small>
                                </td>
                                <td>
                                    <?php if ($platformDisplay): ?>
                                        <span class="badge" <?= $badgeStyle ?>><?= esc($platformDisplay) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($typeName) ?></td>
                                <td><?= esc($offer['zip']) ?> <?= esc($offer['city']) ?></td>
                                <td class="text-end">
                                    <strong><?= number_format(abs($purchase['paid_amount'] ?? $purchase['amount']), 2) ?> CHF</strong>
                                </td>
                                <td>
                                    <a href="<?= site_url('admin/offer/' . $offer['id']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
        $(document).ready(function() {
            $('#purchasesTable').DataTable({
                "order": [[0, "desc"]], // Sortiere nach Datum absteigend
                "pageLength": 25,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json"
                }
            });
        });
        </script>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Dieser Benutzer hat noch keine Angebote gekauft.
        </div>
        <?php endif; ?>

    </div>
    <!-- Ende Tab 2: Anfragen -->

    <!-- Tab 3: Branchen -->
    <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-tags"></i> Branchen-Filter</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($filterCategories)): ?>
                    <p class="text-muted mb-3">Dieser Benutzer erhält Anfragen für folgende Branchen:</p>
                    <div class="row">
                        <?php foreach ($filterCategories as $category): ?>
                            <?php
                            $categoryName = $typeMapping[trim($category)] ?? ucfirst(str_replace('_', ' ', trim($category)));
                            ?>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <span class="badge bg-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                    <i class="bi bi-check-circle me-1"></i> <?= esc($categoryName) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Keine Branchen-Filter festgelegt. Dieser Benutzer erhält keine Anfragen.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Ende Tab 3: Branchen -->

    <!-- Tab 4: Regionen -->
    <div class="tab-pane fade" id="regions" role="tabpanel" aria-labelledby="regions-tab">

        <!-- Kantone -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Kantone</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($filterCantons)): ?>
                    <p class="text-muted mb-3">Dieser Benutzer erhält Anfragen aus folgenden Kantonen:</p>
                    <div class="row">
                        <?php foreach ($filterCantons as $canton): ?>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <span class="badge bg-success" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                                    <i class="bi bi-check-circle me-1"></i> <?= esc(trim($canton)) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Keine Kantone ausgewählt.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bezirke/Regionen -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-map"></i> Bezirke / Regionen</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($filterRegions)): ?>
                    <p class="text-muted mb-3">Dieser Benutzer erhält Anfragen aus folgenden Bezirken/Regionen (<?= count($filterRegions) ?> gesamt):</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover" id="regionsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Bezirk / Region</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($filterRegions as $region): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= esc(trim($region)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                    $(document).ready(function() {
                        $('#regionsTable').DataTable({
                            "order": [[1, "asc"]],
                            "pageLength": 25,
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json"
                            }
                        });
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Keine Bezirke/Regionen ausgewählt.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Eigene PLZ -->
        <?php if (!empty($user->filter_custom_zip)): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-mailbox"></i> Eigene Postleitzahlen</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Zusätzliche benutzerdefinierte Postleitzahlen:</p>
                <code><?= esc($user->filter_custom_zip) ?></code>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <!-- Ende Tab 4: Regionen -->

    <!-- Tab 5: Finanzen (Transaktionen) -->
    <div class="tab-pane fade" id="finance" role="tabpanel" aria-labelledby="finance-tab">

        <!-- Kontostand & Aktionen -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Aktueller Kontostand</h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addCreditModal">
                    <i class="bi bi-plus-circle"></i> Guthaben gutschreiben
                </button>
            </div>
            <div class="card-body">
                <h3 class="mb-0">
                    <?php if ($balance >= 0): ?>
                        <span class="text-success"><?= number_format($balance, 2) ?> CHF</span>
                    <?php else: ?>
                        <span class="text-danger"><?= number_format($balance, 2) ?> CHF</span>
                    <?php endif; ?>
                </h3>
            </div>
        </div>

        <!-- Modal: Guthaben gutschreiben -->
        <div class="modal fade" id="addCreditModal" tabindex="-1" aria-labelledby="addCreditModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= site_url('admin/user/add-credit/' . $user->id) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="addCreditModalLabel">
                                <i class="bi bi-plus-circle"></i> Guthaben gutschreiben
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Schliessen"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="credit_amount" class="form-label"><strong>Betrag (CHF)</strong></label>
                                <input type="number" class="form-control" id="credit_amount" name="amount" step="0.01" min="0.01" required placeholder="z.B. 50.00">
                            </div>
                            <div class="mb-3">
                                <label for="credit_description" class="form-label"><strong>Beschreibung / Grund</strong></label>
                                <textarea class="form-control" id="credit_description" name="description" rows="2" required placeholder="z.B. Kulanz wegen Reklamation"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Gutschreiben
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Transaktionen -->
<?php if (!empty($transactions)): ?>
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Alle Transaktionen (<?= count($transactions) ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Typ</th>
                        <th>Beschreibung</th>
                        <th class="text-end">Betrag</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $runningBalance = 0;
                    $modalsHtml = ''; // Modals sammeln für Ausgabe nach der Tabelle
                    // Transaktionen in umgekehrter Reihenfolge für korrekte Saldo-Berechnung
                    $transactionsReversed = array_reverse($transactions);
                    foreach ($transactionsReversed as $transaction):
                        $runningBalance += $transaction['amount'];
                        $isRefundable = in_array($transaction['type'], ['offer_purchase', 'topup']) && $transaction['amount'] != 0;
                        $isAlreadyRefunded = $transaction['type'] === 'refund' || $transaction['type'] === 'refund_purchase';
                    ?>
                        <tr>
                            <td><?= \CodeIgniter\I18n\Time::parse($transaction['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'offer_purchase' => 'Angebotskauf',
                                    'topup' => 'Aufladung',
                                    'refund' => 'Rückerstattung',
                                    'refund_purchase' => 'Kauf storniert',
                                    'adjustment' => 'Anpassung',
                                    'admin_credit' => 'Admin Gutschrift',
                                ];
                                $typeLabel = $typeLabels[$transaction['type']] ?? esc($transaction['type']);
                                $badgeClass = $transaction['amount'] >= 0 ? 'success' : 'danger';
                                if ($transaction['type'] === 'refund' || $transaction['type'] === 'refund_purchase') {
                                    $badgeClass = 'warning';
                                } elseif ($transaction['type'] === 'admin_credit') {
                                    $badgeClass = 'info';
                                }
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $typeLabel ?>
                                </span>
                            </td>
                            <td>
                                <?= esc($transaction['description'] ?? '-') ?>
                                <?php if (!empty($transaction['reference_id'])): ?>
                                    <br><small class="text-muted">Ref: <?= esc($transaction['reference_id']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <strong class="<?= $transaction['amount'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $transaction['amount'] >= 0 ? '+' : '' ?><?= number_format($transaction['amount'], 2) ?> CHF
                                </strong>
                            </td>
                            <td class="text-end">
                                <strong><?= number_format($runningBalance, 2) ?> CHF</strong>
                            </td>
                            <td class="text-center">
                                <?php
                                // Prüfe ob dieser Kauf bereits storniert wurde (über die Offer-ID / reference_id)
                                $isAlreadyRefunded = isset($refundedOfferIds[$transaction['reference_id']]);
                                // Prüfe ob diese Aufladung bereits rückerstattet wurde (über die Booking-ID)
                                $isTopupRefunded = isset($refundedTopupIds[$transaction['id']]);
                                ?>
                                <?php if ($transaction['type'] === 'offer_purchase' && $transaction['amount'] < 0 && !$isAlreadyRefunded): ?>
                                    <!-- Kauf stornieren / rückerstatten -->
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal<?= $transaction['id'] ?>" title="Kauf stornieren">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php elseif ($transaction['type'] === 'offer_purchase' && $isAlreadyRefunded): ?>
                                    <!-- Bereits storniert -->
                                    <span class="badge bg-secondary" title="Bereits storniert"><i class="bi bi-check-lg"></i></span>
                                <?php elseif ($transaction['type'] === 'topup' && $transaction['amount'] > 0 && !$isTopupRefunded): ?>
                                    <!-- Aufladung rückerstatten (Saferpay) -->
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundTopupModal<?= $transaction['id'] ?>" title="Aufladung rückerstatten">
                                        <i class="bi bi-credit-card"></i>
                                    </button>
                                <?php elseif ($transaction['type'] === 'topup' && $isTopupRefunded): ?>
                                    <!-- Bereits rückerstattet -->
                                    <span class="badge bg-secondary" title="Bereits rückerstattet"><i class="bi bi-check-lg"></i></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($transaction['type'] === 'offer_purchase' && $transaction['amount'] < 0): ?>
                        <?php ob_start(); ?>
                        <!-- Modal: Kauf stornieren -->
                        <div class="modal fade" id="refundModal<?= $transaction['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= site_url('admin/user/refund-purchase/' . $user->id) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="booking_id" value="<?= $transaction['id'] ?>">
                                        <div class="modal-header bg-warning text-dark">
                                            <h5 class="modal-title">
                                                <i class="bi bi-arrow-counterclockwise"></i> Kauf stornieren / rückerstatten
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            // Mit Kreditkarte bezahlt wenn: amount = 0 UND paid_amount > 0
                                            // (Bei Guthaben-Kauf ist amount negativ)
                                            $paidWithCard = (floatval($transaction['amount']) == 0 && ($transaction['paid_amount'] ?? 0) > 0);
                                            ?>
                                            <?php if ($paidWithCard): ?>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Achtung:</strong> Dieser Kauf wurde mit <strong>Kreditkarte</strong> bezahlt.
                                                Die Rückerstattung auf die Karte muss im
                                                <a href="https://test.saferpay.com/BO/Login" target="_blank">Saferpay Backend</a>
                                                manuell durchgeführt werden!
                                            </div>
                                            <?php endif; ?>
                                            <div class="alert alert-info">
                                                <strong>Transaktion:</strong> <?= esc($transaction['description']) ?><br>
                                                <strong>Betrag:</strong> <?= number_format(abs($transaction['amount']), 2) ?> CHF<br>
                                                <strong>Bezahlt mit:</strong> <?= $paidWithCard ? '<span class="badge bg-primary">Kreditkarte</span>' : '<span class="badge bg-success">Guthaben</span>' ?>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Rückerstattungsbetrag (CHF)</strong></label>
                                                <input type="number" class="form-control" name="refund_amount" step="0.01" min="0.01" max="<?= abs($transaction['amount']) ?>" value="<?= abs($transaction['amount']) ?>" required>
                                                <small class="text-muted">Max: <?= number_format(abs($transaction['amount']), 2) ?> CHF</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Grund für Stornierung</strong></label>
                                                <textarea class="form-control" name="refund_reason" rows="2" required placeholder="z.B. Anfrage war fehlerhaft"></textarea>
                                            </div>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="invalidate_purchase" id="invalidate<?= $transaction['id'] ?>" value="1" role="switch" checked>
                                                <label class="form-check-label" for="invalidate<?= $transaction['id'] ?>">
                                                    Kauf als ungültig markieren (Firma kann Anfrage nicht mehr sehen)
                                                </label>
                                            </div>
                                            <?php if ($paidWithCard): ?>
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="saferpay_refunded" id="saferpayRefundPurchase<?= $transaction['id'] ?>" value="1" role="switch">
                                                <label class="form-check-label" for="saferpayRefundPurchase<?= $transaction['id'] ?>">
                                                    <strong>Bereits im Saferpay Backend rückerstattet</strong>
                                                </label>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-check-lg"></i> Stornieren & Gutschreiben
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php $modalsHtml .= ob_get_clean(); ?>
                        <?php endif; ?>

                        <?php if ($transaction['type'] === 'topup' && $transaction['amount'] > 0): ?>
                        <?php ob_start(); ?>
                        <?php
                        // Versuche die zugehörige Saferpay-Transaktion zu finden
                        $transactionDate = date('Y-m-d', strtotime($transaction['created_at']));
                        $saferpayKey = $user->id . '_' . $transaction['amount'] . '_' . $transactionDate;
                        $saferpayTx = $saferpayMap[$saferpayKey] ?? null;
                        $hasCaptureId = $saferpayTx && !empty($saferpayTx['capture_id']);
                        ?>
                        <!-- Modal: Aufladung rückerstatten -->
                        <div class="modal fade" id="refundTopupModal<?= $transaction['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= site_url('admin/user/refund-topup/' . $user->id) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="booking_id" value="<?= $transaction['id'] ?>">
                                        <?php if ($hasCaptureId): ?>
                                        <input type="hidden" name="saferpay_transaction_id" value="<?= esc($saferpayTx['id']) ?>">
                                        <input type="hidden" name="capture_id" value="<?= esc($saferpayTx['capture_id']) ?>">
                                        <input type="hidden" name="currency" value="<?= esc($saferpayTx['currency'] ?? 'CHF') ?>">
                                        <?php endif; ?>
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-credit-card"></i> Aufladung rückerstatten
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if ($hasCaptureId): ?>
                                            <!-- Capture-ID vorhanden: Automatische Rückerstattung möglich -->
                                            <div class="alert alert-success">
                                                <i class="bi bi-check-circle"></i>
                                                <strong>Automatische Rückerstattung möglich!</strong><br>
                                                Capture-ID gefunden: <code><?= esc($saferpayTx['capture_id']) ?></code>
                                            </div>
                                            <?php else: ?>
                                            <!-- Keine Capture-ID: Manuelle Rückerstattung erforderlich -->
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Achtung:</strong> Bei dieser Zahlung ist keine automatische Rückerstattung möglich.<br>
                                                Die Rückerstattung auf die Kreditkarte muss im
                                                <a href="https://test.saferpay.com/BO/Login" target="_blank">Saferpay Backend</a>
                                                manuell durchgeführt werden.
                                            </div>
                                            <?php endif; ?>
                                            <div class="alert alert-info">
                                                <strong>Transaktion:</strong> <?= esc($transaction['description']) ?><br>
                                                <strong>Betrag:</strong> <?= number_format($transaction['amount'], 2) ?> CHF
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Rückerstattungsbetrag (CHF)</strong></label>
                                                <input type="number" class="form-control" name="refund_amount" step="0.01" min="0.01" max="<?= $transaction['amount'] ?>" value="<?= $transaction['amount'] ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Grund</strong></label>
                                                <textarea class="form-control" name="refund_reason" rows="2" required placeholder="z.B. Kunde möchte Guthaben zurück"></textarea>
                                            </div>

                                            <?php if ($hasCaptureId): ?>
                                            <!-- Option: Automatisch oder bereits manuell rückerstattet -->
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Rückerstattungsmethode</strong></label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="refund_method" id="refundAuto<?= $transaction['id'] ?>" value="auto" checked>
                                                    <label class="form-check-label" for="refundAuto<?= $transaction['id'] ?>">
                                                        <i class="bi bi-lightning text-warning"></i> <strong>Jetzt automatisch via Saferpay rückerstatten</strong>
                                                        <br><small class="text-muted">Der Betrag wird direkt auf die Kreditkarte des Kunden zurückerstattet.</small>
                                                    </label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="radio" name="refund_method" id="refundManual<?= $transaction['id'] ?>" value="manual">
                                                    <label class="form-check-label" for="refundManual<?= $transaction['id'] ?>">
                                                        <i class="bi bi-check-square text-success"></i> <strong>Bereits im Saferpay Backend rückerstattet</strong>
                                                        <br><small class="text-muted">Die Rückerstattung wurde bereits manuell durchgeführt.</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <!-- Keine Capture-ID: Nur manuelle Bestätigung -->
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="saferpay_refunded" id="saferpayRefund<?= $transaction['id'] ?>" value="1" role="switch">
                                                <label class="form-check-label" for="saferpayRefund<?= $transaction['id'] ?>">
                                                    <strong>Bereits im Saferpay Backend rückerstattet</strong>
                                                </label>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-check-lg"></i> Rückerstattung verbuchen
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php $modalsHtml .= ob_get_clean(); ?>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modals ausserhalb der Tabelle ausgeben -->
<?= $modalsHtml ?>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Keine Transaktionen vorhanden.
        </div>
        <?php endif; ?>

    </div>
    <!-- Ende Tab 5: Finanzen -->

    <!-- Tab 6: Bewertungen -->
    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
        <?php if (!empty($reviews)): ?>
            <?php
            // Helper-Funktion: Hole Wert aus Array oder Object
            $getVal = function($item, $key) {
                return is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            };

            // Berechne Durchschnittsbewertung
            $totalRating = 0;
            $ratingCount = count($reviews);
            foreach ($reviews as $review) {
                $totalRating += $getVal($review, 'rating');
            }
            $averageRating = $ratingCount > 0 ? round($totalRating / $ratingCount, 1) : 0;
            ?>

            <!-- Zusammenfassung -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-star-fill"></i> Bewertungs-Übersicht</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="mb-0">
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($averageRating)): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php elseif ($i - $averageRating < 1 && $i - $averageRating > 0): ?>
                                            <i class="bi bi-star-half"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <span class="ms-2"><?= number_format($averageRating, 1) ?></span>
                            </h3>
                            <p class="text-muted mb-0">Durchschnitt aus <?= $ratingCount ?> Bewertung<?= $ratingCount != 1 ? 'en' : '' ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php
                            // Berechne Bewertungsverteilung
                            $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                            foreach ($reviews as $review) {
                                $ratingDistribution[$getVal($review, 'rating')]++;
                            }
                            ?>
                            <?php foreach ([5, 4, 3, 2, 1] as $stars): ?>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="me-2" style="width: 60px;"><?= $stars ?> <i class="bi bi-star-fill text-warning"></i></span>
                                    <div class="progress flex-grow-1" style="height: 20px;">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                             style="width: <?= $ratingCount > 0 ? ($ratingDistribution[$stars] / $ratingCount * 100) : 0 ?>%">
                                            <?= $ratingDistribution[$stars] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bewertungen Liste -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-chat-left-quote"></i> Alle Bewertungen (<?= count($reviews) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($reviews as $review): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        <?= esc($getVal($review, 'created_by_firstname')) ?> <?= esc($getVal($review, 'created_by_lastname')) ?>
                                        <small class="text-muted">aus <?= esc($getVal($review, 'created_by_zip')) ?> <?= esc($getVal($review, 'created_by_city')) ?></small>
                                    </h6>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $getVal($review, 'rating')): ?>
                                                <i class="bi bi-star-fill"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="text-dark ms-2">(<?= $getVal($review, 'rating') ?>/5)</span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= \CodeIgniter\I18n\Time::parse($getVal($review, 'created_at'))->setTimezone(app_timezone())->format('d.m.Y H:i') ?>
                                </small>
                            </div>
                            <?php if (!empty($getVal($review, 'comment'))): ?>
                                <p class="mb-1"><?= nl2br(esc($getVal($review, 'comment'))) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($getVal($review, 'offer_id'))): ?>
                                <small class="text-muted">
                                    <i class="bi bi-link-45deg"></i>
                                    <a href="<?= site_url('admin/offer/' . $getVal($review, 'offer_id')) ?>" target="_blank">
                                        Angebot #<?= $getVal($review, 'offer_id') ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Dieser Benutzer hat noch keine Bewertungen erhalten.
            </div>
        <?php endif; ?>
    </div>
    <!-- Ende Tab 6: Bewertungen -->

    <!-- Tab 7: Agenda -->
    <div class="tab-pane fade" id="agenda" role="tabpanel" aria-labelledby="agenda-tab">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Abwesenheiten / Gesperrte Tage</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($blockedDays)): ?>
                    <p class="text-muted mb-3">An diesen Tagen erhält der Benutzer keine neuen Anfragen (<?= count($blockedDays) ?> gesamt):</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover" id="agendaTable">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Wochentag</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blockedDays as $blockedDay): ?>
                                    <?php
                                    $date = \CodeIgniter\I18n\Time::parse($blockedDay['date'])->setTimezone(app_timezone());
                                    $today = \CodeIgniter\I18n\Time::now()->setTimezone(app_timezone())->format('Y-m-d');
                                    $blockedDate = $date->format('Y-m-d');
                                    $isPast = $blockedDate < $today;
                                    $isToday = $blockedDate === $today;
                                    ?>
                                    <tr class="<?= $isPast ? 'text-muted' : '' ?>">
                                        <td>
                                            <strong><?= $date->format('d.m.Y') ?></strong>
                                        </td>
                                        <td><?= $date->toLocalizedString('EEEE') ?></td>
                                        <td>
                                            <?php if ($isToday): ?>
                                                <span class="badge bg-warning">Heute</span>
                                            <?php elseif ($isPast): ?>
                                                <span class="badge bg-secondary">Vergangen</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Geplant</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                    $(document).ready(function() {
                        $('#agendaTable').DataTable({
                            "order": [[0, "desc"]],
                            "pageLength": 25,
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json"
                            }
                        });
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Keine Abwesenheiten eingetragen. Der Benutzer ist immer verfügbar.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Ende Tab 7: Agenda -->

    <!-- Tab 8: Notizen -->
    <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">

        <!-- Neue Notiz hinzufügen -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Neue Notiz hinzufügen</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle"></i> Notizen sind nur für Administratoren sichtbar und werden nicht dem Benutzer angezeigt.
                </p>

                <form action="<?= site_url('admin/user/add-note/' . $user->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="note_type" class="form-label"><strong>Kontakt-Typ</strong></label>
                            <select class="form-select" id="note_type" name="note_type" required>
                                <option value="phone">Telefonisch</option>
                                <option value="email">Per E-Mail</option>
                            </select>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="note_text" class="form-label"><strong>Notiz-Text</strong></label>
                            <textarea
                                class="form-control"
                                id="note_text"
                                name="note_text"
                                rows="3"
                                placeholder="Beschreiben Sie hier die Konversation oder wichtige Informationen..."
                                required
                            ></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Notiz hinzufügen
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter & Notizen-Liste -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Alle Notizen
                    <span class="badge bg-light text-dark ms-2"><?= $noteCounts['all'] ?> gesamt</span>
                    <span class="badge bg-info ms-1"><?= $noteCounts['phone'] ?> telefonisch</span>
                    <span class="badge bg-success ms-1"><?= $noteCounts['email'] ?> per E-Mail</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Filter-Formular -->
                <form method="get" action="<?= site_url('admin/user/' . $user->id) ?>" class="mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="note_type_filter" class="form-label"><strong>Typ</strong></label>
                            <select class="form-select" id="note_type_filter" name="note_type">
                                <option value="all" <?= $noteType === 'all' ? 'selected' : '' ?>>Alle</option>
                                <option value="phone" <?= $noteType === 'phone' ? 'selected' : '' ?>>Telefonisch</option>
                                <option value="email" <?= $noteType === 'email' ? 'selected' : '' ?>>Per E-Mail</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label"><strong>Von Datum</strong></label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= esc($dateFrom ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label"><strong>Bis Datum</strong></label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= esc($dateTo ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Filter anwenden
                            </button>
                        </div>
                    </div>
                </form>

                <!-- DataTables mit Notizen -->
                <?php if (!empty($notes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="notesTable">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Typ</th>
                                    <th>Notiz</th>
                                    <th>Erfasst von</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td>
                                            <strong><?= \CodeIgniter\I18n\Time::parse($note['created_at'])->setTimezone(app_timezone())->format('d.m.Y') ?></strong><br>
                                            <small class="text-muted"><?= \CodeIgniter\I18n\Time::parse($note['created_at'])->setTimezone(app_timezone())->format('H:i') ?> Uhr</small>
                                        </td>
                                        <td>
                                            <?php if ($note['type'] === 'phone'): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-telephone"></i> Telefonisch
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-envelope"></i> E-Mail
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= nl2br(esc($note['note_text'])) ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= esc($note['admin_name'] ?? 'Unbekannt') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('admin/user/delete-note/' . $user->id . '/' . $note['id']) ?>"
                                               class="btn btn-sm btn-danger del"
                                               title="Notiz löschen">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                    $(document).ready(function() {
                        $('#notesTable').DataTable({
                            "order": [[0, "desc"]], // Sortiere nach Datum absteigend
                            "pageLength": 25,
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json"
                            }
                        });
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Noch keine Notizen vorhanden. Fügen Sie oben die erste Notiz hinzu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Ende Tab 8: Notizen -->

    <!-- Tab 9: Monatsrechnungen -->
    <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Monatsrechnungen</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($invoices)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>Rechnung-Nr.</th>
                                    <th>Periode</th>
                                    <th class="text-center">Käufe</th>
                                    <th class="text-center">Stornos</th>
                                    <th class="text-end">Betrag</th>
                                    <th>Ausgestellt</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
                                        <td>
                                            <?php
                                            $periodDate = DateTime::createFromFormat('Y-m', $invoice['period']);
                                            echo $periodDate ? $periodDate->format('m/Y') : $invoice['period'];
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $invoice['purchase_count'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($invoice['refund_count'] > 0): ?>
                                                <span class="badge bg-danger"><?= $invoice['refund_count'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php $amountClass = $invoice['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>
                                            <strong class="<?= $amountClass ?>">
                                                <?= number_format($invoice['amount'], 2, ".", "'") ?> <?= esc($invoice['currency']) ?>
                                            </strong>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($invoice['created_at'])) ?></td>
                                        <td>
                                            <a href="<?= site_url('admin/invoices/download-pdf/' . $invoice['period'] . '/' . $invoice['user_id']) ?>"
                                               class="btn btn-sm btn-warning" target="_blank" title="PDF herunterladen">
                                                <i class="bi bi-file-earmark-pdf"></i> PDF
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                    $(document).ready(function() {
                        $('#invoicesTable').DataTable({
                            "order": [[1, "desc"]],
                            "pageLength": 25,
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json"
                            }
                        });
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Keine Monatsrechnungen vorhanden.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Ende Tab 9: Monatsrechnungen -->

</div>
<!-- Ende Tab Content -->

<script>
// Tab aus URL-Hash aktivieren
document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if (hash) {
        // Finde den Tab-Button für diesen Hash
        var tabButton = document.querySelector('button[data-bs-target="' + hash + '"]');
        if (tabButton) {
            var tab = new bootstrap.Tab(tabButton);
            tab.show();
        }
    }

    // Hash aktualisieren wenn Tab gewechselt wird
    var tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(function(button) {
        button.addEventListener('shown.bs.tab', function(e) {
            var target = e.target.getAttribute('data-bs-target');
            if (target) {
                history.replaceState(null, null, target);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
