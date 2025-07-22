<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Datatrans extends BaseConfig
{
    /**
     * Dein Merchant ID
     */
    public string $merchantId = 'DEIN_MERCHANT_ID';

    /**
     * Basic Auth Token (Base64 codiert 'user:password')
     * Tipp: am besten per ENV-Variable laden und hier nur setzen.
     */
    public string $basicAuth = 'Basic xxxxxxxx';

    /**
     * API Endpoints
     */
    public string $apiBaseUrl = 'https://api.sandbox.datatrans.com/v1';

    /**
     * Redirect URL zum Starten der Zahlung (mit {transactionId} als Platzhalter)
     */
    public string $redirectUrlTemplate = 'https://pay.sandbox.datatrans.com/v1/start/{transactionId}';

    /**
     * Timeout für API Requests in Sekunden
     */
    public int $timeout = 30;

    // Weitere Konfigurationsparameter, die du brauchst...
}
