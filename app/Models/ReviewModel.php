<?php

namespace App\Models;

class ReviewModel extends BaseModel
{
    protected $table            = 'reviews';
    protected $primaryKey       = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Review::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'offer_id',
        'recipient_id',
        'recipient_name',
        'hash',
        'reviewer_firstname',
        'reviewer_lastname',
        'rating',
        'comment',
        'created_at',
    ];

    protected $useTimestamps = true; // oder true, falls du `updated_at` etc. einbaust
    protected $dateFormat = 'datetime';

    protected $dates = ['created_at'];

    protected $beforeInsert = ['setRecipientName'];
    protected $beforeUpdate = ['setRecipientName'];

    protected function setRecipientName(array $data)
    {
        if (isset($data['data']['recipient_id']) && $data['data']['recipient_id']) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($data['data']['recipient_id']);

            if ($user) {
                // Falls Firmenname und Ansprechperson gesetzt sind
                $nameParts = [];
                if (!empty($user->company_name)) {
                    $nameParts[] = $user->company_name;
                }
                if (!empty($user->contact_person)) {
                    $nameParts[] = $user->contact_person;
                }

                // Name zusammenfügen, z.B. "Firma - Ansprechperson"
                $data['data']['recipient_name'] = implode(' - ', $nameParts);
            } else {
                // Fallback, falls User nicht gefunden wird
                $data['data']['recipient_name'] = 'Unbekannt';
            }
        }

        return $data;
    }

    public function getTotalEntries() {
        $userModel = new \App\Models\UserModel();
        $query_elements = $this;
        $count_all_results = $query_elements->countAllResults(false);

        return $count_all_results;
    }

    public function getEntries($limit=100, $offset=0)
    {
        $userModel = new \App\Models\UserModel();
        $query_elements = $this;
        $query_elements->orderBy($this->getPrimaryKeyField(), 'DESC');
        $query_elements = $query_elements->findAll($limit, $offset);

        return $query_elements;
    }

    public function getTableHeader()
    {
        return [
            'ID',
            'Angebot ID',
            'Firma bewertet',
            'Bewertet von',
            'Bewertung',
            'Kommentar',
            'Erstellt am',
        ];
    }

    public function getTableFields($entity)
    {
        return [
            $entity->id,
            $entity->offer_id,
            $entity->recipient_name,
            esc($entity->reviewer_firstname ?? '-') . esc($entity->reviewer_lastname ?? '-'),
            $entity->rating ?? '-',
            esc($entity->comment ?? '-'),
            $entity->created_at ? date('d.m.Y', strtotime($entity->created_at)) : '-',
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

        $form_data = [
            'tabs' => [
                'general' => 'Bewertung',
            ],
            'fields' => [
                'general' => [
                    'id' => [
                        'type' => 'hidden',
                    ],
                    'offer_id' => [
                        'type' => 'hidden',
                    ],
                    'offer_details' => [
                        'type' => 'message',
                        'message' => 'Bewertung für ' . anchor('admin/offer/' . $entity->offer_id, 'Offerte Details', 'target="_blank"'),
                    ],
                    'rating' => [
                        'type' => 'dropdown',
                        'label' => 'Bewertung',
                        'options' => [
                            1 => '1 Stern',
                            2 => '2 Sterne',
                            3 => '3 Sterne',
                            4 => '4 Sterne',
                            5 => '5 Sterne',
                        ],
                        'required' => 'required',
                        'value' => $entity->rating ?? 5,
                    ],
                    'comment' => [
                        'type' => 'textarea',
                        'label' => 'Kommentar',
                        'value' => $entity->comment ?? '',
                    ],
                    'reviewer_firstname' => [
                        'type' => 'text',
                        'label' => 'Vorname Bewerter',
                        'value' => $entity->reviewer_firstname ?? '',
                    ],
                    'reviewer_lastname' => [
                        'type' => 'text',
                        'label' => 'Nachname Bewerter',
                        'value' => $entity->reviewer_lastname ?? '',
                    ],
                    'recipient_id' => [
                        'type' => 'dropdown_db',
                        'json_url' => 'admin/user/json',
                        'results_on_query' => true,
                        'label' => 'Bewertete Firma',
                        'required' => 'required',
                        'display_field' => 'user_fullname_email',
                        'selected_text' => $entity->recipient_name ?? 'Auswahl',
                    ],
                    'recipient_name' => [
                        'type' => 'text',
                        'label' => 'Name Bewertete Firma',
                        'value' => $entity->recipient_name ?? '',
                        'readonly' => true, // wird automatisch gesetzt
                    ],
                ],
            ],
            'config' => [
                'first_tab' => 'general',
                'translation' => false,
                'row_fields' => [
                    'general' => [
                        ['offer_details'],
                        ['rating', 'comment'],
                        ['reviewer_firstname', 'reviewer_lastname'],
                        ['recipient_id', 'recipient_name'],
                    ],
                ],
            ],
        ];

        return $form_data;
    }



}
