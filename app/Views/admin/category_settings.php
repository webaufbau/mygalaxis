<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!--<h2>Kategorien bearbeiten</h2>-->

<form method="post">
    <?= csrf_field() ?>

    <table class="table">
        <thead>
        <tr>
            <th>Typ (fix)</th>
            <th>Bezeichnung</th>
            <th>Preis (CHF)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $key => $cat): ?>
            <tr>
                <td><?= esc($key) ?></td>
                <td>
                    <input type="text" name="categories[<?= esc($key) ?>][name]" value="<?= esc($cat['name']) ?>" class="form-control">
                </td>
                <td>
                    <input type="number" name="categories[<?= esc($key) ?>][price]" value="<?= esc($cat['price']) ?>" step="0.05" min="0" class="form-control">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary">Speichern</button>
</form>


<?= $this->endSection() ?>
