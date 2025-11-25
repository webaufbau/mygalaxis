<?php
namespace App\Libraries;

use Config\SiteConfig;

class SiteConfigLoader
{
    protected SiteConfig $baseConfig;
    protected string $jsonPath;
    protected array $values = [];

    public function __construct()
    {
        $this->baseConfig = new SiteConfig();
        $this->jsonPath = WRITEPATH . 'config/site_settings.json';
        $this->loadValues();
    }

    protected function loadValues(): void
    {
        // 1. Erst Default-Werte aus Config
        foreach ($this->baseConfig->fields as $key => $meta) {
            $this->values[$key] = $this->baseConfig->{$key} ?? ($meta['default'] ?? null);
        }

        // 2. JSON laden und mergen
        if (file_exists($this->jsonPath)) {
            $jsonData = json_decode(file_get_contents($this->jsonPath), true);
            if (is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $this->values[$key] = $value;
                }
            }
        }
    }

    /**
     * Magic Getter
     * Für mehrsprachige Felder gibt es arrays zurück (z.B. ['de' => 'Hallo', 'fr' => 'Bonjour'])
     */
    public function __get(string $name)
    {
        return $this->values[$name] ?? null;
    }

    public function getFields(): array
    {
        return $this->baseConfig->fields;
    }

    public function getFieldGroups(): array
    {
        return $this->baseConfig->fieldGroups ?? [];
    }

    public function get(string $key, ?string $locale = null)
    {
        $locale = $locale ?? service('request')->getLocale();
        $value = $this->values[$key] ?? null;

        if (is_array($value)) {
            return $value[$locale] ?? null;
        }

        return $value;
    }

    /**
     * Lädt SiteConfig für eine bestimmte Plattform
     * @param string|null $platform z.B. 'my_offertenheld_ch'
     * @return SiteConfigLoader
     */
    public static function loadForPlatform(?string $platform = null): self
    {
        if (empty($platform)) {
            // Fallback zur aktuellen Plattform
            return siteconfig();
        }

        // Erstelle neue Instanz mit custom JSON-Pfad
        $instance = new self();

        // Pfad zur anderen Plattform konstruieren
        // z.B. /var/www/my_offertenheld_ch/writable/config/site_settings.json
        $currentRoot = ROOTPATH; // z.B. /var/www/my_offertenschweiz_ch/
        $parentDir = dirname(rtrim($currentRoot, '/'));  // /var/www/
        $platformJsonPath = $parentDir . '/' . $platform . '/writable/config/site_settings.json';

        // Falls Pfad existiert, lade Settings
        if (file_exists($platformJsonPath)) {
            $instance->jsonPath = $platformJsonPath;
            $instance->loadValues();
        } else {
            // Fallback: Nutze aktuelle Config
            log_message('warning', "SiteConfig für Platform '{$platform}' nicht gefunden: {$platformJsonPath}. Nutze aktuelle Config.");
        }

        return $instance;
    }

    /**
     * Speichern: Berücksichtige Mehrsprachigkeit
     *
     * @param array $data Post-Daten aus Formular, evtl. mehrsprachige Felder als Array
     */
    public function save(array $data): bool
    {
        $filtered = [];
        foreach ($this->baseConfig->fields as $key => $meta) {
            if (!isset($data[$key])) {
                continue;
            }

            if (($meta['multilang'] ?? false) === true) {
                // Mehrsprachiges Feld: Array mit Sprachcodes erwartet
                if (is_array($data[$key])) {
                    // Für jede Sprache trimmen (nur Strings)
                    $langs = [];
                    foreach ($data[$key] as $lang => $val) {
                        $langs[$lang] = is_string($val) ? trim($val) : $val;
                    }
                    $filtered[$key] = $langs;
                }
            } else {
                // Checkbox, Number, sonst String
                if ($meta['type'] === 'checkbox') {
                    $filtered[$key] = (bool)$data[$key];
                } elseif ($meta['type'] === 'number') {
                    $filtered[$key] = (float)$data[$key];
                } else {
                    $filtered[$key] = is_string($data[$key]) ? trim($data[$key]) : $data[$key];
                }
            }
        }

        $dir = dirname($this->jsonPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $success = file_put_contents($this->jsonPath, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;

        if ($success) {
            // Neu laden, damit $values aktuell sind
            $this->values = $filtered;
        }

        return $success;
    }
}
