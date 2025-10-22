<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Audit Log</h2>
            <p class="text-muted">Nachverfolgung von Formular-Submissions, Weiterleitungen, Verifikationen und E-Mails</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="/admin/audit-log">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Suche (UUID, E-Mail, Telefon)" value="<?= esc($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="category">
                            <option value="">Alle Kategorien</option>
                            <option value="form" <?= ($filters['event_category'] ?? '') === 'form' ? 'selected' : '' ?>>Form</option>
                            <option value="redirect" <?= ($filters['event_category'] ?? '') === 'redirect' ? 'selected' : '' ?>>Redirect</option>
                            <option value="verification" <?= ($filters['event_category'] ?? '') === 'verification' ? 'selected' : '' ?>>Verification</option>
                            <option value="email" <?= ($filters['event_category'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="platform">
                            <option value="">Alle Platforms</option>
                            <option value="my_offertenschweiz_ch" <?= ($filters['platform'] ?? '') === 'my_offertenschweiz_ch' ? 'selected' : '' ?>>Offertenschweiz</option>
                            <option value="my_offertenheld_ch" <?= ($filters['platform'] ?? '') === 'my_offertenheld_ch' ? 'selected' : '' ?>>Offertenheld</option>
                            <option value="my_renovo24_ch" <?= ($filters['platform'] ?? '') === 'my_renovo24_ch' ? 'selected' : '' ?>>Renovo24</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" placeholder="Von">
                    </div>
                    <div class="col-md-2">
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
                <p class="text-muted">Keine Logs gefunden.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Zeit</th>
                                <th>Kategorie</th>
                                <th>Event</th>
                                <th>Nachricht</th>
                                <th>UUID</th>
                                <th>Offer ID</th>
                                <th>Kontakt</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <small><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($log['event_category']) {
                                            'form' => 'bg-primary',
                                            'redirect' => 'bg-info',
                                            'verification' => 'bg-warning',
                                            'email' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($log['event_category']) ?></span>
                                    </td>
                                    <td><small><?= esc($log['event_type']) ?></small></td>
                                    <td><?= esc($log['message']) ?></td>
                                    <td>
                                        <?php if ($log['uuid']): ?>
                                            <a href="/admin/audit-log/uuid/<?= esc($log['uuid']) ?>" class="text-decoration-none">
                                                <small><?= esc(substr($log['uuid'], 0, 8)) ?>...</small>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['offer_id']): ?>
                                            <a href="/admin/audit-log/offer/<?= esc($log['offer_id']) ?>" class="text-decoration-none">
                                                #<?= esc($log['offer_id']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if ($log['email']): ?>
                                                <div><?= esc($log['email']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($log['phone']): ?>
                                                <div><?= esc($log['phone']) ?></div>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['details']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailsModal<?= $log['id'] ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="detailsModal<?= $log['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Details - <?= esc($log['event_type']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <pre><?= esc(json_encode(json_decode($log['details']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-2"><small>Zeige <?= count($logs) ?> Einträge</small></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
