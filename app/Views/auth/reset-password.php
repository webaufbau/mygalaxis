<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h2>Neues Passwort setzen</h2>
<form method="post" action="/auth/reset-password">
    <?= csrf_field() ?>

    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <div class="mb-3">
        <label for="password" class="form-label">Neues Passwort</label>
        <input type="password" id="password" name="password" class="form-control" required minlength="6">
    </div>
    <div class="mb-3">
        <label for="password_confirm" class="form-label">Passwort bestätigen</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="6">
    </div>
    <button type="submit" class="btn btn-primary">Passwort zurücksetzen</button>
</form>

<?= $this->endSection() ?>
