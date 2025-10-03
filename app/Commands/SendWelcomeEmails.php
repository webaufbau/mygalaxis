<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Libraries\CompanyWelcomeMailer;

class SendWelcomeEmails extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'email:welcome';
    protected $description = 'Sendet Willkommens-Emails an alle Firmen, die noch keine erhalten haben.';

    public function run(array $params)
    {
        // Aktuelle Platform aus Hostname ermitteln
        $hostname = $_SERVER['HTTP_HOST'] ?? gethostname();
        $currentPlatform = str_replace(['.', '-'], '_', $hostname);

        CLI::write('Platform: ' . $currentPlatform, 'cyan');
        CLI::write('Suche nach Firmen ohne Willkommens-Email...', 'yellow');

        $userModel = new UserModel();
        $mailer = new CompanyWelcomeMailer();

        // Hole alle User die noch keine Welcome-Mail erhalten haben
        // und die in der Gruppe "user" sind
        // UND deren platform zur aktuellen Platform passt
        $users = $userModel
            ->select('users.*')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'user')
            ->where('users.platform', $currentPlatform)
            ->where('users.welcome_email_sent', null)
            ->where('users.active', 1)
            ->findAll();

        if (empty($users)) {
            CLI::write('✅ Keine Firmen gefunden, die eine Willkommens-Email benötigen.', 'green');
            return;
        }

        CLI::write('Gefunden: ' . count($users) . ' Firma(en)', 'cyan');
        CLI::newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            CLI::write('Sende Email an: ' . $user->getEmail() . ' (' . ($user->company_name ?? 'Keine Firma') . ')...', 'white');

            try {
                if ($mailer->sendWelcomeEmail($user)) {
                    CLI::write('  ✅ Erfolgreich gesendet', 'green');
                    $successCount++;
                } else {
                    CLI::write('  ❌ Fehler beim Senden', 'red');
                    $errorCount++;
                }
            } catch (\Exception $e) {
                CLI::write('  ❌ Exception: ' . $e->getMessage(), 'red');
                $errorCount++;
            }

            CLI::newLine();
        }

        CLI::newLine();
        CLI::write('===========================================', 'cyan');
        CLI::write('Zusammenfassung:', 'cyan');
        CLI::write('  Erfolgreich: ' . $successCount, 'green');
        CLI::write('  Fehler:      ' . $errorCount, 'red');
        CLI::write('  Gesamt:      ' . count($users), 'white');
        CLI::write('===========================================', 'cyan');
    }
}
