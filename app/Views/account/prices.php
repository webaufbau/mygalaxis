<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2 class="mb-3"><?= esc($title) ?></h2>
<p class="lead">
    <?= lang('Offers.offerFeeInfo1') ?><br>
    <?= lang('Offers.offerFeeInfo2') ?>
</p>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <caption class="caption-top fw-semibold mb-2"><?= lang('Offers.priceListCaption') ?></caption>
        <thead class="table-light">
        <tr>
            <th scope="col"><?= lang('Offers.object') ?></th>
            <th scope="col"><?= lang('Offers.category') ?></th>
            <th scope="col"><?= lang('Offers.accept') ?></th>
            <th scope="col"><?= lang('Offers.reducedPrice') ?></th>
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
