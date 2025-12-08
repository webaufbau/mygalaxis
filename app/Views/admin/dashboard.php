<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>


<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
    /* Kompaktere Tabellen-Darstellung */
    #offersTable {
        font-size: 0.9rem;
    }
    #offersTable thead th {
        padding: 0.5rem 0.3rem;
        white-space: nowrap;
        font-size: 0.875rem;
    }
    #offersTable tbody td {
        padding: 0.4rem 0.3rem;
        vertical-align: middle;
    }
    /* Kleinere Badges */
    #offersTable .badge {
        font-size: 0.75rem;
        padding: 0.2em 0.5em;
    }
    /* Tabelle scrollbar bei Bedarf */
    .table-responsive {
        overflow-x: auto;
    }
    /* Spaltenbreiten optimieren */
    /* Verifiziert-Spalte schmaler */
    #offersTable th:nth-child(14),
    #offersTable td:nth-child(14) {
        max-width: 100px;
        width: 100px;
    }
    /* Name-Spalte breiter */
    #offersTable th:nth-child(7),
    #offersTable td:nth-child(7) {
        min-width: 150px;
    }
    /* Umsatz/Rabatt-Spalten kompakt */
    #offersTable th:nth-child(9),
    #offersTable td:nth-child(9),
    #offersTable th:nth-child(10),
    #offersTable td:nth-child(10),
    #offersTable th:nth-child(11),
    #offersTable td:nth-child(11),
    #offersTable th:nth-child(12),
    #offersTable td:nth-child(12) {
        text-align: right;
        white-space: nowrap;
    }
</style>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
    <div class="col-md-2">
        <label class="form-label">Typ</label>
        <select name="type" id="typeSelect" class="form-select" onchange="this.form.submit()">
            <option value="">Alle</option>
            <?php foreach ($types as $typeValue => $typeLabel): ?>
                <option value="<?= esc($typeValue) ?>" <?= (isset($filter_type) && $filter_type === $typeValue) ? 'selected' : '' ?>>
                    <?= esc($typeLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Verifiziert</label>
        <select name="verified" class="form-select" onchange="this.form.submit()">
            <option value="">Alle</option>
            <option value="yes" <?= ($request['verified'] ?? '') === 'yes' ? 'selected' : '' ?>>Ja</option>
            <option value="no" <?= ($request['verified'] ?? '') === 'no' ? 'selected' : '' ?>>Nein</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Käufe</label>
        <select name="purchases" class="form-select" onchange="this.form.submit()">
            <option value="">Alle</option>
            <option value="yes" <?= ($request['purchases'] ?? '') === 'yes' ? 'selected' : '' ?>>Mit Käufen</option>
            <option value="no" <?= ($request['purchases'] ?? '') === 'no' ? 'selected' : '' ?>>Ohne Käufe</option>
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

    <?php if(!empty($filter_type) || !empty($request['from']) || !empty($request['to']) || !empty($request['verified']) || !empty($request['purchases'])): ?>
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
        <th>ID</th>
        <th>Datum</th>
        <th>Typ</th>
        <th>PLZ</th>
        <th>Ort</th>
        <th>Name</th>
        <th>Preis</th>
        <th>Käufe</th>
        <th>Umsatz</th>
        <th>N.P.</th>
        <th>1.R.</th>
        <th>2.R.</th>
        <th>Plattform</th>
        <th>Kampagne</th>
        <th>Verifiziert</th>
        <th>Test</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php

    foreach ($offers as $o):
        if(isset($o['verified']) && $o['verified']=='1') {
            if(isset($o['verify_type'])) {
                if ($o['verify_type'] === 'manual') {
                    $verified = '✓ Admin';
                } elseif ($o['verify_type'] === 'sms') {
                    $verified = '✓ SMS';
                } elseif ($o['verify_type'] === 'call') {
                    $verified = '✓ Anruf';
                } else {
                    $verified = '✓';
                }
            } else {
                $verified = '✓';
            }
            $verified = '<div class="badge bg-success" style="font-size: 0.7rem; white-space: nowrap;">' . $verified . '</div>';
        } else {
            $verified = 'Noch nicht';
            $verified = '<div class="badge bg-danger" style="font-size: 0.7rem; white-space: nowrap;">' . $verified . '</div>';
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
        <td><?= esc($o['id']) ?></td>
        <?php
        // DateTime direkt aus DB-Wert erstellen ohne Timezone-Konvertierung
        $date = new DateTime($o['created_at']);
        ?>
        <td data-order="<?= $date->format('Y-m-d-H-i-s') ?>">
            <?= $date->format('d.m.Y H:i') ?>
        </td>
        <td><?= esc(lang('Offers.type.' . $o['type']) ?? $o['type']) ?></td>
        <td><?= esc($o['zip']) ?></td>
        <td><?= esc($o['city']) ?></td>
        <td><?= esc($o['firstname'] . ' ' . $o['lastname']) ?></td>
        <td>
            <?php
            // Aktueller Preis: discounted_price > custom_price > price
            $currentPrice = $o['price'] ?? 0;
            if (!empty($o['discounted_price']) && $o['discounted_price'] > 0) {
                $currentPrice = $o['discounted_price'];
                echo '<span class="text-success">' . number_format($currentPrice, 2, '.', "'") . '</span>';
            } elseif (!empty($o['custom_price']) && $o['custom_price'] > 0) {
                $currentPrice = $o['custom_price'];
                echo '<span class="text-info">' . number_format($currentPrice, 2, '.', "'") . '</span>';
            } else {
                echo number_format($currentPrice, 2, '.', "'");
            }
            ?>
        </td>
        <td><?= esc($o['buyers']) ?></td>
        <td><?= number_format($o['purchase_stats']['total_revenue'] ?? 0, 2, '.', "'") ?></td>
        <td><?= ($o['purchase_stats']['revenue_normal'] ?? 0) > 0 ? number_format($o['purchase_stats']['revenue_normal'], 2, '.', "'") : '-' ?></td>
        <td><?= ($o['purchase_stats']['revenue_discount_1'] ?? 0) > 0 ? number_format($o['purchase_stats']['revenue_discount_1'], 2, '.', "'") : '-' ?></td>
        <td><?= ($o['purchase_stats']['revenue_discount_2'] ?? 0) > 0 ? number_format($o['purchase_stats']['revenue_discount_2'], 2, '.', "'") : '-' ?></td>
        <td>
            <?php
            if (!empty($o['platform'])) {
                // Formatiere Plattform: my_offertenschweiz_ch -> Offertenschweiz.ch
                $platform = $o['platform'];
                $platform = str_replace('my_', '', $platform); // Entferne "my_"
                $platform = str_replace('_', '.', $platform);   // Ersetze _ durch .
                $platform = ucfirst($platform);                 // Erster Buchstabe groß

                // Bestimme Farbe basierend auf Plattform
                $badgeColor = 'bg-secondary'; // Fallback
                $platformLower = strtolower($o['platform']);

                if (strpos($platformLower, 'offertenschweiz') !== false ||
                    strpos($platformLower, 'offertenaustria') !== false ||
                    strpos($platformLower, 'offertendeutschland') !== false) {
                    // Rosa für Offertenschweiz/Austria/Deutschland
                    $badgeColor = 'style="background-color: #E91E63; color: white;"';
                } elseif (strpos($platformLower, 'offertenheld') !== false) {
                    // Lila/Violett für Offertenheld
                    $badgeColor = 'style="background-color: #6B5B95; color: white;"';
                } elseif (strpos($platformLower, 'renovo') !== false) {
                    // Schwarz für Renovo
                    $badgeColor = 'style="background-color: #212529; color: white;"';
                } else {
                    $badgeColor = 'class="bg-primary"';
                }

                echo '<span class="badge" ' . $badgeColor . '>' . esc($platform) . '</span>';
            } else {
                echo '<span class="badge bg-secondary">-</span>';
            }
            ?>
        </td>
        <td><?= $utmStatus ?></td>
        <td><?=$verified;?></td>
        <td>
            <?php if (!empty($o['is_test'])): ?>
                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Test</span>
            <?php else: ?>
                <span class="text-muted">-</span>
            <?php endif; ?>
        </td>
        <td style="white-space: nowrap;">
            <a href="<?= site_url('admin/offer/' . $o['id']) ?>" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 0.75rem;" target="_blank">
                Details
            </a>
            <a href="<?= site_url('admin/offers/edit/' . $o['id']) ?>" class="btn btn-secondary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                Bearbeiten
            </a>
            <form method="post" action="<?= site_url('dashboard/delete/' . $o['id']) ?>" style="display: inline;" onsubmit="return confirm('<?= esc(lang('Offers.type.' . $o['type']) ?? $o['type']) ?> <?= esc($o['firstname'] . ' ' . $o['lastname']) ?> <?= esc($o['city']) ?> - Wirklich löschen?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning btn-sm py-0 px-2" style="font-size: 0.75rem;">Löschen</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php } else {
        echo "Keine Daten mit dieser Filterung";
    } ?>


<script>
    $(document).ready(function() {
        $('#offersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.22/i18n/German.json'
            },
            pageLength: 10,
            stateSave: true,
            order: [[1, 'desc']] // Nach Datum (zweite Spalte) absteigend sortieren
        });
    });
</script>


<?= $this->endSection() ?>
