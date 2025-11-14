<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye me-2"></i>Gelöschte Anfrage #<?= esc($offer['original_offer_id']) ?></h2>
        <div>
            <a href="<?= site_url('admin/trash') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Zurück zum Papierkorb
            </a>
            <a href="<?= site_url('admin/trash/restore/'.$offer['id']) ?>"
               class="btn btn-success"
               onclick="return confirm('Möchten Sie diese Anfrage wirklich wiederherstellen?');">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Wiederherstellen
            </a>
        </div>
    </div>

    <!-- Löschungs-Informationen -->
    <div class="card mb-4 shadow-sm border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Löschungs-Informationen</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Gelöscht am:</strong><br>
                    <?= date('d.m.Y H:i', strtotime($offer['deleted_at'])) ?> Uhr
                </div>
                <div class="col-md-3">
                    <strong>Gelöscht von:</strong><br>
                    <?= esc($offer['deleted_by_username']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Grund:</strong><br>
                    <?= esc($offer['deletion_reason'] ?? 'Kein Grund angegeben') ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Original erstellt am:</strong><br>
                    <?= !empty($offer['original_created_at']) ? date('d.m.Y H:i', strtotime($offer['original_created_at'])) . ' Uhr' : '-' ?>
                </div>
                <div class="col-md-6">
                    <strong>Ursprüngliche Tabelle:</strong><br>
                    <code><?= esc($offer['original_table']) ?></code>
                </div>
            </div>
        </div>
    </div>

    <!-- Basis-Informationen -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Basis-Informationen</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Original ID:</strong><br>
                    <?= esc($offer['original_offer_id']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Typ:</strong><br>
                    <span class="badge bg-info"><?= esc($offer['type']) ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Original-Typ:</strong><br>
                    <?= esc($offer['original_type'] ?? '-') ?>
                </div>
                <div class="col-md-3">
                    <strong>Sub-Typ:</strong><br>
                    <?= esc($offer['sub_type'] ?? '-') ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-secondary"><?= esc($offer['status'] ?? '-') ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Preis:</strong><br>
                    <?= number_format($offer['price'], 2, '.', "'") ?> CHF
                </div>
                <div class="col-md-3">
                    <strong>Rabatt-Preis:</strong><br>
                    <?= !empty($offer['discounted_price']) ? number_format($offer['discounted_price'], 2, '.', "'") . ' CHF' : '-' ?>
                </div>
                <div class="col-md-3">
                    <strong>Käufer:</strong><br>
                    <?= esc($offer['buyers'] ?? 0) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <strong>Plattform:</strong><br>
                    <?php
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
                        <?= esc($offer['platform']) ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Sprache:</strong><br>
                    <?= esc(strtoupper($offer['language'] ?? 'de')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Kundentyp:</strong><br>
                    <?= esc($offer['customer_type'] ?? '-') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kunden-Informationen -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Kunden-Informationen</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Name:</strong><br>
                    <?= esc($offer['firstname'] ?? '') ?> <?= esc($offer['lastname'] ?? '') ?>
                </div>
                <div class="col-md-6">
                    <strong>Firma:</strong><br>
                    <?= esc($offer['company'] ?? '-') ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>E-Mail:</strong><br>
                    <a href="mailto:<?= esc($offer['email']) ?>"><?= esc($offer['email']) ?></a>
                </div>
                <div class="col-md-6">
                    <strong>Telefon:</strong><br>
                    <?= esc($offer['phone'] ?? '-') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <strong>Stadt:</strong><br>
                    <?= esc($offer['zip']) ?> <?= esc($offer['city']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Land:</strong><br>
                    <?= esc($offer['country'] ?? '-') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Type-Specific Data -->
    <?php if (!empty($offer['type_specific_decoded'])): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Typ-spezifische Daten</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Feld</th>
                            <th>Wert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offer['type_specific_decoded'] as $key => $value): ?>
                            <?php if ($key !== 'id' && $key !== 'offer_id' && $key !== 'created_at' && $key !== 'updated_at'): ?>
                            <tr>
                                <td><strong><?= esc($key) ?></strong></td>
                                <td><?= is_array($value) ? json_encode($value) : esc($value ?? '-') ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Fields -->
    <?php if (!empty($offer['form_fields_decoded'])): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-code me-2"></i>Formular-Felder</h5>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3" style="max-height: 500px; overflow-y: auto;"><?= json_encode($offer['form_fields_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <!-- Weitere Informationen -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-three-dots me-2"></i>Weitere Informationen</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>UUID:</strong><br>
                    <code><?= esc($offer['uuid'] ?? '-') ?></code>
                </div>
                <div class="col-md-4">
                    <strong>Access Hash:</strong><br>
                    <code><?= esc($offer['access_hash'] ?? '-') ?></code>
                </div>
                <div class="col-md-4">
                    <strong>Group ID:</strong><br>
                    <code><?= esc($offer['group_id'] ?? '-') ?></code>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Verifiziert:</strong><br>
                    <?= $offer['verified'] ? '<span class="badge bg-success">Ja</span>' : '<span class="badge bg-secondary">Nein</span>' ?>
                </div>
                <div class="col-md-4">
                    <strong>Verify Type:</strong><br>
                    <?= esc($offer['verify_type'] ?? '-') ?>
                </div>
                <div class="col-md-4">
                    <strong>Von Kampagne:</strong><br>
                    <?= esc($offer['from_campaign'] ?? '-') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <strong>Arbeitsbeginn:</strong><br>
                    <?= !empty($offer['work_start_date']) ? date('d.m.Y', strtotime($offer['work_start_date'])) : '-' ?>
                </div>
                <div class="col-md-4">
                    <strong>Checked at:</strong><br>
                    <?= !empty($offer['checked_at']) ? date('d.m.Y H:i', strtotime($offer['checked_at'])) : '-' ?>
                </div>
                <div class="col-md-4">
                    <strong>Reminder sent at:</strong><br>
                    <?= !empty($offer['reminder_sent_at']) ? date('d.m.Y H:i', strtotime($offer['reminder_sent_at'])) : '-' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
