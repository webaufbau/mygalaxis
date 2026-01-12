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
        foreach ($this->types as $catKey => $defaultName) {
            $labels = $this->options[$catKey] ?? [];
            $existingOptions = $values['categories'][$catKey]['options'] ?? [];

            $options = [];
            foreach ($labels as $labelInfo) {
                $key = $labelInfo['key'];
                $label = $labelInfo['label'];
                $price = $existingOptions[$key]['price'] ?? 0; // Jetzt nach key suchen
                $options[$key] = [
                    'key' => $key,
                    'label' => $label,
                    'price' => $price
                ];
            }

            $values['categories'][$catKey] = [
                'name'    => $values['categories'][$catKey]['name'] ?? $defaultName,
                'max' => $values['categories'][$catKey]['max'] ?? null,
                'review_email_days' => $values['categories'][$catKey]['review_email_days'] ?? 5,
                'review_reminder_days' => $values['categories'][$catKey]['review_reminder_days'] ?? 10,
                'form_link' => $values['categories'][$catKey]['form_link'] ?? '',
                'color' => $values['categories'][$catKey]['color'] ?? '#6c757d',
                'options' => $options
            ];
        }

        // Discount Rules
        if (!isset($values['discountRules'])) {
            $config = config('CategoryOptions');
            $values['discountRules'] = $config->discountRules;
        }

        return $values;
    }


    public function save(array $categories, array $discountRules): bool
    {
        $filteredCategories = [];

        foreach ($this->types as $catKey => $defaultName) {
            if (isset($categories[$catKey])) {
                $options = [];
                foreach ($categories[$catKey]['options'] as $optKey => $opt) {
                    $options[$optKey] = [
                        'key' => $optKey,
                        'label' => $opt['label'], // Label fix
                        'price' => floatval($opt['price'] ?? 0)
                    ];
                }

                $filteredCategories[$catKey] = [
                    'name'    => $categories[$catKey]['name'] ?? $defaultName,
                    'max' => (isset($categories[$catKey]['max']) && $categories[$catKey]['max'] !== '')
                        ? intval($categories[$catKey]['max'])
                        : null,
                    'review_email_days' => isset($categories[$catKey]['review_email_days']) && $categories[$catKey]['review_email_days'] !== ''
                        ? intval($categories[$catKey]['review_email_days'])
                        : 5,
                    'review_reminder_days' => isset($categories[$catKey]['review_reminder_days']) && $categories[$catKey]['review_reminder_days'] !== ''
                        ? intval($categories[$catKey]['review_reminder_days'])
                        : 10,
                    'form_link' => $categories[$catKey]['form_link'] ?? '',
                    'color' => $categories[$catKey]['color'] ?? '#6c757d',
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
