<?php

namespace App\Commands;

use App\Entities\User;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

class CreateUser extends BaseCommand
{
    protected $group       = 'shield';
    protected $name        = 'shield:create-user';
    protected $description = 'Erstellt einen neuen Benutzer mit email/password Identity und optionaler Gruppe.';

    protected $usage = 'shield:create-user [email] [passwort] [gruppe]';

    public function run(array $params)
    {
        $email    = $params[0] ?? CLI::prompt('E-Mail', null, 'required');
        $password = $params[1] ?? CLI::prompt('Passwort', null, 'required');
        // Gruppe als 3. Parameter (optional)
        $groupArg = $params[2] ?? CLI::prompt('Gruppe') ?? null;

        $userModel     = new UserModel();
        $identityModel = new UserIdentityModel();

        // Prüfen, ob Identity mit dieser E-Mail bereits existiert
        $existing = $identityModel
            ->where('type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->where('secret', $email)
            ->first();

        if ($existing !== null) {
            CLI::error("❌ Ein Benutzer mit dieser E-Mail existiert bereits.");
            return;
        }

        // Benutzer erstellen (ohne username, aber explizit null setzen)
        $user = new User([
            'username' => $email,
            'active'    => 1,
            'email_text' => $email,
        ]);
        $saved = $userModel->save($user);
        if (! $saved) {
            // Validierungsfehler abfragen
            $errors = $userModel->errors();
            CLI::error('Speichern fehlgeschlagen: ' . implode(', ', $errors));
            return;
        }

        // Frisch erstellten Benutzer abrufen
        $user = $userModel->findById($userModel->getInsertID());

        // Identity anlegen (email/password)
        $identityModel->insert([
            'user_id'  => $user->id,
            'type'     => Session::ID_TYPE_EMAIL_PASSWORD,
            'secret'   => $email,
            'secret2'  => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // Gruppe zuweisen
        $group = $groupArg ?? CLI::prompt('Gruppe (leer für Standard)', setting('AuthGroups.defaultGroup'));

        $groupModel = new GroupModel();
        $groups     = $groupModel->findAll();
        $groupNames = array_column($groups, 'group');

        if (!in_array($group, $groupNames, true)) {
            CLI::error("⚠️ Gruppe '$group' existiert nicht. Verfügbare Gruppen: " . implode(', ', $groupNames));
            return;
        }

        $user->addGroup($group);

        CLI::write("✅ Benutzer erfolgreich erstellt:", 'green');
        CLI::write("- ID: {$user->id}");
        CLI::write("- E-Mail: {$email}");
        CLI::write("- Gruppe: {$group}");
    }
}
