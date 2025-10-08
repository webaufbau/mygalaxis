<?php
namespace App\Libraries;

class OfferPriceCalculator
{
    protected array $categoryPrices;
    protected array $discountRules;

    public function __construct()
    {
        // JSON-Datei laden (kann z.B. in /app/Config/category_prices.json liegen)
        $jsonPath = WRITEPATH . 'config/category_settings.json';
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            $this->categoryPrices = $data['categories'] ?? [];
            $this->discountRules = $data['discountRules'] ?? [];
        } else {
            $this->categoryPrices = [];
            $this->discountRules = [];
        }
    }

    /**
     * Berechnet den Basispreis
     */
    public function calculatePrice(string $type, string $originalType, array $fields, array $fields_combo): float
    {
        $price = 0;
        $category = $this->categoryPrices[$type] ?? null;
        if (!$category) return 0;

        switch ($type) {

            case 'move':
                // Unterscheidung zwischen privatem und Firmen-Umzug
                if ($originalType === 'umzug_firma') {
                    // Firmen-Umzug: basiert auf Arbeitsplätzen oder Fläche
                    $selected = $this->mapCompanyMoveToRooms($fields);
                } else {
                    // Privat-Umzug: basiert auf Zimmerzahl
                    $selected = $fields['auszug_zimmer'] ?? null;
                    $selected = $this->normalizeRoomValue($selected);
                }

                if($selected && isset($category['options'][$selected])) {
                    $price = $category['options'][$selected]['price'];
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'move_cleaning':
                // Unterscheidung zwischen privatem und Firmen-Umzug + Reinigung
                if ($originalType === 'umzug_firma') {
                    // Firmen-Umzug + Reinigung: basiert auf Arbeitsplätzen oder Fläche
                    $selected = $this->mapCompanyMoveToRooms($fields);

                    // Fallback auf combo fields wenn nicht in fields
                    if (!$selected) {
                        $selected = $this->mapCompanyMoveToRooms($fields_combo);
                    }
                } else {
                    // Privat-Umzug + Reinigung: basiert auf Zimmerzahl
                    $selected = $fields['auszug_zimmer'] ?? $fields_combo['auszug_zimmer'] ?? null;
                    $selected = $this->normalizeRoomValue($selected);
                }

                if($selected && isset($category['options'][$selected])) {
                    $price = $category['options'][$selected]['price'];
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'cleaning':
                // Sonderfälle über $originalType (exklusive Reinigungsarten)
                if (in_array($originalType, ['reinigung_nur_fenster', 'reinigung_fassaden', 'reinigung_hauswartung', 'reinigung_andere'])) {
                    switch ($originalType) {
                        case 'reinigung_nur_fenster':
                            $price = $category['options']['nur_fenster']['price'];
                            break;
                        case 'reinigung_fassaden':
                            $price = $category['options']['nur_fassaden']['price'];
                            break;
                        case 'reinigung_hauswartung':
                            $price = $category['options']['hauswartung']['price'];
                            // Wiederkehrend/täglich hinzufügen
                            if (!empty($fields['ausfuehrung']) && strtolower($fields['ausfuehrung']) === 'täglich') {
                                $price += $category['options']['wiederkehrend']['price'];
                            }
                            break;
                        case 'reinigung_andere':
                            // Andere Reinigungsarbeiten (z.B. Auto, Boot, etc.)
                            $price = $category['options']['andere_arbeiten']['price'] ?? 39;
                            break;
                    }
                }
                else {
                    // komplette Wohnung oder Teil der Wohnung
                    if (!empty($fields['wohnung_groesse'])) {
                        $value = $fields['wohnung_groesse']; // z.B. "1-Zimmer", "2-Zimmer", "Andere"
                        if ($value === 'Andere') {
                            $key = '6';
                        } else {
                            // Zahl vor dem Bindestrich extrahieren
                            preg_match('/^\d+/', $value, $matches);
                            $key = $matches[0] ?? null; // "1", "2", ...
                        }

                        if ($key && isset($category['options'][$key])) {
                            $price = $category['options'][$key]['price'];
                        }
                    }
                    elseif (!empty($fields['komplett_anzahlzimmer'])) {
                        $key = $fields['komplett_anzahlzimmer']; // 1..4
                        $key = $key > 5 ? '6' : (string)$key;
                        $price = $category['options'][$key]['price'] ?? 0;
                    }

                    // Wiederkehrend hinzufügen
                    if (!empty($fields['reinigungsart_wiederkehrend']) && $fields['reinigungsart_wiederkehrend']=='Wiederkehrend') {
                        $price += $category['options']['wiederkehrend']['price'];
                    }

                    // Fensterreinigung als Zusatz (wenn normale Wohnung + Fenster gewünscht)
                    if (!empty($fields['fensterreinigung']) && strtolower($fields['fensterreinigung']) === 'ja') {
                        $price += $category['options']['nur_fenster']['price'];
                    }

                    // Fassadenreinigung als Zusatz (wenn normale Wohnung + Fassade gewünscht)
                    if (!empty($fields['aussenfassade']) && strtolower($fields['aussenfassade']) === 'ja') {
                        $price += $category['options']['nur_fassaden']['price'];
                    }

                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'painting':

                $price = 0;
                $category = $this->categoryPrices['painting'] ?? [];

                if (isset($fields['art_gewerbe']) && $fields['art_gewerbe'] !== 'Andere') { // Wenn art_gewerbe dann Büro / Laden / Lager / Industrie ABER NICHT ANDERE => das ist teuerer
                    $price = $category['options']['gewerbe_buero_laden_lager_industrie']['price'] ?? 0;
                } elseif (isset($fields['art_objekt'])) { // Wenn art_objekt dann Wohnung / Haus / Gewerbe
                    $price = $category['options']['neubau_renovierung_andere']['price'] ?? 0;
                } else {
                    // Andere → Fixpreis 39.-
                    $price = $category['options']['gewerbe_andere']['price'] ?? 0;
                }

                // arbeiten_wohnung
                // Arbeiten
                foreach ($fields['arbeiten_wohnung'] ?? [] as $arbeit) {
                    $arbeit = strtolower($arbeit);           // "wände"
                    $arbeit = convert_umlaute($arbeit);      // "waende"
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $arbeit); // "waende"
                    $price += $category['options'][$aKey]['price'] ?? 0;
                }

                // Malerarbeiten Übersicht (Innenräume / Fassade / Andere)
                foreach ($fields['malerarbeiten_uebersicht'] ?? [] as $arbeit) {
                    $key = match(strtolower($arbeit)) {
                        'innenräume', 'innenraeume' => 'arbeiten_innenraeume',
                        'fassade' => 'arbeiten_fassade',
                        'andere' => 'arbeiten_andere',
                        default => null
                    };
                    if ($key) $price += $category['options'][$key]['price'] ?? 0;
                }

                // Zimmergrößen für Wände
                $wandAnzahl = $fields['wand_komplett_anzahl'] ?? $fields['wand_teil_anzahl'] ?? null;
                if ($wandAnzahl) {
                    if ($wandAnzahl === 'Andere') {
                        $key = 'andere_zimmer';
                    } else {
                        preg_match('/^\d+/', $wandAnzahl, $matches);
                        $key = $matches[0] ?? null;
                    }
                    if ($key) $price += $category['options'][$key]['price'] ?? 0;
                }

                // Zimmergrößen für Decken
                $deckenAnzahl = $fields['decken_komplett_anzahl'] ?? $fields['decken_teil_anzahl'] ?? null;
                if ($deckenAnzahl) {
                    if ($deckenAnzahl === 'Andere') {
                        $key = 'andere_zimmer';
                    } else {
                        preg_match('/^\d+/', $deckenAnzahl, $matches);
                        $key = $matches[0] ?? null;
                    }
                    if ($key) $price += $category['options'][$key]['price'] ?? 0;
                }

                // Trennwände
                if (!empty($fields['wand_option_trennwand']) && $fields['wand_option_trennwand'] === 'Ja') {
                    $price += $category['options']['trennwaende']['price'] ?? 0;
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;


            case 'gardening':

                $category = $this->categoryPrices['gardening'] ?? [];

                // --- Basis: Mieter / Eigentümer / Verwaltung / Andere ---
                $price = $category['options']['mieter_eigentuemer_verwaltung_andere']['price'] ?? 0;

                // --- Garten anlegen (Mehrfachauswahl) ---
                foreach ($fields['garten_anlegen'] ?? [] as $arbeit) {
                    // Normierung wie bei Painting
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (isset($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Wiederkehrende Arbeiten ---
                $wiederkehrendFields = [
                    'teich_reinigung_intervall',
                    'hecke_schneiden_einmalig',
                    'baum_schneiden_einmalig',
                    'rasen_maehen_einmalig',
                ];

                foreach ($wiederkehrendFields as $fieldKey) {
                    if (!empty($fields[$fieldKey]) && $fields[$fieldKey] === 'Wiederkehrend') {
                        $price += $category['options']['wiederkehrend']['price'] ?? 0;
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'electrician':
                $category = $this->categoryPrices['electrician'] ?? [];
                $price = 0;

                // --- Art Objekt ---
                if (!empty($fields['art_objekt'])) {
                    // Normierung wie bei Painting / Gardening
                    $aKey = strtolower($fields['art_objekt']);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Arbeiten (Mehrfach möglich) ---
                foreach ($fields['arbeiten_elektriker'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'plumbing':
                $category = $this->categoryPrices['plumbing'] ?? [];
                $price = 0;

                // --- Art Objekt ---
                if (!empty($fields['art_objekt'])) {
                    $aKey = strtolower($fields['art_objekt']);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Arbeiten (Mehrfach möglich) ---
                foreach ($fields['arbeiten_sanitaer'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;


            case 'heating':
                $category = $this->categoryPrices['heating'] ?? [];
                $price = 0;

                // --- Step 2 Arbeiten (Neubau, Renovierung, Umbau) ---
                $step2Keys = ['neubau', 'renovierung', 'umbau'];
                $selectedStep2 = [];

                foreach ($fields['arbeiten_heizung'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (in_array($aKey, $step2Keys)) {
                        $price += $category['options'][$aKey]['price'] ?? 0;
                        $selectedStep2[] = $aKey;
                    }
                }

                // --- Step 3 Arbeiten (Neue Anlagen / Heizkörper / Andere) ---
                $hasStep3 = false;
                foreach ($fields['arbeiten_heizung'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!in_array($aKey, $step2Keys) && !empty($category['options'][$aKey])) {
                        $hasStep3 = true;
                        $additionalPrice = $category['options'][$aKey]['price'] ?? 0;

                        // Wenn Step2-Arbeiten gewählt wurden, ziehen wir die Basis ab
                        if (in_array($aKey, ['neue_waermepumpe','neue_gasheizung','neue_oelheizung','neue_erdwaerme'])) {
                            foreach ($selectedStep2 as $step2Key) {
                                $additionalPrice -= $category['options'][$step2Key]['price'] ?? 0;
                            }
                            $additionalPrice = max($additionalPrice, 0); // nicht negativ
                        }

                        $price += $additionalPrice;
                    }
                }

                // --- Art Objekt ---
                // Objektart wird NUR addiert, wenn Step 3 (neue Anlagen etc.) gewählt wurde
                // Bei nur Step 2 (Neubau/Renovierung/Umbau) wird die Objektart NICHT addiert
                if ($hasStep3 && !empty($fields['art_objekt'])) {
                    $aKey = strtolower($fields['art_objekt']);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'flooring':
                $category = $this->categoryPrices['flooring'] ?? [];
                $price = 0;

                // --- Art Objekt ---
                if (!empty($fields['art_objekt'])) {
                    $aKey = strtolower($fields['art_objekt']);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Arbeiten Boden ---
                foreach ($fields['arbeiten_boden'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;

            case 'tiling':
                $category = $this->categoryPrices['tiling'] ?? [];
                $price = 0;

                // --- Art Objekt ---
                if (!empty($fields['art_objekt'])) {
                    $aKey = strtolower($fields['art_objekt']);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Arbeiten Platten ---
                foreach ($fields['arbeiten_platten'] ?? [] as $arbeit) {
                    $aKey = strtolower($arbeit);
                    $aKey = convert_umlaute($aKey);
                    $aKey = preg_replace('/[^a-z0-9]/i', '_', $aKey);

                    if (!empty($category['options'][$aKey])) {
                        $price += $category['options'][$aKey]['price'];
                    }
                }

                // --- Maximalpreis berücksichtigen ---
                $maxPrice = $category['max'] ?? null;
                if ($maxPrice !== null && $price > $maxPrice) {
                    $price = $maxPrice;
                }

                break;




        }

        return $price;
    }

    /**
     * Discount anwenden
     */
    public function applyDiscount(float $price, float|int $hoursDiff): float
    {
        foreach ($this->discountRules as $rule) {
            if ($hoursDiff >= $rule['hours']) {
                $discounted = $price * (1 - $rule['discount'] / 100);
                return ceil($discounted);
            }
        }

        return $price;
    }

    /**
     * Berechnet sowohl Preis als auch discounted_price
     */
    public function calculateWithDiscount(string $type, string $originalType, array $fields, array $fields_combo, float|int $hoursDiff): array
    {
        $price = $this->calculatePrice($type, $originalType , $fields, $fields_combo);
        $discountedPrice = $this->applyDiscount($price, $hoursDiff);

        return [
            'price' => $price,
            'discounted_price' => $discountedPrice,
        ];
    }

    /**
     * Normalisiert den Zimmer-Wert für die Preis-Berechnung
     * Behandelt Arrays, "X-Zimmer" Strings und "Andere"
     */
    protected function normalizeRoomValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Wenn Array, ersten Wert nehmen
        if (is_array($value)) {
            $value = $value[0] ?? null;
            if (empty($value)) {
                return null;
            }
        }

        // Zu String konvertieren
        $value = (string)$value;

        // "Andere" → "6" (entspricht der Kategorie-Konfiguration)
        if (strtolower($value) === 'andere') {
            return '6';
        }

        // "1-Zimmer", "2-Zimmer" etc. → "1", "2"
        if (preg_match('/^(\d+)-?zimmer/i', $value, $matches)) {
            return $matches[1];
        }

        // Wenn es bereits eine Zahl ist (1, 2, 3, 4, 5, 6)
        if (is_numeric($value) && $value >= 1 && $value <= 6) {
            return (string)((int)$value);
        }

        // Andere Fälle → null (wird nicht gefunden, Preis bleibt 0)
        return null;
    }

    /**
     * Mappt Firmen-Umzug Felder (Arbeitsplätze/Fläche) auf Zimmer-Äquivalente
     * für die Preis-Berechnung
     */
    protected function mapCompanyMoveToRooms(array $fields): ?string
    {
        // Primär: Arbeitsplatz-Anzahl
        $workplaces = $fields['auszug_arbeitsplatz_firma'] ?? null;

        if ($workplaces) {
            // "1 - 5", "6 - 10", "11 - 15", "16 - 20", "Mehr als 20"
            if (preg_match('/(\d+)\s*-\s*(\d+)/', $workplaces, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                $avg = ($min + $max) / 2;

                // Mapping: Arbeitsplätze → Zimmer-Äquivalent
                if ($avg <= 5) return '2';      // 1-5 Arbeitsplätze → 2 Zimmer (29 CHF)
                if ($avg <= 10) return '3';     // 6-10 Arbeitsplätze → 3 Zimmer (39 CHF)
                if ($avg <= 15) return '4';     // 11-15 Arbeitsplätze → 4 Zimmer (45 CHF)
                if ($avg <= 20) return '5';     // 16-20 Arbeitsplätze → 5 Zimmer (49 CHF)
                return '6';                     // 20+ Arbeitsplätze → 6 Zimmer/EFH (55 CHF)
            }

            // "Mehr als 20" oder ähnliche Strings
            if (preg_match('/mehr|20/i', $workplaces)) {
                return '6';
            }
        }

        // Fallback: Fläche
        $area = $fields['auszug_flaeche_firma'] ?? null;

        if ($area) {
            // "0 - 50 m²", "51 - 100 m²", "101 - 150 m²", "151 - 200 m²", "Mehr als 200 m²"
            if (preg_match('/(\d+)\s*-\s*(\d+)/', $area, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                $avg = ($min + $max) / 2;

                // Mapping: Fläche → Zimmer-Äquivalent
                if ($avg <= 50) return '2';     // 0-50 m² → 2 Zimmer
                if ($avg <= 100) return '3';    // 51-100 m² → 3 Zimmer
                if ($avg <= 150) return '4';    // 101-150 m² → 4 Zimmer
                if ($avg <= 200) return '5';    // 151-200 m² → 5 Zimmer
                return '6';                     // 200+ m² → 6 Zimmer/EFH
            }

            // "Mehr als 200 m²"
            if (preg_match('/mehr|200/i', $area)) {
                return '6';
            }
        }

        // Wenn beides fehlt → maximale Kategorie (sicherheitshalber)
        return '6';
    }
}
