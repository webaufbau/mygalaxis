<?php

namespace App\Models;

class SubscriptiontypeModel extends BaseModel {
    protected $DBGroup = 'default';
    protected $table = 'subscription_types';
    protected $title = 'Abotyp';
    protected $primaryKey = 'subscription_type_id';
    protected $useAutoIncrement = true;
    protected $insertID = 0;
    protected $returnType = 'App\Entities\Subscriptiontype';
    protected $useSoftDeletes = 1;
    protected $protectFields = true;
    protected $allowedFields = [
        'subscription_type_name',
        'subscription_type_slug',
        'subscription_type_group',
        'subscription_type_sort',
        'subscription_type_duration_days',
        'subscription_type_price',
        'subscription_type_is_public',
        'subscription_buyable_multiple_times',
        'subscription_type_description',
        'subscription_type_recurring_unit',
        'subscription_type_recurring_amount',
        'subscription_type_recurring_until_unit',
        'subscription_type_recurring_until_amount',
        'subscription_type_cancel_until_unit',
        'subscription_type_cancel_until_amount',
        'subscription_type_highlight',
        'send_emails_active',
        'admin_email_template_id',
        'user_email_template_id',
        'apple_product_id',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeChange'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeChange'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getTotalEntries() {
        $query_elements = $this;
        $count_all_results = $query_elements->countAllResults(false);

        return $count_all_results;
    }

    public function getEntries($limit=100, $offset=0)
    {
        $query_elements = $this;
        $query_elements->orderBy('subscription_type_sort', 'ASC');
        $query_elements = $query_elements->findAll($limit, $offset);

        return $query_elements;
    }

    public function getTableHeader() {
        return [
            'Name',
            'Reihenfolge',
            'Tage',
            'Preis',
            'Öffentlich',
            'E-Mail senden?'
        ];
    }

    public function getTableFields($entity) {
        return [
            $entity->subscription_type_name,
            $entity->subscription_type_sort,
            $entity->subscription_type_duration_days,
            $entity->subscription_type_price,
            $entity->subscription_type_is_public==1?'Ja':'Nein',
            $entity->send_emails_active==1?'Ja':'Nein',
        ];
    }

    public function beforeChange(array $data) {
        $subscriptionTypeId = $data['id'][0] ?? null;

        if(!isset($data['data']['subscription_type_slug']) || $data['data']['subscription_type_slug']=="") {
            if ($subscriptionTypeId > 0) {
                $subscriptionType = $this->find($subscriptionTypeId);
                $data['data']['subscription_type_slug'] = $subscriptionType->subscription_type_name;
            } else {
                if (isset($data['data']['subscription_type_name'])) {
                    $data['data']['subscription_type_slug'] = $data['data']['subscription_type_name'];
                } else {
                    $data['data']['subscription_type_slug'] = md5(time());
                }
            }
        }

        if(isset($data['data']['subscription_type_slug'])) {
            $baseSlug = slugify($data['data']['subscription_type_slug']);
            $slug = $baseSlug;
            $i = 1;

            // Check if it's an update or insert
            if ($subscriptionTypeId) {
                // Update operation
                while ($this->where('subscription_type_slug', $slug)->where('subscription_type_id !=', $subscriptionTypeId)->first()) {
                    $slug = $baseSlug . '-' . $i;
                    $i++;
                }
            } else {
                // Insert operation
                while ($this->where('subscription_type_slug', $slug)->first()) {
                    $slug = $baseSlug . '-' . $i;
                    $i++;
                }
            }

            $data['data']['subscription_type_slug'] = $slug;
        }



        return $data;
    }

    public function getFormConfiguration($entity=null, $request=null)
    {
        if(!$entity) {
            $entity = $this->getEntity();
        }
        if(!$request) {
            $request = service('request');
        }

        //$subscription = new \App\Libraries\Subscription();

        $subscription_library = new \App\Libraries\Subscription();
        //$subscription_type_category_options = $subscription_library->getSubscriptionTypeCategoryOptions();

        $email_library = new \App\Libraries\Email();
        $email_template_options = $email_library->getEmailTemplateOptions();

        $form_data = [
            'tabs' => [
                'general' => 'Allgemein',
                'seo' => 'SEO',
            ],
            'fields' => [
                'general' => [
                    'subscription_type_name' => [
                        'type' => 'text',
                        'label' => 'Abo Name',
                        'required' => 'required',
                    ],
                    'subscription_type_group' => [
                        'type' => 'text',
                        'label' => 'Gruppe',
                        'info' => 'Gruppierung im Frontend als Tab',
                    ],
                    'subscription_type_sort' => [
                        'type' => 'number',
                        'min' => 0,
                        'label' => 'Sortierungsposition',
                    ],
                    'subscription_type_highlight' => [
                        'type' => 'dropdown',
                        'label' => 'Highlight?',
                        'options' => [
                            '0' => 'Ohne',
                            '1' => 'Mit Hervorhebung',
                        ]
                    ],
                    'subscription_type_duration_days' => [
                        'type' => 'number',
                        'min' => 0,
                        'label' => 'Abo Dauer in Tagen (Gültigkeit ab Buchung)',
                        'required' => 'required',
                    ],
                    'subscription_type_price' => [
                        'type' => 'number',
                        'step' => '0.05',
                        'min' => 0.00,
                        'label' => 'Abo Preis',
                        'required' => 'required',
                    ],
                    'subscription_type_recurring_unit' => [
                        'type' => 'dropdown',
                        'label' => 'Wiederholung Einheit',
                        'options' => [
                            '' => 'Keine Wiederholung',
                            'D' => 'Tage',
                            'M' => 'Monate',
                            'Y' => 'Jahre'
                        ],
                    ],
                    'subscription_type_recurring_amount' => [
                        'type' => 'number',
                        'min' => '0',
                        'label' => 'Wiederholung Anzahl',
                        'info' => '(nur bei aktiver Wiederholung)',
                        'style' => 'display:none;',
                        'script' => "if($('#subscription_type_recurring_unit') !== '') { $('#subscription_type_recurring_amount').show(); }",
                    ],
                    'subscription_type_recurring_until_unit' => [
                        'type' => 'dropdown',
                        'label' => 'Wiederholung bis Einheit',
                        'options' => [
                            '' => 'Ohne Ende',
                            'D' => 'Tage',
                            'M' => 'Monate',
                            'Y' => 'Jahre'
                        ],
                    ],
                    'subscription_type_recurring_until_amount' => [
                        'type' => 'number',
                        'min' => '0',
                        'label' => 'Wiederholung bis Anzahl',
                        'info' => '(nur bei aktiver Wiederholung bis)',
                        'style' => 'display:none;',
                        'script' => "if($('#subscription_type_recurring_unit') !== '') { $('#subscription_type_recurring_amount').show(); }",
                    ],
                    'subscription_type_cancel_until_unit' => [
                        'type' => 'dropdown',
                        'label' => 'Kündigung des Abos möglich ab Einheit',
                        'info' => 'Bei jedem Abo braucht es ein mögliches Kündigungsdatum. ZB. 3 Monate',
                        'options' => [
                            'D' => 'Tage',
                            'M' => 'Monate',
                            'Y' => 'Jahre'
                        ],
                    ],
                    'subscription_type_cancel_until_amount' => [
                        'type' => 'number',
                        'min' => '0',
                        'label' => 'Kündigung des Abos ab Anzahl',
                        'info' => '(nur bei aktiver Wiederholung bis)',
                        'style' => 'display:none;',
                        'script' => "if($('#subscription_type_recurring_unit') !== '') { $('#subscription_type_recurring_amount').show(); }",
                    ],
                    'subscription_type_is_public' => [
                        'type' => 'dropdown',
                        'label' => 'Abo ist öffentlich',
                        'required' => 'required',
                        'options' => [
                            '1' => 'Ja, Kunde kann buchen',
                            '0' => 'Nein, nur intern buchbar',
                        ]
                    ],
                    'subscription_buyable_multiple_times' => [
                        'type' => 'dropdown',
                        'label' => 'Abo mehrmals buchbar?',
                        'required' => 'required',
                        'options' => [
                            '1' => 'Ja, mehrmals buchbar',
                            '0' => 'Nein, nur einmal buchbar',
                        ]
                    ],

                    'subscription_type_description' => [
                        'label' => 'Beschreibung',
                        'info' => '(je Punkt eine Zeile)',
                        'type' => 'textarea',
                        'rows' => '5',
                    ],

                    'send_emails_active' => [
                        'type' => 'dropdown',
                        'label' => 'E-Mail versenden?',
                        'required' => 'required',
                        'options' => [
                            '1' => 'Ja, E-Mail an Admin und Kunden senden',
                            '0' => 'Nein, keine E-Mails versenden',
                        ]
                    ],

                    'admin_email_template_id' => [
                        'type' => 'dropdown',
                        'label' => 'E-Mail-Text Admin',
                        'info' => 'E-Mail Vorlage, die an Gruppe Admin gesendet werden',
                        'required' => 'required',
                        'options' => $email_template_options,
                    ],

                    'user_email_template_id' => [
                        'type' => 'dropdown',
                        'label' => 'E-Mail-Text Kunde',
                        'info' => 'E-Mail Vorlage, die an Kunde gesendet wird',
                        'required' => 'required',
                        'options' => $email_template_options,
                    ],

                    'apple_product_id' => [
                        'type' => 'text',
                        'label' => 'Apple App Product ID',
                    ],

                ],
                'seo' => [
                    'subscription_type_slug' => [
                        'type' => 'slug',
                        'label' => 'Abo URL-Slug',
                        'prefix' => 'shop/',
                    ],
                ],
            ],
            'config' => [
                'first_tab' => 'general',
                'translation' => true,
                'seo_slug_field' => 'subscription_type_slug',
                'row_fields' => [
                    'general' => [
                        ['subscription_type_name', 'subscription_type_sort', 'subscription_type_highlight'],
                        ['subscription_type_duration_days', 'subscription_type_price', 'subscription_type_group'],
                        ['subscription_type_psp_id'],
                        ['subscription_type_recurring_unit', 'subscription_type_recurring_amount'],
                        ['subscription_type_recurring_until_unit', 'subscription_type_recurring_until_amount'],
                        ['subscription_type_cancel_until_unit', 'subscription_type_cancel_until_amount'],
                        ['subscription_type_is_public', 'subscription_buyable_multiple_times'],
                        ['subscription_type_description'],
                        ['send_emails_active', 'admin_email_template_id', 'user_email_template_id'],
                        ['apple_product_id', '', ''],
                    ]
                ],
            ]
        ];

        return $form_data;
    }

    public function getFilterConfiguration($entity=null, $request=null)
    {
        if(!$entity) {
            $entity = $this->getEntity();
        }
        if(!$request) {
            $request = service('request');
        }

        $filter_data = [
            'fields' => [
                'name' => [
                    'type' => 'search',
                    'name' => 'name',
                    'like' => [
                        'subscription_type_name' => '%value%',
                        'subscription_type_description' => '%value%',
                        'subscription_type_price' => '%value%',
                    ],
                    'operator' => 'OR',
                    'onchange' => 'this.form.submit()',
                ],
            ],

        ];

        return $filter_data;
    }

}
