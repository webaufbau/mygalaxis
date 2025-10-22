<?php

namespace App\Models;

use CodeIgniter\Model;

class FieldDisplayRuleModel extends Model
{
    protected $table = 'field_display_rules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'rule_key',
        'offer_type',
        'label',
        'conditions',
        'fields_to_hide',
        'is_active',
        'sort_order',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'rule_key' => 'required|max_length[100]',
        'offer_type' => 'required|max_length[50]',
        'label' => 'required|max_length[255]',
        'conditions' => 'required',
        'fields_to_hide' => 'required',
    ];

    protected $validationMessages = [
        'rule_key' => [
            'required' => 'Der Regel-Schlüssel ist erforderlich',
            'max_length' => 'Der Regel-Schlüssel darf maximal 100 Zeichen lang sein',
        ],
        'offer_type' => [
            'required' => 'Der Offer-Type ist erforderlich',
        ],
        'label' => [
            'required' => 'Das Label ist erforderlich',
        ],
        'conditions' => [
            'required' => 'Mindestens eine Bedingung ist erforderlich',
        ],
        'fields_to_hide' => [
            'required' => 'Mindestens ein Feld muss versteckt werden',
        ],
    ];

    // Callbacks
    protected $beforeInsert = ['jsonEncodeFields'];
    protected $beforeUpdate = ['jsonEncodeFields'];
    protected $afterFind = ['jsonDecodeFields'];

    /**
     * JSON-Encode für conditions und fields_to_hide vor dem Speichern
     */
    protected function jsonEncodeFields(array $data): array
    {
        if (isset($data['data']['conditions']) && is_array($data['data']['conditions'])) {
            $data['data']['conditions'] = json_encode($data['data']['conditions']);
        }

        if (isset($data['data']['fields_to_hide']) && is_array($data['data']['fields_to_hide'])) {
            $data['data']['fields_to_hide'] = json_encode($data['data']['fields_to_hide']);
        }

        return $data;
    }

    /**
     * JSON-Decode für conditions und fields_to_hide nach dem Laden
     */
    protected function jsonDecodeFields(array $data): array
    {
        if (isset($data['data'])) {
            // Single result
            if (isset($data['data']['conditions']) && is_string($data['data']['conditions'])) {
                $data['data']['conditions'] = json_decode($data['data']['conditions'], true);
            }

            if (isset($data['data']['fields_to_hide']) && is_string($data['data']['fields_to_hide'])) {
                $data['data']['fields_to_hide'] = json_decode($data['data']['fields_to_hide'], true);
            }
        } elseif (isset($data['singleton'])) {
            // Multiple results
            foreach ($data['data'] as &$row) {
                if (isset($row['conditions']) && is_string($row['conditions'])) {
                    $row['conditions'] = json_decode($row['conditions'], true);
                }

                if (isset($row['fields_to_hide']) && is_string($row['fields_to_hide'])) {
                    $row['fields_to_hide'] = json_decode($row['fields_to_hide'], true);
                }
            }
        }

        return $data;
    }

    /**
     * Hole alle aktiven Rules für einen bestimmten Offer-Type
     */
    public function getActiveRulesByOfferType(string $offerType = 'default'): array
    {
        return $this->where('offer_type', $offerType)
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Hole alle Rules (für Admin-Liste)
     */
    public function getAllRulesWithPagination(int $perPage = 20): array
    {
        return $this->orderBy('offer_type', 'ASC')
                    ->orderBy('sort_order', 'ASC')
                    ->paginate($perPage);
    }

    /**
     * Prüfe ob eine Rule mit diesem Key bereits existiert
     */
    public function ruleExists(string $ruleKey, string $offerType, ?int $excludeId = null): bool
    {
        $builder = $this->where('rule_key', $ruleKey)
                        ->where('offer_type', $offerType);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Konvertiere Rules ins FieldRenderer-Format
     */
    public function getRulesForRenderer(string $offerType = 'default'): array
    {
        $rules = $this->getActiveRulesByOfferType($offerType);
        $formatted = [];

        foreach ($rules as $rule) {
            $formatted[$rule['rule_key']] = [
                'type' => 'conditional_group',
                'label' => $rule['label'],
                'conditions' => $rule['conditions'],
                'fields_to_hide' => $rule['fields_to_hide'],
            ];
        }

        return $formatted;
    }
}
