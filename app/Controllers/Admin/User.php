<?php

namespace App\Controllers\Admin;

use App\Controllers\Crud;
use App\Models\AuthIdentityModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;

class User extends Crud {

    protected string $url_prefix = 'admin/';

    protected $user_organization_model = false;

    protected string $permission_prefix = 'user';

    public function __construct($init_model_name='') {
        parent::__construct('user');
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->set('text_add_new', '+ Neuer Benutzer');
    }

    public function index() {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }


        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $offset = $page * $perPage - $perPage;


        $filter_configuration = $this->model_class->getFilterConfiguration();

        $request_get_data = $this->request->getGet();
        if($request_get_data) {
            foreach($request_get_data as $key => $value) {
                if(isset($filter_configuration['fields'][$key])) {
                    $field_config = $filter_configuration['fields'][$key];
                    if (isset($field_config['where']) && strlen($value)) {
                        $this->model_class->where(str_replace('%value%', $value, $field_config['where']));
                    } elseif (isset($field_config['like']) && $value) {
                        $this->model_class->groupStart();
                        foreach ($field_config['like'] as $field_config_like => $field_config_value) {
                            $value_like = $field_config['like'][$field_config_like];
                            $this->model_class->orLike($field_config_like, str_replace('%value%', $value, $value_like));
                        }
                        $this->model_class->groupEnd();
                    } elseif (isset($field_config['in']) && $value) {
                        if($key == 'author_id') {
                            $this->model_class->groupStart();
                            if(in_array("privat", $value)) {
                                $this->model_class->where('author_id', auth()->user()->id);
                            }
                            $this->model_class->orWhereIn('organization_id', $value);
                            $this->model_class->groupEnd();
                            continue;
                        }
                        $this->model_class->groupStart();
                        foreach ($field_config['in'] as $field_config_in => $field_config_value) {
                            $this->model_class->whereIn($field_config_in, $value);
                        }
                        $this->model_class->groupEnd();
                    }
                }

                // Spezielle Behandlung für Benutzergruppen-Filter
                if($key == 'user_group' && !empty($value)) {
                    $this->model_class->select('users.*');
                    $this->model_class->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'inner');
                    $this->model_class->where('auth_groups_users.group', $value);
                    $this->model_class->groupBy('users.id');
                }
            }
        }


        // Export
        $export = $this->request->getGet('export') ?? false;
        if($export) {
            if(!auth()->user()->can('my.user_edit')) {
                return redirect()->to('/');
            }
            //$this->model_class->join('users_subscriptions', 'users_subscriptions.user_id = users.id AND users_subscriptions.valid_start_date <= NOW() AND users_subscriptions.valid_stop_date >= NOW()', 'left');
            $entries = $this->model_class->orderBy($this->model_class->getPrimaryKeyField(), 'DESC')->findAll(99999999999999999);

            $this->exportUserCSV($entries);
            exit();
        }


        // Output

        $total = $this->model_class->getTotalEntries();
        $entries = $this->model_class->getEntries($perPage, $offset);

        $filter_html = '';
        if(isset($filter_configuration['fields'])) {
            foreach ($filter_configuration['fields'] as $key => $value) {
                if (isset($filter_configuration['fields'][$key])) {
                    $field_config = $filter_configuration['fields'][$key];
                    $filter_html .= '<li class="nav-item px-3">
                    ' . form_build_one_field($key, $field_config, $request_get_data, true) . '
                </li>';
                }
            }
        }

        $this->template->set('has_filter_configuration', count($filter_configuration));

        $this->template->set('form_filters', $filter_html);


        $this->template->set('page_title', $this->model_class->getTitle());

        $this->template->set('navbar_html', anchor('admin/user?export=csv', 'Export CSV', 'class="btn btn-primary btn-sm"'));

        $table_heading = $this->model_class->getTableHeader();

        $table = $this->getCrudTable($this->app_controller, $this->model_name, (array)$entries);
        $this->template->set('pager_table', $table);

        $pager_links = $pager->makeLinks($page, $perPage, $total, 'bs5_prev_next');
        $this->template->set('pager_details', $pager->getDetails('default'));

        $this->template->set('has_filter_configuration', 0);

        $this->template->set('pager_links', $pager_links);

        $this->template->load('account/default_list');
    }

    /**
     * @throws \ReflectionException
     */
    protected function formSubmitAction($id=0, $posted_values=[], $return_id=false) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_edit')) {
            return redirect()->to('/');
        }

        // E-Mail schon vorhanden bei Neuanlage prüfen
        if ($id <= 0 && isset($posted_values['email'])) {
            $authIdentityModel = new AuthIdentityModel();
            $email_exists = $authIdentityModel->where('secret', $posted_values['email'])->first();
            if ($email_exists) {
                $this->setFlash('Die eingegebene E-Mail-Adresse existiert bereits bei einem anderen Account.', 'error');
                return redirect()->to('/admin/user');
            }
        }

        $userModel = new \App\Models\UserModel();

        // Neues Entity befüllen & speichern
        if ($id <= 0) {
            $allowedPostFields = array_keys($this->getValidationRules());
            $user = new \App\Entities\User();
            //$user->fill(array_intersect_key($posted_values, array_flip($allowedPostFields)));
            $user->fill($this->request->getPost('data'));

            // Weitere erlaubte Felder manuell setzen (wenn im POST vorhanden)
            $extraFields = ['active', 'filter_regions', 'filter_languages', 'filter_cantons'];

            if (isset($posted_values['email'])) {
                $user->setAttribute('email', $posted_values['email']);
                $user->setEmail($posted_values['email']);
                $user->setAttribute('username', $posted_values['email']);
            }
            try {
                $userModel->save($user);
                $id = $userModel->getInsertID();
                $user = $userModel->find($id);
            } catch (ValidationException $e) {
                $this->setFlash('Fehler beim Speichern: ' . implode(', ', $userModel->errors()), 'error');
                return redirect()->back()->withInput();
            }
        } else {
            // Update
            $user = $userModel->find($id);
            if (!$user) {
                $this->setFlash('Benutzer nicht gefunden.', 'error');
                return redirect()->back();
            }
            $user->fill($posted_values);
            try {
                $userModel->save($user);
            } catch (ValidationException $e) {
                $this->setFlash('Fehler beim Speichern: ' . implode(', ', $userModel->errors()), 'error');
                return redirect()->back()->withInput();
            }
        }

        // Gruppen synchronisieren, nur wenn User gültig
        if ($user && $user->id !== null) {
            $request = service('request');
            $posted_data = $request->getPost('data');
            if (isset($posted_data['user_group']) && is_array($posted_data['user_group'])) {
                $user->syncGroups(...$posted_data['user_group']);
                $userModel->save($user);
            }
            if (isset($posted_data['user_permissions']) && is_array($posted_data['user_permissions'])) {
                $user->syncPermissions(...array_keys($posted_data['user_permissions']));
            } else {
                $user->removePermission(...$user->getPermissions());
            }
            $userModel->save($user);
        }

        $this->setFlash('Eintrag gespeichert.', 'success');

        // E-Mail in AuthIdentity updaten wenn geändert
        if ($user->getEmail() != $posted_values['email']) {
            $authIdentityModel = new AuthIdentityModel();
            $authIdentityModel->where('user_id', $id)->set(['secret' => $posted_values['email']])->update();
        }

        // Passwort setzen wenn übergeben
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $authIdentityModel = new AuthIdentityModel();
            $passwords = service('passwords');
            $hashed_password = $passwords->hash($_POST['password']);
            $authIdentityModel->where('user_id', $id)->set(['secret2' => $hashed_password])->update();
        }

        // Redirect oder ID zurückgeben
        if ($return_id) {
            return $id;
        }

        return redirect()->to($this->url_prefix . $this->app_controller . '/form/' . $id . '?model=' . $this->model_name);
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return array<string, array<string, list<string>|string>>
     */
    protected function getValidationRules(): array {
        /*$rules = new ValidationRules();

        return $rules->getRegistrationRules();*/

        $config = config('Validation');
        $rules = $config->registration;

        return $rules;
    }

    protected function upload_avatar($user_id)
    {
        $data = $this->request->getPost('data');
        $img = $this->request->getFile('userfile');

        if($img->getSize() <= 0) {
            return false;
        }

        $rules = [
            'userfile' => [
                'label' => 'Profilbild',
                'rules' => [
                    'uploaded[userfile]',
                    'is_image[userfile]',
                    'mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[userfile,100]',
                    'max_dims[userfile,100,100]',
                ],
            ],
        ];
        if (! $this->validateData([], $rules)) {
            $this->template->set(['errors' => $this->validator->getErrors()]);
        }

        if ($img && ! $img->hasMoved()) {
            $user_id_md5 = md5($user_id);
            $file_extension = $img->getClientExtension();
            $filepath = 'uploads/profile/' . $user_id_md5 . '.' . $file_extension;
            $avatar_file_name = 'thumb_' . $user_id_md5 . '.' . $file_extension;
            $img->move(WRITEPATH . 'uploads/profile/', $user_id_md5 . '.' . $file_extension, true);

            $image = \Config\Services::image();
            //unlink(WRITEPATH . 'uploads/profile/' . $avatar_file_name);

            $image->withFile(WRITEPATH . $filepath)
                ->fit(44, 44, $data['photo_crop'])
                ->save(WRITEPATH . 'uploads/profile/' . $avatar_file_name);

            return $filepath;
        }

        $this->template->set(['errors' => 'The file has already been moved.']);

        return false;
    }

    public function organizations() {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $offset = $page * $perPage - $perPage;

        $date_start = $this->request->getGet('date_start');
        $date_stop = $this->request->getGet('date_stop');
        $query = $this->request->getGet('query');
        if($query) {
            $this->user_organization_model->like('name', $query);
        }
        $entries = $this->user_organization_model->orderBy($this->user_organization_model->getPrimaryKeyField(), 'DESC')->findAll($perPage, $offset);
        $total = $this->user_organization_model->getTotalEntries();

        $this->template->set('form_filters', '
                    <li class="nav-item px-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="query" class="form-control" name="query" placeholder="Suche">
                        </div>
                    </li>');

        $this->template->set('page_title', $this->user_organization_model->getTitle());

        $table_heading = $this->user_organization_model->getTableHeader();

        $table = $this->getCrudTable($this->app_controller, 'Usersorganization', (array)$entries);
        $this->template->set('pager_table', $table);

        $pager_links = $pager->makeLinks($page, $perPage, $total, 'bs5_prev_next');
        $this->template->set('pager_details', $pager->getDetails('default'));

        $this->template->set('pager_links', $pager_links);

        $this->template->load('account/default_list');
    }

    protected function getRowActions($entity_entity) {
        $primary_key_field = $this->model_class->getPrimaryKeyField();

        $actions = $this->model_class->getEntryActions($entity_entity);

        if(auth()->user()->inGroup('admin','superadmin')) {
            $actions .= anchor($this->url_prefix . $this->app_controller . '/form/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-pencil"></i>', 'class="btn btn-default action" title="Bearbeiten"');
            //$actions .= anchor($this->url_prefix . $this->app_controller . '/copy/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-files"></i>', 'class="btn btn-default action" title="Kopieren"');
            if(auth()->user()->id !== $entity_entity->{$primary_key_field}) {
                $actions .= anchor($this->url_prefix . $this->app_controller . '/delete/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-trash"></i>', 'class="btn btn-default action del" title="Löschen"');
            }
        }

        return $actions;
    }

    public function json() {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $results_on_query = $this->request->getVar('results_on_query');
        $default_results = $this->request->getVar('default_results');
        $display_field = $this->request->getVar('display_field');
        $query = $this->request->getVar('q');
        $user_model = new \App\Models\UserModel();

        $response = [];
        if(!$results_on_query) {
            $entity_entity = $user_model->getEntity();
            $results = $user_model->select('*')->orderBy($entity_entity->getTitleField(), 'ASC')->findAll();

        } elseif($query) {
            $entity_entity = $user_model->getEntity();
            $keywords = preg_split('/\s+/', trim($query));

            $builder = $user_model->select('*')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND type=\'email_password\'', 'left');

            // Dynamisch WHERE-Bedingung mit allen Keywords
            foreach ($keywords as $word) {
                $builder->groupStart()
                    ->like('contact_person', $word)
                    ->orLike('company_name', $word)
                    ->orLike('company_uid', $word)
                    ->orLike('company_street', $word)
                    ->orLike('company_zip', $word)
                    ->orLike('company_city', $word)
                    ->orLike('company_email', $word)
                    ->orLike('company_phone', $word)
                    ->orLike('email_text', $word)
                    ->orLike('auth_identities.secret', $word)
                    ->groupEnd();
            }

            $results = $builder
                ->orderBy($entity_entity->getTitleField(), 'ASC')
                ->findAll(50);

        } else {
            $entity_entity = $user_model->getEntity();
            $result = new \App\Entities\User(); // new \stdClass();
            $result->{$entity_entity->user_id} = '';
            $result->{$entity_entity->title_field} = 'Keine Resultate';
            $results[] = $result;
        }

        if(!$display_field) {
            $display_field = $entity_entity->titleField;
        }

        // echo $user_model->db->getLastQuery();

        foreach ($results as $result) {
            // Check if the value is valid UTF-8

            $value = '';
            if($display_field == 'email' && $result->hasAttribute('secret')) {
                $value = $result->getAttribute('secret');
            } elseif($display_field == 'user_fullname' && $result->hasAttribute('firstname') && $result->hasAttribute('lastname')) {
                $value = $result->getAttribute('firstname') . ' ' . $result->getAttribute('lastname');
            } elseif($display_field == 'user_fullname_email' && $result->hasAttribute('company_name')) { //  && $result->hasAttribute('contact_person')

                $value = $result->getAttribute('company_name') . ' ' . $result->getAttribute('contact_person') . ' (' . $result->getAttribute('email_text') . ')';

            } elseif($result->hasAttribute($display_field)) {
                $value = $result->getAttribute($display_field);
            } elseif(property_exists($result, $display_field)) {
                $value = $result->{$display_field};
            }

            if (!mb_detect_encoding($value, 'UTF-8', true)) {
                // If not valid UTF-8, convert it
                $value = mb_convert_encoding($value, 'UTF-8', 'auto');
            }

            $response[$result->user_id] = $value;
        }

        return $this->response->setJSON($response);
    }

    private function exportUserCSV($users)
    {
        $filename = 'benutzer_export.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV-Header in Deutsch
        fputcsv($output, [
            'Benutzer ID',
            'E-Mail',
            /*'Vorname',
            'Nachname',
            'Strasse',
            'Strassennummer',
            'Postleitzahl',
            'Stadt',
            'Land',*/
            'Aktiv',
            'Letzter Login',
            'Erstellt am',
            'Geändert am',
            'Firma: Name',
            'Firma: Ansprechperson',
            'Firma: UID',
            'Firma: Strasse',
            'Firma: PLZ',
            'Firma: Ort',
            'Firma: Website',
            'Firma: E-Mail',
            'Firma: Telefon',
            'Filter: Kategorien',
            'Filter: Kantone',
            'Filter: Regionen',
            'Filter: Sprachen',
            'Filter: Abwesenheiten',
            'Filter: Eigene PLZ',
            //'Kontostand',
            'Automatischer Kauf',
            'E-Mail-Text',
            'Stripe-Kunde ID',
            'Payrexx-Kunde ID',
        ]);

        foreach ($users as $user) {
            fputcsv($output, [
                $user->id,
                $user->getEmail(),
                /*$user->firstname,
                $user->lastname,
                $user->street,
                $user->street_nr,
                $user->postcode,
                $user->city,
                $user->country ?? '',*/
                $user->active ? 'Ja' : 'Nein',
                $user->last_active,
                $user->created_at,
                $user->updated_at,
                $user->company_name,
                $user->contact_person,
                $user->company_uid,
                $user->company_street,
                $user->company_zip,
                $user->company_city,
                $user->company_website,
                $user->company_email,
                $user->company_phone,
                $user->filter_categories,
                $user->filter_cantons,
                $user->filter_regions,
                $user->filter_languages,
                $user->filter_absences,
                $user->filter_custom_zip,
                //$user->getAcc,
                $user->auto_purchase ? 'Ja' : 'Nein',
                $user->email_text,
                $user->stripe_customer_id,
                $user->payrexx_customer_id,
            ]);
        }

        fclose($output);
        exit;
    }


    public function delete($entity_id=0) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_delete')) {
            return redirect()->to('/');
        }

        if (auth()->user()->id == $entity_id) {
            session()->setFlashdata('error', 'Eigener Benutzer kann nicht gelöscht werden');
            return redirect()->to('/');
        }

        $entity_entity = $this->model_class->getEntry($entity_id);
        if(!is_object($entity_entity)):
            return redirect()->to($this->url_prefix.$this->app_controller . '?model=' . $this->model_name);
        endif;

        $entity_deletable = false;

        if(count($this->model_class->entitiesWithParent($entity_id))<=0):
            $entity_deletable = true;
        else:
            $this->setFlash('Eintrag kann nicht gelöscht werden. Bitte erste die Kinder Einträge löschen.','error');
        endif;

        if($entity_deletable):
            $this->model_class->where($this->model_class->getPrimaryKeyField(), $entity_entity->getPrimaryKeyValue($this->model_class->getPrimaryKeyField()))->delete();
            $this->setFlash('Eintrag gelöscht.','success');
        endif;

        return redirect()->to($this->url_prefix.$this->app_controller . '?model=' . $this->model_name);
    }



}
