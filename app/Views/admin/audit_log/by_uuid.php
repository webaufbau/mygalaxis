<?= $this->extend('layout/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Audit Log - UUID: <?= esc($uuid) ?></h2>
            <a href="/admin/audit-log" class="btn btn-sm btn-secondary">← Zurück zur Übersicht</a>
        </div>
    </div>

    <?php if ($offer): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5>Offerte Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> #<?= esc($offer['id']) ?><br>
                        <strong>Typ:</strong> <?= esc($offer['type']) ?><br>
                        <strong>Platform:</strong> <?= esc($offer['platform']) ?><br>
                        <strong>Verifiziert:</strong> <?= $offer['verified'] ? '✅ Ja' : '❌ Nein' ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Erstellt:</strong> <?= date('d.m.Y H:i:s', strtotime($offer['created_at'])) ?><br>
                        <strong>Preis:</strong> CHF <?= esc($offer['price']) ?><br>
                        <?php if ($offer['group_id']): ?>
                            <strong>Group:</strong> <a href="/admin/audit-log/group/<?= esc($offer['group_id']) ?>"><?= esc($offer['group_id']) ?></a><br>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5>Event Timeline</h5>
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine Logs für diese UUID gefunden.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($logs as $log): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-<?= match($log['event_category']) {
                                        'form' => 'primary',
                                        'redirect' => 'info',
                                        'verification' => 'warning',
                                        'email' => 'success',
                                        default => 'secondary'
                                    } ?>"><?= esc($log['event_category']) ?></span>
                                    <strong class="ms-2"><?= esc($log['event_type']) ?></strong>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></small>
                            </div>
                            <p class="mt-2 mb-1"><?= esc($log['message']) ?></p>
                            <?php if ($log['phone']): ?>
                                <small class="text-muted">📞 <?= esc($log['phone']) ?></small>
                            <?php endif; ?>
                            <?php if ($log['email']): ?>
                                <small class="text-muted">✉️ <?= esc($log['email']) ?></small>
                            <?php endif; ?>
                            <?php if ($log['details']): ?>
                                <details class="mt-2">
                                    <summary class="text-primary" style="cursor: pointer;">Details anzeigen</summary>
                                    <pre class="mt-2 p-2 bg-light rounded"><code><?= esc(json_encode(json_decode($log['details']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                                </details>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
