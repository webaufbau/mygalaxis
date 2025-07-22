<?php

namespace App\Models;

class CampaignModel extends \App\Models\BaseModel
{
    protected $table            = 'campaigns';
    protected $primaryKey       = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = '\App\Entities\Campaign';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'company_name',
        'company_email',
        'company_contact_person',
        'company_address',
        'company_zip',
        'company_city',
        'company_canton',
        'company_phone',
        'company_website',
        'company_industry',
        'company_categories',
        'company_languages',
        'company_notes',

        'subject',
        'message',
        'status',
        'sent_at',
        'response_at',

        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    public function getTotalEntries()
    {
        return $this->countAllResults(false);
    }

    public function getEntries($limit = 100, $offset = 0)
    {
        return $this->orderBy($this->primaryKey, 'DESC')->findAll($limit, $offset);
    }

    public function getTableHeader(): array
    {
        return [
            'ID',
            'Firma',
            'Betreff',
            'Status',
            'Gesendet am',
            'Antwort am',
        ];
    }

    public function getTableFields($entity): array
    {
        return [
            $entity->id,
            esc($entity->company_name ?? '-'),
            esc($entity->subject ?? '-'),
            lang('Campaign.status.'.$entity->status ?? '-'),
            strtotime($entity->sent_at)>0 ? date('d.m.Y H:i', strtotime($entity->sent_at)) : '-',
            strtotime($entity->response_at)>0 ? date('d.m.Y H:i', strtotime($entity->response_at)) : '-',
        ];
    }

    public function getFormConfiguration($entity = null, $request = null)
    {
        if (!$entity) {
            $entity = $this->getEntity();
        }
        if (!$request) {
            $request = service('request');
        }

        return [
            'tabs' => [
                'general' => 'Kampagne',
                'company' => 'Firma',
            ],
            'fields' => [
                'company' => [
                    'company_name' => [
                        'type' => 'text',
                        'label' => 'Firmenname',
                        'value' => $entity->company_name ?? '',
                        'required' => true,
                    ],
                    'company_email' => [
                        'type' => 'email',
                        'label' => 'E-Mail Firma',
                        'value' => $entity->company_email ?? '',
                    ],
                    'company_contact_person' => [
                        'type' => 'text',
                        'label' => 'Ansprechperson',
                        'value' => $entity->company_contact_person ?? '',
                    ],
                    'company_address' => [
                        'type' => 'text',
                        'label' => 'Adresse',
                        'value' => $entity->company_address ?? '',
                    ],
                    'company_zip' => [
                        'type' => 'text',
                        'label' => 'PLZ',
                        'value' => $entity->company_zip ?? '',
                    ],
                    'company_city' => [
                        'type' => 'text',
                        'label' => 'Ort',
                        'value' => $entity->company_city ?? '',
                    ],
                    'company_canton' => [
                        'type' => 'text',
                        'label' => 'Kanton',
                        'value' => $entity->company_canton ?? '',
                    ],
                    'company_phone' => [
                        'type' => 'text',
                        'label' => 'Telefon',
                        'value' => $entity->company_phone ?? '',
                    ],
                    'company_website' => [
                        'type' => 'url',
                        'label' => 'Website',
                        'value' => $entity->company_website ?? '',
                    ],
                    'company_industry' => [
                        'type' => 'text',
                        'label' => 'Branche',
                        'value' => $entity->company_industry ?? '',
                    ],
                    'company_categories' => [
                        'type' => 'text',
                        'label' => 'Kategorien',
                        'value' => $entity->company_categories ?? '',
                        'description' => 'Kommagetrennt',
                    ],
                    'company_languages' => [
                        'type' => 'text',
                        'label' => 'Sprachen',
                        'value' => $entity->company_languages ?? '',
                        'description' => 'Kommagetrennt (z.B. de,en)',
                    ],
                    'company_notes' => [
                        'type' => 'textarea',
                        'label' => 'Notizen',
                        'value' => $entity->company_notes ?? '',
                        'rows' => 3,
                    ],
                ],
                'general' => [
                    'subject' => [
                        'type' => 'text',
                        'label' => 'Betreff',
                        'value' => $entity->subject ?? '',
                    ],
                    'message' => [
                        'type' => 'textarea',
                        'label' => 'Nachricht',
                        'value' => $entity->message ?? '',
                        'rows' => 6,
                    ],
                    'status' => [
                        'type' => 'dropdown',
                        'label' => 'Status',
                        'options' => [
                            'pending' => 'Offen',
                            'sent' => 'Gesendet',
                            'responded' => 'Geantwortet',
                            'error' => 'Fehler',
                        ],
                        'value' => $entity->status ?? 'pending',
                    ],
                    'sent_at' => [
                        'type' => 'message',
                        'label' => 'Gesendet am',
                        'message' => $entity->sent_at ?? '-',
                    ],
                    'response_at' => [
                        'type' => 'message',
                        'label' => 'Antwort am',
                        'message' => $entity->response_at ?? '-',
                    ],
                ],
            ],
            'config' => [
                'first_tab' => 'company',
                'translation' => false,
                'row_fields' => [
                    'company' => [
                        ['company_name', 'company_email'],
                        ['company_contact_person', 'company_phone'],
                        ['company_address', 'company_zip'],
                        ['company_city', 'company_canton'],
                        ['company_website', 'company_industry'],
                        ['company_categories', 'company_languages'],
                        ['company_notes'],
                    ],
                    'general' => [
                        ['subject'],
                        ['message'],
                        ['status'],
                        ['sent_at', 'response_at'],
                    ],
                ],
            ],
        ];
    }

    public function getFilterConfiguration($entity = null, $request = null): array
    {
        if (!$entity) {
            $entity = $this->getEntity();
        }
        if (!$request) {
            $request = service('request');
        }

        return [
            'fields' => [
                'query' => [
                    'type' => 'search',
                    'name' => 'query',
                    'like' => [
                        'subject' => '%value%',
                        'message' => '%value%',
                        'company_name' => '%value%',
                        'status' => '%value%',
                    ],
                    'operator' => 'OR',
                    'onchange' => 'this.form.submit()',
                ],
                'status' => [
                    'type' => 'dropdown',
                    'options' => [
                        '' => 'Alle',
                        'pending' => 'Offen',
                        'sent' => 'Gesendet',
                        'responded' => 'Geantwortet',
                        'error' => 'Fehler',
                    ],
                    'name' => 'status',
                    'where' => '(status = \'%value%\')',
                    'onchange' => 'this.form.submit()',
                ],
            ],
        ];
    }
}
