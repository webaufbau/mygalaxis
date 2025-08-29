<?php
namespace App\Libraries;

class OfferPriceCalculator
{
    protected array $categoryPrices;

    public function __construct()
    {
        $categoryManager = new \App\Libraries\CategoryManager();
        $this->categoryPrices = $categoryManager->getAll();
    }

    /**
     * Berechnet den Preis für ein Angebot
     *
     * @param string $type  Kategorie (z.B. move, painting)
     * @param array  $fields  FormFields aus Angebot
     * @return float
     */
    public function calculatePrice(string $type, array $fields): float
    {
        $price = 0;

        $category = $this->categoryPrices[$type] ?? null;
        if (!$category) return 0;

        // Beispiel: Move / Umzug
        if ($type === 'move' || $type === 'move_cleaning') {
            $zimmer = $fields['zimmer_size'] ?? null; // 1Z, 2Z, EFH...
            foreach ($category['options'] as $opt) {
                if ($opt['label'] === $zimmer) {
                    $price += $opt['price'];
                    break;
                }
            }
        }

        // Beispiel: Wiederkehrend
        if (!empty($fields['recurring'])) {
            $price += 20;
        }

        // Beispiel: Fenster / Fassaden / Hauswartung
        if (!empty($fields['fenster'])) $price += 19;
        if (!empty($fields['fassade'])) $price += 39;
        if (!empty($fields['hauswartung'])) $price += 79;

        // MALER Beispiel
        if ($type === 'painting') {
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
            foreach ($category['options'] as $opt) {
                if ($opt['label'] === $zimmer) {
                    $price += $opt['price'];
                    break;
                }
            }
            if (!empty($fields['trennwaende'])) $price += 15;
        }

        // TODO: weitere Kategorien: gardening, plumbing, electrician, heating, flooring, tiling

        // Rabatte nach Zeit
        if (!empty($fields['hours_since_request'])) {
            $h = $fields['hours_since_request'];
            if ($h >= 24) $price *= 0.3;
            elseif ($h >= 14) $price *= 0.5;
            elseif ($h >= 8) $price *= 0.7;

            $price = ceil($price); // aufrunden
        }

        return $price;
    }
}
