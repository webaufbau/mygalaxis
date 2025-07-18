<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Twilio extends BaseConfig
{
    public string $accountSid    = '';
    public string $authToken     = '';
    public string $smsNumber     = '';
    public string $callerId      = '';
    public string $voiceLanguage = 'de-DE';
    public string $voiceName     = 'Polly.Marlene'; // andere Option: 'Alice'

    // Testmode aktivieren
    public bool $testMode        = true;

    // Optional: Twilio Test-Credentials (https://www.twilio.com/docs/iam/test-credentials)
    public string $testAccountSid = 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    public string $testAuthToken  = 'your_test_auth_token';
    public string $testSmsNumber  = '+15005550006'; // Twilio Magic Test Number
    public string $testCallerId   = '+15005550006'; // Auch für Test-Anrufe geeignet

}
