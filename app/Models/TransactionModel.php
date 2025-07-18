<?php namespace App\Models;

class TransactionModel extends BaseModel {
    protected $DBGroup = 'default';
    protected $table = 'transaction';
    protected $title = 'Transaktionen';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $insertID = 0;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id',
        'uuid',
        'time',
        'status',
        'lang',
        'psp',
        'pspId',
        'amount',
        'payrexx_fee',
        'preAuthorizationId',
        'payment_brand',
        'payment_cardNumber',
        'payment_expiry',
        'payment_wallet',
        'referenceId',
        'metadata',
        'subscription_id',
        'invoice_id',
        'contact_id',
        'refundable',
        'partially_refundable',
        'purchaseOnInvoiceInformation',
        'instanceName',
        'instanceUuid',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'uuid' => 'required|string|max_length[255]',
        'time' => 'required|valid_date[Y-m-d H:i:s]',
        'status' => 'required|in_list[waiting,confirmed,cancelled,declined,authorized,reserved,refunded,refundpending,partially-refunded,chargeback,error,uncaptured]',
        'lang' => 'required|string|max_length[5]',
        'psp' => 'required|string|max_length[255]',
        'pspId' => 'required|integer',
        'amount' => 'required|integer',
        'payrexx_fee' => 'required|integer',
        'preAuthorizationId' => 'permit_empty|integer',
        'payment_brand' => 'required|string|max_length[50]',
        'payment_cardNumber' => 'required|string|max_length[20]',
        'payment_expiry' => 'required|string|max_length[5]',
        'payment_wallet' => 'permit_empty|string|max_length[50]',
        'referenceId' => 'permit_empty|string|max_length[255]',
        'metadata' => 'permit_empty',
        'subscription_id' => 'permit_empty|integer',
        'invoice_id' => 'permit_empty|integer',
        'contact_id' => 'permit_empty|integer',
        'refundable' => 'required|boolean',
        'partially_refundable' => 'required|boolean',
        'purchaseOnInvoiceInformation' => 'permit_empty|string',
        'instanceName' => 'required|string|max_length[255]',
        'instanceUuid' => 'required|string|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeChange'];
    protected $beforeUpdate = ['beforeChange'];

    protected $afterInsert = ['indexAfterInsert'];
    protected $afterUpdate = ['indexAfterUpdate'];

    public function getTableHeader() {
        return [
            'Transaktionsdatum',
            'Benutzer',
            'Betrag',
            'Zahlungsart',
            'Transaktions-ID',
            'Referenznummer',
            'Status',
        ];
    }

    public function getEntries($limit = 100, $offset = 0) {
        $query_elements = $this;
        $query_elements->orderBy('transaction_date', 'DESC');
        $query_elements = $query_elements->findAll($limit, $offset);

        return $query_elements;
    }

    public function getTotalEntries() {
        $query_elements = $this;
        $count_all_results = $query_elements->countAllResults(false);

        return $count_all_results;
    }

    public function getTableFields($entity) {
        $payment_details = '';

        // Zahlungsdetails sammeln
        if(!is_null($entity->transaction_date)) {
            $payment_details .= ' ' . date("d.m.Y H:i", strtotime($entity->transaction_date));
        }
        if($entity->transaction_type !== '') {
            $payment_details .= ' ' . $entity->transaction_type;
        }
        if($entity->transaction_notes !== '') {
            $payment_details .= ' ' . $entity->transaction_notes;
        }

        $get_user_full_name = 'Kein Name';
        $get_user = $entity->getUser();
        if($get_user) {
            $get_user_full_name = $get_user->getFullname();
        }

        return [
            date("d.m.Y H:i", strtotime($entity->created_at)),
            anchor(site_url('admin/user/form/' . $entity->user_id), $get_user_full_name, 'target="_blank"'),
            $entity->amount,
            $entity->transaction_type,
            $entity->transaction_id,
            $entity->transaction_reference,
            $entity->status == 1 ? 'Erfolgreich' : 'Fehlgeschlagen',
        ];
    }

    public function getFormConfiguration($entity = null, $request = null) {
        if (!$entity) {
            $entity = $this->getEntity();
        }
        if (!$request) {
            $request = service('request');
        }

        $form_data = [
            'tabs' => [
                'general' => 'Allgemein',
            ],
            'fields' => [
                'general' => [
                    'user_id' => [
                        'type' => 'dropdown_db',
                        'json_url' => 'admin/user/json',
                        'results_on_query' => true,
                        'label' => 'Benutzer',
                        'required' => 'required',
                    ],
                    'transaction_type' => [
                        'type' => 'dropdown',
                        'label' => 'Zahlungsart',
                        'options' => [
                            'invoice' => 'Rechnung',
                            'payrexx' => 'Payrexx Online Zahlung',
                            'apple-app' => 'Apple iOS App',
                            'android-app' => 'Google Android App',
                        ],
                    ],
                    'transaction_id' => [
                        'type' => 'text',
                        'label' => 'Transaction-ID',
                        'rows' => '5',
                        'class' => 'h-100',
                    ],
                    'transaction_reference' => [
                        'type' => 'text',
                        'label' => 'Transaktions-Referenz',
                    ],
                    'transaction_date' => [
                        'type' => 'datetime',
                        'label' => 'Transaktionsdatum',
                    ],
                    'amount' => [
                        'type' => 'number',
                        'step' => '0.01',
                        'label' => 'Betrag',
                    ],
                    'status' => [
                        'type' => 'dropdown',
                        'label' => 'Status',
                        'options' => [
                            1 => 'Erfolgreich',
                            0 => 'Fehlgeschlagen',
                        ],
                    ],
                ],
            ],
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

        $filter_configuration = [];

        return $filter_configuration;
    }

    public function beforeChange(array $data) {
        if(isset($data['data']['user_id']) && (int)$data['data']['user_id']>0) {
            $user_model = new \App\Models\UserModel();
            $user = $user_model->find($data['data']['user_id']);
            if($user) {
                $data['data']['user_fullname'] = $user->getFullname();
            }
        }

        return $data;
    }

    // Methode zur Aktualisierung des Status
    public function updateStatus($transactionId, $status) {
        return $this->update($transactionId, ['status' => $status]);
    }

}
