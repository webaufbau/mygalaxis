<?php
function trim_recursive($value) {
    if (is_array($value)) {
        return array_map('trim_recursive', $value);
    }
    if (is_string($value)) {
        return trim($value);
    }
    return $value; // z.B. null, int, etc. unverändert lassen
}

function normalize_keys_recursive(array $array): array
{
    $result = [];
    foreach ($array as $key => $value) {
        // Key normalisieren
        $normalizedKey = normalize_key($key);

        // Rekursiv normalisieren, falls verschachteltes Array
        if (is_array($value)) {
            $value = normalize_keys_recursive($value);
        }

        $result[$normalizedKey] = $value;
    }

    return $result;
}

function normalize_key(string $key): string
{
    // Umlaute ersetzen
    $umlaute = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss'];
    $key = strtr($key, $umlaute);

    // Kleinbuchstaben
    $key = mb_strtolower($key, 'UTF-8');

    // Sonderzeichen entfernen außer Leerzeichen
    $key = preg_replace('/[^a-z0-9\s]/u', '', $key);

    // Leerzeichen zu Unterstrichen
    $key = preg_replace('/\s+/', '_', $key);

    return $key;
}

if (!function_exists('is_mobile_number')) {
    /**
     * Prüft, ob eine Telefonnummer eine Mobilnummer ist (CH, DE, AT).
     *
     * @param string $phone
     * @return bool
     */
    function is_mobile_number(string $phone): bool
    {
        // Alle Leerzeichen, Klammern und Bindestriche entfernen
        $phone = preg_replace('/[\s\-\(\)\/]/', '', $phone);

        // Liste der Länder und deren Mobilfunk-Präfixe
        $patterns = [
            // Schweiz: +41 oder 0 / Mobile: 75, 76, 77, 78, 79
            '/^(\+41|0)(75|76|77|78|79)[0-9]{7}$/',
            // Deutschland: +49 oder 0 / Mobile: 15x, 16x, 17x
            '/^(\+49|0)(15[0-9]|16[0-9]|17[0-9])[0-9]{7,8}$/',
            // Österreich: +43 oder 0 / Mobile: 650-699 (diverse Provider)
            '/^(\+43|0)(65[0-9]|66[0-9]|67[0-9]|68[0-9]|69[0-9])[0-9]{6,8}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }
}
