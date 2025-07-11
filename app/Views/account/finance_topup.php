<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Guthaben aufladen</h2>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

<form action="<?= site_url('finance/topup') ?>" method="post">

    <div class="mb-3">
        <label for="amount" class="form-label">Betrag (CHF)</label>
        <input type="number" step="0.01" min="20" name="amount" id="amount" class="form-control" required />
    </div>

    <fieldset class="mb-3">
        <legend class="form-label">Gespeicherte Zahlungsmethoden</legend>

        <?php if (empty($paymentMethods)): ?>
            <p>Keine Zahlungsmethoden vorhanden. <a href="<?= site_url('finance/userpaymentmethods/add') ?>">Zahlungsmethode hinzufügen</a></p>
        <?php else: ?>
            <?php foreach ($paymentMethods as $index => $method): ?>
                <div class="form-check">
                    <input
                            class="form-check-input-xxx"
                            type="radio"
                            name="payment_method"
                            id="payment_method_<?= esc($method['id']) ?>"
                            value="<?= esc($method['code']) ?>"
                            required
                        <?= $index === 0 ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="payment_method_<?= esc($method['id']) ?>">
                        <?= esc($method['name']) ?>

                        <?php
                        $providerDataJson = $method['provider_data'] ?? '{}';
                        $data = json_decode($providerDataJson, true);

                        if ($method['code'] === 'creditcard' && !empty($data['last4'])) {
                            echo ' (**** **** **** ' . esc($data['last4']) . ')';
                        } elseif ($method['code'] === 'paypal' && !empty($data['email'])) {
                            echo ' (' . esc($data['email']) . ')';
                        } elseif ($method['code'] === 'twint' && !empty($data['phone'])) {
                            echo ' (' . esc($data['phone']) . ')';
                        }
                        ?>
                    </label>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </fieldset>

    <button type="submit" class="btn btn-primary">Zahlung ausführen</button>

</form>



<br>
<p><a href="<?= site_url('finance/userpaymentmethods/add') ?>">Weitere Zahlungsmethode hinzufügen</a></p>

<?= $this->endSection() ?>
