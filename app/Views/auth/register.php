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
    <h2 class="my-4">Registrieren</h2>
    <form method="post" action="/register" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <!--<input type="hidden" name="username" value="user_<?php echo uniqid(); ?>">-->


        <!-- Protection -->
        <div class="mb-2 d-none">
            <label for="registerHP" class="form-label"><?= lang('Auth.registerhp') ?></label>
            <input type="text" class="form-control" id="registerHP" name="registerhp" inputmode="text" autocomplete="registerhp" placeholder="<?= lang('Auth.registerhp') ?>" value="<?= old('registerhp') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-Mail-Adresse</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
            <div class="invalid-feedback">
                Bitte geben Sie eine gültige E-Mail-Adresse ein.
            </div>
        </div>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Passwort</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    Bitte geben Sie ein Passwort mit mindestens 6 Zeichen ein.
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirm" class="form-label">Passwort bestätigen</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    Bitte bestätigen Sie Ihr Passwort.
                </div>
            </div>

        </div>

        <hr class="my-4">

        <h4 class="mb-3">Firmendaten</h4>

        <div class="mb-3">
            <label for="company_name" class="form-label">Firmenname</label>
            <input type="text" name="company_name" id="company_name" class="form-control" required>
            <div class="invalid-feedback">
                Bitte geben Sie den Firmennamen ein.
            </div>
        </div>

        <div class="mb-3">
            <label for="company_uid" class="form-label">Handelsregister (UID)</label>
            <input
                    type="text"
                    name="company_uid"
                    id="company_uid"
                    class="form-control"
                    required
                    placeholder="CHE-___.___.___"
            >
            <div class="invalid-feedback">
                Bitte geben Sie die UID im Format CHE-999.999.999 ein.
            </div>
        </div>

        <div class="mb-3">
            <label for="company_street" class="form-label">Strasse</label>
            <input type="text" name="company_street" id="company_street" class="form-control" required>
            <div class="invalid-feedback">
                Bitte geben Sie die Strasse ein.
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="company_zip" class="form-label">PLZ</label>
                <input type="text" name="company_zip" id="company_zip" class="form-control" required>
                <div class="invalid-feedback">
                    Bitte geben Sie die Postleitzahl ein.
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <label for="company_city" class="form-label">Ort</label>
                <input type="text" name="company_city" id="company_city" class="form-control" required>
                <div class="invalid-feedback">
                    Bitte geben Sie den Ort ein.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_phone" class="form-label">Telefonnummer</label>
            <input
                    type="tel"
                    name="company_phone"
                    id="company_phone"
                    class="form-control"
                    required
                    pattern="^\+41\s?\d{2}\s?\d{3}\s?\d{2}\s?\d{2}$"
                    placeholder="+41 78 123 45 67"
                    title="Bitte geben Sie eine gültige Schweizer Mobilnummer im Format +41 78 123 45 67 ein."
            >
            <div class="invalid-feedback">
                Bitte geben Sie eine gültige Telefonnummer im Format +41 78 123 45 67 ein.
            </div>
        </div>

        <div class="mb-3">
            <label for="company_website" class="form-label">Website</label>
            <input type="url" name="company_website" id="company_website" class="form-control">
            <div class="invalid-feedback">
                Bitte geben Sie eine gültige URL ein.
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Registrieren</button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js"></script>

<script defer>
    $(document).ready(function() {
        // UID-Maske
        $('#company_uid').inputmask('CHE-999.999.999');

        // Schweizer Telefonnummer mit Ländervorwahl und Leerzeichen
        $('#company_phone').inputmask({
            mask: "+41 99 999 99 99",
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
                            pwdConfirm.setCustomValidity("Die Passwörter stimmen nicht überein.");
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
