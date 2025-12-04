<?= $this->extend('layout/admin') ?>
<?= $this->section('content') ?>

<?php
// Deutsche Übersetzungen für E-Mail-Typen
$emailTypeLabels = [
    'new_offer' => 'Neue Anfrage',
    'discount_notification' => 'Rabatt-Info',
    'offer_purchased' => 'Kauf-Bestätigung',
    'offer_expired' => 'Abgelaufen',
    'company_notification' => 'Firmen-Benachrichtigung',
    'confirmation' => 'Bestätigung',
    'customer_notification' => 'Kunden-Info',
    'reminder' => 'Erinnerung',
    'welcome' => 'Willkommen',
    'password_reset' => 'Passwort zurücksetzen',
];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="bi bi-envelope-paper me-2"></i>E-Mail Verlauf</h2>
            <p class="text-muted">Alle versendeten E-Mails zu Angeboten (Benachrichtigungen, Rabatte, etc.)</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="/admin/email-log">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Suche (E-Mail, Betreff, Offer ID)" value="<?= esc($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="email_type">
                            <option value="">Alle Typen</option>
                            <?php foreach ($emailTypes as $type): ?>
                                <?php $displayLabel = $emailTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)); ?>
                                <option value="<?= esc($type) ?>" <?= ($filters['email_type'] ?? '') === $type ? 'selected' : '' ?>><?= esc($displayLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="recipient_type">
                            <option value="">Alle Empfänger</option>
                            <option value="customer" <?= ($filters['recipient_type'] ?? '') === 'customer' ? 'selected' : '' ?>>Kunde</option>
                            <option value="company" <?= ($filters['recipient_type'] ?? '') === 'company' ? 'selected' : '' ?>>Firma</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select class="form-select" name="status">
                            <option value="">Status</option>
                            <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Gesendet</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Fehlgeschlagen</option>
                            <option value="bounced" <?= ($filters['status'] ?? '') === 'bounced' ? 'selected' : '' ?>>Bounced</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" placeholder="Von">
                    </div>
                    <div class="col-md-1">
                        <input type="date" class="form-control" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" placeholder="Bis">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine E-Mails gefunden.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="emailLogTable" class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Gesendet</th>
                                <th>Typ</th>
                                <th>Empfänger</th>
                                <th>Betreff</th>
                                <th>Angebot</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr class="clickable-row" data-href="/admin/email-log/offer/<?= esc($log['offer_id']) ?>">
                                    <td class="text-nowrap">
                                        <small><?= date('d.m.Y H:i', strtotime($log['sent_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($log['email_type']) {
                                            'new_offer' => 'bg-primary',
                                            'discount_notification' => 'bg-warning text-dark',
                                            'offer_purchased' => 'bg-success',
                                            'offer_expired' => 'bg-secondary',
                                            'company_notification' => 'bg-info',
                                            'confirmation' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                        $typeLabel = $emailTypeLabels[$log['email_type']] ?? ucfirst(str_replace('_', ' ', $log['email_type']));
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($typeLabel) ?></span>
                                        <br>
                                        <small class="text-muted"><?= $log['recipient_type'] === 'company' ? 'Firma' : 'Kunde' ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($log['recipient_email']) ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($log['subject'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($log['offer_id']): ?>
                                            #<?= esc($log['offer_id']) ?>
                                            <?php if (!empty($log['offer_title'])): ?>
                                                <br><small class="text-muted"><?= esc(mb_substr($log['offer_title'], 0, 30)) ?><?= mb_strlen($log['offer_title']) > 30 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                            <br>
                                            <a href="/admin/offer/<?= esc($log['offer_id']) ?>" class="btn btn-sm btn-outline-secondary mt-1" title="Angebot öffnen">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($log['status']) {
                                            'sent' => 'text-success',
                                            'failed' => 'text-danger',
                                            'bounced' => 'text-warning',
                                            default => 'text-muted'
                                        };
                                        $statusIcon = match($log['status']) {
                                            'sent' => 'bi-check-circle-fill',
                                            'failed' => 'bi-x-circle-fill',
                                            'bounced' => 'bi-exclamation-triangle-fill',
                                            default => 'bi-question-circle'
                                        };
                                        ?>
                                        <i class="bi <?= $statusIcon ?> <?= $statusClass ?>"></i>
                                        <?php if (!empty($log['error_message'])): ?>
                                            <br><small class="text-danger"><?= esc(mb_substr($log['error_message'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-2"><small>Zeige <?= count($logs) ?> Einträge (max. <?= esc($filters['limit']) ?>)</small></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.clickable-row {
    cursor: pointer;
}
.clickable-row:hover {
    background-color: rgba(0, 123, 255, 0.1) !important;
}
</style>

<script>
$(document).ready(function() {
    $('#emailLogTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.22/i18n/German.json'
        },
        pageLength: 25,
        stateSave: true,
        order: [[0, 'desc']] // Nach Datum absteigend sortieren
    });

    // Klickbare Zeilen
    $(document).on('click', '.clickable-row', function(e) {
        // Nicht navigieren wenn auf einen Link geklickt wurde
        if ($(e.target).closest('a, button').length) return;
        window.location = $(this).data('href');
    });
});
</script>

<?= $this->endSection() ?>
