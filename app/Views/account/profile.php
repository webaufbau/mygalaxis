<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$siteConfig = siteconfig();
$authUser = auth()->user();

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
    $companyUidInputmask = 'DEA****.***99999';
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
<h2 class="my-4"><?= esc(lang('Profile.titleAccount')) ?></h2>

<!-- Aktuelle Kontodaten Übersicht -->
<div class="alert alert-info mb-4">
    <h5 class="alert-heading mb-3"><i class="bi bi-info-circle"></i> <?= esc(lang('Profile.currentAccountData')) ?></h5>
    <div class="row">
        <div class="col-md-6">
            <p class="mb-2"><strong><?= esc(lang('Profile.companyName')) ?>:</strong><br><?= esc($user->company_name) ?></p>
            <p class="mb-2"><strong><?= esc(lang('Profile.contactPerson')) ?>:</strong><br><?= esc($user->contact_person) ?></p>
            <p class="mb-2"><strong><?= esc(lang('Profile.companyEmail')) ?>:</strong><br><?= esc($user->company_email) ?></p>
            <p class="mb-0"><strong><?= esc(lang('Profile.phone')) ?>:</strong><br><?= esc($user->company_phone) ?></p>
        </div>
        <div class="col-md-6">
            <p class="mb-2">
                <strong><?= esc(lang('Profile.loginEmail')) ?>:</strong><br>
                <?= esc($authUser->email) ?><br>
                <small class="text-muted"><i class="bi bi-envelope"></i> <?= esc(lang('Profile.loginEmailReceivesNotifications')) ?></small>
            </p>
            <p class="mb-2"><strong><?= esc(lang('Profile.street')) ?>:</strong><br><?= esc($user->company_street) ?></p>
            <p class="mb-2"><strong><?= esc(lang('Profile.zip')) ?> / <?= esc(lang('Profile.city')) ?>:</strong><br><?= esc($user->company_zip) ?> <?= esc($user->company_city) ?></p>
            <?php if(!empty($user->company_website)): ?>
            <p class="mb-0"><strong><?= esc(lang('Profile.website')) ?>:</strong><br><?= esc($user->company_website) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <hr class="my-3">
    <small class="text-muted"><i class="bi bi-shield-check"></i> <?= esc(lang('Profile.currentDataInfo')) ?></small>
</div>

<h4 class="mb-3"><?= esc(lang('Profile.updateAccountData')) ?></h4>

<form method="post" action="/profile/update">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.languageAccountEmail')) ?></label>
        <?php
        $locales = ['de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'it' => 'Italiano'];
        $currentLocale = $user->language;
        ?>

        <select name="language" id="language" class="form-control form-select">
            <?php foreach ($locales as $code => $name):
                $selected = ($code === $currentLocale) ? 'selected' : '';
                ?>
                <option value="<?= esc($code) ?>" <?= $selected ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-check form-switch mb-4">
        <input type="hidden" name="email_notifications_enabled" value="0">
        <input
                class="form-check-input"
                type="checkbox"
                id="email_notifications_enabled"
                name="email_notifications_enabled"
                value="1"
            <?= old('email_notifications_enabled', $user->email_notifications_enabled ?? 1) ? 'checked' : '' ?>
        >
        <label class="form-check-label" for="email_notifications_enabled">
            Tägliche Update-E-Mails erhalten
        </label>
        <small class="form-text text-muted d-block">
            Deaktivieren Sie diese Option, um keine täglichen Benachrichtigungen über neue Offerten mehr zu erhalten.
        </small>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.companyName')) ?> *</label>
        <input type="text" name="company_name" id="company-name" class="form-control" value="<?= esc($user->company_name) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.contactPerson')) ?> *</label>
        <input type="text" name="contact_person" class="form-control" value="<?= esc($user->contact_person) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label">
            <?= esc(lang('Profile.companyUID')) ?> <?php if($siteConfig->companyUidCheck !== '') { echo '<a href="'.$companyUidLink.'" target="_blank">'.$companyUidName.'</a>'; } ?></label>
        </label>
        <input type="text" name="company_uid" class="form-control"
               value="<?= esc($user->company_uid) ?>"

        <?php if(isset($companyUidPattern)) { echo 'pattern="'.$companyUidPattern.'"'; } ?>
        <?php if(isset($companyUidPlaceholder)) { echo 'placeholder="'.$companyUidPlaceholder.'"'; } ?>
        <?php if(isset($companyUidInvalidFeedback)) { echo 'title="'.$companyUidInvalidFeedback.'"'; } ?>
        >
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.street')) ?> *</label>
        <input type="text" name="company_street" class="form-control" value="<?= esc($user->company_street) ?>" required="required">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label"><?= esc(lang('Profile.zip')) ?> *</label>
            <input type="text" name="company_zip" class="form-control" value="<?= esc($user->company_zip) ?>" required="required">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label"><?= esc(lang('Profile.city')) ?> *</label>
            <input type="text" name="company_city" class="form-control" value="<?= esc($user->company_city) ?>" required="required">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.website')) ?></label>
        <input type="text" name="company_website" class="form-control" value="<?= esc($user->company_website) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.companyEmail')) ?> *</label>
        <input type="email" name="company_email" class="form-control" value="<?= esc($user->company_email) ?>">
        <small class="form-text text-muted"><?= esc(lang('Profile.companyEmailHelp')) ?></small>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.phone')) ?> *</label>
        <input type="text" name="company_phone" class="form-control" value="<?= esc($user->company_phone) ?>"
               required
            <?php if(isset($companyPhonePattern)) { echo 'pattern="'.$companyPhonePattern.'"'; } ?>
            <?php if(isset($companyPhonePlaceholder)) { echo 'placeholder="'.$companyPhonePlaceholder.'"'; } ?>
            <?php if(isset($companyPhoneInvalidFeedback)) { echo 'title="'.$companyPhoneInvalidFeedback.'"'; } ?>
        >
    </div>

    <button type="submit" class="btn btn-primary"><?= esc(lang('Profile.saveButton')) ?></button>
</form>

<hr class="my-4">

<!-- Login & Sicherheit -->
<h4 class="mt-4"><i class="bi bi-shield-lock"></i> <?= esc(lang('Profile.loginAndSecurity')) ?></h4>
<p class="text-muted"><?= esc(lang('Profile.loginSecurityDescription')) ?></p>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-envelope"></i> <?= esc(lang('Profile.changeEmail')) ?></h5>
                <p class="card-text text-muted"><?= esc(lang('Profile.changeEmailDescription')) ?></p>
                <p class="mb-2"><strong><?= esc(lang('Profile.currentEmail')) ?>:</strong><br><?= esc(auth()->user()->email) ?></p>
                <a href="/profile/email" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> <?= esc(lang('Profile.changeEmail')) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-key"></i> <?= esc(lang('Profile.changePassword')) ?></h5>
                <p class="card-text text-muted"><?= esc(lang('Profile.changePasswordDescription')) ?></p>
                <p class="mb-2"><strong><?= esc(lang('Profile.lastChanged')) ?>:</strong><br><?= esc(lang('Profile.securePassword')) ?></p>
                <a href="/profile/password" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> <?= esc(lang('Profile.changePassword')) ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('company-name').addEventListener('input', function () {
        const name = encodeURIComponent(this.value.trim());
        const link = document.getElementById('zefix-link');

        if (name) {
            link.href = `https://www.zefix.ch/de/search/entity/list?mainSearch=${name}`;
        } else {
            link.href = 'https://www.zefix.ch/de/search/entity/welcome';
        }
    });
</script>

<?= $this->endSection() ?>
