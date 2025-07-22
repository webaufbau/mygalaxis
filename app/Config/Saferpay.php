<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Saferpay extends BaseConfig
{
    /**
     * Client ID (z. B. von Saferpay JSON API)
     */
    public string $customerId = 'YOUR_CUSTOMER_ID';

    /**
     * Terminal ID
     */
    public string $terminalId = 'YOUR_TERMINAL_ID';

    /**
     * Basic Auth (Base64 'username:password')
     */
    public string $basicAuth = 'Basic xxxxxxxx';

    /**
     * Saferpay API URL
     */
    public string $apiBaseUrl = 'https://test.saferpay.com/api';

    /**
     * Timeout für API Requests
     */
    public int $timeout = 30;
}
