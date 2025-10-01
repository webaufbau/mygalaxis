<?php
namespace App\Validation;

class MyGalaxisRules
{
    public static function emailUniqueWithPortal(string $str, string $fields, array $data): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('auth_identities');
        return $builder->where('secret', $str)->countAllResults() === 0;
    }
}
