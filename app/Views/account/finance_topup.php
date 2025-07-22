<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Guthaben aufladen</h2>

<?php if(session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
<?php endif; ?>

<form action="<?= site_url('finance/topup') ?>" method="post">

    <?php if (!empty($myPaymentMethods)): ?>
    <div class="mb-3">
        <label for="amount" class="form-label">Betrag (CHF)</label>
        <input type="number" step="0.01" min="20" name="amount" id="amount" class="form-control" value="100" required />
    </div>

    <fieldset class="mb-3">
        <legend class="form-label">Gespeicherte Zahlungsmethoden</legend>
        <?php endif; ?>

        <?php if (empty($myPaymentMethods)): ?>
            <p>Keine Zahlungsmethoden vorhanden.
                <a href="#" data-bs-toggle="modal" data-bs-target="#paymentModal">Zahlungsmethode hinzufügen</a>
            </p>

        <?php else: ?>
            <?php foreach ($myPaymentMethods as $index => $method): ?>
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


                        <a href="/finance/userpaymentmethods/delete/<?= esc($method['id']) ?>"><i class="bi bi-trash del"></i> </a>

                    </label>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <?php if (!empty($myPaymentMethods)): ?>
    </fieldset>

    <button type="submit" class="btn btn-primary">Zahlung ausführen</button>
<?php endif; ?>

</form>


<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Zahlungsmethode hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <?php echo view('finance/add_user_payment_method'); ?>
            </div>
        </div>
    </div>
</div>



<?php if(count($myPaymentMethods) > 1) { ?>
<br>
<p><a href="<?= site_url('finance/userpaymentmethods/add') ?>">Weitere Zahlungsmethode hinzufügen</a></p>
<?php } ?>

<?= $this->endSection() ?>
