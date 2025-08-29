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
    public function calculatePrice(string $type, string $originalType, array $fields, array $fields_combo = null): float
    {
        $price = 0;
        $category = $this->categoryPrices[$type] ?? null;
        if (!$category) return 0;

        switch ($type) {

            case 'move':
                $selected = $fields['auszug_zimmer'] ?? null; // z.B. "1", "2", ..., "6"
                if($selected && isset($category['options'][$selected])) {
                    $price = $category['options'][$selected]['price'];
                }
                break;

            case 'move_cleaning':
                $selected = $fields['auszug_zimmer'] ?? $fields_combo['auszug_zimmer'] ?? null; // z.B. "1", "2", ..., "6"
                if($selected && isset($category['options'][$selected])) {
                    $price = $category['options'][$selected]['price'];
                }
                break;

            case 'cleaning':
                // Sonderfälle über $originalType
                if (in_array($originalType, ['reinigung_nur_fenster', 'reinigung_fassaden', 'reinigung_hauswartung'])) {
                    switch ($originalType) {
                        case 'reinigung_nur_fenster':
                            $price = $category['options']['nur_fenster']['price'];
                            break;
                        case 'reinigung_fassaden':
                            $price = $category['options']['nur_fassaden']['price'];
                            break;
                        case 'reinigung_hauswartung':
                            $price = $category['options']['hauswartung']['price'];
                            break;
                    }
                }
                else {
                    // komplette Wohnung oder Teil der Wohnung
                    if (!empty($fields['wohnung_groesse'])) {
                        $value = $fields['wohnung_groesse']; // z.B. "1-Zimmer", "2-Zimmer", "Andere"
                        if ($value === 'Andere') {
                            $key = 'andere';
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
                        $key = $key > 5 ? 'andere' : (string)$key;
                        $price = $category['options'][$key]['price'] ?? 0;
                    }

                    // Wiederkehrend hinzufügen
                    if (!empty($fields['reinigungsart_wiederkehrend']) && $fields['reinigungsart_wiederkehrend']=='Wiederkehrend') {
                        $price += $category['options']['wiederkehrend']['price'];
                    }
                }
                break;

            case 'painting':
                foreach ($fields['arbeiten'] ?? [] as $arbeit) {
                    foreach ($category['options'] as $opt) {
                        if ($opt['label'] === $arbeit) {
                            $price += $opt['price'];
                            break;
                        }
                    }
                }
                // Zimmergrösse hinzufügen
                $zimmer = $fields['zimmer_size'] ?? null;
                if ($zimmer) {
                    foreach ($category['options'] as $opt) {
                        if ($opt['label'] === $zimmer) {
                            $price += $opt['price'];
                            break;
                        }
                    }
                }
                if (!empty($fields['trennwaende'])) $price += 15;
                break;


            // TODO: weitere Kategorien: gardening, plumbing, electrician, heating, flooring, tiling
        }





dd($price);
        // Beispiel für move / move_cleaning
        if ($type === 'move' || $type === 'move_cleaning') {
            $zimmer = $fields['zimmer_size'] ?? null;
            foreach ($category['options'] as $opt) {
                if ($opt['label'] === $zimmer) {
                    $price += $opt['price'];
                    break;
                }
            }
        }

        // Wiederkehrend
        if (!empty($fields['recurring'])) $price += 20;

        // Fenster / Fassaden / Hauswartung
        if (!empty($fields['fenster'])) $price += 19;
        if (!empty($fields['fassade'])) $price += 39;
        if (!empty($fields['hauswartung'])) $price += 79;

        // Maler
        if ($type === 'painting') {
            // Standard Grundpreis 19;
            $price = 19;

            // Wenn Anfangs Andere
            if(isset($field['art_objeke']) && $field['art_objeke']=='Andere') {
                $price = 39;
            }

            // Oder bei Gewerbe Andere
            if(isset($field['art_gewerbe']) && $field['art_gewerbe']=='Andere') {
                $price = 39;
            }

            dd($category);

            foreach ($fields['arbeiten_wohnung'] ?? [] as $arbeit) {
                foreach ($category['options'] as $opt) {
                    if ($opt['label'] === $arbeit) {
                        $price += $opt['price'];
                        break;
                    }
                }
            }
            $zimmer = $fields['zimmer_size'] ?? null;
            foreach ($category['options'] as $opt) {
                if ($opt['label'] === $zimmer) {
                    $price += $opt['price'];
                    break;
                }
            }
            if (!empty($fields['trennwaende'])) $price += 15;
        }

        // TODO: weitere Kategorien

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
    public function calculateWithDiscount(string $type, array $fields, float|int $hoursDiff): array
    {
        $price = $this->calculatePrice($type, $fields);
        $discountedPrice = $this->applyDiscount($price, $hoursDiff);

        return [
            'price' => $price,
            'discounted_price' => $discountedPrice,
        ];
    }
}
