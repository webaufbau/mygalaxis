<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<h2>Passwort vergessen</h2>
<p>Gib deine E-Mail-Adresse ein, um einen Link zum Zurücksetzen des Passworts zu erhalten.</p>

<form method="post" action="/auth/forgot-password">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">E-Mail-Adresse</label>
        <input type="email" id="email" name="email" class="form-control" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary">Link anfordern</button>
</form>

<?= $this->endSection() ?>
