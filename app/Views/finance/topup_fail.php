<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<h1><?= lang('Finance.topupFailTitle') ?></h1>
<p><?= lang('Finance.topupFailMessage') ?></p>
<a href="<?= site_url('finance') ?>" class="btn btn-primary"><?= lang('Finance.backToTopup') ?></a>
<?= $this->endSection() ?>
