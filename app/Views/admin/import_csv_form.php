<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<h1><?= esc($page_title) ?></h1>

<form action="<?= site_url('admin/campaign/import_csv_process') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="csv_file" class="form-label">CSV-Datei auswählen</label>
        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,text/csv" required>
    </div>

    <button type="submit" class="btn btn-primary">Import starten</button>
    <a href="<?= site_url('admin/campaign/download-sample-csv') ?>" class="btn btn-outline-secondary">Vorlage herunterladen</a>
</form>
<?= $this->endSection() ?>
