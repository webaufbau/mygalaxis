<?= $this->extend('layout/minimal') ?>
<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">

            <div class="mb-4">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>

            <h2 class="mb-3">Vielen Dank für Ihre Anfrage!</h2>

            <p class="lead text-muted mb-4">
                Ihre Anfrage wurde erfolgreich übermittelt. Sie erhalten in Kürze Offerten von qualifizierten Fachbetrieben.
            </p>

            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Ihre Anfrage umfasst:</h6>
                    <ul class="list-unstyled mb-0">
                        <?php if (!empty($sessionData['form_links'])): ?>
                            <?php foreach ($sessionData['form_links'] as $link): ?>
                                <li><i class="bi bi-check text-success me-2"></i><?= esc($link['name']) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-envelope me-2"></i>
                Eine Bestätigung wurde an Ihre E-Mail-Adresse gesendet.
            </div>

            <a href="<?= site_url('/') ?>" class="btn btn-primary">
                Zur Startseite
            </a>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
