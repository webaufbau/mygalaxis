<?php

namespace App\Libraries;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\UserModel;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Erstellt oder lädt einen Stripe-Customer für den Nutzer
     */
    public function getOrCreateCustomer($user)
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }

        // Neuer Kunde
        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        // Speichern in der DB
        $userModel = new UserModel();
        $userModel->update($user->id, [
            'stripe_customer_id' => $customer->id,
        ]);

        return $customer;
    }

    /**
     * Prüft, ob der Nutzer eine gespeicherte Karte hat
     */
    public function hasCardOnFile($user): bool
    {
        if (!$user->stripe_customer_id) {
            return false;
        }

        $methods = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return count($methods->data) > 0;
    }

    /**
     * Belastet den Kunden mit dem angegebenen Betrag (in CHF)
     */
    public function charge($user, float $amountCHF, string $description = ''): string
    {
        $customer = $this->getOrCreateCustomer($user);

        // Betrag in Rappen (Stripe erwartet Cent)
        $amount = intval($amountCHF * 100);

        // Aktuelle Zahlungsmethode holen
        $paymentMethods = PaymentMethod::all([
            'customer' => $customer->id,
            'type' => 'card',
        ]);

        if (empty($paymentMethods->data)) {
            throw new \Exception('Keine gespeicherte Zahlungsmethode gefunden.');
        }

        $paymentMethodId = $paymentMethods->data[0]->id;

        $intent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'chf',
            'customer' => $customer->id,
            'payment_method' => $paymentMethodId,
            'off_session' => true,
            'confirm' => true,
            'description' => $description,
        ]);

        return $intent->id; // Stripe PaymentIntent-ID zur Referenz
    }

    /**
     * Speichert eine neue Kreditkarte über einen Stripe Setup Intent (Frontend erforderlich)
     */
    public function createSetupIntent($user)
    {
        $customer = $this->getOrCreateCustomer($user);

        return \Stripe\SetupIntent::create([
            'customer' => $customer->id,
        ]);
    }
}
