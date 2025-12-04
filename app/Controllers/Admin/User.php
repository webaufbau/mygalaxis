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

    public function detail($id)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userModel = new \App\Models\UserModel();
        $targetUser = $userModel->find($id);

        if (!$targetUser) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Benutzer nicht gefunden.");
        }

        // Hole alle Käufe (Bookings) des Benutzers - nur aktive (nicht stornierte)
        $bookingModel = new \App\Models\BookingModel();
        $allPurchases = $bookingModel
            ->where('user_id', $id)
            ->where('type', 'offer_purchase')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Hole Angebots-Details und Status für jeden Kauf
        $offerModel = new \App\Models\OfferModel();
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
        $purchases = [];
        foreach ($allPurchases as $purchase) {
            $offer = $offerModel->find($purchase['reference_id']);
            $purchase['offer'] = $offer;

            // Hole Status aus offer_purchases
            $offerPurchase = $offerPurchaseModel
                ->where('user_id', $id)
                ->where('offer_id', $purchase['reference_id'])
                ->first();
            $purchase['purchase_status'] = $offerPurchase['status'] ?? 'active';

            // Nur aktive Käufe anzeigen (nicht stornierte)
            if ($purchase['purchase_status'] !== 'refunded') {
                $purchases[] = $purchase;
            }
        }

        // Hole alle Transaktionen (Bookings)
        $bookingModel = new \App\Models\BookingModel();
        $transactions = $bookingModel
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Hole Saferpay-Transaktionen für Refund-Funktionalität
        $db = \Config\Database::connect();
        $saferpayTransactions = $db->table('saferpay_transactions')
            ->where('user_id', $id)
            ->where('status', 'CAPTURED')
            ->get()
            ->getResultArray();

        // Erstelle ein Mapping von Betrag+Zeitraum zu capture_id für einfache Zuordnung
        $saferpayMap = [];
        foreach ($saferpayTransactions as $st) {
            // Key aus User-ID, Betrag (in CHF, von Rappen konvertiert) und ungefährem Datum
            $amountChf = ($st['amount'] ?? 0) / 100;
            $date = date('Y-m-d', strtotime($st['created_at']));
            $key = $id . '_' . $amountChf . '_' . $date;
            $saferpayMap[$key] = $st;
        }

        // Ermittle bereits stornierte Käufe (reference_id zeigt auf die Offer-ID)
        // Wir sammeln die Offer-IDs, die bereits storniert wurden
        $refundedOfferIds = [];
        foreach ($transactions as $t) {
            if ($t['type'] === 'refund_purchase' && !empty($t['reference_id'])) {
                $refundedOfferIds[$t['reference_id']] = true;
            }
        }

        // Ermittle bereits rückerstattete Topups (reference_id zeigt auf die Booking-ID)
        $refundedTopupIds = [];
        foreach ($transactions as $t) {
            if ($t['type'] === 'refund' && !empty($t['reference_id'])) {
                $refundedTopupIds[$t['reference_id']] = true;
            }
        }

        // Berechne Kontostand
        $balance = $bookingModel->getUserBalance($id);

        // Hole Bewertungen des Benutzers
        $reviewModel = new \App\Models\ReviewModel();
        $reviews = $reviewModel
            ->where('recipient_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Hole Agenda (Abwesenheiten / blocked days) des Benutzers
        $blockedDayModel = new \App\Models\BlockedDayModel();
        $blockedDays = $blockedDayModel
            ->where('user_id', $id)
            ->orderBy('date', 'DESC')
            ->findAll();

        // Parse User-Filter
        $filterCategories = [];
        if (!empty($targetUser->filter_categories)) {
            $filterCategories = is_string($targetUser->filter_categories)
                ? explode(',', $targetUser->filter_categories)
                : $targetUser->filter_categories;
        }

        $filterCantons = [];
        if (!empty($targetUser->filter_cantons)) {
            $filterCantons = is_string($targetUser->filter_cantons)
                ? explode(',', $targetUser->filter_cantons)
                : json_decode($targetUser->filter_cantons, true) ?? [];
        }

        $filterRegions = [];
        if (!empty($targetUser->filter_regions)) {
            $filterRegions = is_string($targetUser->filter_regions)
                ? explode(',', $targetUser->filter_regions)
                : json_decode($targetUser->filter_regions, true) ?? [];
        }

        $filterLanguages = [];
        if (!empty($targetUser->filter_languages)) {
            $filterLanguages = is_string($targetUser->filter_languages)
                ? json_decode($targetUser->filter_languages, true)
                : $targetUser->filter_languages;
        }

        // Hole Notizen für diesen Benutzer
        $userNoteModel = new \App\Models\UserNoteModel();

        // Filter aus Request holen
        $noteType = $this->request->getGet('note_type') ?? 'all';
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        $notes = $userNoteModel->getNotesForUser($id, $noteType, $dateFrom, $dateTo);
        $noteCounts = $userNoteModel->countByType($id);

        $data = [
            'user' => $targetUser,
            'purchases' => $purchases,
            'transactions' => $transactions,
            'saferpayMap' => $saferpayMap,
            'refundedOfferIds' => $refundedOfferIds,
            'refundedTopupIds' => $refundedTopupIds,
            'balance' => $balance,
            'reviews' => $reviews,
            'blockedDays' => $blockedDays,
            'filterCategories' => $filterCategories,
            'filterCantons' => $filterCantons,
            'filterRegions' => $filterRegions,
            'filterLanguages' => $filterLanguages,
            'notes' => $notes,
            'noteCounts' => $noteCounts,
            'noteType' => $noteType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        return view('admin/user_detail', $data);
    }

    public function addNote($id)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userModel = new \App\Models\UserModel();
        $targetUser = $userModel->find($id);

        if (!$targetUser) {
            session()->setFlashdata('error', 'Benutzer nicht gefunden.');
            return redirect()->to('/admin/user');
        }

        $userNoteModel = new \App\Models\UserNoteModel();

        $noteData = [
            'user_id' => $id,
            'admin_user_id' => $user->id,
            'type' => $this->request->getPost('note_type'),
            'note_text' => $this->request->getPost('note_text'),
        ];

        if ($userNoteModel->insert($noteData)) {
            session()->setFlashdata('success', 'Notiz erfolgreich hinzugefügt.');
        } else {
            session()->setFlashdata('error', 'Fehler beim Speichern der Notiz: ' . implode(', ', $userNoteModel->errors()));
        }

        return redirect()->to('/admin/user/' . $id . '#notes');
    }

    public function deleteNote($userId, $noteId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userNoteModel = new \App\Models\UserNoteModel();
        $note = $userNoteModel->find($noteId);

        if (!$note || $note['user_id'] != $userId) {
            session()->setFlashdata('error', 'Notiz nicht gefunden.');
            return redirect()->to('/admin/user/' . $userId . '#notes');
        }

        if ($userNoteModel->delete($noteId)) {
            session()->setFlashdata('success', 'Notiz erfolgreich gelöscht.');
        } else {
            session()->setFlashdata('error', 'Fehler beim Löschen der Notiz.');
        }

        return redirect()->to('/admin/user/' . $userId . '#notes');
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
            $actions .= anchor($this->url_prefix . $this->app_controller . '/' . $entity_entity->{$primary_key_field}, '<i class="bi bi-eye"></i>', 'class="btn btn-default action" title="Details" target="_blank"');
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


    /**
     * Guthaben manuell gutschreiben (Admin)
     */
    public function addCredit($userId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userModel = new \App\Models\UserModel();
        $targetUser = $userModel->find($userId);

        if (!$targetUser) {
            session()->setFlashdata('error', 'Benutzer nicht gefunden.');
            return redirect()->to('/admin/user');
        }

        $amount = floatval($this->request->getPost('amount'));
        $description = trim($this->request->getPost('description'));

        if ($amount <= 0) {
            session()->setFlashdata('error', 'Ungültiger Betrag.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        $bookingModel = new \App\Models\BookingModel();
        $bookingModel->insert([
            'user_id' => $userId,
            'type' => 'admin_credit',
            'amount' => $amount,
            'paid_amount' => 0,
            'description' => 'Admin Gutschrift: ' . $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_message('info', 'Admin Gutschrift erstellt', [
            'user_id' => $userId,
            'amount' => $amount,
            'description' => $description,
            'admin_user' => $user->id,
        ]);

        session()->setFlashdata('success', 'Guthaben von ' . number_format($amount, 2) . ' CHF erfolgreich gutgeschrieben.');
        return redirect()->to('/admin/user/' . $userId . '#finance');
    }

    /**
     * Kauf stornieren / rückerstatten
     */
    public function refundPurchase($userId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $bookingId = $this->request->getPost('booking_id');
        $refundAmount = floatval($this->request->getPost('refund_amount'));
        $refundReason = trim($this->request->getPost('refund_reason'));
        $invalidatePurchase = $this->request->getPost('invalidate_purchase') == '1';

        $bookingModel = new \App\Models\BookingModel();
        $originalBooking = $bookingModel->find($bookingId);

        if (!$originalBooking || $originalBooking['user_id'] != $userId) {
            session()->setFlashdata('error', 'Transaktion nicht gefunden.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        if ($originalBooking['type'] !== 'offer_purchase') {
            session()->setFlashdata('error', 'Nur Angebotskäufe können storniert werden.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        // Rückerstattungs-Buchung erstellen (positiver Betrag)
        $bookingModel->insert([
            'user_id' => $userId,
            'type' => 'refund_purchase',
            'amount' => $refundAmount,
            'paid_amount' => 0,
            'description' => 'Stornierung: ' . $refundReason . ' (Original: ' . $originalBooking['description'] . ')',
            'reference_id' => $originalBooking['reference_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Kauf als storniert markieren, wenn gewünscht
        if ($invalidatePurchase && !empty($originalBooking['reference_id'])) {
            $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
            $offerPurchaseModel->where('offer_id', $originalBooking['reference_id'])
                               ->where('user_id', $userId)
                               ->set(['status' => 'refunded'])
                               ->update();
        }

        log_message('info', 'Kauf storniert/rückerstattet', [
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'refund_amount' => $refundAmount,
            'reason' => $refundReason,
            'invalidated' => $invalidatePurchase,
            'admin_user' => $user->id,
        ]);

        session()->setFlashdata('success', 'Kauf erfolgreich storniert. ' . number_format($refundAmount, 2) . ' CHF wurden gutgeschrieben.');
        return redirect()->to('/admin/user/' . $userId . '#finance');
    }

    /**
     * Aufladung rückerstatten (bei Saferpay-Zahlung)
     */
    public function refundTopup($userId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $bookingId = $this->request->getPost('booking_id');
        $refundAmount = floatval($this->request->getPost('refund_amount'));
        $refundReason = trim($this->request->getPost('refund_reason'));

        // Neue Parameter für automatische Rückerstattung
        $refundMethod = $this->request->getPost('refund_method'); // 'auto' oder 'manual'
        $captureId = $this->request->getPost('capture_id');
        $currency = $this->request->getPost('currency') ?? 'CHF';

        // Legacy: Falls kein refund_method gesetzt, prüfe alte Checkbox
        $saferpayRefunded = $this->request->getPost('saferpay_refunded') == '1';
        if ($refundMethod === 'manual') {
            $saferpayRefunded = true;
        }

        $bookingModel = new \App\Models\BookingModel();
        $originalBooking = $bookingModel->find($bookingId);

        if (!$originalBooking || $originalBooking['user_id'] != $userId) {
            session()->setFlashdata('error', 'Transaktion nicht gefunden.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        if ($originalBooking['type'] !== 'topup') {
            session()->setFlashdata('error', 'Nur Aufladungen können so rückerstattet werden.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        // Automatische Saferpay-Rückerstattung versuchen
        $saferpayRefundSuccess = false;
        $saferpayRefundError = null;

        if ($refundMethod === 'auto' && !empty($captureId)) {
            try {
                $saferpayService = new \App\Services\SaferpayService();
                $amountInCents = (int) ($refundAmount * 100);

                log_message('info', 'Admin Saferpay Refund gestartet', [
                    'user_id' => $userId,
                    'capture_id' => $captureId,
                    'amount_cents' => $amountInCents,
                    'currency' => $currency,
                    'admin_user' => $user->id,
                ]);

                $refundResponse = $saferpayService->refundTransaction($captureId, $amountInCents, $currency);

                // Prüfe ob Refund erfolgreich war
                if (isset($refundResponse['Transaction'])) {
                    $saferpayRefundSuccess = true;
                    $saferpayRefunded = true;

                    log_message('info', 'Admin Saferpay Refund erfolgreich', [
                        'user_id' => $userId,
                        'refund_transaction_id' => $refundResponse['Transaction']['Id'] ?? 'unknown',
                        'admin_user' => $user->id,
                    ]);
                } else {
                    $saferpayRefundError = 'Unerwartete Antwort von Saferpay: ' . json_encode($refundResponse);
                    log_message('error', 'Admin Saferpay Refund fehlgeschlagen', [
                        'user_id' => $userId,
                        'response' => $refundResponse,
                    ]);
                }
            } catch (\Exception $e) {
                $saferpayRefundError = $e->getMessage();
                log_message('error', 'Admin Saferpay Refund Exception: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'capture_id' => $captureId,
                ]);
            }
        }

        // Bei Fehler bei automatischer Rückerstattung: Abbruch mit Fehlermeldung
        if ($refundMethod === 'auto' && !$saferpayRefundSuccess) {
            session()->setFlashdata('error', 'Saferpay Rückerstattung fehlgeschlagen: ' . ($saferpayRefundError ?? 'Unbekannter Fehler') . ' - Bitte manuell im Saferpay Backend durchführen.');
            return redirect()->to('/admin/user/' . $userId . '#finance');
        }

        // Rückerstattungs-Buchung erstellen (negativer Betrag - zieht vom Guthaben ab)
        $description = 'Rückerstattung Aufladung: ' . $refundReason;
        if ($saferpayRefundSuccess) {
            $description .= ' (Saferpay automatisch rückerstattet)';
        } elseif ($saferpayRefunded) {
            $description .= ' (Saferpay manuell rückerstattet)';
        } else {
            $description .= ' (Saferpay-Rückerstattung ausstehend!)';
        }

        $bookingModel->insert([
            'user_id' => $userId,
            'type' => 'refund',
            'amount' => -$refundAmount, // Negativ, weil vom Guthaben abgezogen
            'paid_amount' => $saferpayRefunded ? $refundAmount : 0,
            'description' => $description,
            'reference_id' => $bookingId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_message('info', 'Aufladung rückerstattet', [
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'refund_amount' => $refundAmount,
            'reason' => $refundReason,
            'saferpay_refunded' => $saferpayRefunded,
            'saferpay_auto' => $saferpayRefundSuccess,
            'admin_user' => $user->id,
        ]);

        if ($saferpayRefundSuccess) {
            $message = 'Rückerstattung von ' . number_format($refundAmount, 2) . ' CHF erfolgreich via Saferpay durchgeführt und verbucht.';
        } elseif ($saferpayRefunded) {
            $message = 'Rückerstattung von ' . number_format($refundAmount, 2) . ' CHF verbucht (manuell bestätigt).';
        } else {
            $message = 'Rückerstattung von ' . number_format($refundAmount, 2) . ' CHF verbucht. ACHTUNG: Bitte im Saferpay Backend die Rückerstattung auf die Kreditkarte manuell durchführen!';
        }

        session()->setFlashdata('success', $message);
        return redirect()->to('/admin/user/' . $userId . '#finance');
    }

    /**
     * Firma blockieren / deblockieren
     */
    public function toggleBlock($userId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userModel = new \App\Models\UserModel();
        $targetUser = $userModel->find($userId);

        if (!$targetUser) {
            session()->setFlashdata('error', 'Benutzer nicht gefunden.');
            return redirect()->to('/admin/user');
        }

        // Toggle is_blocked
        $newBlockedStatus = $targetUser->is_blocked ? 0 : 1;
        $userModel->update($userId, ['is_blocked' => $newBlockedStatus]);

        if ($newBlockedStatus) {
            session()->setFlashdata('success', 'Firma wurde blockiert. Die Firma kann sich nicht mehr einloggen und erhält keine neuen Anfragen.');
            log_message('info', 'Firma blockiert', [
                'user_id' => $userId,
                'company_name' => $targetUser->company_name,
                'admin_user' => $user->id,
            ]);
        } else {
            session()->setFlashdata('success', 'Firma wurde deblockiert und kann sich wieder einloggen.');
            log_message('info', 'Firma deblockiert', [
                'user_id' => $userId,
                'company_name' => $targetUser->company_name,
                'admin_user' => $user->id,
            ]);
        }

        return redirect()->to('/admin/user/' . $userId);
    }

    /**
     * Testfirma-Status umschalten
     */
    public function toggleTest($userId)
    {
        $user = auth()->user();
        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/');
        }

        $userModel = new \App\Models\UserModel();
        $targetUser = $userModel->find($userId);

        if (!$targetUser) {
            session()->setFlashdata('error', 'Benutzer nicht gefunden.');
            return redirect()->to('/admin/user');
        }

        // Toggle is_test
        $newTestStatus = $targetUser->is_test ? 0 : 1;
        $userModel->update($userId, ['is_test' => $newTestStatus]);

        if ($newTestStatus) {
            session()->setFlashdata('success', 'Firma wurde als Testfirma markiert. Sie erhält ab jetzt NUR Testanfragen.');
            log_message('info', 'Firma als Testfirma markiert', [
                'user_id' => $userId,
                'company_name' => $targetUser->company_name,
                'admin_user' => $user->id,
            ]);
        } else {
            session()->setFlashdata('success', 'Testfirma-Status wurde entfernt. Die Firma erhält jetzt normale Anfragen.');
            log_message('info', 'Testfirma-Status entfernt', [
                'user_id' => $userId,
                'company_name' => $targetUser->company_name,
                'admin_user' => $user->id,
            ]);
        }

        return redirect()->to('/admin/user/' . $userId);
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
