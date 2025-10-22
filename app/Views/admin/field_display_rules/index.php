<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= esc($title) ?></h1>
        <div>
            <a href="/admin/field-display-rules/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Neue Rule erstellen
            </a>
            <a href="/admin/email-templates" class="btn btn-secondary">
                <i class="bi bi-envelope"></i> Email-Templates
            </a>
        </div>
    </div>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Field Display Rules
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Hinweis:</strong> Field Display Rules definieren, wie bedingte Felder in Emails und Firmen-Ansichten dargestellt werden.
                Diese Rules gelten an allen Stellen, wo der FieldRenderer verwendet wird.
            </div>

            <?php if (empty($rules)): ?>
                <p class="text-muted">Noch keine Field Display Rules vorhanden.</p>
                <a href="/admin/field-display-rules/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Erste Rule erstellen
                </a>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Offer-Type</th>
                                <th>Label</th>
                                <th>Rule-Key</th>
                                <th>Versteckte Felder</th>
                                <th>Status</th>
                                <th>Reihenfolge</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($rule['offer_type']) ?></span>
                                    </td>
                                    <td><?= esc($rule['label']) ?></td>
                                    <td><code><?= esc($rule['rule_key']) ?></code></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= count($rule['fields_to_hide']) ?> Felder
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($rule['is_active']): ?>
                                            <span class="badge bg-success">Aktiv</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inaktiv</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $rule['sort_order'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/field-display-rules/edit/<?= $rule['id'] ?>"
                                               class="btn btn-outline-primary"
                                               title="Bearbeiten">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteRule(<?= $rule['id'] ?>)"
                                                    title="Löschen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
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

<script>
function deleteRule(id) {
    if (confirm('Möchten Sie diese Rule wirklich löschen?')) {
        window.location.href = '/admin/field-display-rules/delete/' + id;
    }
}
</script>

<?= $this->endSection() ?>
