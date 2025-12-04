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
    'purchase_company' => 'Kauf-Info (Firma)',
    'purchase_customer' => 'Kauf-Info (Kunde)',
    'review_reminder' => 'Bewertungs-Erinnerung',
    'review_reminder_2' => 'Bewertungs-Erinnerung 2',
];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <a href="/admin/email-log" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
            </a>
            <h2><i class="bi bi-envelope-paper me-2"></i>E-Mails für Angebot #<?= esc($offer['id'] ?? $offer->id ?? '-') ?></h2>
        </div>
    </div>

    <!-- Offer Info -->
    <?php if ($offer): ?>
        <?php
        $offerId = is_array($offer) ? $offer['id'] : $offer->id;
        $offerTitle = is_array($offer) ? ($offer['title'] ?? '-') : ($offer->title ?? '-');
        $offerCreatedAt = is_array($offer) ? ($offer['created_at'] ?? null) : ($offer->created_at ?? null);
        $offerUuid = is_array($offer) ? ($offer['uuid'] ?? '') : ($offer->uuid ?? '');
        ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>Angebot-Details</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> <?= esc($offerId) ?></p>
                        <p><strong>Titel:</strong> <?= esc($offerTitle) ?></p>
                        <?php if ($offerUuid): ?>
                            <p><strong>UUID:</strong> <code><?= esc($offerUuid) ?></code></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($offerCreatedAt): ?>
                            <p><strong>Erstellt:</strong> <?= date('d.m.Y H:i', strtotime($offerCreatedAt)) ?></p>
                            <p><strong>Alter:</strong>
                                <?php
                                $created = new DateTime($offerCreatedAt);
                                $now = new DateTime();
                                $diff = $created->diff($now);
                                $hours = ($diff->days * 24) + $diff->h;
                                echo $diff->days . ' Tage (' . number_format($hours, 0) . ' Stunden)';
                                ?>
                            </p>
                        <?php endif; ?>
                        <a href="/admin/offer/<?= esc($offerId) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Angebot öffnen
                        </a>
                        <a href="/admin/audit-log/offer/<?= esc($offerId) ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> Audit Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Timeline -->
    <div class="card">
        <div class="card-header">
            <strong>E-Mail Verlauf</strong> (<?= count($logs) ?> E-Mails)
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine E-Mails für dieses Angebot gefunden.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php
                    $offerCreatedAt = isset($offer) ? (is_array($offer) ? ($offer['created_at'] ?? null) : ($offer->created_at ?? null)) : null;
                    ?>
                    <?php if ($offerCreatedAt): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($offerCreatedAt)) ?></small>
                                <p class="mb-0"><strong>Angebot erstellt</strong></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($logs as $log): ?>
                        <?php
                        $badgeClass = match($log['email_type']) {
                            'new_offer' => 'bg-primary',
                            'discount_notification' => 'bg-warning',
                            'offer_purchased', 'purchase_company', 'purchase_customer' => 'bg-success',
                            'offer_expired' => 'bg-secondary',
                            'company_notification' => 'bg-info',
                            'confirmation' => 'bg-success',
                            default => 'bg-secondary'
                        };
                        $typeLabel = $emailTypeLabels[$log['email_type']] ?? ucfirst(str_replace('_', ' ', $log['email_type']));

                        // Berechne Zeit seit Angebotserstellung
                        $hoursSinceCreation = '';
                        if ($offerCreatedAt && $log['sent_at']) {
                            $created = new DateTime($offerCreatedAt);
                            $sent = new DateTime($log['sent_at']);
                            $diff = $created->diff($sent);
                            $hours = ($diff->days * 24) + $diff->h;
                            $hoursSinceCreation = '(' . $diff->days . ' Tage / ' . number_format($hours, 0) . 'h nach Erstellung)';
                        }
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $badgeClass ?>"></div>
                            <div class="timeline-content">
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($log['sent_at'])) ?>
                                    <?php if ($hoursSinceCreation): ?>
                                        <span class="text-info"><?= $hoursSinceCreation ?></span>
                                    <?php endif; ?>
                                </small>
                                <p class="mb-1">
                                    <span class="badge <?= $badgeClass ?>"><?= esc($typeLabel) ?></span>
                                    <span class="badge bg-light text-dark"><?= $log['recipient_type'] === 'company' ? 'An Firma' : 'An Kunde' ?></span>
                                    <?php if ($log['status'] !== 'sent'): ?>
                                        <span class="badge bg-danger"><?= esc($log['status']) ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong><?= esc($log['subject'] ?? '-') ?></strong></p>
                                <p class="mb-0">
                                    <small>
                                        <i class="bi bi-envelope"></i> <?= esc($log['recipient_email']) ?>
                                        <?php if (!empty($log['company_name'])): ?>
                                            <br><i class="bi bi-building"></i>
                                            <a href="/admin/user/<?= esc($log['company_id']) ?>"><?= esc($log['company_name']) ?></a>
                                        <?php endif; ?>
                                    </small>
                                </p>
                                <?php if (!empty($log['error_message'])): ?>
                                    <p class="text-danger mb-0"><small><?= esc($log['error_message']) ?></small></p>
                                <?php endif; ?>

                                <?php
                                // Zeige benachrichtigte Firmen wenn vorhanden
                                if (!empty($log['notified_company_ids'])):
                                    $companyIds = json_decode($log['notified_company_ids'], true);
                                    if (!empty($companyIds) && is_array($companyIds)):
                                        // Lade Firmen-Namen
                                        $db = \Config\Database::connect();
                                        $companies = $db->table('users')
                                            ->select('id, company_name, company_email')
                                            ->whereIn('id', $companyIds)
                                            ->get()
                                            ->getResultArray();
                                ?>
                                <div class="mt-2">
                                    <small class="text-muted"><i class="bi bi-people"></i> Benachrichtigte Firmen:</small>
                                    <ul class="list-unstyled mb-0 ms-3">
                                        <?php foreach ($companies as $company): ?>
                                            <li>
                                                <small>
                                                    <a href="/admin/user/<?= esc($company['id']) ?>" class="text-decoration-none">
                                                        <?= esc($company['company_name'] ?: 'Firma #' . $company['id']) ?>
                                                    </a>
                                                    <span class="text-muted">(<?= esc($company['company_email']) ?>)</span>
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php
                                    endif;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}
.timeline-content {
    padding-left: 10px;
}
</style>

<?= $this->endSection() ?>
