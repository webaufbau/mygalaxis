<?php
namespace App\Models;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use Config\Database;

class UserModel extends \CodeIgniter\Shield\Models\UserModel {
    protected $title = 'Benutzer';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\User::class;
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $afterFind = ['fetchIdentities'];
    protected $afterInsert = ['saveEmailIdentity', 'replicateQuery'];
    protected $afterUpdate = ['saveEmailIdentity', 'saveGroups', 'savePermissions', 'replicateQuery'];
    protected $afterDelete = ['replicateQuery'];

    protected function initialize(): void {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'status',
            'status_message',
            'active',
            'last_active',
            'created_at',
            'updated_at',
            'deleted_at',
            // Neue Felder:
            'company_name',
            'contact_person',
            'company_uid',
            'company_street',
            'company_zip',
            'company_city',
            'company_website',
            'company_email',
            'company_phone',
            'filter_categories',
            'filter_cantons',
            'filter_regions',
            'filter_languages',
            'filter_absences',
            'filter_custom_zip',
            'account_balance',
            'auto_purchase',
            'email_text',
            'stripe_customer_id',
            'language',
            'welcome_email_sent',
            'platform',
            'email_notifications_enabled',
            'stats_always_open',
            'is_blocked',
        ];
    }

    protected $queryLog = [];

    protected function replicateQuery(array $data)
    {
        return $data;
    }


    public function getPrimaryKeyField() {
        return $this->primaryKey;
    }

    public function getTitle() {
        return $this->title;
    }


    public function save($row): bool
    {

        if (is_object($row) && $row->email) {
            $row->email_text = $row->email;
            $row->username = $row->email;
        }

        return parent::save($row);
    }

    protected function saveGroups(array $data): array {

        $request = service('request');

        $posted_data = $request->getPost('data');

        if(isset($data['id'][0]) && isset($posted_data['user_group']) && is_array($posted_data['user_group'])) {
            $user = $this->find($data['id'][0]);
            $user->syncGroups(...$posted_data['user_group']);
            //$this->save($user);
        }

        return $data;
    }

    protected function savePermissions(array $data): array {
        $request = service('request');
        $posted_data = $request->getPost('data');

        if (isset($data['id'][0])) {
            $user = $this->find($data['id'][0]);

            if (isset($posted_data['user_permissions']) && is_array($posted_data['user_permissions'])) {
                $user->syncPermissions(...array_keys($posted_data['user_permissions']));
            } else {
                $user->removePermission(...$user->getPermissions());
            }
        }

        return $data;
    }




    public function getTotalEntries() {
        return $this->countAllResults(false);
    }

    public function getTableHeader()
    {
        return [
            'ID',
            'Blockiert',
            'Gruppen',
            'Ansprechsperson',
            'Firma',
            'Branchen',
            'Ort (Firma)',
            'Plattform',
            'E-Mail',
            'Autom. Kauf',
            'Letzter Login',
        ];
    }

    public function getTableFields($entity)
    {
        $last_login_date = '-';
        if ($entity->lastLogin()) {
            $last_login_date = $entity->lastLogin()->date->toLocalizedString('dd.MM.YYYY');
        }

        $groups = '';
        foreach($entity->groups as $group_id=>$group) {
            $groups .= '<span class="badge group group-' . $group . '">' . lang('Wa.group_' . $group) . '</span>';
        }


        // Benutzer Branchen:
        $filter_categories = $entity->filter_categories;
        // Wenn NULL, dann "-" ausgeben
        if (empty($filter_categories)) {
            $filter_categories_display = '-';
        } else {
            $keys = explode(',', $filter_categories);

            // Übersetzung über lang() holen
            $filter_categories_display = [];
            foreach ($keys as $key) {
                $key = trim($key);
                // Prüfen ob $key bereits "Filter." enthält (z.B. "Filter.tiling")
                // Falls ja, direkt übersetzen. Falls nein, "Filter." voranstellen
                if (strpos($key, 'Filter.') === 0) {
                    $translated = lang($key);
                } else {
                    $translated = lang('Filter.' . $key);
                }
                $filter_categories_display[] = $translated ?: $key; // fallback auf Key, falls keine Übersetzung
            }

            $filter_categories_display = implode(', ', $filter_categories_display);
        }

        // Plattform-Namen und Farben
        $platformMapping = [
            'my_offertenheld_ch'     => 'Offertenheld.ch',
            'my_offertenschweiz_ch'  => 'Offertenschweiz.ch',
            'my_renovo24_ch'         => 'Renovo24.ch',
        ];

        // Plattform-Farben wie im Dashboard
        $platformName = $platformMapping[$entity->platform ?? ''] ?? ($entity->platform ?? '-');
        $platformLower = strtolower($entity->platform ?? '');

        $badgeStyle = 'class="badge bg-secondary"'; // Fallback
        if (strpos($platformLower, 'offertenschweiz') !== false ||
            strpos($platformLower, 'offertenaustria') !== false ||
            strpos($platformLower, 'offertendeutschland') !== false) {
            // Rosa für Offertenschweiz/Austria/Deutschland
            $badgeStyle = 'style="background-color: #E91E63; color: white;"';
        } elseif (strpos($platformLower, 'offertenheld') !== false) {
            // Lila/Violett für Offertenheld
            $badgeStyle = 'style="background-color: #6B5B95; color: white;"';
        } elseif (strpos($platformLower, 'renovo') !== false) {
            // Schwarz für Renovo
            $badgeStyle = 'style="background-color: #212529; color: white;"';
        }

        $platformBadge = $entity->platform ? '<span class="badge" ' . $badgeStyle . '>' . esc($platformName) . '</span>' : '-';

        return [
            $entity->id,
            $entity->is_blocked ? '<i class="bi bi-check text-danger"></i>' : '-',
            $groups,
            esc(($entity->contact_person ?? '-')),
            esc($entity->company_name ?? '-'),
            $filter_categories_display,
            esc(($entity->company_zip ?? '') . ' ' . ($entity->company_city ?? '')),
            $platformBadge,
            esc($entity->getEmail() ?? '-'),
            $entity->auto_purchase ? 'Ja' : 'Nein',
            '<span class="text-nowrap">' . $last_login_date . '</span>',
        ];
    }


    public function getEntryActions($entity) {
        return '';
    }

    public function getDefaultOrderField() {
        return $this->default_order_field;
    }

    public function getDefaultOrderDirection() {
        return $this->default_order_direction;
    }

    public function getDefaultOrderColumn() {
        return $this->default_order_column;
    }

    public function useFilter(): bool
    {
        return $this->use_filter;
    }

    public function getEntity() {
        $entity = $this->returnType;
        return new $entity();
    }

    public function getEntry($uid) {
        return $this->asObject($this->returnType)
            ->where([$this->primaryKey => $uid])
            ->first();
    }

    public function getEntries($limit=100, $offset=0)
    {
        return $this->orderBy($this->getPrimaryKeyField(), 'DESC')->findAll($limit, $offset);
    }

    public function getFormConfiguration($entity=null, $request=null)
    {
        if(!$entity) {
            $entity = $this->getEntity();
        }
        if(!$request) {
            $request = service('request');
        }

        // Benutzer Gruppen
        $user_group_options = [];
        $groupsConfig = new \Config\AuthGroups();
        foreach($groupsConfig->groups as $group=>$group_description) {
            $user_group_options[$group] = $group_description['title'].' ('.$group_description['description'].')';
        }

        // auth_permissions_users
        $user_permission_options = [];
        foreach($groupsConfig->permissions as $permission=>$permission_description) {
            $user_permission_options[$permission] = ''.$permission_description.'';
        }


// Angenommen, $entity ist ein \CodeIgniter\Shield\Entities\User
        $permissions = $entity->getPermissions(); // Berechtigungen des Benutzers als Array von Strings

// Lade die Berechtigungs- und Gruppenkonfiguration
        $authConfig = config('AuthGroups');

// Initialisiere ein Array für die gruppierten Berechtigungen
        $groupedPermissions = [];

        $auth_config_permissions = $authConfig->permissions;

// Gehe durch jede Gruppe in der Konfiguration
        foreach ($authConfig->groups as $groupName => $group) {
            $groupedPermissions[$groupName] = [
                'title'       => $group['title'],
                'description' => $group['description'],
                'permissions' => [], // Hier werden die Berechtigungen dieser Gruppe gespeichert
            ];

            // Überprüfe, ob die Gruppe Berechtigungen in der Matrix hat

            if (isset($authConfig->matrix[$groupName])) {
                foreach ($authConfig->matrix[$groupName] as $permission) {
                    // Überprüfe, ob der Benutzer die Berechtigung hat
                    if (in_array($permission, $permissions)) {
                        // Füge die Berechtigung der Gruppe hinzu

                    }

                    $groupedPermissions[$groupName]['permissions'][$permission] = $auth_config_permissions[$permission];
                }
            }
        }

        // Konvertiere das gruppierte Array in JSON
        //$jsonPermissions = json_encode($groupedPermissions);

        $user_permission_options = [];
        foreach($groupedPermissions as $group=>$permissions) {
            foreach ($permissions['permissions'] as $permission => $permission_description) {
                $user_permission_options[$group][$permission] = '' . $permission_description . '';
            }
        }




        $form_data = [
            'tabs' => [
                'general' => 'Allgemein',
            ],
            'fields' => [
                'general' => [
                    'id' => [
                        'type' => 'hidden',
                    ],
                    'company_name' => [
                        'type' => 'text',
                        'label' => 'Firmenname',
                    ],
                    'contact_person' => [
                        'type' => 'text',
                        'label' => 'Ansprechperson',
                    ],
                    'company_uid' => [
                        'type' => 'text',
                        'label' => 'UID',
                    ],
                    'company_street' => [
                        'type' => 'text',
                        'label' => 'Strasse',
                    ],
                    'company_zip' => [
                        'type' => 'text',
                        'label' => 'PLZ',
                    ],
                    'company_city' => [
                        'type' => 'text',
                        'label' => 'Ort',
                    ],
                    'company_website' => [
                        'type' => 'text',
                        'label' => 'Website',
                    ],
                    'company_email' => [
                        'type' => 'email',
                        'label' => 'E-Mail (Firma)',
                    ],
                    'company_phone' => [
                        'type' => 'text',
                        'label' => 'Telefon',
                    ],
                    /*'country' => [
                        'type' => 'dropdown_model',
                        'label' => lang('Auth.country'),
                        'required' => 'required',
                        'model' => 'CountryModel',
                    ],*/
                    /*'auto_purchase' => [
                        'type' => 'dropdown',
                        'label' => 'Automatischer Kauf von passenden Angeboten aktivieren',
                        'options' => [
                            '1' => 'Ja',
                            '0' => 'Nein',
                        ],
                    ],*/
                    'active' => [
                        'type' => 'dropdown',
                        'label' => 'Aktiv',
                        'required' => 'required',
                        'options' => [
                            '1' => 'Ja',
                            '0' => 'Nein',
                        ],
                        'info' => 'Login möglich',
                    ],
                    'is_blocked' => [
                        'type' => 'dropdown',
                        'label' => 'Blockiert',
                        'options' => [
                            '0' => 'Nein',
                            '1' => 'Ja - Firma blockiert',
                        ],
                        'info' => 'Blockierte Firmen können sich nicht einloggen und erhalten keine Anfragen',
                    ],
                    'email' => [
                        'type' => 'email',
                        'label' => 'E-Mail-Adresse (Login)',
                        'required' => 'required',
                        'value' => $entity->id ? $entity->getEmail() : '',
                    ],
                    'user_group' => [
                        'type' => 'multiple',
                        'label' => 'Benutzer-Gruppen',
                        'required' => 'required',
                        'options' => $user_group_options,
                        'value' => $entity->getGroups(),
                    ],
                    'user_permissions' => [
                        'type' => 'checkboxes_grouped',
                        'label' => 'Zusätzliche Berechtigungen zur Gruppe',
                        'options' => $user_permission_options,
                        'value' => json_encode($entity->getPermissions()),
                    ],
                    'users_organizations' => [
                        'type' => 'variants',
                        'label' => 'Organisationen',
                        'model_filename' => 'usersorganization',
                        'model_name' => 'Usersorganization',
                        'model_primary_key' => 'user_id',
                        'user_id' => $entity->id,
                        'params' => '&user_id=' . $entity->id,
                    ],
                    /*'photo' => [
                        'label' => 'Neues Profilbild',
                        'type' => 'file',
                        'accept' => '.jpg,.jpeg,.png',
                        'name' => 'userfile',
                        'public_path' => 'image/profile/thumb_',
                        'preview' => true,
                        'info' => 'Mindestens 100x100',
                    ],
                    'photo_crop' => [
                        'label' => 'Profilbild Ausschnitt',
                        'type' => 'dropdown',
                        'options' => [
                            'top-left'     => 'Bildausschnitt: Oben Links',
                            'top'          => 'Bildausschnitt: Oben',
                            'top-right'    => 'Bildausschnitt: Oben Rechts',
                            'left'         => 'Bildausschnitt: Links',
                            'center'       => 'Bildausschnitt: Mitte',
                            'right'        => 'Bildausschnitt: Rechts',
                            'bottom-left'  => 'Bildausschnitt: Unten Links',
                            'bottom'       => 'Bildausschnitt: Unten',
                            'bottom-right' => 'Bildausschnitt: Unten Rechts',
                        ],
                        'value' => 'center',
                    ],*/
                    'email_activate_code' => [
                        'type' => 'message',
                        'message' => 'Benutzer aktiviert',
                    ],

                ],
            ],
            'config' => [
                'first_tab' => 'general',
                'translation' => true,
                'row_fields' => [
                    'general' => [
                        ['company_name', 'contact_person'],
                        ['company_uid'],
                        ['company_street'],
                        ['company_zip', 'company_city'],
                        ['company_email', 'company_phone'],
                        ['company_website'],
                        ['auto_purchase'],
                        ['is_blocked'],
                        ['email'],
                        ['user_group'],
                        //['user_permissions'],
                        ['email_activate_code']
                    ]
                ],
            ]
        ];

        if($entity->id > 0) {
            $form_data['fields']['general']['password'] = [
                'type' => 'text',
                'label' => 'Neues Passwort',
                'name' => 'password',
            ];

            $form_data['config']['row_fields']['general'][] = ['password'];
        } else {
            $form_data['fields']['general']['password'] = [
                'type' => 'text',
                'label' => 'Neues Passwort (Login)',
                'required' => 'required',
                'name' => 'password',
            ];

            $form_data['config']['row_fields']['general'][] = ['password'];
        }

        // has email activation code?
        $authIdentityModel = new AuthIdentityModel();
        if($authIdentityModel->where('type','email_activate')->where('user_id', $entity->id)->first()) {
            $authIdentity = $authIdentityModel->where('type','email_activate')->where('user_id', $entity->id)->first();
            $form_data['fields']['general']['email_activate_code']['message'] = 'E-Mail-Aktivierungscode: ' . $authIdentity->secret;
        } else {
            unset($form_data['fields']['general']['email_activate_code']);
            unset($form_data['config']['row_fields']['general']['email_activate_code']);
        }


        return $form_data;
    }

    public function getModelShortname(): string
    {
        return strtolower(preg_replace('#App\\\\Models\\\\([A-Za-z]*)Model#', '$1', get_class($this)));
    }

    protected function indexAfterInsert(array $data)
    {
        $this->indexContent($this->getInsertID(), $data['data']);
        return $data;
    }

    protected function indexAfterUpdate(array $data)
    {
        $this->indexContent($data['id'][0], $data['data']);
        return $data;
    }

    protected function indexContent($id, $data)
    {
        $modelType = get_class($this);
        $title = $data["firstname"] ?? null;
        $link = $this->generateLink($id);
        $content = $this->generateContent($data);
        $table = $this->table;
        $id_field = $this->getPrimaryKeyField();

        $db = \Config\Database::connect();
        $builder = $db->table('search_index');

        // Prüfen, ob der Datensatz bereits existiert
        $existing = $builder->where('model_type', $modelType)->where('model_id', $id)->get()->getRowArray();

        $existing_entity = model($modelType)->find($id);
        if($existing_entity) {
            $title = $existing_entity->getTitle();
            $content = $existing_entity->getRawContent();
        }

        $content = str_replace("><", "> <", $content);
        $content = strip_tags($content);
        $content = str_replace("\n", " ", $content);

        $indexData = [
            'user_id' => $data['user_id'] ?? $existing_entity->user_id,
            'organization_id' => $data['organization_id'] ?? $existing_entity->organization_id,
            'model_type' => $modelType,
            'model_id' => $id,
            'model_table' => $table,
            'model_id_field' => $id_field,
            'title' => $title,
            'link' => $link,
            'content' => $content,
            'additional_data' => json_encode($data),
        ];

        if ($existing) {
            // Update existing record
            $builder->where('id', $existing['id'])->update($indexData);
        } else {
            // Insert new record
            $builder->insert($indexData);
        }
    }

    protected function generateLink($id)
    {
        return site_url("admin/user/form/{$id}");
    }

    protected function generateContent($data)
    {
        // Kombiniere relevante Felder, um den durchsuchbaren Inhalt zu erstellen
        return implode(' ', $data);
    }

    public function entitiesWithParent($parent_id) {
        if(!in_array('pid', $this->allowedFields)) {
            return [];
        }
        return $this->where('pid', $parent_id)->findAll();
    }

    public function getFilterConfiguration($entity=null, $request=null)
    {
        if(!$entity) {
            $entity = $this->getEntity();
        }
        if(!$request) {
            $request = service('request');
        }

        // Benutzergruppen für Filter laden
        $groupsConfig = new \Config\AuthGroups();
        $group_options = ['' => 'Alle Benutzergruppen'];
        foreach($groupsConfig->groups as $group=>$group_description) {
            $group_options[$group] = $group_description['title'];
        }

        // Plattform-Optionen für Filter
        $platform_options = [
            '' => 'Alle Plattformen',
            'my_offertenheld_ch' => 'Offertenheld.ch',
            'my_offertenschweiz_ch' => 'Offertenschweiz.ch',
            'my_renovo24_ch' => 'Renovo24.ch',
        ];

        $filter_data = [
            'fields' => [
                'query' => [
                    'type' => 'search',
                    'name' => 'query',
                    'like' => [
                        'company_name' => '%value%',
                        'contact_person' => '%value%',
                        'company_uid' => '%value%',
                        'company_street' => '%value%',
                        'company_zip' => '%value%',
                        'company_city' => '%value%',
                        'company_website' => '%value%',
                        'company_email' => '%value%',
                        'company_phone' => '%value%',
                        'filter_categories' => '%value%',
                        'filter_cantons' => '%value%',
                        'filter_regions' => '%value%',
                        'filter_languages' => '%value%',
                        'filter_absences' => '%value%',
                        'filter_custom_zip' => '%value%',
                        'account_balance' => '%value%',
                        'auto_purchase' => '%value%',
                        'email_text' => '%value%',
                        'stripe_customer_id' => '%value%',
                    ],
                    'operator' => 'OR',
                    'onchange' => 'this.form.submit()',
                ],
                'status' => [
                    'type' => 'dropdown',
                    'options' => [''=>'Alle', '0'=>'Inaktiv', '1'=>'Aktiv'],
                    'name' => 'status',
                    'where' => '( active = \'%value%\' )',
                    'onchange' => 'this.form.submit()',
                ],
                'user_group' => [
                    'type' => 'dropdown',
                    'options' => $group_options,
                    'name' => 'user_group',
                    'onchange' => 'this.form.submit()',
                ],
                'platform' => [
                    'type' => 'dropdown',
                    'options' => $platform_options,
                    'name' => 'platform',
                    'where' => '( platform = \'%value%\' )',
                    'onchange' => 'this.form.submit()',
                ],
            ],

        ];

        return $filter_data;
    }

    public function getSortableFields() {
        /*$fields = $this->allowedFields;

        $headers = [];
        foreach ($fields as $field) {
            $headers[$field] = $field;
        }

        return $headers;*/
        return null;
    }

}
