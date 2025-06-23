<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Anfragen Übersicht</h2>

<table class="table">
    <thead>
    <tr>
        <th>Titel</th>
        <th>Erstellt am</th>
        <th>Preis</th>
        <th>Aktion</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($anfragen as $a): ?>
        <tr>
            <td><?= esc($a['form_name']) ?></td>
            <td><?= date('d.m.Y H:i', strtotime($a['created_at'])) ?></td>
            <td>CHF <?= number_format($a['price'], 2) ?></td>
            <td><a href="/anfrage/<?= $a['id'] ?>" class="btn btn-primary btn-sm">Details</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?= $this->endSection() ?>
