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
