<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Projekte verwalten</h2>
    <a href="/admin/projects/create" class="btn btn-primary">+ Neues Projekt</a>
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<p class="text-muted mb-3">Ziehen Sie die Zeilen, um die Sortierung zu ändern. Die Reihenfolge wird automatisch gespeichert.</p>

<table class="table table-hover" id="projects-table">
    <thead>
        <tr>
            <th style="width: 40px;"></th>
            <th>Slug</th>
            <th>Name (DE)</th>
            <th>Formular-Link</th>
            <th>Farbe</th>
            <th>Sortierung</th>
            <th>Status</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody id="sortable-projects">
        <?php foreach ($projects as $project): ?>
            <tr data-id="<?= esc($project['id']) ?>">
                <td class="drag-handle" style="cursor: grab;">
                    <i class="bi bi-grip-vertical"></i>
                </td>
                <td><code><?= esc($project['slug']) ?></code></td>
                <td><?= esc($project['name_de']) ?></td>
                <td>
                    <?php if (!empty($project['form_link'])): ?>
                        <a href="<?= esc($project['form_link']) ?>" target="_blank" class="small">
                            <?= esc(substr($project['form_link'], 0, 30)) ?>...
                        </a>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge" style="background-color: <?= esc($project['color']) ?>; width: 24px; height: 24px; display: inline-block;"></span>
                    <span class="small text-muted"><?= esc($project['color']) ?></span>
                </td>
                <td><?= esc($project['sort_order']) ?></td>
                <td>
                    <?php if ($project['is_active']): ?>
                        <span class="badge bg-success">Aktiv</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/admin/projects/edit/<?= esc($project['id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Bearbeiten
                    </a>
                    <form action="/admin/projects/delete/<?= esc($project['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Projekt wirklich löschen?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($projects)): ?>
    <div class="alert alert-info">Noch keine Projekte vorhanden.</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('sortable-projects');

    if (tbody) {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const order = [];
                tbody.querySelectorAll('tr').forEach(function(row) {
                    order.push(row.dataset.id);
                });

                fetch('/admin/projects/update-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        order: order,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Sortierung gespeichert');
                    }
                });
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
