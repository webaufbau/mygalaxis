<?= $this->extend('layout/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <a href="/admin/email-log" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Zurück zur Übersicht
            </a>
            <h2><i class="bi bi-envelope-paper me-2"></i>E-Mails für <?= esc($company->company_name ?? 'Firma #' . $company->id) ?></h2>
        </div>
    </div>

    <!-- Company Info -->
    <div class="card mb-3">
        <div class="card-header">
            <strong>Firmen-Details</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Firma:</strong> <?= esc($company->company_name ?? '-') ?></p>
                    <p><strong>E-Mail:</strong> <?= esc($company->email ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                    <a href="/admin/user/<?= esc($company->id) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-building"></i> Firma öffnen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <strong>E-Mail Verlauf</strong> (<?= count($logs) ?> E-Mails)
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine E-Mails für diese Firma gefunden.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Gesendet</th>
                                <th>Typ</th>
                                <th>Betreff</th>
                                <th>Angebot</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
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
                                            default => 'bg-info'
                                        };
                                        $typeLabel = match($log['email_type']) {
                                            'new_offer' => 'Neue Anfrage',
                                            'discount_notification' => 'Rabatt',
                                            'offer_purchased' => 'Gekauft',
                                            'offer_expired' => 'Abgelaufen',
                                            default => $log['email_type']
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($typeLabel) ?></span>
                                    </td>
                                    <td>
                                        <small><?= esc($log['subject'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($log['offer_id']): ?>
                                            <a href="/admin/email-log/offer/<?= esc($log['offer_id']) ?>" class="text-decoration-none">
                                                #<?= esc($log['offer_id']) ?>
                                            </a>
                                            <?php if (!empty($log['offer_title'])): ?>
                                                <br><small class="text-muted"><?= esc(mb_substr($log['offer_title'], 0, 40)) ?></small>
                                            <?php endif; ?>
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

<?= $this->endSection() ?>
