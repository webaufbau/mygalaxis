<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<h1>Zahlung fehlgeschlagen</h1>
<p>Ihre Aufladung konnte leider nicht abgeschlossen werden.</p>
<a href="<?= site_url('finance') ?>" class="btn btn-primary">Zurück zur Aufladung</a>
<?= $this->endSection() ?>
