<?php
namespace App\Validation;

class MyGalaxisRules
{
    /**
     * Prüft, ob die E-Mail bereits existiert und gibt eine mehrsprachige Meldung zurück
     */
    public function emailUniqueWithPortal(string $value, array $data, ?string &$error = null): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('auth_identities');

        if ($builder->where('secret', $value)->countAllResults() > 0) {
            // Fehlermeldung aus den Sprachdateien holen
            $error = lang('Auth.isUniqueEmail');
            return false;
        }

        return true;
    }
}