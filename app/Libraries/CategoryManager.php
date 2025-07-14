<?php

namespace App\Libraries;

class CategoryManager
{
    protected array $types;
    protected string $path;

    public function __construct()
    {
        $config = config('CategoryOptions');
        $this->types = $config->categoryTypes;
        $this->path = $config->storagePath;
    }

    public function getAll(): array
    {
        $values = [];

        if (file_exists($this->path)) {
            $json = file_get_contents($this->path);
            $values = json_decode($json, true) ?? [];
        }

        // Ergänze fehlende Typen aus Config
        foreach ($this->types as $key => $defaultName) {
            if (!isset($values[$key])) {
                $values[$key] = [
                    'name'  => $defaultName,
                    'price' => 0.00,
                ];
            }
        }

        return $values;
    }

    public function save(array $data): bool
    {
        // Nur erlaubte Typen speichern
        $filtered = [];
        foreach ($this->types as $key => $defaultName) {
            if (isset($data[$key])) {
                $filtered[$key] = [
                    'name'  => $data[$key]['name'] ?? $defaultName,
                    'price' => floatval($data[$key]['price'] ?? 0),
                ];
            }
        }

        return file_put_contents($this->path, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}
