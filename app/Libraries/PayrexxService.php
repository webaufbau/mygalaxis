<?php
namespace App\Libraries;

use Payrexx\Models\Request\Gateway;
use Payrexx\Models\Request\Transaction;
use Payrexx\PayrexxException;

class PayrexxService
{
    protected \Payrexx\Payrexx $payrexx;
    protected \Config\Payrexx $config;

    /**
     * @throws PayrexxException
     */
    public function __construct()
    {
        $this->config = new \Config\Payrexx();
        $this->payrexx = new \Payrexx\Payrexx(
            $this->config->instance,
            $this->config->apiKey
        );
        $paymentProvider = new \Payrexx\Models\Request\PaymentProvider();

        try {
            $response = $this->payrexx->getAll($paymentProvider);
        } catch (\Payrexx\PayrexxException $e) {
            print $e->getMessage();
        }
    }

    /**
     * Erstellt einen Tokenization-Gateway (preAuthorization)
     * @throws PayrexxException
     */
    public function createTokenCheckout($user, string $successUrl, string $cancelUrl): ?string
    {
        $userId       = is_object($user) ? $user->id : $user['id'];
        $userEmail    = is_object($user) ? $user->email : $user['email'];
        $userFirstname = is_object($user) ? $user->first_name : ($user['first_name'] ?? '');
        $userLastname  = is_object($user) ? $user->last_name : ($user['last_name'] ?? '');

        $payrexx = new \Payrexx\Payrexx($this->config->instance, $this->config->apiKey);

        // Payment methods
        $paymentMethod = new \Payrexx\Models\Request\PaymentMethod();
        $paymentMethod->setFilterCurrency('CHF');
        $paymentMethod->setFilterPaymentType('one-time');

        // default
        $payrexx_payment_methods = ['mastercard', 'visa'];

        try {
            $payrexx_payment_methods = $payrexx->getAll($paymentMethod);
        } catch (PayrexxException $e) {
            print $e->getMessage();
        }


        $gateway = new Gateway();
        $gateway->setAmount(0); // 0 = keine Sofortzahlung
        $gateway->setCurrency('CHF');
        $gateway->setPreAuthorization(true); // aktiviert Tokenization
        $gateway->setReferenceId('user-' . $userId);
        $gateway->setPm($payrexx_payment_methods);
        $gateway->setPsp([44, 36]);

        $gateway->setSuccessRedirectUrl($successUrl);
        $gateway->setCancelRedirectUrl($cancelUrl);
        $gateway->setFailedRedirectUrl($cancelUrl);

        $gateway->addField('forename', $userFirstname);
        $gateway->addField('surname', $userLastname);
        $gateway->addField('email', $userEmail);

        try {
            $response = $payrexx->create($gateway);
            return $response->getLink(); // z.B. Weiterleitung zu Payrexx UI
        } catch (\Payrexx\PayrexxException $e) {
            log_message('error', 'Payrexx Token-Gateway Fehler: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Belastet eine autorisierte Transaktion (nach Tokenisierung)
     *
     * @param int $transactionId Die transaction.id aus dem Webhook oder getGateway
     * @param int $amount Betrag in Rappen (z. B. 1000 für CHF 10.00)
     * @param string $description Beschreibung auf der Abrechnung
     * @return array|null
     */
    public function chargeAuthorizedTransaction(int $transactionId, int $amount, string $description = ''): ?array
    {
        $transaction = new Transaction();
        $transaction->setId($transactionId); // WICHTIG!
        $transaction->setAmount($amount);
        $transaction->setCurrency($this->config->currency);
        $transaction->setPurpose($description);

        try {
            $response = $this->payrexx->create($transaction);
            return $response->toArray();
        } catch (\Exception $e) {
            log_message('error', 'Payrexx Charge Error: ' . $e->getMessage());
            return null;
        }
    }
}
