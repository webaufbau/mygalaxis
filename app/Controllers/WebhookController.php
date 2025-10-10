<?php
namespace App\Controllers;

use App\Libraries\Subscription;
use App\Libraries\Transaction;

class WebhookController extends BaseController
{
    protected Subscription $subscriptionLib;
    protected Transaction $transactionLib;

    public function __construct()
    {
        $this->subscriptionLib = new Subscription();
        $this->transactionLib = new Transaction();
    }

    /**
     * @throws \ReflectionException
     */
    public function payrexx(): \CodeIgniter\HTTP\ResponseInterface {
        $json = $this->request->getJSON(true);

        if (!$json) {
            log_message('error', 'Ungültiges JSON erhalten', []);
            return $this->response->setStatusCode(400)->setBody('Ungültiges JSON');
        }

        // Log webhook payload for debugging
        log_message('info', 'Payrexx Webhook', ['payload' => $json]);

        if (isset($json['subscription'])) {
            // Handle subscription webhook
            $this->handleSubscriptionWebhook($json['subscription']);
        } elseif (isset($json['transaction'])) {
            // Handle transaction webhook
            $this->handleTransactionWebhook($json['transaction']);
        } elseif (isset($json['data'][0])) {
            $payload = $json['data'][0];

            // Prüfe ob Transaction-Info enthalten ist
            if (isset($payload['invoices'][0]['transactions'][0])) {
                $transaction = $payload['invoices'][0]['transactions'][0];
                $transactionId = $transaction['id'];
                $status = $transaction['status'] ?? null;

                log_message('info', "Payrexx Transaktion empfangen: ID=$transactionId Status=$status");

                // Beispiel: Status speichern oder verarbeiten
                // $this->transactionLib->updateStatus($transactionId, $status, $transaction);

                // Beispiel: Token speichern, wenn verfügbar
                if (isset($transaction['token'])) {
                    $token = $transaction['token'];
                    log_message('info', "Token erhalten: $token");
                    // Token in DB speichern
                }

                // Je nach Status weitere Aktionen durchführen
                switch ($status) {
                    case 'authorized':
                        // Autorisierte Zahlung, evtl. später Charge ausführen
                        break;
                    case 'confirmed':
                        // Zahlung erfolgreich
                        break;
                    case 'cancelled':
                    case 'declined':
                        // Zahlung abgelehnt oder storniert
                        break;
                    // ... weitere Fälle
                }
            }
        } else {
            log_message('info', 'Unbekannte Webhook-Payload-Struktur', []);
            return $this->response->setStatusCode(400)->setBody('Unbekannte Payload-Struktur');
        }

        return $this->response->setStatusCode(200)->setBody('Webhook empfangen');
    }

    /* public function stripe(): \CodeIgniter\HTTP\ResponseInterface {
        $payload = $this->request->getBody();
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = getenv('stripe.webhook_secret'); // aus .env oder Config

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $secret
            );
        } catch (\UnexpectedValueException $e) {
            log_message('error', 'Ungültiger Stripe-Payload: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setBody('Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            log_message('error', 'Ungültige Stripe-Signatur: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        }

        log_message('info', 'Stripe Webhook empfangen', ['type' => $event->type]);

        switch ($event->type) {
            case 'invoice.paid':
                $invoice = $event->data->object;
                // Rechnung wurde bezahlt – Abo verlängern
                //$this->subscriptionLib->renewByStripeInvoice($invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                // Zahlung fehlgeschlagen – eventuell E-Mail senden
                //$this->subscriptionLib->failByStripeInvoice($invoice);
                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                // Abo beendet oder gekündigt
                //$this->subscriptionLib->cancelByStripeSubscription($subscription);
                break;

            case 'checkout.session.completed':
                $session = $event->data->object;
                // Checkout abgeschlossen – eventuell erste Zahlung
                //$this->transactionLib->completeStripeCheckout($session);
                break;

            // Weitere Events je nach Bedarf

            default:
                log_message('info', 'Unbehandelter Stripe-Webhook', ['type' => $event->type]);
                break;
        }

        return $this->response->setStatusCode(200)->setBody('Stripe webhook received');
    } */

    private function handleSubscriptionWebhook($subscription): void {
        $subscriptionId = $subscription['id'];
        $referenceId = $subscription['invoice']['referenceId'];
        $status = $subscription['status'];

        /*$user_subscription_model = new \App\Models\UsersubscriptionModel();
        $user_subscription = $user_subscription_model->where('transaction_reference', $referenceId)->first();
        if($user_subscription) {
            $subscriptionId = $user_subscription->users_subscriptions_id;
        } else {
            log_message('info', 'Unbekannte Subscription mit Referenz', ['referenceId' => $referenceId]);
            exit();
        }*/

        switch ($status) {
            case 'active':
                $this->renewSubscription($subscriptionId, $subscription);
                break;
            case 'failed':
                $this->handleFailedSubscription($subscriptionId, $subscription);
                break;
            case 'cancelled':
                $this->handleSubscriptionCancellation($subscriptionId, $subscription);
                break;
            case 'in_notice':
                $this->handleSubscriptionInNotice($subscriptionId, $subscription);
                break;
            default:
                log_message('info', 'Unbekannter Status', ['status' => $status]);
                break;
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleTransactionWebhook($transaction): void {
        $transactionId = $transaction['id'];
        $status = $transaction['status'];

        switch ($status) {
            case 'waiting':
                $this->handleWaitingTransaction($transactionId, $transaction);
                break;
            case 'confirmed':
                $this->handleConfirmedTransaction($transactionId, $transaction);
                break;
            case 'cancelled':
                $this->handleCancelledTransaction($transactionId, $transaction);
                break;
            case 'declined':
                $this->handleDeclinedTransaction($transactionId, $transaction);
                break;
            case 'authorized':
                $this->handleAuthorizedTransaction($transactionId, $transaction);
                break;
            case 'reserved':
                $this->handleReservedTransaction($transactionId, $transaction);
                break;
            case 'refunded':
                $this->handleRefundedTransaction($transactionId, $transaction);
                break;
            case 'refundpending':
                $this->handleRefundPendingTransaction($transactionId, $transaction);
                break;
            case 'partially-refunded':
                $this->handlePartiallyRefundedTransaction($transactionId, $transaction);
                break;
            case 'chargeback':
                $this->handleChargebackTransaction($transactionId, $transaction);
                break;
            case 'error':
                $this->handleErrorTransaction($transactionId, $transaction);
                break;
            case 'uncaptured':
                $this->handleUncapturedTransaction($transactionId, $transaction);
                break;
            default:
                log_message('info', 'Unbekannter Status', ['status' => $status]);
                break;
        }
    }

    private function renewSubscription($subscriptionId, $data): void {
        log_message('info', 'Abonnement erneuert', ['subscriptionId' => $subscriptionId, 'data' => $data]);
        $validUntil = $data['valid_until'];
        $result = $this->subscriptionLib->renew($subscriptionId, $validUntil);
        if ($result) {
            log_message('info', 'Abonnement erfolgreich erneuert', ['subscriptionId' => $subscriptionId]);
        } else {
            log_message('info', 'Fehler bei der Erneuerung des Abonnements', ['subscriptionId' => $subscriptionId]);
        }
    }

    private function handleFailedSubscription($subscriptionId, $data): void {
        log_message('info', 'Abonnementerneuerung fehlgeschlagen', ['subscriptionId' => $subscriptionId, 'data' => $data]);
        $result = $this->subscriptionLib->failed($subscriptionId);
        if ($result) {
            log_message('info', 'Abonnement erfolgreich als fehlgeschlagen markiert', ['subscriptionId' => $subscriptionId]);

            /*
            // Optional: Admin informieren
            $user_subscription = (new \App\Models\UsersubscriptionModel())->find($subscriptionId);
            $user = (new \App\Models\UserModel())->find($user_subscription->user_id);
            $email_data = ['user-firstname' => $user->firstname, 'user-email' => $user->getEmail()];

            $email = new \App\Libraries\Email();
            $email->sendMailWithTemplateCode(\get_setting('subscription_admin_mail'), $email_data, 'abo_renewfail_inter');

            // Optional: Benutzer informieren
            $user_subscription = (new \App\Models\UsersubscriptionModel())->find($subscriptionId);
            $user = (new \App\Models\UserModel())->find($user_subscription->user_id);
            $email_data = ['user-firstname' => $user->firstname, 'user-email' => $user->getEmail()];

            $email = new \App\Libraries\Email();
            $email->sendMailWithTemplateCode($user->getEmail(), $email_data, 'abo_renewfail_kunde');
            */

        } else {
            log_message('info', 'Fehler beim Markieren des Abonnements als fehlgeschlagen', ['subscriptionId' => $subscriptionId]);
        }
    }

    private function handleSubscriptionCancellation($subscriptionId, $data): void {
        log_message('info', 'Abonnement storniert', ['subscriptionId' => $subscriptionId, 'data' => $data]);

        $endDate = $data['end'];
        $result = $this->subscriptionLib->cancel($subscriptionId, $endDate);

        if ($result) {
            // UserSubscription erneut laden, falls geändert
            /*$user_subscription = (new \App\Models\UsersubscriptionModel())->find($subscriptionId);
            if ($user_subscription) {
                $this->sendCancellationEmails($user_subscription); // ➕ Mails senden
            }*/
            log_message('info', 'Abonnement erfolgreich storniert', ['subscriptionId' => $subscriptionId]);
        } else {
            log_message('info', 'Fehler bei der Stornierung des Abonnements', ['subscriptionId' => $subscriptionId]);
        }
    }

    private function handleSubscriptionInNotice($subscriptionId, $data): void {
        log_message('info', 'Abonnement in Kündigungsfrist', ['subscriptionId' => $subscriptionId]);
        $endDate = $data['end'];
        $result = $this->subscriptionLib->notice($subscriptionId);
        if ($result) {
            log_message('info', 'Abonnement erfolgreich gekündigt', ['subscriptionId' => $subscriptionId]);
        } else {
            log_message('info', 'Fehler bei der Kündigung des Abonnements', ['subscriptionId' => $subscriptionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleWaitingTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion wartend', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'waiting', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich auf wartend gesetzt', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler beim Aktualisieren der Transaktion auf wartend', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleConfirmedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion bestätigt', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'confirmed', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich bestätigt', ['transactionId' => $transactionId, 'data' => $data]);
        } else {
            log_message('info', 'Fehler bei der Bestätigung der Transaktion', ['transactionId' => $transactionId, 'data' => $data]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleCancelledTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion storniert', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'cancelled', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich storniert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Stornierung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleDeclinedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion abgelehnt', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'declined', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich abgelehnt', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Ablehnung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleAuthorizedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion autorisiert', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'authorized', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich autorisiert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Autorisierung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleReservedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion reserviert', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'reserved', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich reserviert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Reservierung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleRefundedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion erstattet', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'refunded', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich erstattet', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Erstattung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleRefundPendingTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion Rückerstattung ausstehend', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'refundpending', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich als rückerstattet markiert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Rückerstattung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handlePartiallyRefundedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion teilweise erstattet', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'partially-refunded', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich teilweise erstattet', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der teilweise Erstattung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleChargebackTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion Chargeback', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'chargeback', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich mit Chargeback markiert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Markierung der Chargeback-Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleErrorTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion Fehler', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'error', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich als Fehler markiert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Fehlerbehandlung der Transaktion', ['transactionId' => $transactionId]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function handleUncapturedTransaction($transactionId, $data): void {
        log_message('info', 'Transaktion nicht erfasst', ['transactionId' => $transactionId, 'data' => $data]);
        $result = $this->transactionLib->updateStatus($transactionId, 'uncaptured', $data);
        if ($result) {
            log_message('info', 'Transaktion erfolgreich als nicht erfasst markiert', ['transactionId' => $transactionId]);
        } else {
            log_message('info', 'Fehler bei der Markierung der Transaktion als nicht erfasst', ['transactionId' => $transactionId]);
        }
    }


    /**
     * SaferPay NotifyURL Webhook (Server-to-Server Benachrichtigung)
     * Wird von SaferPay aufgerufen, wenn eine Zahlung erfolgreich war
     * WICHTIG: Funktioniert unabhängig vom Browser-Redirect (SuccessUrl)
     */
    public function saferpayNotify(): \CodeIgniter\HTTP\ResponseInterface
    {
        // JSON-Payload von SaferPay empfangen
        $json = $this->request->getJSON(true);

        if (!$json) {
            log_message('error', 'SaferPay NotifyURL: Ungültiges JSON erhalten');
            return $this->response->setStatusCode(400)->setBody('Ungültiges JSON');
        }

        // Payload loggen für Debugging
        log_message('info', 'SaferPay NotifyURL empfangen', ['payload' => $json]);

        // SaferPay sendet bei Erfolg das Token und ggf. weitere Infos
        if (!isset($json['Token'])) {
            log_message('error', 'SaferPay NotifyURL: Token fehlt', ['payload' => $json]);
            return $this->response->setStatusCode(400)->setBody('Token fehlt');
        }

        $token = $json['Token'];

        try {
            $saferpay = new \App\Services\SaferpayService();

            // Assert Transaction um Details zu holen
            $response = $saferpay->assertTransaction($token);

            if (!isset($response['Transaction']) || $response['Transaction']['Status'] !== 'AUTHORIZED') {
                log_message('error', 'SaferPay NotifyURL: Transaktion nicht autorisiert', ['response' => $response]);
                return $this->response->setStatusCode(400)->setBody('Transaktion nicht autorisiert');
            }

            $transaction = $response['Transaction'];
            $transactionId = $transaction['Id'];
            $amount = $transaction['Amount']['Value'];
            $currency = $transaction['Amount']['CurrencyCode'];
            $orderId = $transaction['OrderId'] ?? null;

            // Capture durchführen (Geld tatsächlich abbuchen)
            try {
                $captureResponse = $saferpay->captureTransaction($transactionId);
                log_message('info', 'SaferPay NotifyURL: Capture erfolgreich', ['response' => $captureResponse]);
                $captureStatus = $captureResponse['Status'] ?? 'CAPTURED';
            } catch (\Exception $captureError) {
                log_message('error', 'SaferPay NotifyURL: Capture fehlgeschlagen', ['error' => $captureError->getMessage()]);
                return $this->response->setStatusCode(500)->setBody('Capture fehlgeschlagen');
            }

            // User anhand OrderId finden (falls möglich)
            $db = \Config\Database::connect();
            $transactionRow = $db->table('saferpay_transactions')
                ->where('token', $token)
                ->get()
                ->getRow();

            if (!$transactionRow) {
                log_message('error', 'SaferPay NotifyURL: Token nicht in DB gefunden', ['token' => $token]);
                return $this->response->setStatusCode(404)->setBody('Token nicht gefunden');
            }

            $userId = $transactionRow->user_id;

            // Guthaben gutschreiben
            $bookingModel = new \App\Models\BookingModel();
            $bookingModel->insert([
                'user_id' => $userId,
                'type' => 'topup',
                'description' => 'SaferPay Top-up (NotifyURL) - ' . ($transaction['AcquirerName'] ?? 'Online-Zahlung'),
                'amount' => $amount / 100, // Rappen → CHF
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Alias sichern, falls vorhanden
            if (isset($transaction['PaymentMeans']['Alias'])) {
                $aliasId = $transaction['PaymentMeans']['Alias']['Id'];
                $paymentMethodModel = new \App\Models\UserPaymentMethodModel();

                // Prüfen ob Alias bereits existiert
                $existing = $paymentMethodModel
                    ->where('user_id', $userId)
                    ->where('payment_method_code', 'saferpay')
                    ->first();

                if (!$existing) {
                    $paymentMethodModel->save([
                        'user_id' => $userId,
                        'payment_method_code' => 'saferpay',
                        'provider_data' => json_encode(['alias_id' => $aliasId]),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Transaktion-Status aktualisieren
            $saferpay->updateTransaction($token, [
                'transaction_id' => $transactionId,
                'status' => $captureStatus,
                'amount' => $amount,
                'currency' => $currency,
                'transaction_data' => json_encode($transaction),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', 'SaferPay NotifyURL: Verarbeitung erfolgreich', [
                'user_id' => $userId,
                'amount' => $amount / 100,
                'transaction_id' => $transactionId
            ]);

            // SaferPay erwartet HTTP 200 OK
            return $this->response->setStatusCode(200)->setBody('OK');

        } catch (\Exception $e) {
            log_message('error', 'SaferPay NotifyURL: Fehler bei Verarbeitung', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->response->setStatusCode(500)->setBody('Interner Fehler');
        }
    }

    /*private function sendCancellationEmails($user_subscription): void {
        $user = (new \App\Models\UserModel())->find($user_subscription->user_id);

        // E-Mail-Daten zusammenstellen
        $email_data = $user_subscription->getAttributes() + $user->getAttributes();

        // Keys formatieren
        foreach ($email_data as $key => $value) {
            if (strpos($key, '_') === false) {
                continue;
            }
            $newKey = str_replace('_', '-', $key);
            $email_data[$newKey] = $value;
            unset($email_data[$key]);
        }

        // Zusätzliche Felder setzen
        $email_data['user-firstname'] = $email_data['firstname'];
        $email_data['user-lastname'] = $email_data['lastname'];
        $email_data['user-street'] = $email_data['street'];
        $email_data['user-street-nr'] = $email_data['street-nr'];
        $email_data['user-postcode'] = $email_data['postcode'];
        $email_data['user-city'] = $email_data['city'];
        $email_data['user-country'] = $email_data['country'];
        $email_data['user-photo'] = $email_data['photo'];
        $email_data['user-phone'] = $email_data['phone'];
        $email_data['user-birthday'] = $email_data['birthday'];
        $email_data['user-email'] = $user->getEmail();
        $email_data['subscription-start'] = $email_data['valid-start-date'];
        $email_data['subscription-stop'] = $email_data['valid-stop-date'];
        $email_data['subscription-id'] = $email_data['users-subscriptions-id'];
        $email_data['subscription-price'] = $email_data['subscription-type-price'];
        $email_data['subscription-category'] = $email_data['subscription-type-category'];

        // Mails senden
        $email = new \App\Libraries\Email();
        $email->sendMailWithTemplateCode(\get_setting('subscription_admin_mail'), $email_data, 'abo_kuendigung_inter');
        $email->sendMailWithTemplateCode($user->getEmail(), $email_data, 'abo_kuendigung_kunde');
    }*/


}
