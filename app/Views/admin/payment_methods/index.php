<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1 class="my-3"><?= esc($title) ?></h1>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

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
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>


<?= $this->endSection() ?>
