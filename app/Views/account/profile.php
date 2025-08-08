<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc(lang('Profile.titleAccount')) ?></h2>

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
        <input
                class="form-check-input"
                type="checkbox"
                id="auto_purchase"
                name="auto_purchase"
                value="1"
            <?= old('auto_purchase', $user->auto_purchase) ? 'checked' : '' ?>
        >
        <label class="form-check-label" for="auto_purchase">
            <?= esc(lang('Profile.autoPurchase')) ?>
        </label>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.companyName')) ?></label>
        <input type="text" name="company_name" id="company-name" class="form-control" value="<?= esc($user->company_name) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.contactPerson')) ?></label>
        <input type="text" name="contact_person" class="form-control" value="<?= esc($user->contact_person) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label">
            <?= esc(lang('Profile.companyUID')) ?>
            <a id="zefix-link" href="https://www.zefix.ch/de/search/entity/welcome" target="_blank">Zefix</a>
        </label>
        <input type="text" name="company_uid" class="form-control"
               value="<?= esc($user->company_uid) ?>"
               required
               pattern="CHE-\d{3}\.\d{3}\.\d{3}"
               title="<?= esc(lang('Profile.companyUIDPattern')) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.street')) ?></label>
        <input type="text" name="company_street" class="form-control" value="<?= esc($user->company_street) ?>" required="required">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label"><?= esc(lang('Profile.zip')) ?></label>
            <input type="text" name="company_zip" class="form-control" value="<?= esc($user->company_zip) ?>" required="required">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label"><?= esc(lang('Profile.city')) ?></label>
            <input type="text" name="company_city" class="form-control" value="<?= esc($user->company_city) ?>" required="required">
        </div>
    </div>

    <?php if(strlen($user->company_street) > 3 && strlen($user->company_zip) > 1 && strlen($user->company_city) > 1) { ?>

        <h5 class="mt-4"><?= esc(lang('Profile.mapPreview')) ?></h5>

        <script>
            // Dein JS unverändert
        </script>

        <div id="iframe-map-container" style="width: 100%; height: 600px;" class="mb-3 border">
            <iframe
                    id="iframe-map"
                    width="100%"
                    height="100%"
                    frameborder="0"
                    scrolling="no"
                    marginheight="0"
                    marginwidth="0"
                    src=""
                    allowfullscreen
            ></iframe>
        </div>

    <?php } ?>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.website')) ?></label>
        <input type="text" name="company_website" class="form-control" value="<?= esc($user->company_website) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.companyEmail')) ?></label>
        <input type="email" name="company_email" class="form-control" value="<?= esc($user->company_email) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Profile.phone')) ?></label>
        <input type="text" name="company_phone" class="form-control" value="<?= esc($user->company_phone) ?>">
    </div>


    <button type="submit" class="btn btn-primary"><?= esc(lang('Profile.saveButton')) ?></button>
</form>

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
