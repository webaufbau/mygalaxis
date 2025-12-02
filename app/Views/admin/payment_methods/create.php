<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<h1 class="my-3"><?= esc($title) ?></h1>

<form action="<?= site_url('/admin/paymentmethods/create') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="code" class="form-label">Code (eindeutig)</label>
        <input type="text" name="code" id="code" class="form-control" value="<?= old('code') ?>" required>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control" value="<?= old('name') ?>" required>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" name="active" id="active" class="form-check-input" checked>
        <label for="active" class="form-check-label">Aktiv</label>
    </div>

    <button type="submit" class="btn btn-primary">Speichern</button>
    <a href="<?= site_url('/admin/paymentmethods') ?>" class="btn btn-secondary">Abbrechen</a>
</form>


<?= $this->endSection() ?>
