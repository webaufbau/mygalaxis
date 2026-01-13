<?php

namespace App\Services;

use DateTime;

/**
 * Field Renderer Service
 *
 * Zentrale Logik für das Rendern von Formular-Feldern mit Bedingungen.
 * Wird sowohl für Email-Templates als auch für Firmen-Ansichten verwendet.
 *
 * Features:
 * - Bedingte Feldgruppen (conditional groups)
 * - Automatische Datumsformatierung
 * - JSON-Array-Handling
 * - File-Upload-Anzeige
 * - Bild-URLs für erklärende Bilder
 */
class FieldRenderer
{
    protected array $data = [];
    protected array $labels = [];
    protected array $fieldDisplayRules = [];
    protected array $excludedFields = [];
    protected array $fieldsWithImages = [];
    protected string $imageBaseUrl = '';

    // Felder die IMMER ausgeschlossen werden (technische/interne Felder)
    protected array $alwaysExcludedFields = [
        'edit_token',
        'skip_kontakt',
        'session',
        'request_session_id',
        '_fluentform_',
        '__fluent_form_embded_post_id',
    ];

    public function __construct()
    {
        $this->labels = lang('Offers.labels');

        $fieldConfig = new \Config\FormFieldOptions();
        $this->fieldsWithImages = $fieldConfig->fieldsWithImages;
        $this->imageBaseUrl = $fieldConfig->imageBaseUrl;

        // Lade Display Rules
        $this->loadDisplayRules();
    }

    /**
     * Setze die Daten für das Rendering
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Setze ausgeschlossene Felder
     */
    public function setExcludedFields(array $excludedFields): self
    {
        $this->excludedFields = $excludedFields;
        return $this;
    }

    /**
     * Lade Display Rules aus Datenbank (mit Fallback auf Config)
     *
     * @param string $offerType Offer-Type für spezifische Rules (default, gartenbau, umzug, etc.)
     */
    protected function loadDisplayRules(string $offerType = 'default'): void
    {
        try {
            // Versuche zuerst aus Datenbank zu laden
            $model = new \App\Models\FieldDisplayRuleModel();
            $dbRules = $model->getRulesForRenderer($offerType);

            if (!empty($dbRules)) {
                $this->fieldDisplayRules = $dbRules;
                return;
            }

            // Fallback: Lade aus Config (für Abwärtskompatibilität)
            $config = config('FieldDisplayRules');
            if ($config && method_exists($config, 'getRules')) {
                $this->fieldDisplayRules = $config->getRules();
                return;
            }

            // Wenn weder DB noch Config verfügbar, arbeite ohne Rules
            $this->fieldDisplayRules = [];
        } catch (\Exception $e) {
            // Bei Fehlern (z.B. Tabelle existiert nicht), versuche Config-Fallback
            try {
                $config = config('FieldDisplayRules');
                if ($config && method_exists($config, 'getRules')) {
                    $this->fieldDisplayRules = $config->getRules();
                } else {
                    $this->fieldDisplayRules = [];
                }
            } catch (\Exception $innerException) {
                log_message('warning', 'FieldDisplayRules konnte nicht geladen werden: ' . $e->getMessage());
                $this->fieldDisplayRules = [];
            }
        }
    }

    /**
     * Setze den Offer-Type und lade entsprechende Rules
     */
    public function setOfferType(string $offerType): self
    {
        $this->loadDisplayRules($offerType);
        return $this;
    }

    /**
     * Rendere alle Felder mit Berücksichtigung der Display Rules
     *
     * @param string $format 'html' oder 'email'
     * @return array Bereinigte Felder mit Labels und Werten
     */
    public function renderFields(string $format = 'html'): array
    {
        $renderedFields = [];
        $processedFields = [];

        // Zuerst: Verarbeite conditional groups (falls vorhanden)
        // Wenn keine Rules definiert sind, wird dieser Block übersprungen
        foreach ($this->fieldDisplayRules as $groupName => $rule) {
            if ($rule['type'] === 'conditional_group') {
                $result = $this->processConditionalGroup($rule);

                if ($result !== null) {
                    $renderedFields[] = [
                        'key' => $groupName,
                        'label' => $rule['label'],
                        'value' => $result['value'],
                        'display' => $result['display'],
                        'image' => $result['image'] ?? null,
                    ];

                    // Markiere alle Felder dieser Gruppe als verarbeitet
                    foreach ($rule['fields_to_hide'] as $field) {
                        $processedFields[] = $field;
                    }
                }
            }
        }

        // Dann: Verarbeite normale Felder
        foreach ($this->data as $key => $value) {
            // Skip, wenn bereits verarbeitet
            if (in_array($key, $processedFields)) {
                continue;
            }

            // Skip, wenn ausgeschlossen
            $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
            if (in_array($normalizedKey, $this->excludedFields)) {
                continue;
            }

            // Skip, wenn in alwaysExcludedFields (technische/interne Felder)
            $shouldSkip = false;
            foreach ($this->alwaysExcludedFields as $excludePattern) {
                if ($normalizedKey === $excludePattern || str_contains($normalizedKey, $excludePattern)) {
                    $shouldSkip = true;
                    break;
                }
            }
            if ($shouldSkip) {
                continue;
            }

            // Skip, wenn "nein", false, null, leer
            if (!$this->isFieldTruthy($value)) {
                continue;
            }

            // Label
            $label = $this->labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

            // Display-Wert formatieren
            $displayValue = $this->formatValue($value);

            // Bild-URL wenn vorhanden
            $imageUrl = in_array($key, $this->fieldsWithImages)
                ? $this->imageBaseUrl . $key . '.jpg'
                : null;

            $renderedFields[] = [
                'key' => $key,
                'label' => $label,
                'value' => $value,
                'display' => $displayValue,
                'image' => $imageUrl,
            ];
        }

        return $renderedFields;
    }

    /**
     * Verarbeite eine conditional group
     */
    protected function processConditionalGroup(array $rule): ?array
    {
        // Prüfe alle Bedingungen
        foreach ($rule['conditions'] as $condition) {
            $matched = true;

            // Prüfe ob alle "when"-Bedingungen erfüllt sind
            foreach ($condition['when'] as $field => $expectedValue) {
                if (!isset($this->data[$field]) || $this->data[$field] !== $expectedValue) {
                    $matched = false;
                    break;
                }
            }

            // Wenn Bedingung erfüllt, rendere den Display-Text
            if ($matched) {
                $display = $this->renderDisplayTemplate($condition['display']);

                if ($display !== null && $display !== '') {
                    return [
                        'value' => $display,
                        'display' => $display,
                        'image' => $condition['image'] ?? null,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Rendere Display-Template mit Platzhaltern
     * z.B. "{bodenplatten_vorplatz_flaeche_ja} m²"
     */
    protected function renderDisplayTemplate(string $template): ?string
    {
        // Ersetze Platzhalter wie {feldname}
        $result = preg_replace_callback('/\{([a-zA-Z0-9_-]+)\}/', function($matches) {
            $fieldName = $matches[1];
            $value = $this->data[$fieldName] ?? null;

            if (!$this->isFieldTruthy($value)) {
                return null;
            }

            return $this->formatValue($value);
        }, $template);

        // Wenn null zurückkommt (weil Feld leer), ganze Gruppe nicht anzeigen
        if ($result === null || trim($result) === '') {
            return null;
        }

        return $result;
    }

    /**
     * Prüfe ob Feldwert "truthy" ist
     */
    protected function isFieldTruthy($value): bool
    {
        if ($value === null || $value === '' || $value === false) {
            return false;
        }

        $cleanValue = is_string($value) ? trim(strtolower($value)) : $value;

        return $cleanValue !== 'nein';
    }

    /**
     * Formatiere Wert für Anzeige
     */
    protected function formatValue($value): string
    {
        if (is_array($value)) {
            // Übersetze jeden Wert im Array
            $translated = array_map(fn($v) => $this->translateValue($v), $value);
            return implode(', ', array_map('esc', $translated));
        }

        if (is_string($value)) {
            // Versuche JSON zu dekodieren
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $filtered = array_filter($decoded, fn($v) => !in_array(strtolower((string)$v), ['nein', '', null], true));
                $translated = array_map(fn($v) => $this->translateValue($v), $filtered);
                return implode(', ', array_map('esc', $translated));
            }

            // Übersetze bekannte Schlüsselwerte
            $translated = $this->translateValue($value);

            // Auto-Datumsformatierung
            return $this->autoFormatDate($translated);
        }

        return (string) $value;
    }

    /**
     * Übersetze bekannte Schlüsselwerte in lesbare Texte
     */
    protected function translateValue(string $value): string
    {
        // Mapping von Schlüssel zu lesbarem Text
        static $valueTranslations = [
            // Zeit-Flexibilität
            'no' => 'Nein',
            '1_2_days' => '1 - 2 Tage',
            '1_2_weeks' => '1 - 2 Wochen',
            '1_month' => 'ca. 1 Monat',
            'by_arrangement' => 'Nach Absprache',

            // Erreichbarkeit
            '8_12' => '08:00 - 12:00 Uhr',
            '12_14' => '12:00 - 14:00 Uhr',
            '14_18' => '14:00 - 18:00 Uhr',
            '18_20' => '18:00 - 20:00 Uhr',
            'anytime' => 'Jederzeit',

            // Auftraggeber-Typ
            'owner' => 'Eigentümer',
            'tenant' => 'Mieter',
            'privat' => 'Privat',
            'firma' => 'Firma',
            'private' => 'Privat',
            'business' => 'Geschäftlich',

            // Allgemeine Werte
            'yes' => 'Ja',
            'ja' => 'Ja',
            'nein' => 'Nein',
        ];

        return $valueTranslations[$value] ?? $value;
    }

    /**
     * Auto-detect und formatiere Datum
     */
    protected function autoFormatDate(string $value): string
    {
        // Detect dd/mm/YYYY format
        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $value)) {
            $timestamp = DateTime::createFromFormat('d/m/Y', $value);
            if ($timestamp) {
                return $timestamp->format('d.m.Y');
            }
        }

        // Detect YYYY-mm-dd format
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
            $timestamp = DateTime::createFromFormat('Y-m-d', $value);
            if ($timestamp) {
                return $timestamp->format('d.m.Y');
            }
        }

        return $value;
    }

    /**
     * Prüfe ob ein Feld ein File-Upload ist
     */
    public function isFileUploadField(string $key): bool
    {
        return in_array($key, ['file-upload', 'file_upload', 'upload_file']);
    }

    /**
     * Formatiere File-Upload für HTML-Anzeige
     */
    public function formatFileUpload($value, string $context = 'html'): string
    {
        // Handle comma-separated URLs (from Fluent Forms or other sources)
        if (is_string($value) && strpos($value, ',') !== false) {
            $urls = array_map('trim', explode(',', $value));
        } else {
            $urls = is_array($value) ? $value : [$value];
        }

        $html = '';

        foreach ($urls as $url) {
            $url = trim($url);

            if (empty($url)) {
                continue;
            }

            if (is_string($url) && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $url)) {
                // Für E-Mails: kleinere Bilder (max 400px breit)
                if ($context === 'email') {
                    $html .= '<img src="' . esc($url) . '" alt="Upload" style="max-width: 400px; height: auto; border:1px solid #ccc; padding: 5px; margin: 5px 0; display: block;">';
                } else {
                    // Für HTML-Ansicht: volle Breite
                    $html .= '<img src="' . esc($url) . '" alt="Upload" style="max-width: 100%; height: auto; border:1px solid #ccc; padding: 5px; margin: 5px 0;">';
                }
            } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
                $html .= '<a href="' . esc($url) . '" target="_blank">' . esc(basename($url)) . '</a><br>';
            } else {
                $html .= esc($url);
            }
        }

        return $html;
    }

    /**
     * Hole einen einzelnen Feldwert
     */
    public function getFieldValue(string $fieldName)
    {
        return $this->data[$fieldName] ?? null;
    }
}
