<?= $this->extend('layout/main') ?>

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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= esc($user->company_name ?? 'Benutzer') ?> - Details</h2>
    <div>
        <a href="<?= site_url('admin/user/form/' . $user->id . '?model=user') ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-pencil"></i> Bearbeiten
        </a>
        <a href="<?= site_url('admin/user') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Zurück
        </a>
    </div>
</div>

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

<!-- Gekaufte Angebote -->
<?php if (!empty($purchases)): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-cart-check"></i> Gekaufte Angebote (<?= count($purchases) ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Angebot</th>
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
                            ?>
                            <tr>
                                <td><?= \CodeIgniter\I18n\Time::parse($purchase['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?></td>
                                <td>
                                    <strong>ID <?= esc($offer['id']) ?></strong><br>
                                    <small class="text-muted"><?= esc($offer['firstname']) ?> <?= esc($offer['lastname']) ?></small>
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
<?php else: ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Dieser Benutzer hat noch keine Angebote gekauft.
</div>
<?php endif; ?>

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
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $runningBalance = 0;
                    // Transaktionen in umgekehrter Reihenfolge für korrekte Saldo-Berechnung
                    $transactionsReversed = array_reverse($transactions);
                    foreach ($transactionsReversed as $transaction):
                        $runningBalance += $transaction['amount'];
                    ?>
                        <tr>
                            <td><?= \CodeIgniter\I18n\Time::parse($transaction['created_at'])->setTimezone(app_timezone())->format('d.m.Y H:i') ?></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'offer_purchase' => 'Angebotskauf',
                                    'topup' => 'Aufladung',
                                    'refund' => 'Rückerstattung',
                                    'adjustment' => 'Anpassung',
                                ];
                                $typeLabel = $typeLabels[$transaction['type']] ?? esc($transaction['type']);
                                ?>
                                <span class="badge bg-<?= $transaction['amount'] >= 0 ? 'success' : 'danger' ?>">
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Keine Transaktionen vorhanden.
</div>
<?php endif; ?>

<?= $this->endSection() ?>
