<?php
$months = lang('Calendar.months');
$total = 0;
foreach ($bookings as $entry) {
    $total += $entry['amount'];
}
?>

<style>
    .finance-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    .finance-table th {
        background-color: #2c3e50;
        color: white;
        padding: 10px 8px;
        text-align: left;
        font-weight: bold;
    }
    .finance-table th:last-child {
        text-align: right;
    }
    .finance-table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    .finance-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .finance-table .amount-positive {
        color: #27ae60;
    }
    .finance-table .amount-negative {
        color: #e74c3c;
    }
    .finance-table tfoot tr {
        background-color: #ecf0f1;
        font-weight: bold;
    }
    .finance-table tfoot td {
        padding: 12px 8px;
        border-top: 2px solid #2c3e50;
    }
</style>

<h1 style="font-family: Arial, sans-serif; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">
    <?= esc(lang('Finance.pdf_title')) ?> <?= $month ? esc($months[(int)$month]) . ' ' : '' ?><?= esc($year ?: '') ?>
</h1>

<table class="finance-table">
    <thead>
    <tr>
        <th style="width: 120px;"><?= esc(lang('Finance.date')) ?></th>
        <th style="width: 120px;"><?= esc(lang('Finance.type')) ?></th>
        <th><?= esc(lang('Finance.description')) ?></th>
        <th style="width: 100px; text-align: right;"><?= esc(lang('Finance.amount')) ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $entry): ?>
        <tr>
            <td><?= date('d.m.Y H:i', strtotime($entry['created_at'])) ?></td>
            <td><?= esc(lang('Offers.credit_type.' . $entry['type'])) ?></td>
            <td><?= esc($entry['description']) ?></td>
            <td style="text-align: right;" class="<?= $entry['amount'] >= 0 ? 'amount-positive' : 'amount-negative' ?>">
                <?= $entry['amount'] >= 0 ? '+' : '' ?><?= number_format($entry['amount'], 2, ".", "'") ?> <?= currency() ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3" style="text-align: right;"><?= esc(lang('Finance.totalAmount')) ?>:</td>
        <td style="text-align: right;" class="<?= $total >= 0 ? 'amount-positive' : 'amount-negative' ?>">
            <?= $total >= 0 ? '+' : '' ?><?= number_format($total, 2, ".", "'") ?> <?= currency() ?>
        </td>
    </tr>
    </tfoot>
</table>
