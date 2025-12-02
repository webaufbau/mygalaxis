<?= $this->extend('layout/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Audit Log - Group: <?= esc($group_id) ?></h2>
            <a href="/admin/audit-log" class="btn btn-sm btn-secondary">← Zurück zur Übersicht</a>
        </div>
    </div>

    <?php if (!empty($offers)): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5>Offerten in dieser Gruppe</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>UUID</th>
                            <th>Typ</th>
                            <th>Platform</th>
                            <th>Erstellt</th>
                            <th>Verifiziert</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $offer): ?>
                            <tr>
                                <td>#<?= esc($offer['id']) ?></td>
                                <td><small><?= esc(substr($offer['uuid'], 0, 13)) ?>...</small></td>
                                <td><?= esc($offer['type']) ?></td>
                                <td><?= esc($offer['platform']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($offer['created_at'])) ?></td>
                                <td><?= $offer['verified'] ? '✅' : '❌' ?></td>
                                <td>
                                    <a href="/admin/audit-log/uuid/<?= esc($offer['uuid']) ?>" class="btn btn-sm btn-outline-primary">Logs</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5>Timeline für gesamte Gruppe</h5>
            <?php if (empty($logs)): ?>
                <p class="text-muted">Keine Logs für diese Gruppe gefunden.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-<?= match($log['event_category']) {
                                    'form' => 'primary',
                                    'redirect' => 'info',
                                    'verification' => 'warning',
                                    'email' => 'success',
                                    default => 'secondary'
                                } ?>"><?= esc($log['event_category']) ?></span>
                                <strong class="ms-2"><?= esc($log['event_type']) ?></strong>
                                <?php if ($log['uuid']): ?>
                                    <small class="text-muted">(UUID: <?= esc(substr($log['uuid'], 0, 8)) ?>...)</small>
                                <?php endif; ?>
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
