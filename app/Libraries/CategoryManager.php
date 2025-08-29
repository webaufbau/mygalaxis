<?php

namespace App\Libraries;

class CategoryManager
{
    protected array $types;
    protected array $options;
    protected string $path;

    public function __construct()
    {
        $config = config('CategoryOptions');
        $this->types = $config->categoryTypes;
        $this->options = $config->categoryOptions;
        $this->path = $config->storagePath;
    }

    public function getAll(): array
    {
        $values = [];

        // JSON einlesen
        if (file_exists($this->path)) {
            $json = file_get_contents($this->path);
            $values = json_decode($json, true) ?? [];
        }

        // Kategorien ergänzen
        foreach ($this->types as $key => $defaultName) {
            $labels = $this->options[$key] ?? [];
            $existingOptions = $values['categories'][$key]['options'] ?? [];

            $options = [];
            foreach ($labels as $idx => $label) {
                $price = $existingOptions[$idx]['price'] ?? 0;
                $options[] = [
                    'label' => $label,
                    'price' => $price
                ];
            }

            $values['categories'][$key] = [
                'name'    => $values['categories'][$key]['name'] ?? $defaultName,
                'options' => $options
            ];
        }

        // Falls keine discountRules existieren, Default aus Config nehmen
        if (!isset($values['discountRules'])) {
            $config = config('CategoryOptions');
            $values['discountRules'] = $config->discountRules;
        }

        return $values;
    }

    public function save(array $categories, array $discountRules): bool
    {
        $filteredCategories = [];

        foreach ($this->types as $key => $defaultName) {
            if (isset($categories[$key])) {
                $options = [];
                foreach ($categories[$key]['options'] as $opt) {
                    $options[] = [
                        'label' => $opt['label'], // Label fix
                        'price' => floatval($opt['price'] ?? 0)
                    ];
                }

                $filteredCategories[$key] = [
                    'name'    => $categories[$key]['name'] ?? $defaultName,
                    'options' => $options
                ];
            }
        }

        // Discount Rules filtern
        $filteredDiscounts = [];
        foreach ($discountRules as $rule) {
            if (!empty($rule['hours']) && !empty($rule['discount'])) {
                $filteredDiscounts[] = [
                    'hours'    => (int)$rule['hours'],
                    'discount' => (int)$rule['discount'],
                ];
            }
        }

        $saveData = [
            'categories'    => $filteredCategories,
            'discountRules' => $filteredDiscounts
        ];

        return file_put_contents(
                $this->path,
                json_encode($saveData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ) !== false;
    }

}
