<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Stripe extends BaseConfig
{
    public string $secretKey = 'sk_test_...'; // Deine Secret API Key
    public string $publicKey = 'pk_test_...';
    public string $currency = 'chf';
}
