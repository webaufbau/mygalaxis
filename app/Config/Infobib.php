<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration class for Infobip API credentials.
 *
 * Hier werden API-Host und API-Key für Infobip definiert.
 */
class Infobib extends BaseConfig
{
    /**
     * Basis-URL des Infobip API-Servers.
     *
     * Beispiel: https://api.infobip.com
     *
     * @var string
     */
    public string $api_host = '';

    /**
     * API-Key für die Authentifizierung bei Infobip.
     *
     * Wird für alle API-Anfragen benötigt.
     *
     * @var string
     */
    public string $api_key = '';
}
