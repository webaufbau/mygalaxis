<?php
namespace App\Models;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends \CodeIgniter\Shield\Models\UserModel {
    protected $title = 'Benutzer';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\User::class;
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $afterFind = ['fetchIdentities'];
    protected $afterInsert = ['saveEmailIdentity'];
    protected $afterUpdate = ['saveEmailIdentity', 'saveGroups', 'savePermissions'];

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
            'filter_address',
            'filter_cantons',
            'filter_languages',
            'filter_absences',
            'account_balance',
            'auto_purchase',
            'email_text',
        ];
    }


    public function save($row): bool
    {
        if(property_exists($row, 'email') && $row->email) {
            $row->email_text = $row->email;
        }

        return parent::save($row);
    }

    protected function saveGroups(array $data): array {
        $request = service('request');

        $posted_data = $request->getPost('data');
        if(isset($posted_data['user_group']) && is_array($posted_data['user_group'])) {
            $user = $this->find($data['id'][0]);
            $user->syncGroups(...$posted_data['user_group']);
            $this->save($user);
        }

        return $data;
    }

    protected function savePermissions(array $data): array {
        $request = service('request');

        $posted_data = $request->getPost('data');
        if(isset($data['id'][0])) {
            $user = $this->find($data['id'][0]);
            if (isset($posted_data['user_permissions']) && is_array($posted_data['user_permissions'])) {
                $user->syncPermissions(...array_keys($posted_data['user_permissions']));
            } else {
                $user->removePermission(...$user->getPermissions());
            }
            $this->save($user);
        }

        return $data;
    }

}
