<?php

namespace App\Services;

use DateTime;

/**
 * Email Template Parser
 *
 * Parses email templates with shortcodes and replaces them with actual data
 *
 * Available Shortcodes:
 * - {field:fieldname} - Display field value
 * - {field:fieldname|date:d.m.Y} - Display field with date formatting
 * - {site_name} - Site name from config
 * - {site_url} - Site URL
 * - [if field:fieldname]...[/if] - Conditional block
 * - [if field:fieldname > value]...[/if] - Conditional with comparison
 * - [show_all exclude="field1,field2"] - Show all fields except excluded ones
 * - [show_field name="fieldname" label="Custom Label"] - Show single field with custom label
 */
class EmailTemplateParser
{
    protected array $data = [];
    protected array $excludedFields = [];
    protected $siteConfig;
    protected array $labels = [];
    protected ?\App\Services\FieldRenderer $fieldRenderer = null;

    public function __construct()
    {
        $this->siteConfig = siteconfig();
        $this->labels = lang('Offers.labels');
        $this->fieldRenderer = new \App\Services\FieldRenderer();
    }

    /**
     * Parse template with data
     *
     * @param string $template
     * @param array $data Form field data
     * @param array $excludedFields Fields to exclude from display
     * @return string Parsed HTML
     */
    public function parse(string $template, array $data, array $excludedFields = []): string
    {
        $this->data = $data;
        $this->excludedFields = $excludedFields;

        // Parse conditional blocks first
        $template = $this->parseConditionals($template);

        // Parse shortcodes
        $template = $this->parseShowAll($template);
        $template = $this->parseShowField($template);
        $template = $this->parseFieldShortcodes($template);
        $template = $this->parseSiteShortcodes($template);

        return $template;
    }

    /**
     * Parse conditional blocks [if ...]...[/if]
     * Supports nested conditionals by processing from innermost to outermost
     */
    protected function parseConditionals(string $template): string
    {
        // Process conditionals iteratively until no more matches
        $maxIterations = 10; // Prevent infinite loops
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;

            // Pattern: [if field:fieldname] or [if field:fieldname > value]
            // Match innermost [if] blocks (those without nested [if] inside)
            $pattern = '/\[if\s+field:([a-zA-Z0-9_-]+)(?:\s*(>|<|>=|<=|==|!=)\s*([^\]]+))?\]((?:(?!\[if\s+field:).)*?)\[\/if\]/s';

            $replaced = preg_replace_callback($pattern, function ($matches) {
                $fieldName = $matches[1];
                $operator = $matches[2] ?? null;
                $compareValue = isset($matches[3]) ? trim($matches[3]) : null;
                $content = $matches[4];

                $fieldValue = $this->getFieldValue($fieldName);

                // Simple existence check
                if (!$operator) {
                    // Field exists and is not empty/nein/false
                    if ($this->isFieldTruthy($fieldValue)) {
                        return $content;
                    }
                    return '';
                }

                // Comparison check
                if ($this->compareValues($fieldValue, $operator, $compareValue)) {
                    return $content;
                }

                return '';
            }, $template);

            // If nothing was replaced, we're done
            if ($replaced === $template) {
                break;
            }

            $template = $replaced;
        }

        return $template;
    }

    /**
     * Parse [show_all exclude="field1,field2"]
     */
    protected function parseShowAll(string $template): string
    {
        $pattern = '/\[show_all(?:\s+exclude="([^"]*)")?\]/';

        return preg_replace_callback($pattern, function ($matches) {
            $additionalExcludes = isset($matches[1]) ? explode(',', $matches[1]) : [];
            $additionalExcludes = array_map('trim', $additionalExcludes);

            $allExcludes = array_merge($this->excludedFields, $additionalExcludes);

            return $this->generateFieldList($allExcludes);
        }, $template);
    }

    /**
     * Parse [show_field name="fieldname" label="Custom Label"]
     */
    protected function parseShowField(string $template): string
    {
        $pattern = '/\[show_field\s+name="([^"]+)"(?:\s+label="([^"]*)")?\]/';

        return preg_replace_callback($pattern, function ($matches) {
            $fieldName = $matches[1];
            $customLabel = $matches[2] ?? null;

            $value = $this->getFieldValue($fieldName);

            if (!$this->isFieldTruthy($value)) {
                return '';
            }

            $label = $customLabel ?? ($this->labels[$fieldName] ?? ucwords(str_replace(['_', '-'], ' ', $fieldName)));
            $displayValue = $this->formatValue($value);

            return '<li><strong>' . esc($label) . ':</strong> ' . esc($displayValue) . '</li>';
        }, $template);
    }

    /**
     * Parse {field:fieldname} and {field:fieldname|date:format}
     */
    protected function parseFieldShortcodes(string $template): string
    {
        $pattern = '/\{field:([a-zA-Z0-9_-]+)(?:\|([a-z]+):([^\}]+))?\}/';

        return preg_replace_callback($pattern, function ($matches) {
            $fieldName = $matches[1];
            $filter = $matches[2] ?? null;
            $filterParam = $matches[3] ?? null;

            $value = $this->getFieldValue($fieldName);

            if ($value === null || $value === '') {
                return '';
            }

            // Apply filter
            if ($filter === 'date' && $filterParam) {
                return $this->formatDate($value, $filterParam);
            }

            return esc($value);
        }, $template);
    }

    /**
     * Parse {site_name}, {site_url} etc.
     */
    protected function parseSiteShortcodes(string $template): string
    {
        $replacements = [
            '{site_name}' => $this->siteConfig->name ?? '',
            '{site_url}'  => $this->siteConfig->url ?? base_url(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Generate field list HTML using FieldRenderer
     */
    protected function generateFieldList(array $excludes): string
    {
        $html = '';

        // Normalisiere Ausschlussfelder
        $normalizedExcludes = array_map(function($key) {
            return str_replace([' ', '-'], '_', strtolower($key));
        }, $excludes);

        // Merge mit bereits gesetzten Ausschlussfeldern
        $allExcludes = array_merge($this->excludedFields, $normalizedExcludes);

        // Verwende FieldRenderer für intelligente Felddarstellung
        $this->fieldRenderer->setData($this->data)
                           ->setExcludedFields($allExcludes);

        $renderedFields = $this->fieldRenderer->renderFields('email');

        // Generiere HTML für Email
        foreach ($renderedFields as $field) {
            if ($this->fieldRenderer->isFileUploadField($field['key'])) {
                $html .= '<li><strong>' . esc($field['label']) . ':</strong> ' . $this->fieldRenderer->formatFileUpload($field['value']) . '</li>';
            } else {
                $html .= '<li><strong>' . esc($field['label']) . ':</strong> ' . esc($field['display']) . '</li>';
            }
        }

        return $html;
    }

    /**
     * Get field value from data
     */
    protected function getFieldValue(string $fieldName)
    {
        return $this->data[$fieldName] ?? null;
    }

    /**
     * Check if field value is "truthy" (not empty, not "nein", not false)
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
     * Compare values with operator
     */
    protected function compareValues($fieldValue, string $operator, $compareValue): bool
    {
        // Try to convert to numbers if possible
        if (is_numeric($fieldValue) && is_numeric($compareValue)) {
            $fieldValue = (float) $fieldValue;
            $compareValue = (float) $compareValue;
        }

        switch ($operator) {
            case '>':
                return $fieldValue > $compareValue;
            case '<':
                return $fieldValue < $compareValue;
            case '>=':
                return $fieldValue >= $compareValue;
            case '<=':
                return $fieldValue <= $compareValue;
            case '==':
                return $fieldValue == $compareValue;
            case '!=':
                return $fieldValue != $compareValue;
            default:
                return false;
        }
    }

    /**
     * Format value for display
     */
    protected function formatValue($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map('esc', $value));
        }

        if (is_string($value)) {
            // Try to decode JSON
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $filtered = array_filter($decoded, fn($v) => !in_array(strtolower((string)$v), ['nein', '', null], true));
                return implode(', ', array_map('esc', $filtered));
            }

            // Auto-detect and format dates
            return $this->autoFormatDate($value);
        }

        return (string) $value;
    }

    /**
     * Auto-detect and format dates
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
     * Format date with custom format
     */
    protected function formatDate(string $value, string $format): string
    {
        // Try dd/mm/YYYY first
        $timestamp = DateTime::createFromFormat('d/m/Y', $value);
        if (!$timestamp) {
            // Try YYYY-mm-dd
            $timestamp = DateTime::createFromFormat('Y-m-d', $value);
        }

        if ($timestamp) {
            return $timestamp->format($format);
        }

        return $value;
    }

}
