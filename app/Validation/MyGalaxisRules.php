<?php
namespace App\Validation;

class MyGalaxisRules
{
    public function emailUniqueWithPortal(string $str, string $fields, array $data): bool
    {
        // Prüfe ob E-Mail schon existiert
        $db = \Config\Database::connect();
        $builder = $db->table('auth_identities');
        $exists = $builder->where('secret', $str)->countAllResults() > 0;

        return !$exists;
    }

    public function emailUniqueWithPortalError(): string
    {
        return lang('Auth.isUniqueEmail');
    }
}
