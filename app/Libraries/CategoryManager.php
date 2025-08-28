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

            $existingOptions = $values[$key]['options'] ?? [];

            $options = [];
            foreach ($labels as $idx => $label) {
                $price = $existingOptions[$idx]['price'] ?? 0;
                $options[] = [
                    'label' => $label,
                    'price' => $price
                ];
            }

            $values[$key] = [
                'name' => $values[$key]['name'] ?? $defaultName,
                'options' => $options
            ];
        }

        return $values;
    }

    public function save(array $data): bool
    {
        $filtered = [];

        foreach ($this->types as $key => $defaultName) {
            if (isset($data[$key])) {
                $options = [];
                foreach ($data[$key]['options'] as $opt) {
                    $options[] = [
                        'label' => $opt['label'], // Label fix aus Config
                        'price' => floatval($opt['price'] ?? 0)
                    ];
                }

                $filtered[$key] = [
                    'name' => $data[$key]['name'] ?? $defaultName,
                    'options' => $options
                ];
            }
        }

        return file_put_contents($this->path, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}
