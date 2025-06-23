<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h1>Login</h1>

<form method="post" action="/login" class="w-50 mx-auto">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email Adresse</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Passwort</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>

<?= $this->endSection() ?>
