<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Profile.changeEmail')) ?></h2>

<div class="row">
    <div class="col-lg-6">

        <!-- Wichtiger Hinweis -->
        <div class="alert alert-warning mb-4">
            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> <?= esc(lang('Profile.loginEmailNotice')) ?></h6>
            <p class="mb-2"><?= esc(lang('Profile.loginEmailNoticeText')) ?></p>
            <hr>
            <p class="mb-0">
                <i class="bi bi-arrow-right"></i>
                <strong><?= esc(lang('Profile.changeCompanyEmailHere')) ?>:</strong>
                <a href="/profile" class="alert-link"><?= esc(lang('Profile.goToProfile')) ?></a>
            </p>
        </div>

        <!-- Aktuelle E-Mail anzeigen -->
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> <?= esc(lang('Profile.currentEmail')) ?> (Login)</h6>
            <p class="mb-0"><strong><?= esc($user->email) ?></strong></p>
        </div>

        <!-- Fehler-Meldungen -->
        <?php if (session('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <!-- Formular -->
        <form method="post" action="/profile/email/update">
            <?= csrf_field() ?>

            <?php if (!session('success')): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <?= esc(lang('Profile.emailChangeWarning')) ?>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Profile.newEmail')) ?> *</label>
                <input type="email" name="new_email" class="form-control" required
                       value="<?= old('new_email') ?>"
                       autocomplete="off">
            </div>

            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Profile.currentPassword')) ?> *</label>
                <input type="password" name="current_password" class="form-control" required
                       autocomplete="current-password">
                <small class="form-text text-muted"><?= esc(lang('Profile.currentPasswordHelp')) ?></small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> <?= esc(lang('Profile.saveButton')) ?>
                </button>
                <a href="/profile" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <?= esc(lang('General.back')) ?>
                </a>
            </div>
        </form>

    </div>
</div>

<?= $this->endSection() ?>
