<?php
namespace App\Libraries;

use Config\Payrexx;

class PayrexxService
{
    protected $config;

    public function __construct()
    {
        $this->config = new Payrexx();
    }

    public function request($endpoint, $data): ?array
    {
        $ch = curl_init("https://{$this->config->instance}.payrexx.com/api/v1/{$endpoint}");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->config->apiKey}",
            ],
        ]);

        $response = curl_exec($ch);
        log_message('debug', 'Payrexx Roh-Antwort: ' . $response);

        if ($response === false) {
            log_message('error', 'Payrexx API Fehler: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        log_message('debug', 'Payrexx Roh-Antwort: ' . $response);

        curl_close($ch);
        $decoded = json_decode($response, true);

        // Logge Fehler falls vorhanden
        if (!isset($decoded['data'])) {
            log_message('error', 'Ungültige Antwort von Payrexx: ' . $response);
            return null;
        }

        return $decoded;
    }

    public function createTokenCheckout($user, string $successUrl, string $cancelUrl): ?array
    {
        $userId = is_object($user) ? $user->id : $user['id'];
        $userEmail = is_object($user) ? $user->email : $user['email'];

        return $this->request('Payment', [
            'amount' => 0,
            'currency' => $this->config->currency,
            'referenceId' => 'user-' . $userId,
            'generateToken' => true,
            'fields[email]' => $userEmail,
            'successUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
        ]);
    }

    public function chargeWithToken(string $token, int $amount, string $description = ''): array
    {
        return $this->request('Payment', [
            'amount' => $amount,
            'currency' => $this->config->currency,
            'token' => $token,
            'referenceId' => 'charge-' . time(),
            'description' => $description,
        ]);
    }

}
