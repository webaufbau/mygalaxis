<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailTemplateModel extends Model
{
    protected $table            = 'email_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'offer_type',
        'subtype',
        'language',
        'subject',
        'body_template',
        'field_display_template',
        'is_active',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'offer_type'    => 'required|max_length[50]',
        'language'      => 'required|max_length[5]',
        'subject'       => 'required|max_length[255]',
        'body_template' => 'required',
    ];

    protected $validationMessages = [
        'offer_type' => [
            'required' => 'Bitte wählen Sie einen Angebotstyp aus.',
        ],
        'subject' => [
            'required' => 'Bitte geben Sie einen Betreff ein.',
        ],
        'body_template' => [
            'required' => 'Bitte geben Sie einen Template-Inhalt ein.',
        ],
    ];

    /**
     * Get template for specific offer type, subtype and language
     * Falls back intelligently through: subtype-specific → type-generic → default
     *
     * @param string $offerType
     * @param string $language
     * @param string|null $subtype Optional subtype (e.g., 'umzug_privat', 'umzug_firma')
     * @return array|null
     */
    public function getTemplateForOffer(string $offerType, string $language = 'de', ?string $subtype = null): ?array
    {
        // Priority 1: Try to get template with matching offer_type AND subtype
        if ($subtype !== null) {
            $template = $this->where('offer_type', $offerType)
                             ->where('subtype', $subtype)
                             ->where('language', $language)
                             ->where('is_active', 1)
                             ->first();

            if ($template) {
                return $template;
            }
        }

        // Priority 2: Try to get generic template for this offer_type (subtype = NULL = applies to all)
        $template = $this->where('offer_type', $offerType)
                         ->where('subtype IS NULL')
                         ->where('language', $language)
                         ->where('is_active', 1)
                         ->first();

        if ($template) {
            return $template;
        }

        // Priority 3: Fallback to default template
        $template = $this->where('offer_type', 'default')
                         ->where('language', $language)
                         ->where('is_active', 1)
                         ->first();

        return $template;
    }

    /**
     * Get all available offer types from database
     *
     * @return array
     */
    public function getOfferTypes(): array
    {
        return $this->distinct()
                    ->select('offer_type')
                    ->orderBy('offer_type', 'ASC')
                    ->findColumn('offer_type');
    }

    /**
     * Get all templates grouped by offer type and language
     * 'default' type is always sorted first
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        $templates = $this->orderBy('offer_type', 'ASC')
                          ->orderBy('language', 'ASC')
                          ->findAll();

        $grouped = [];
        foreach ($templates as $template) {
            $type = $template['offer_type'];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $template;
        }

        // Sort so 'default' comes first
        if (isset($grouped['default'])) {
            $default = $grouped['default'];
            unset($grouped['default']);
            $grouped = ['default' => $default] + $grouped;
        }

        return $grouped;
    }
}
