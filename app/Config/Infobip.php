<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration class for Infobip API credentials.
 *
 * Hier werden API-Host und API-Key für Infobip definiert.
 */
class Infobip extends BaseConfig
{
    /**
     * Basis-URL des Infobip API-Servers.
     *
     * Beispiel: https://api.infobip.com
     *
     * @var string
     */
    public string $api_host;

    /**
     * API-Key für die Authentifizierung bei Infobip.
     *
     * Wird für alle API-Anfragen benötigt.
     *
     * @var string
     */
    public string $api_key;


    public string $sender; // Absender-Name (muss registriert sein)

    public function __construct()
    {
        parent::__construct();

        // Lade Werte aus Umgebungsvariablen
        // Unterstütze sowohl INFOBIP_API_HOST als auch infobip.api_host
        $this->api_host = getenv('INFOBIP_API_HOST') ?: getenv('infobip.api_host') ?: 'https://api.infobip.com';
        $this->api_key = getenv('INFOBIP_API_KEY') ?: getenv('infobip.api_key') ?: '';
        $this->sender = getenv('INFOBIP_SENDER') ?: getenv('infobip.sender') ?: 'InfoSMS';
    }
}
