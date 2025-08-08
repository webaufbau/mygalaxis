<?php
$months = lang('Calendar.months');
?>

<h1>
    <?= esc(lang('Finance.pdf_title')) ?> <?= $month ? esc($months[(int)$month]) . ' ' : '' ?><?= esc($year ?: '') ?>
</h1>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th><?= esc(lang('Finance.date')) ?></th>
        <th><?= esc(lang('Finance.type')) ?></th>
        <th><?= esc(lang('Finance.description')) ?></th>
        <th style="text-align: right;"><?= esc(lang('Finance.amount')) ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $entry): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
            <td><?= esc(lang('Offers.credit_type.' . $entry['type'])) ?></td>
            <td><?= esc($entry['description']) ?></td>
            <td style="text-align: right;"><?= number_format($entry['amount'], 2, ".", "'") ?> CHF</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
