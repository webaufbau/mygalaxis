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
                'color' => $values['categories'][$catKey]['color'] ?? '#6c757d',
                'forms' => $values['categories'][$catKey]['forms'] ?? [],
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
                $categoryOptions = $categories[$catKey]['options'] ?? [];
                foreach ($categoryOptions as $optKey => $opt) {
                    $options[$optKey] = [
                        'key' => $optKey,
                        'label' => $opt['label'] ?? '',
                        'price' => floatval($opt['price'] ?? 0)
                    ];
                }

                // Formulare verarbeiten (mehrsprachig)
                $forms = [];
                $rawForms = $categories[$catKey]['forms'] ?? [];
                foreach ($rawForms as $form) {
                    // Mindestens deutscher Name muss vorhanden sein
                    if (!empty($form['name_de'])) {
                        $forms[] = [
                            'name_de' => $form['name_de'],
                            'name_en' => $form['name_en'] ?? '',
                            'name_fr' => $form['name_fr'] ?? '',
                            'name_it' => $form['name_it'] ?? '',
                            'form_link_de' => $form['form_link_de'] ?? '',
                            'form_link_en' => $form['form_link_en'] ?? '',
                            'form_link_fr' => $form['form_link_fr'] ?? '',
                            'form_link_it' => $form['form_link_it'] ?? '',
                        ];
                    }
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
                    'color' => $categories[$catKey]['color'] ?? '#6c757d',
                    'forms' => $forms,
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

    /**
     * Alle verfügbaren Formulare als flache Liste holen
     * Jedes Formular hat: category_key, form_index, name (lokalisiert), form_link (lokalisiert)
     */
    public function getAllForms(?string $locale = null): array
    {
        $locale = $locale ?? service('request')->getLocale();
        $data = $this->getAll();
        $forms = [];

        foreach ($data['categories'] as $catKey => $cat) {
            $categoryForms = $cat['forms'] ?? [];

            if (empty($categoryForms)) {
                // Keine Formulare definiert = Branche ohne Formulare
                continue;
            }

            foreach ($categoryForms as $index => $form) {
                $nameField = "name_{$locale}";
                $linkField = "form_link_{$locale}";

                // Name mit Fallback zu DE
                $name = !empty($form[$nameField]) ? $form[$nameField] : ($form['name_de'] ?? '');

                // Link mit Fallback zu DE
                $link = !empty($form[$linkField]) ? $form[$linkField] : ($form['form_link_de'] ?? '');

                if (empty($link)) {
                    continue; // Kein Link = nicht anzeigen
                }

                $forms[] = [
                    'category_key' => $catKey,
                    'category_name' => $cat['name'],
                    'category_color' => $cat['color'] ?? '#6c757d',
                    'form_index' => $index,
                    'form_id' => $catKey . ':' . $index, // Eindeutige ID
                    'name' => $name,
                    'form_link' => $link,
                ];
            }
        }

        return $forms;
    }

    /**
     * Ein spezifisches Formular nach ID holen (format: "category_key:form_index")
     */
    public function getFormById(string $formId, ?string $locale = null): ?array
    {
        $forms = $this->getAllForms($locale);

        foreach ($forms as $form) {
            if ($form['form_id'] === $formId) {
                return $form;
            }
        }

        return null;
    }
}
