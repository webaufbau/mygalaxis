<?php

if (! function_exists('siteconfig')) {
    function siteconfig(): \App\Libraries\SiteConfigLoader
    {
        static $loader = null;
        if ($loader === null) {
            $loader = new \App\Libraries\SiteConfigLoader();
        }
        return $loader;
    }
}

if (! function_exists('currency')) {
    /**
     * Gibt die Währung basierend auf dem Land zurück
     *
     * @param string|null $platform Optional: Platform override (z.B. für Emails)
     * @return string 'CHF' für Schweiz, 'EUR' für Deutschland/Österreich
     */
    function currency(?string $platform = null): string
    {
        // Falls Platform explizit übergeben wurde, lade Config für diese Platform
        if ($platform) {
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);
            $country = $siteConfig->siteCountry ?? 'ch';
        } else {
            // Sonst verwende aktuelle Platform
            $country = siteconfig()->siteCountry ?? 'ch';
        }

        // Schweiz = CHF, Deutschland/Österreich = EUR
        return match(strtolower($country)) {
            'de', 'at' => 'EUR',
            default => 'CHF'
        };
    }
}

if (! function_exists('languageCode')) {
    /**
     * Gibt den Sprachcode für Zahlungsanbieter zurück
     *
     * @param string|null $platform Optional: Platform override
     * @return string z.B. 'de-CH', 'de-DE', 'de-AT'
     */
    function languageCode(?string $platform = null): string
    {
        if ($platform) {
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);
            $country = $siteConfig->siteCountry ?? 'ch';
        } else {
            $country = siteconfig()->siteCountry ?? 'ch';
        }

        return match(strtolower($country)) {
            'ch' => 'de-CH',
            'de' => 'de-DE',
            'at' => 'de-AT',
            default => 'de-CH'
        };
    }
}

if (! function_exists('countryCode')) {
    /**
     * Gibt den ISO-Ländercode zurück (uppercase)
     *
     * @param string|null $platform Optional: Platform override
     * @return string z.B. 'CH', 'DE', 'AT'
     */
    function countryCode(?string $platform = null): string
    {
        if ($platform) {
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);
            $country = $siteConfig->siteCountry ?? 'ch';
        } else {
            $country = siteconfig()->siteCountry ?? 'ch';
        }

        return strtoupper($country);
    }
}
