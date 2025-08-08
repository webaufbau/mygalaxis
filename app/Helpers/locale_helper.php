<?php

if (! function_exists('getCurrentLocale'))
{
    /**
     * Ermittelt die aktuelle Sprache aus der URI.
     *
     * @param array $locales erlaubte Sprachen, z.B. ['de', 'en', 'fr', 'it']
     * @return string aktueller Sprachcode oder 'de' als Default
     */
    function getCurrentLocale(array $locales = ['de', 'en', 'fr', 'it']): string
    {
        $uri = service('uri')->getPath();
        $segments = explode('/', trim($uri, '/'));

        if (isset($segments[0]) && in_array($segments[0], $locales)) {
            return $segments[0];
        }
        return 'de';
    }
}

if (! function_exists('changeLocaleInUri'))
{
    /**
     * Ersetzt den Sprachcode im URI oder fügt ihn hinzu, falls keiner vorhanden ist.
     *
     * @param string $uri aktueller URI, z.B. "en/login"
     * @param string $newLocale gewünschter Sprachcode
     * @param array $locales erlaubte Sprachen
     * @return string modifizierter URI mit neuem Sprachcode
     */
    function changeLocaleInUri(string $uri, string $newLocale, array $locales = ['de', 'en', 'fr', 'it']): string
    {
        $segments = explode('/', trim($uri, '/'));

        // Prüfen, ob erstes Segment eine Sprache ist
        if (isset($segments[0]) && in_array($segments[0], $locales)) {
            // Wenn neue Sprache 'de' ist, entfernen wir das Segment
            if ($newLocale === 'de') {
                array_shift($segments);  // Sprachsegment entfernen
            } else {
                $segments[0] = $newLocale; // Sprache ersetzen
            }
        } else {
            // Kein Sprachsegment, nur hinzufügen wenn nicht 'de'
            if ($newLocale !== 'de') {
                array_unshift($segments, $newLocale);
            }
            // wenn 'de', dann nichts tun (kein Sprachsegment)
        }

        $uri = implode('/', $segments);

        return '/' . $uri;
    }
}
