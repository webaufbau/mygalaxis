<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SiteConfig extends BaseConfig {
    public string $name = 'Offertenschweiz';
    public string $email = 'info@offertenschweiz.ch';
    public string $thankYouUrl = 'https://offertenschweiz.ch/dankesseite-umzug/';
    public string $frontendURL = 'https://offertenschweiz.ch';
    public string $backendURL  = 'https://my.offertenschweiz.ch';
    public string $logoUrl = 'https://offertenschweiz.ch/wp-content/uploads/2025/06/OFFERTENSchweiz00001.ch_.png';

    // Testmodus-Optionen
    public bool $testMode = false;
    public string $testEmail = 'testbenutzer@offertenschweiz.ch';
}
