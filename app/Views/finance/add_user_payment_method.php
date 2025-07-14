<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Zahlungsmethode hinzufügen</h2>

<form method="post" action="<?= site_url('finance/userpaymentmethods/add') ?>">
    <?= csrf_field() ?>

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('payment_method_code');

        select.addEventListener('change', function () {
            if (this.value === 'creditcard') {
                window.location.href = '<?= site_url('finance/startAddPaymentMethod') ?>';
            }
        });
    });
</script>



<?= $this->endSection() ?>
