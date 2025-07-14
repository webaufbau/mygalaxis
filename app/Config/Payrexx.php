<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Payrexx extends BaseConfig
{
    public string $instance = 'dein-payrexx-instance';
    public string $apiKey = 'dein_api_key';
    public string $currency = 'CHF';
    public bool $useSandbox = false; // bei Bedarf
}
