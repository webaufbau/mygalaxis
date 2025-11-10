<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Profile.changePassword')) ?></h2>

<div class="row">
    <div class="col-lg-6">

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
        <form method="post" action="/profile/password/update">
            <?= csrf_field() ?>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <?= esc(lang('Profile.passwordChangeInfo')) ?>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Profile.currentPassword')) ?> *</label>
                <input type="password" name="current_password" class="form-control" required
                       autocomplete="current-password">
            </div>

            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Profile.newPassword')) ?> *</label>
                <input type="password" name="new_password" class="form-control" required
                       minlength="8"
                       autocomplete="new-password">
                <small class="form-text text-muted"><?= esc(lang('Profile.passwordRequirements')) ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Profile.confirmPassword')) ?> *</label>
                <input type="password" name="confirm_password" class="form-control" required
                       minlength="8"
                       autocomplete="new-password">
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
