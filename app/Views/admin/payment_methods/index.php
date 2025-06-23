<h1><?= esc($title) ?></h1>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

<a href="<?= site_url('/admin/paymentmethods/create') ?>" class="btn btn-primary mb-3">Neue Zahlungsart hinzufügen</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>Name</th>
        <th>Aktiv</th>
        <th>Aktionen</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($methods as $method): ?>
        <tr>
            <td><?= esc($method['id']) ?></td>
            <td><?= esc($method['code']) ?></td>
            <td><?= esc($method['name']) ?></td>
            <td><?= $method['active'] ? 'Ja' : 'Nein' ?></td>
            <td>
                <a href="<?= site_url('/admin/paymentmethods/edit/' . $method['id']) ?>" class="btn btn-sm btn-warning">Bearbeiten</a>
                <a href="<?= site_url('/admin/paymentmethods/delete/' . $method['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Wirklich löschen?');">Löschen</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
