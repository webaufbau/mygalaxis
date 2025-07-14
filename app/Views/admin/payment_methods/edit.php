<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1 class="my-3"><?= esc($title) ?></h1>

<form action="<?= site_url('/admin/paymentmethods/edit/' . $method['id']) ?>" method="post">
    <?= csrf_field() ?>

    <input type="hidden" name="code" id="code" class="form-control" value="<?= old('code', $method['code']) ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control" value="<?= old('name', $method['name']) ?>" required>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" name="active" id="active" class="form-check-input" <?= $method['active'] ? 'checked' : '' ?>>
        <label for="active" class="form-check-label ms-1">Aktiv</label>
    </div>

    <button type="submit" class="btn btn-primary">Speichern</button>
    <a href="<?= site_url('/admin/paymentmethods') ?>" class="btn btn-secondary">Abbrechen</a>
</form>


<?= $this->endSection() ?>
