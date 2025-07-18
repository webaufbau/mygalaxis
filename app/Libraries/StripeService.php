<?php

namespace App\Libraries;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected \Config\Stripe $config;

    public function __construct()
    {
        $this->config = new \Config\Stripe();
        Stripe::setApiKey($this->config->secretKey);
    }

    /**
     * Erstellt einen SetupIntent, um die Zahlungsmethode zu speichern
     * @param array|object $user
     * @return string|null client_secret für Stripe.js
     */
    public function createTokenCheckout($user): ?string
    {
        $userId    = is_object($user) ? $user->id : $user['id'];
        $userEmail = is_object($user) ? $user->email : $user['email'];

        try {
            // Customer erstellen oder holen
            $customer = Customer::create([
                'email' => $userEmail,
                'metadata' => ['user_id' => $userId],
            ]);

            // SetupIntent erstellen
            $setupIntent = SetupIntent::create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
            ]);

            return $setupIntent->client_secret;
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe SetupIntent Fehler: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Führt eine Zahlung mit einer gespeicherten Zahlungsmethode aus
     *
     * @param string $customerId
     * @param string $paymentMethodId (z. B. aus SetupIntent)
     * @param int $amountInCents (z. B. 1000 = 10.00 CHF)
     * @param string $description
     * @return array|null
     */
    public function chargeAuthorizedTransaction(string $customerId, string $paymentMethodId, int $amountInCents, string $description = ''): ?array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'amount' => $amountInCents,
                'currency' => $this->config->currency,
                'description' => $description,
                'off_session' => true,
                'confirm' => true,
            ]);

            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe PaymentIntent Fehler: ' . $e->getMessage());
            return null;
        }
    }
}
