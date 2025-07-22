<?php
namespace App\Services;

use Config\Datatrans;

class DatatransService
{
    protected Datatrans $config;

    public function __construct()
    {
        // Config Instanz laden
        $this->config = new Datatrans();
    }

    // Initialisiert Transaktion inkl. Alias erzeugen (Tokenization)
    public function initTransactionWithAlias(string $successUrl, string $cancelUrl, string $errorUrl, int $amount, string $refno)
    {
        $url = $this->config->apiBaseUrl . '/transactions';

        $data = [
            "currency" => "CHF",
            "amount" => $amount, // z.B. 1000 = 10.00 CHF
            "refno" => $refno,
            "paymentMethods" => ["VIS", "TWI", "PAP"], // gewünschte Zahlungsmethoden
            "autoSettle" => true,
            "option" => ["createAlias" => true],
            "redirect" => [
                "successUrl" => $successUrl,
                "cancelUrl" => $cancelUrl,
                "errorUrl" => $errorUrl
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->config->basicAuth,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return json_decode($response, true); // Enthält transactionId etc.
        } else {
            throw new \Exception("Datatrans API initTransaction failed: HTTP $httpCode - $response");
        }
    }

    // Autorisiert eine wiederkehrende Zahlung mit gespeicherter Token (alias)
    public function authorizeWithAlias(string $alias, int $amount, string $refno, int $expiryMonth, int $expiryYear)
    {
        $url = $this->config->apiBaseUrl . '/transactions/authorize';

        $data = [
            "currency" => "CHF",
            "amount" => $amount,
            "refno" => $refno,
            "card" => [
                "alias" => $alias,
                "expiryMonth" => $expiryMonth,
                "expiryYear" => $expiryYear
            ],
            "autoSettle" => true
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->config->basicAuth,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return json_decode($response, true);
        } else {
            throw new \Exception("Datatrans API authorizeWithAlias failed: HTTP $httpCode - $response");
        }
    }
}
