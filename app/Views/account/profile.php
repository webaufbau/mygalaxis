<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title) ?></h2>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif ?>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form method="post" action="/profile">
    <?= csrf_field() ?>

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
            Automatischer Kauf von passenden Angeboten aktivieren
        </label>
    </div>

    <div class="mb-3">
        <label class="form-label">Firmenname</label>
        <input type="text" name="company_name" class="form-control" value="<?= esc($user->company_name) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Ansprechperson</label>
        <input type="text" name="contact_person" class="form-control" value="<?= esc($user->contact_person) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">UID</label>
        <input type="text" name="company_uid" class="form-control" value="<?= esc($user->company_uid) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Strasse</label>
        <input type="text" name="company_street" class="form-control" value="<?= esc($user->company_street) ?>">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">PLZ</label>
            <input type="text" name="company_zip" class="form-control" value="<?= esc($user->company_zip) ?>">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">Ort</label>
            <input type="text" name="company_city" class="form-control" value="<?= esc($user->company_city) ?>">
        </div>
    </div>

    <h5 class="mt-4">Vorschau Standort (Google Maps)</h5>
    <div id="map" style="width: 100%; height: 300px;" class="mb-3 border"></div>

    <script>
        function initMap() {
            const address = [
                document.querySelector('input[name="company_street"]').value,
                document.querySelector('input[name="company_zip"]').value,
                document.querySelector('input[name="company_city"]').value,
                'Schweiz'
            ].join(', ');

            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': address }, function(results, status) {
                if (status === 'OK') {
                    const map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 14,
                        center: results[0].geometry.location
                    });
                    new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });
                } else {
                    document.getElementById('map').innerHTML = '<div class="text-muted p-3">Adresse konnte nicht geladen werden</div>';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initMap();

            // Map bei Adressänderung aktualisieren
            ['company_street', 'company_zip', 'company_city'].forEach(id => {
                document.getElementById(id).addEventListener('change', initMap);
            });
        });
    </script>

    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=DEIN_API_KEY&callback=initMap">
    </script>


    <div class="mb-3">
        <label class="form-label">Website</label>
        <input type="url" name="company_website" class="form-control" value="<?= esc($user->company_website) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">E-Mail (Firma)</label>
        <input type="email" name="company_email" class="form-control" value="<?= esc($user->company_email) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefon</label>
        <input type="text" name="company_phone" class="form-control" value="<?= esc($user->company_phone) ?>">
    </div>


    <button type="submit" class="btn btn-primary">Speichern</button>
</form>

<?= $this->endSection() ?>
