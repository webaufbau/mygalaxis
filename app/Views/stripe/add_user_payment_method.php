<h2>Zahlungsmethode hinzufügen</h2>

<form id="payment-method-form" method="post" action="<?= site_url('finance/userpaymentmethods/add') ?>">
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

    <div id="stripe-card-container" style="display:none;">
        <label class="form-label">Kreditkartendaten eingeben</label>
        <div id="card-element"></div>
        <input type="hidden" name="payment_method" id="payment_method" />
    </div>

    <button type="submit" class="btn btn-primary mt-3" id="submit-button">Hinzufügen</button>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stripePublicKey = "<?= esc(config('Stripe')->publicKey) ?>";
        let stripe = null;
        let elements = null;
        let card = null;
        let clientSecret = null;

        const select = document.getElementById('payment_method_code');
        const form = document.getElementById('payment-method-form');
        const stripeContainer = document.getElementById('stripe-card-container');
        const paymentMethodInput = document.getElementById('payment_method');

        select.addEventListener('change', async () => {
            if (select.value === 'creditcard') {
                stripeContainer.style.display = 'block';

                if (!stripe) {
                    stripe = Stripe(stripePublicKey);
                    elements = stripe.elements();
                }

                if (!card) {
                    card = elements.create('card');
                    card.mount('#card-element');
                }

                // Hole clientSecret via AJAX
                try {
                    const response = await fetch('<?= site_url('finance/startAddPaymentMethodAjax') ?>');
                    const data = await response.json();

                    if (data.clientSecret) {
                        clientSecret = data.clientSecret;
                    } else {
                        alert('Fehler beim Laden der Zahlungsseite.');
                    }
                } catch (err) {
                    alert('Fehler beim Laden der Zahlungsseite.');
                }

            } else {
                stripeContainer.style.display = 'none';

                if (card) {
                    card.destroy();
                    card = null;
                    clientSecret = null;
                }
            }
        });

        form.addEventListener('submit', async (e) => {
            if (select.value === 'creditcard') {
                e.preventDefault();

                if (!clientSecret || !card) {
                    alert('Zahlungsseite ist nicht bereit.');
                    return;
                }

                const result = await stripe.confirmCardSetup(clientSecret, {
                    payment_method: {
                        card: card,
                    }
                });

                if (result.error) {
                    alert(result.error.message);
                } else {
                    // payment_method_id in hidden input setzen und Formular abschicken
                    paymentMethodInput.value = result.setupIntent.payment_method;
                    form.submit();
                }
            }
            // Andere Zahlungsarten: normales Submit, kein preventDefault
        });
    });


</script>
