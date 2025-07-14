<?php namespace App\Models;

class AuthIdentityModel extends BaseModel {
    protected $DBGroup = 'default';
    protected $table = 'auth_identities';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $insertID = 0;
    protected $returnType = 'App\Entities\AuthIdentity';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id',
        'user_id',
        'type',
        'name',
        'secret',
        'secret2',
        'expires',
        'extra',
        'force_reset',
        'last_used_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    // Methods
    public function getAuthIdentity($id = false): object|array|null {
        if ($id === false) {
            return $this->findAll();
        }

        return $this->asObject('App\Entities\AuthIdentity')->where(['user_id' => $id])->first();
    }

    public function getIdentityByEmail($email = false): object|array|null {
        return $this->asObject('App\Entities\AuthIdentity')->where(['secret' => $email])->first();
    }

}

  