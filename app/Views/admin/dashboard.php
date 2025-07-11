<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>


<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


<!-- Filter -->
<form method="get" class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Von</label>
        <input type="date" name="from" class="form-control" value="<?= esc($request['from'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bis</label>
        <input type="date" name="to" class="form-control" value="<?= esc($request['to'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Typ</label>
        <select name="type" id="typeSelect" class="form-select">
            <option value="">Alle</option>
            <?php foreach ($types as $typeValue => $typeLabel): ?>
                <option value="<?= esc($typeValue) ?>" <?= (isset($filter_type) && $filter_type === $typeValue) ? 'selected' : '' ?>>
                    <?= esc($typeLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($filter_type == 'move'): ?>
        <h4>Umzug Filterung</h4>
        <div class="col-md-3">
            <label class="form-label">Zimmergrösse</label>
            <input type="text" name="room_size" class="form-control" value="<?= esc($request['room_size'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Umzugsdatum</label>
            <input type="date" name="move_date" class="form-control" value="<?= esc($request['move_date'] ?? '') ?>">
        </div>
    <?php elseif ($filter_type == 'cleaning'): ?>
        <h4>Reinigung Filterung</h4>
        <div class="col-md-3">
            <label class="form-label">Reinigungsart</label>
            <input type="text" name="cleaning_type" class="form-control" value="<?= esc($request['cleaning_type'] ?? '') ?>">
        </div>
    <?php elseif ($filter_type == 'painting'): ?>
        <h4>Maler Filterung</h4>
        <div class="col-md-3">
            <label class="form-label">Fläche (m²)</label>
            <input type="text" name="area" class="form-control" value="<?= esc($request['area'] ?? '') ?>">
        </div>

    <?php elseif ($filter_type == 'gardening'): ?>
        <h4>Gartenpflege Filterung</h4>
        <div class="col-md-3">
            <label class="form-label">Arbeitsart</label>
            <input type="text" name="work_type" class="form-control" value="<?= esc($request['work_type'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fläche (m²)</label>
            <input type="number" name="area_m2" class="form-control" value="<?= esc($request['area_m2'] ?? '') ?>">
        </div>

    <?php elseif ($filter_type == 'plumbing'): ?>
        <h4>Sanitär Filterung</h4>
        <div class="col-md-3">
            <label class="form-label">Raum</label>
            <input type="text" name="affected_rooms" class="form-control" value="<?= esc($request['affected_rooms'] ?? '') ?>">
        </div>

    <?php endif; ?>

    <div class="col-md-3 d-grid align-items-end">
        <button class="btn btn-primary">Filtern</button>
    </div>

    <?php if(!empty($filter_type) || !empty($request['from']) || !empty($request['to'])): ?>
        <div class="col-md-3 d-grid align-items-end">
            <a href="<?= current_url() ?>" class="btn btn-secondary">Filter zurücksetzen</a>
        </div>
    <?php endif; ?>
</form>


<?php if(isset($offers) && is_array($offers) && count($offers)) { ?>
<!-- Tabelle -->
<table id="offersTable" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Datum</th>
        <th>Typ</th>
        <th>PLZ</th>
        <th>Ort</th>
        <th>Name</th>
        <th>Status</th>
        <th>Käufe</th>
        <th>Kampagne</th>
        <th>Verifiziert</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php

    foreach ($offers as $o):
        if(isset($o['verified']) && $o['verified']=='1') {
            $verified = 'Verifiziert';
            if(isset($o['verify_type'])) {
                $verified .= ' ' . $o['verify_type'];
            }
            $verified = '<div class="badge bg-success">' . $verified . '</div>';
        } else {
            $verified = 'Noch nicht';
            $verified = '<div class="badge bg-danger">' . $verified . '</div>';
        }


        $formFields = json_decode($o['form_fields'] ?? '{}', true);

        // Prüfen, ob mindestens ein UTM-Feld einen Wert hat
        $utmSource = $formFields['utm_source'] ?? null;

        if (!empty($utmSource)) {
            $utmStatus = '<div class="badge bg-info">' . ucwords($utmSource) . '</div>';
        } else {
            // Fallback prüfen, ob andere UTM-Felder gesetzt sind
            $hasOtherUtm = false;
            foreach (['utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $utmKey) {
                if (!empty($formFields[$utmKey])) {
                    $hasOtherUtm = true;
                    break;
                }
            }

            $utmStatus = $hasOtherUtm
                ? '<div class="badge bg-info">Ja</div>'
                : '<div class="badge bg-secondary">Nein</div>';
        }



        ?>
    <tr>
        <?php
        $date = new DateTime($o['created_at'], new DateTimeZone('UTC')); // oder ohne 2. Parameter, wenn CI4 es als UTC liefert
        $date->setTimezone(new DateTimeZone('Europe/Zurich'));
        ?>
        <td data-order="<?= $date->format('Y-m-d-H-i-s') ?>">
            <?= $date->format('d.m.Y H:i') ?>
        </td>
        <td><?= esc(lang('Offers.type.' . $o['type']) ?? $o['type']) ?></td>
        <td><?= esc($o['zip']) ?></td>
        <td><?= esc($o['city']) ?></td>
        <td><?= esc($o['firstname'] . ' ' . $o['lastname']) ?></td>
        <td><?= esc(lang('Offers.status.' . $o['status']) ?? $o['status']) ?></td>
        <td><?= esc($o['buyers']) ?></td>
        <td><?= $utmStatus ?></td>
        <td><?=$verified;?></td>
        <td>
            <a href="<?= site_url('admin/offer/' . $o['id']) ?>" class="btn btn-primary btn-sm" target="_blank">
                Details
            </a>
            <a href="<?= site_url('/dashboard?delete=' . $o['id']) ?>" onclick="return confirm('<?= esc(lang('Offers.type.' . $o['type']) ?? $o['type']) ?> <?= esc($o['firstname'] . ' ' . $o['lastname']) ?> <?= esc($o['city']) ?> - Wirklich löschen?');" class="btn btn-warning btn-sm del">
                Löschen
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php } else {
        echo "Keine Daten mit dieser Filterung";
    } ?>


<script>
    document.querySelector('#typeSelect').addEventListener('change', function() {
        this.form.submit();
    });
</script>


<script>
    $(document).ready(function() {
        $('#offersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.22/i18n/German.json'
            },
            // Optional: Standard-Sortierung, Seitenlänge etc. kannst du hier anpassen
            pageLength: 10,
            stateSave: true,
            order: [[0, 'desc']] // z.B. nach Datum absteigend sortieren
        });
    });
</script>


<?= $this->endSection() ?>
