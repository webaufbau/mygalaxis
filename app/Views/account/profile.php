<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="my-4"><?= esc($title) ?></h2>

<form method="post" action="/profile/update">
    <?= csrf_field() ?>

    <!--
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
    -->

    <div class="mb-3">
        <label class="form-label">Firmenname</label>
        <input type="text" name="company_name" class="form-control" value="<?= esc($user->company_name) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label">Ansprechperson</label>
        <input type="text" name="contact_person" class="form-control" value="<?= esc($user->contact_person) ?>" required="required">
    </div>

    <div class="mb-3">
        <label class="form-label">
            Firmen-UID
            <a id="zefix-link" href="https://www.zefix.ch/de/search/entity/welcome" target="_blank">Zefix</a>
        </label>
        <input type="text" name="company_uid" class="form-control"
               value="<?= esc($user->company_uid) ?>"
               required
               pattern="CHE-\d{3}\.\d{3}\.\d{3}"
               title="Bitte geben Sie eine gültige UID im Format CHE-123.456.789 ein.">
    </div>

    <div class="mb-3">
        <label class="form-label">Strasse</label>
        <input type="text" name="company_street" class="form-control" value="<?= esc($user->company_street) ?>" required="required">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">PLZ</label>
            <input type="text" name="company_zip" class="form-control" value="<?= esc($user->company_zip) ?>" required="required">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">Ort</label>
            <input type="text" name="company_city" class="form-control" value="<?= esc($user->company_city) ?>" required="required">
        </div>
    </div>

    <?php if(strlen($user->company_street) > 3 && strlen($user->company_zip) > 1 && strlen($user->company_city) > 1) { ?>

    <h5 class="mt-4">Vorschau Standort (Google Maps)</h5>

    <script>
        function initMap() {
            const address = [
                document.querySelector('input[name="company_street"]').value,
                document.querySelector('input[name="company_zip"]').value,
                document.querySelector('input[name="company_city"]').value,
                'Schweiz'
            ].join(', ');
console.log('address', address);
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

        function updateIframeMap() {
            const street = encodeURIComponent(document.querySelector('input[name="company_street"]').value);
            const zip = encodeURIComponent(document.querySelector('input[name="company_zip"]').value);
            const city = encodeURIComponent(document.querySelector('input[name="company_city"]').value);
            const country = encodeURIComponent('Schweiz');

            const address = [street, zip, city, country].filter(Boolean).join('%20');

            const iframeSrc = `https://maps.google.com/maps?width=100%25&height=600&hl=de&q=${address}&t=&z=14&ie=UTF8&iwloc=B&output=embed`;

            document.getElementById('iframe-map').src = iframeSrc;
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateIframeMap();

            ['company_street', 'company_zip', 'company_city'].forEach(id => {
                document.querySelector(`input[name="${id}"]`).addEventListener('change', updateIframeMap);
            });
        });

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
        <label class="form-label">Website</label>
        <input type="text" name="company_website" class="form-control" value="<?= esc($user->company_website) ?>">
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
