<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Formulare</h1>
    <a href="/admin/form/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Neues Formular
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="forms-table">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Branche</th>
                        <th>Name (DE)</th>
                        <th>Link (DE)</th>
                        <th class="text-center">EN</th>
                        <th class="text-center">FR</th>
                        <th class="text-center">IT</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                    <tr<?= $form['category_hidden'] ? ' class="table-secondary"' : '' ?>>
                        <td>
                            <code class="small"><?= esc($form['form_id']) ?></code>
                        </td>
                        <td>
                            <span class="badge" style="background-color: <?= esc($form['category_color']) ?>">
                                <?= esc($form['category_name']) ?>
                            </span>
                            <?php if ($form['category_hidden']): ?>
                                <i class="bi bi-eye-slash text-muted ms-1" title="Versteckte Branche"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($form['name_de']) ?></td>
                        <td>
                            <?php if (!empty($form['form_link_de'])): ?>
                                <?php
                                // Nur Pfad nach Domain anzeigen
                                $parsedUrl = parse_url($form['form_link_de']);
                                $pathOnly = ($parsedUrl['path'] ?? '/') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                                $pathOnly = ltrim($pathOnly, '/');
                                ?>
                                <a href="<?= esc($form['form_link_de']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= esc($form['form_link_de']) ?>">
                                    <?= esc($pathOnly) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($form['form_link_en'])): ?>
                                <i class="bi bi-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-dash text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($form['form_link_fr'])): ?>
                                <i class="bi bi-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-dash text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($form['form_link_it'])): ?>
                                <i class="bi bi-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-dash text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/admin/form/edit/<?= esc($form['form_id']) ?>" class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/form/delete/<?= esc($form['form_id']) ?>" class="btn btn-sm btn-outline-danger del" title="Löschen" onclick="return confirm('Formular wirklich löschen?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($forms)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Keine Formulare vorhanden
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 text-muted small">
    <i class="bi bi-info-circle me-1"></i>
    Formulare sind WordPress-Links, die pro Branche definiert werden.
    Die Form-ID wird für Projekte und API-Zuordnungen verwendet.
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#forms-table').DataTable({
            paging: false,
            searching: true,
            info: false,
            order: [[1, 'asc'], [0, 'asc']],
            language: {
                search: "Suchen:",
                zeroRecords: "Keine Formulare gefunden"
            }
        });
    }
});
</script>
