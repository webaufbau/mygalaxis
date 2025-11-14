<?php

/**
 * Email Template Translation Helper
 *
 * Übersetzt Feldnamen in Email-Templates basierend auf der Benutzer-Sprache
 */

if (!function_exists('translate_email_fields')) {
    /**
     * Übersetzt Feldnamen in einem Email-Template
     *
     * @param string $content Email-Content mit {field:xyz} und [field:xyz] Platzhaltern
     * @param string $targetLanguage Zielsprache (en, fr, it)
     * @return string Übersetzter Content
     */
    function translate_email_fields(string $content, string $targetLanguage): string
    {
        // Wenn Sprache Deutsch, gib Original zurück
        if ($targetLanguage === 'de') {
            return $content;
        }

        // Lade globale Übersetzungen
        $translations = load_global_translations();

        if (empty($translations) || !isset($translations[$targetLanguage])) {
            return $content;
        }

        // Parse translations for target language
        $translationMap = parse_translation_string($translations[$targetLanguage]);

        if (empty($translationMap)) {
            return $content;
        }

        // Übersetze {field:xyz} Platzhalter
        // Regex findet {field:irgendwas} aber nicht innerhalb von HTML-Tags oder Conditions
        $content = preg_replace_callback(
            '/\{field:([^}]+)\}/u',
            function ($matches) use ($translationMap) {
                $fieldValue = $matches[1];
                // Übersetze nur wenn eine Übersetzung existiert
                return isset($translationMap[$fieldValue])
                    ? '{field:' . $translationMap[$fieldValue] . '}'
                    : $matches[0];
            },
            $content
        );

        // Übersetze [field:xyz] Platzhalter (in Bedingungen)
        // WICHTIG: Nur den Feldnamen übersetzen, nicht die Condition-Struktur
        $content = preg_replace_callback(
            '/\[if field:([^\]]+?)\]|\[field:([^\]]+?)\]/u',
            function ($matches) use ($translationMap) {
                // $matches[1] = field in [if field:xyz]
                // $matches[2] = field in [field:xyz]
                $fieldValue = $matches[1] ?? $matches[2];

                if (isset($translationMap[$fieldValue])) {
                    // Wenn [if field:xyz], behalte das Format
                    if (isset($matches[1]) && $matches[1]) {
                        return '[if field:' . $translationMap[$fieldValue] . ']';
                    }
                    // Wenn [field:xyz], behalte das Format
                    return '[field:' . $translationMap[$fieldValue] . ']';
                }

                return $matches[0];
            },
            $content
        );

        return $content;
    }
}

if (!function_exists('parse_translation_string')) {
    /**
     * Parse translation string into associative array
     *
     * Format: "Deutsch=Translation\nAndererText=Other Translation"
     *
     * @param string $translationString
     * @return array Associative array ['Deutsch' => 'Translation', ...]
     */
    function parse_translation_string(string $translationString): array
    {
        $map = [];

        if (empty($translationString)) {
            return $map;
        }

        $lines = explode("\n", $translationString);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Split by = sign
            $parts = explode('=', $line, 2);

            if (count($parts) === 2) {
                $german = trim($parts[0]);
                $translation = trim($parts[1]);

                if (!empty($german) && !empty($translation)) {
                    $map[$german] = $translation;
                }
            }
        }

        return $map;
    }
}

if (!function_exists('load_global_translations')) {
    /**
     * Lade globale E-Mail-Übersetzungen aus JSON-Datei
     *
     * @return array ['en' => '...', 'fr' => '...', 'it' => '...']
     */
    function load_global_translations(): array
    {
        $translationsPath = WRITEPATH . 'data/email_field_translations.json';

        if (!file_exists($translationsPath)) {
            return ['en' => '', 'fr' => '', 'it' => ''];
        }

        $loaded = json_decode(file_get_contents($translationsPath), true);

        if (!$loaded || !is_array($loaded)) {
            return ['en' => '', 'fr' => '', 'it' => ''];
        }

        return $loaded;
    }
}

if (!function_exists('get_user_language')) {
    /**
     * Get user's preferred language
     *
     * @param object|null $user User object
     * @return string Language code (de, en, fr, it)
     */
    function get_user_language($user = null): string
    {
        if (!$user) {
            $user = auth()->user();
        }

        if ($user && isset($user->language)) {
            return $user->language;
        }

        // Fallback to German
        return 'de';
    }
}

if (!function_exists('translate_email_field_values')) {
    /**
     * Übersetzt Feldwerte in einem gerenderten Email-Content
     *
     * Diese Funktion übersetzt die tatsächlichen Werte (z.B. "Ja" -> "Yes", "Nein" -> "No")
     * im bereits gerenderten HTML-Content.
     *
     * @param string $content Gerenderter Email-Content mit Feldwerten
     * @param string $targetLanguage Zielsprache (en, fr, it)
     * @return string Content mit übersetzten Feldwerten
     */
    function translate_email_field_values(string $content, string $targetLanguage): string
    {
        // Wenn Sprache Deutsch, gib Original zurück
        if ($targetLanguage === 'de') {
            return $content;
        }

        // Lade globale Übersetzungen
        $translations = load_global_translations();

        if (empty($translations) || !isset($translations[$targetLanguage])) {
            return $content;
        }

        // Parse translations for target language
        $translationMap = parse_translation_string($translations[$targetLanguage]);

        if (empty($translationMap)) {
            return $content;
        }

        // Übersetze Werte im Content
        // Wichtig: Längste Strings zuerst, um Teilstring-Probleme zu vermeiden
        // z.B. "Andere" sollte vor "Andere" übersetzt werden
        uksort($translationMap, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($translationMap as $german => $translation) {
            // Escape für Regex
            $germanEscaped = preg_quote($german, '/');

            // Ersetze nur ganze Wörter/Phrasen (mit Word Boundaries)
            // Aber auch innerhalb von HTML-Tags und am Zeilenanfang/-ende
            $content = preg_replace(
                '/(?<![a-zA-ZäöüÄÖÜß])' . $germanEscaped . '(?![a-zA-ZäöüÄÖÜß])/u',
                $translation,
                $content
            );
        }

        return $content;
    }
}
