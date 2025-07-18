<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("<?= esc(config('Stripe')->publicKey) ?>");
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = await stripe.confirmCardSetup("<?= esc($clientSecret) ?>", {
            payment_method: {
                card: card,
            }
        });

        if (result.error) {
            alert(result.error.message);
        } else {
            // result.setupIntent.payment_method enthält die payment_method_id
            // Schicke das z.B. via POST an deinen Server
            document.getElementById('payment_method').value = result.setupIntent.payment_method;
            form.submit();
        }
    });
</script>

<form id="payment-form" method="POST" action="<?= site_url('finance/addUserPaymentMethod') ?>">
    <div id="card-element"></div>
    <input type="hidden" name="payment_method" id="payment_method">
    <button type="submit">Zahlungsmethode speichern</button>
</form>
