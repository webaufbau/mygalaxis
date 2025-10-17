<?php
namespace App\Services;

use Config\Saferpay;

class SaferpayService
{
    protected Saferpay $config;

    public function __construct()
    {
        $this->config = new Saferpay();
    }

    public function initTransactionWithAlias(string $successUrl, string $failUrl, int $amount, string $refno, string $notifyUrl = null)
    {
        $url = $this->config->apiBaseUrl . '/Payment/v1/PaymentPage/Initialize';

        $user = auth()->user();

        $data = [
            "RequestHeader" => [
                "SpecVersion" => "1.35",
                "CustomerId" => $this->config->customerId,
                "RequestId" => uniqid(),
                "RetryIndicator" => 0
            ],
            "TerminalId" => $this->config->terminalId,
            "Payment" => [
                "Amount" => [
                    "Value" => $amount,
                    "CurrencyCode" => "CHF"
                ],
                "OrderId" => $refno,
                "Description" => "Top-up"
            ],
            "Payer" => [
                "LanguageCode" => "de-CH",
                "Email" => $user->getEmail(),
                //"IpAddress" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                "FirstName" => $user->contact_person ?? '', // Kontaktperson als Vorname
                "LastName" => $user->company_name ?? '',    // Firmenname als Nachname (falls keine echte Person verfügbar)
                "Phone" => $user->company_phone ?? '',
                "BillingAddress" => [
                    "Street" => $user->company_street ?? '',
                    "Zip" => $user->company_zip ?? '',
                    "City" => $user->company_city ?? '',
                    "CountryCode" => 'CH' // oder dynamisch, falls vorhanden
                ],
                "UserAgent" => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                "AcceptHeader" => $_SERVER['HTTP_ACCEPT'] ?? 'text/html',
                "JavaScriptEnabled" => true,
                "JavaEnabled" => false,
                "ScreenWidth" => 1920,
                "ScreenHeight" => 1080,
                "ColorDepth" => "24bits",
                "TimeZoneOffsetMinutes" => -120,
            ],
            "ReturnUrl" => [
                "Url" => $successUrl
            ],
            "RegisterAlias" => [
                "IdGenerator" => "RANDOM"
            ]
        ];

        log_message('info', 'Saferpay Initialize Request mit RegisterAlias: ' . json_encode($data));

        $response = $this->sendRequest($url, $data);

        log_message('info', 'Saferpay Initialize Response: ' . json_encode($response));

        if (!isset($response['RedirectUrl']) || !isset($response['Token'])) {
            throw new \Exception("Fehler bei Initialisierung: " . json_encode($response));
        }

        // Token speichern (wichtig!)
        $this->storeToken($refno, $response['Token']);

        return $response;
    }

    public function authorizeWithAlias(string $aliasId, int $amount, string $refno, $user = null)
    {
        $url = $this->config->apiBaseUrl . '/Payment/v1/Transaction/AuthorizeDirect';

        // Wenn kein User übergeben wurde, versuche auth()->user()
        if ($user === null) {
            $user = auth()->user();
        }

        $data = [
            "RequestHeader" => [
                "SpecVersion" => "1.35",
                "CustomerId" => $this->config->customerId,
                "RequestId" => uniqid(),
                "RetryIndicator" => 0
            ],
            "TerminalId" => $this->config->terminalId,
            "Payment" => [
                "Amount" => [
                    "Value" => $amount,
                    "CurrencyCode" => "CHF"
                ],
                "OrderId" => $refno,
                "Description" => "Offer Purchase"
            ],
            "PaymentMeans" => [
                "Alias" => [
                    "Id" => $aliasId
                ]
            ],
            "Payer" => [
                "LanguageCode" => "de-CH",
                "Email" => $user->getEmail(),
                "IpAddress" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                "UserAgent" => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]
        ];

        log_message('info', 'Saferpay AuthorizeDirect Request: ' . json_encode($data));
        $response = $this->sendRequest($url, $data);
        log_message('info', 'Saferpay AuthorizeDirect Response: ' . json_encode($response));

        if (!isset($response['Transaction'])) {
            throw new \Exception("Autorisierung fehlgeschlagen: " . json_encode($response));
        }

        return $response;
    }

    private function sendRequest(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->config->basicAuth,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($result, true);
        } else {
            throw new \Exception("Saferpay API Fehler: HTTP $httpCode - $result");
        }
    }

    public function assertTransaction(string $token, int $retryIndicator = 0): array
    {
        $url = $this->config->apiBaseUrl . '/Payment/v1/PaymentPage/Assert';

        $data = [
            "RequestHeader" => [
                "SpecVersion" => "1.35",
                "CustomerId" => $this->config->customerId,
                "RequestId" => uniqid('', true),
                "RetryIndicator" => $retryIndicator,
            ],
            "Token" => $token
        ];

        log_message('info', 'Saferpay Assert Request: ' . json_encode($data));
        $response = $this->sendRequest($url, $data);
        log_message('info', 'Saferpay Assert Full Response: ' . json_encode($response));

        return $response;
    }

    public function storeToken(string $refno, string $token)
    {
        $db = \Config\Database::connect();
        $db->table('saferpay_transactions')->insert([
            'user_id'        => auth()->user()->id,
            'refno'          => $refno,
            'token'          => $token,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateTransaction(string $token, array $data)
    {
        $db = \Config\Database::connect();
        $db->table('saferpay_transactions')->where('token', $token)->update($data);
    }

    public function getTokenByRefno(string $refno): ?string
    {
        $db = \Config\Database::connect();
        $row = $db->table('saferpay_transactions')->where('refno', $refno)->get()->getRow();
        return $row ? $row->token : null;
    }

    /**
     * Transaktion verbuchen (Capture)
     * Muss nach erfolgter Autorisierung aufgerufen werden
     *
     * @param string $transactionId Die Transaction ID aus Assert Response
     * @return array Response von Saferpay
     */
    public function captureTransaction(string $transactionId): array
    {
        $url = $this->config->apiBaseUrl . '/Payment/v1/Transaction/Capture';

        $data = [
            "RequestHeader" => [
                "SpecVersion" => "1.35",
                "CustomerId" => $this->config->customerId,
                "RequestId" => uniqid(),
                "RetryIndicator" => 0
            ],
            "TransactionReference" => [
                "TransactionId" => $transactionId
            ]
        ];

        return $this->sendRequest($url, $data);
    }


}
