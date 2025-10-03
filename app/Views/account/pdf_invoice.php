<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rechnung <?= esc($invoice_name) ?></title>
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
    </div>
    <div class="customer-address">
        <p><strong>Kunde:</strong></p>
        <p>
            <?= esc($user->company_name) ?><br>
            <?= esc($user->company_street) ?><br>
            <?= esc($user->company_zip) ?> <?= esc($user->company_city) ?><br>
            <?= esc($user->company_email) ?><br>
            <?= esc($user->company_phone) ?>
        </p>
    </div>

</div>

<h1>Rechnung <?= esc($invoice_name) ?></h1>

<div class="details">
    <p><strong>Rechnungsdatum:</strong> <?= date('d.m.Y', strtotime($booking['created_at'])) ?></p>
    <p><strong>Rechnungsnummer:</strong> <?= esc($invoice_name) ?></p>
</div>

<table>
    <thead>
    <tr>
        <th>Beschreibung</th>
        <th style="width: 100px;">Betrag (CHF)</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?= esc($booking['description']) ?></td>
        <td><?= number_format(abs($booking['amount']), 2, ".", "'") ?></td>
    </tr>
    </tbody>
</table>

<p class="total">Total: <?= number_format(abs($booking['amount']), 2, ".", "'") ?> CHF</p>

<div class="footer" style="margin-top: 40px; font-size: 12px; color: #555; line-height: 1.5;">
    <p><strong>Zahlung:</strong> Diese Rechnung wurde bereits vollständig im Voraus über Saferpay beglichen.</p>
    <p>Wir danken Ihnen für Ihren Einkauf und Ihr Vertrauen.</p>
</div>

</body>
</html>
