<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-3"><?= esc($title) ?></h2>
<p class="lead">
    Wenn Sie eine Offerte annehmen möchte fällt dafür eine Gebühr an. Diese Gebühr wird pro Anfrage berechnet und kann mit den bei uns hinterlegten Zahlungsmitteln bezahlt werden.
    Die Preise variieren je nach Art der Anfrage und Wohnungsgrösse. Nachfolgend sehen Sie die Übersicht der Gebühren — so behalten Sie stets den Überblick.
</p>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <caption class="caption-top fw-semibold mb-2">Preisliste der Anfragen für Offerten</caption>
        <thead class="table-light">
        <tr>
            <th scope="col">Objekt</th>
            <th scope="col">Kategorie</th>
            <th scope="col">Annehmen</th>
            <th scope="col">Reduzierter Preis</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($prices as $objekt => $services): ?>
            <tr>
                <td rowspan="<?= count($services) ?>" class="fw-bold align-middle text-start">
                    <?= esc($objekt) ?>
                </td>
                <?php
                $first = true;
                foreach ($services as $service => $preise):
                    if (!$first) echo '<tr>';
                    ?>
                    <td class="text-start">
                        <?= esc($service) ?>
                    </td>
                    <td class="text-end">
                        <?= number_format($preise['Annehmen'], 2) ?> CHF
                    </td>
                    <td class="text-end">
                        <?= number_format($preise['Reduzierter Preis'], 2) ?> CHF
                    </td>
                    <?php
                    if (!$first) echo '</tr>';
                    $first = false;
                endforeach;
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
