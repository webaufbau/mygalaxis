<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach (session('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<div class="p-4 border rounded-2 bg-light">
    <h2 class="my-4"><?= lang('Auth.registerTitle') ?></h2>

    <form method="post" action="<?=lang_url('register');?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>

        <!-- Protection (Honeypot) -->
        <div class="mb-2 d-none">
            <label for="registerHP" class="form-label"><?= lang('Auth.registerhp') ?></label>
            <input type="text" class="form-control" id="registerHP" name="registerhp" inputmode="text" autocomplete="registerhp" placeholder="<?= lang('Auth.registerhp') ?>" value="<?= old('registerhp') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label"><?= lang('Auth.emailAddress') ?></label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
            <div class="invalid-feedback">
                <?= lang('Auth.emailRequired') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label"><?= lang('Auth.password') ?></label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    <?= lang('Auth.passwordMinLength') ?>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirm" class="form-label"><?= lang('Auth.passwordConfirm') ?></label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    <?= lang('Auth.passwordConfirmRequired') ?>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h4 class="mb-3"><?= lang('Auth.companyDataTitle') ?></h4>

        <div class="mb-3">
            <label for="company_name" class="form-label"><?= lang('Auth.companyName') ?></label>
            <input type="text" name="company_name" id="company_name" class="form-control" required>
            <div class="invalid-feedback">
                <?= lang('Auth.companyNameRequired') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_uid" class="form-label"><?= lang('Auth.companyUid') ?></label>
            <input
                    type="text"
                    name="company_uid"
                    id="company_uid"
                    class="form-control"
                    required
                    placeholder="CHE-___.___.___"
            >
            <div class="invalid-feedback">
                <?= lang('Auth.companyUidRequired') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_street" class="form-label"><?= lang('Auth.companyStreet') ?></label>
            <input type="text" name="company_street" id="company_street" class="form-control" required>
            <div class="invalid-feedback">
                <?= lang('Auth.companyStreetRequired') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="company_zip" class="form-label"><?= lang('Auth.companyZip') ?></label>
                <input type="text" name="company_zip" id="company_zip" class="form-control" required>
                <div class="invalid-feedback">
                    <?= lang('Auth.companyZipRequired') ?>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <label for="company_city" class="form-label"><?= lang('Auth.companyCity') ?></label>
                <input type="text" name="company_city" id="company_city" class="form-control" required>
                <div class="invalid-feedback">
                    <?= lang('Auth.companyCityRequired') ?>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_phone" class="form-label"><?= lang('Auth.companyPhone') ?></label>
            <input
                    type="tel"
                    name="company_phone"
                    id="company_phone"
                    class="form-control"
                    required
                    pattern="^\+\d{2}\s?\d{2}\s?\d{3}\s?\d{2}\s?\d{2}$"
                    placeholder="+41 78 123 45 67"
                    title="<?= lang('Auth.companyPhoneRequired') ?>"
            >
            <div class="invalid-feedback">
                <?= lang('Auth.companyPhoneRequired') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_website" class="form-label"><?= lang('Auth.companyWebsite') ?></label>
            <input type="text" name="company_website" id="company_website" class="form-control">
            <div class="invalid-feedback">
                <?= lang('Auth.companyWebsiteRequired') ?>
            </div>
        </div>

        <div class="mb-3">
            <input type="checkbox" name="accept_agb" id="accept_agb" class="form-radio" value="1" required>
            <label for="accept_agb" class="form-label"><?= lang('General.acceptAGB') ?></label>
            <div class="invalid-feedback">
                <?= lang('Auth.acceptAGBRequired') ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3"><?= lang('Auth.registerButton') ?></button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js"></script>

<script defer>
    $(document).ready(function() {
        // UID-Maske
        $('#company_uid').inputmask('CHE-999.999.999');

        // Schweizer Telefonnummer mit Ländervorwahl und Leerzeichen
        $('#company_phone').inputmask({
            mask: "+99 99 999 99 99",
            placeholder: "_",
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true
        });

        // Bootstrap Validierung
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        // Passwort und Bestätigung prüfen
                        var pwd = form.querySelector('#password');
                        var pwdConfirm = form.querySelector('#password_confirm');
                        if (pwd.value !== pwdConfirm.value) {
                            pwdConfirm.setCustomValidity("<?= lang('Auth.passwordsMismatch') ?>");
                        } else {
                            pwdConfirm.setCustomValidity("");
                        }

                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    });
</script>

<?= $this->endSection() ?>
