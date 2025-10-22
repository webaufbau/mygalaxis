<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Audit Log - Offerte #<?= esc($offer_id) ?></h2>
            <a href="/admin/audit-log" class="btn btn-sm btn-secondary">← Zurück zur Übersicht</a>
            <?php if ($offer && $offer['uuid']): ?>
                <a href="/admin/audit-log/uuid/<?= esc($offer['uuid']) ?>" class="btn btn-sm btn-primary">Timeline für UUID anzeigen</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($offer): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5>Offerte Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>UUID:</strong> <?= esc($offer['uuid']) ?><br>
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
            <h5>Logs für diese Offerte</h5>
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine Logs für diese Offerte gefunden.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-<?= match($log['event_category']) {
                                    'form' => 'primary',
                                    'email' => 'success',
                                    default => 'secondary'
                                } ?>"><?= esc($log['event_category']) ?></span>
                                <strong class="ms-2"><?= esc($log['event_type']) ?></strong>
                            </div>
                            <small class="text-muted"><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></small>
                        </div>
                        <p class="mt-2"><?= esc($log['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
