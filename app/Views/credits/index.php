<h2>Guthaben: CHF <?= number_format($balance, 2, '.', "'") ?></h2>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Datum</th>
        <th>Betrag</th>
        <th>Typ</th>
        <th>Beschreibung</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($transactions as $t): ?>
        <tr>
            <td><?= esc($t['created_at']) ?></td>
            <td><?= number_format($t['amount'], 2) ?></td>
            <td><?= Lang('Offers.credit_type.'.$t['type']) ?></td>
            <td><?= esc($t['description']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
