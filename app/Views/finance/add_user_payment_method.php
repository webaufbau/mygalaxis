<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Zahlungsmethode hinzufügen</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('finance/userpaymentmethods/add') ?>">
    <div class="mb-3">
        <label for="payment_method_code" class="form-label">Zahlungsmethode</label>
        <select name="payment_method_code" id="payment_method_code" class="form-select" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($paymentMethods as $method): ?>
                <option value="<?= esc($method['code']) ?>"><?= esc($method['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Beispiel für provider_data (kann man noch anpassen, je nach Methode) -->
    <div class="mb-3">
        <label for="provider_data[email]" class="form-label">PayPal E-Mail (nur bei PayPal)</label>
        <input type="email" name="provider_data[email]" id="provider_data[email]" class="form-control" />
    </div>

    <button type="submit" class="btn btn-primary">Speichern</button>
</form>

<?= $this->endSection() ?>
