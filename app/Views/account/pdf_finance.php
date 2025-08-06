<?php
$months = lang('Calendar.months');
?>

<h1>Buchungen <?= $month ? $months[(int)$month] . ' ' : '' ?><?= $year ?: '' ?></h1>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>Datum</th>
        <th>Typ</th>
        <th>Beschreibung</th>
        <th style="text-align: right;">Betrag</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $entry): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($entry['created_at'])) ?></td>
            <td><?= Lang('Offers.credit_type.'.$entry['type']) ?></td>
            <td><?= esc($entry['description']) ?></td>
            <td style="text-align: right;"><?= number_format($entry['amount'], 2, ".", "'") ?> CHF</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
