<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class MailTest extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'mail:test';
    protected $description = 'Sendet eine Test-Email an die angegebene Adresse.';

    public function run(array $params)
    {
        if (empty($params)) {
            CLI::error('Bitte Empfängeradresse angeben. Beispiel: php spark mail:test test@example.com');
            return;
        }

        $recipients = $params; // Alle übergebenen Adressen

        $config = Config('Email');

        $email = Services::email();
        $email->setFrom($config->fromEmail, $config->fromName);
        $email->setTo($recipients);
        $email->setSubject('Test-Mail von CodeIgniter');
        $email->setMessage('Dies ist eine Testmail, gesendet am ' . date('Y-m-d H:i:s'));

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if ($email->send()) {
            CLI::write('✅ Testmail erfolgreich an ' . implode(', ', $recipients) . ' gesendet!', 'green');
        } else {
            CLI::error('❌ Fehler beim Senden der Testmail.');
            CLI::write(print_r($email->printDebugger(['headers', 'subject', 'body']), true));
        }
    }
}
