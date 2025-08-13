<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$siteConfig = siteconfig();

// Firmen-ID / UID
if($siteConfig->companyUidCheck == 'ch') {
    $companyUidLink = 'https://www.zefix.ch/de/search/entity/welcome';
    $companyUidName = 'Zefix';
    $companyUidInputmask = 'CHE-999.999.999';
    $companyUidPlaceholder = 'CHE-123.456.789';
    $companyUidPattern = '^CHE-[0-9]{3}\.[0-9]{3}\.[0-9]{3}$';
    $companyUidInvalidFeedback = sprintf(
        lang('Auth.companyUidRequired'), // z.B. "Bitte geben Sie die UID im Format %s ein."
        $companyUidPlaceholder
    );
}
elseif($siteConfig->companyUidCheck == 'at') {
    $companyUidLink = 'https://justizonline.gv.at/jop/web/firmenbuchabfrage';
    $companyUidName = 'Firmenbuch';
    $companyUidInputmask = 'FN999999[a]';
    $companyUidPlaceholder = 'FN123456a';
    $companyUidPattern = 'FN[0-9]{1,6}[a-z]$';
    $companyUidInvalidFeedback = sprintf(
        lang('Auth.companyUidRequired'),
        $companyUidPlaceholder
    );
}
elseif($siteConfig->companyUidCheck == 'de') {
    $companyUidLink = 'https://www.unternehmensregister.de/de/suche';
    $companyUidName = 'Unternehmensregister';
    $companyUidInputmask = 'DEB****.*99999';
    $companyUidPlaceholder = 'DEXxxxx.HRB12345';
    $companyUidPattern = '^DE[A-Z0-9]{4,8}\.(HRB|HRA|GsR)[0-9]{1,5}$';
    $companyUidInvalidFeedback = sprintf(
        lang('Auth.companyUidRequired'),
        $companyUidPlaceholder
    );
}

// Telefonnummer
if($siteConfig->phoneCheck == 'ch') {
    $companyPhonePlaceholder = '+41 78 123 45 67';
    $companyPhoneInputmask = '+99 99 999 99 99';
    $companyPhonePattern = '^\+41\s\d{2}\s\d{3}\s\d{2}\s\d{2}$';
    $companyPhoneInvalidFeedback = sprintf(
        lang('Auth.companyPhoneRequired'),
        $companyPhonePlaceholder
    );
}
elseif($siteConfig->phoneCheck == 'at') {
    $companyPhonePlaceholder = '+43 660 1234567';
    $companyPhoneInputmask = '+43 999 9999999';
    $companyPhonePattern = '^\+43\s\d{1,3}\s\d{5,7}$';
    $companyPhoneInvalidFeedback = sprintf(
        lang('Auth.companyPhoneRequired'),
        $companyPhonePlaceholder
    );
}
elseif($siteConfig->phoneCheck == 'de') {
    $companyPhonePlaceholder = '+49 30 12345678';
    $companyPhoneInputmask = '+49 99999 9999999';
    $companyPhonePattern = '^\+49\s\d{1,5}\s\d{5,8}$';
    $companyPhoneInvalidFeedback = sprintf(
        lang('Auth.companyPhoneRequired'),
        $companyPhonePlaceholder
    );
}


?>



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
            <label for="email" class="form-label"><?= lang('Auth.emailAddress') ?> *</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
            <div class="invalid-feedback">
                <?= lang('Auth.emailRequired') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label"><?= lang('Auth.password') ?> *</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    <?= lang('Auth.passwordMinLength') ?>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirm" class="form-label"><?= lang('Auth.passwordConfirm') ?> *</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="6">
                <div class="invalid-feedback">
                    <?= lang('Auth.passwordConfirmRequired') ?>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h4 class="mb-3"><?= lang('Auth.companyDataTitle') ?></h4>

        <div class="mb-3">
            <label for="company_name" class="form-label"><?= lang('Auth.companyName') ?> *</label>
            <input type="text" name="company_name" id="company_name" class="form-control" required>
            <div class="invalid-feedback">
                <?= lang('Auth.companyNameRequired') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_uid" class="form-label"><?= lang('Auth.companyUid') ?> * <?php if($siteConfig->companyUidCheck !== '') { echo '<a href="'.$companyUidLink.'" target="_blank">'.$companyUidName.'</a>'; } ?></label>
            <input
                    type="text"
                    name="company_uid"
                    id="company_uid"
                    class="form-control"
                    required
                    <?php if(isset($companyUidPattern)) { echo 'pattern="'.$companyUidPattern.'"'; } ?>
                    <?php if(isset($companyUidPlaceholder)) { echo 'placeholder="'.$companyUidPlaceholder.'"'; } ?>
            >
            <div class="invalid-feedback">
                <?=$companyUidInvalidFeedback ?? '';?>
            </div>
        </div>

        <div class="mb-3">
            <label for="company_street" class="form-label"><?= lang('Auth.companyStreet') ?> *</label>
            <input type="text" name="company_street" id="company_street" class="form-control" required>
            <div class="invalid-feedback">
                <?= lang('Auth.companyStreetRequired') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="company_zip" class="form-label"><?= lang('Auth.companyZip') ?> *</label>
                <input type="text" name="company_zip" id="company_zip" class="form-control" required>
                <div class="invalid-feedback">
                    <?= lang('Auth.companyZipRequired') ?>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <label for="company_city" class="form-label"><?= lang('Auth.companyCity') ?> *</label>
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
                    <?php if(isset($companyPhonePattern)) { echo 'pattern="'.$companyPhonePattern.'"'; } ?>
                    <?php if(isset($companyPhonePlaceholder)) { echo 'placeholder="'.$companyPhonePlaceholder.'"'; } ?>
                    title="<?= lang('Auth.companyPhoneRequired') ?>"
            >
            <div class="invalid-feedback">
                <?=$companyPhoneInvalidFeedback ?? ''; ?>
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
            <label for="accept_agb" class="form-label"><?= lang('General.acceptAGB') ?> *</label>
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
    <?php if(isset($companyUidInputmask) && $companyUidInputmask !== '') { ?>
    $('#company_uid').inputmask({
        mask: "<?=$companyUidInputmask;?>",
        definitions: {
            'A': { validator: "[A-Z]" },
            '9': { validator: "[0-9]" },
            '*': { validator: "[A-Za-z0-9.]"}
        },
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true
    });
    <?php } ?>

    <?php if(isset($companyUidPattern) && $companyUidPattern !== '') { ?>
    $('#company_phone').inputmask({
        mask: "+99 99 999 99 99",
        definitions: {
            'A': { validator: "[A-Z]" },
            '9': { validator: "[0-9]" },
            '*': { validator: "[A-Z0-9.]"}
        },
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true
    });
    <?php } ?>

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
