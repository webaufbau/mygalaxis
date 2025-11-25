<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= lang('Finance.invoice') ?> <?= esc($invoice_name) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 40px;
            color: #333;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .company-address, .customer-address {
            width: 45%;
        }
        .details {
            margin-bottom: 30px;
        }
        .details p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background: #f5f5f5;
            text-align: left;
        }
        .total {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="invoice-header">
    <div class="company-address">
        <h2></h2>
        <p><?=nl2br(siteconfig()->address);?></p>
        <?php if (!empty(siteconfig()->company_uid)): ?>
            <p><?= lang('Finance.vatNumber') ?>: <?= esc(siteconfig()->company_uid) ?></p>
        <?php endif; ?>
    </div>
    <div class="customer-address">
        <p><strong><?= lang('Finance.customer') ?>:</strong></p>
        <p>
            <?= esc($user->company_name) ?><br>
            <?php if (!empty($user->company_uid)): ?>
                <?= lang('Finance.vatNumber') ?>: <?= esc($user->company_uid) ?><br>
            <?php endif; ?>
            <?= esc($user->company_street) ?><br>
            <?= esc($user->company_zip) ?> <?= esc($user->company_city) ?><br>
            <?php if (!empty($country)): ?>
                <?= esc($country) ?><br>
            <?php endif; ?>
            <?= esc($user->company_email) ?><br>
            <?= esc($user->company_phone) ?>
        </p>
    </div>

</div>

<?php
$isRefund = ($booking['type'] ?? '') === 'refund_purchase';
$documentTitle = $isRefund ? lang('Finance.creditNote') : lang('Finance.invoice');
?>
<h1><?= $documentTitle ?> <?= esc($invoice_name) ?></h1>

<div class="details">
    <p><strong><?= lang('Finance.invoiceDate') ?>:</strong> <?= date('d.m.Y', strtotime($booking['created_at'])) ?></p>
    <p><strong><?= lang('Finance.invoiceNumber') ?>:</strong> <?= esc($invoice_name) ?></p>
    <p><strong><?= lang('Finance.serviceDate') ?>:</strong> <?= date('d.m.Y', strtotime($booking['created_at'])) ?></p>
</div>

<table>
    <thead>
    <tr>
        <th><?= lang('Finance.description') ?></th>
        <th style="width: 100px;"><?= lang('Finance.amount') ?> (<?= currency() ?>)</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?= esc($booking['description']) ?></td>
        <td><?= number_format(abs($booking['amount']), 2, ".", "'") ?></td>
    </tr>
    </tbody>
</table>

<?php
$siteConfig = siteconfig();
$amount = abs($booking['amount']);
if ($siteConfig->vatEnabled && $siteConfig->vatRate > 0) {
    $amountExclVat = $amount / (1 + $siteConfig->vatRate / 100);
    $vatAmount = $amount - $amountExclVat;
    ?>
    <div style="text-align: right; margin-bottom: 20px;">
        <p><?= lang('Finance.amountExclVat') ?>: <?= number_format($amountExclVat, 2, ".", "'") ?> <?= currency() ?></p>
        <p><?= lang('Finance.vat') ?> (<?= number_format($siteConfig->vatRate, 1) ?>%): <?= number_format($vatAmount, 2, ".", "'") ?> <?= currency() ?></p>
        <p style="font-weight: bold; font-size: 16px;"><?= lang('Finance.amountInclVat') ?>: <?= number_format($amount, 2, ".", "'") ?> <?= currency() ?></p>
    </div>
<?php } else { ?>
    <p class="total"><?= lang('Finance.totalAmount') ?>: <?= number_format($amount, 2, ".", "'") ?> <?= currency() ?></p>
    <?php if (!empty($siteConfig->vatExemptionText)): ?>
        <p style="text-align: right; font-size: 12px; color: #666;"><?= esc($siteConfig->vatExemptionText) ?></p>
    <?php endif; ?>
<?php } ?>

<?php
$siteConfig = siteconfig();
if (!empty($siteConfig->bankIban) || !empty($siteConfig->bankName)): ?>
    <div style="margin-top: 30px; font-size: 12px; color: #555;">
        <p><strong><?= lang('Finance.bankDetails') ?>:</strong></p>
        <?php if (!empty($siteConfig->bankName)): ?>
            <p><?= lang('Finance.bank') ?>: <?= esc($siteConfig->bankName) ?></p>
        <?php endif; ?>
        <?php if (!empty($siteConfig->bankIban)): ?>
            <p><?= lang('Finance.iban') ?>: <?= esc($siteConfig->bankIban) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="footer" style="margin-top: 40px; font-size: 12px; color: #555; line-height: 1.5;">
    <?php if ($isRefund): ?>
    <p><strong><?= lang('Finance.creditNote') ?>:</strong> <?= lang('Finance.creditNoteInfo') ?></p>
    <?php else: ?>
    <p><strong><?= lang('Finance.invoice') ?>:</strong> <?= lang('Finance.paymentNote') ?></p>
    <?php endif; ?>
    <p><?= lang('Finance.thankYou') ?></p>

    <?php if (!empty($siteConfig->email) || !empty($user->company_website)): ?>
        <p style="margin-top: 20px;"><strong><?= lang('Finance.contact') ?>:</strong><br>
        <?php if (!empty($siteConfig->email)): ?>
            <?= esc($siteConfig->email) ?><br>
        <?php endif; ?>
        <?php if (!empty($user->company_website)): ?>
            <?= esc($user->company_website) ?>
        <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

</body>
</html>
