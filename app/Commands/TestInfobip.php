<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\InfobipService;
use Config\Infobip as InfobipConfig;

class TestInfobip extends BaseCommand
{
    protected $group       = 'Test';
    protected $name        = 'test:infobip';
    protected $description = 'Testet die Infobip SMS-Konfiguration';

    public function run(array $params)
    {
        CLI::write('=== Infobip Konfiguration Test ===', 'yellow');
        CLI::newLine();

        // Config laden
        $config = config(InfobipConfig::class);

        CLI::write('API Host: ' . $config->api_host);
        CLI::write('API Key: ' . (strlen($config->api_key) > 0 ? substr($config->api_key, 0, 15) . '...' : 'EMPTY'),
                   strlen($config->api_key) > 0 ? 'green' : 'red');
        CLI::write('Sender: ' . $config->sender);

        if (empty($config->api_key)) {
            CLI::error('FEHLER: API Key ist leer!');
            CLI::write('Prüfen Sie die .env Datei und stellen Sie sicher, dass infobip.api_key gesetzt ist.');
            return;
        }

        CLI::newLine();
        CLI::write('=== Test SMS-Versand ===', 'yellow');

        try {
            $infobip = new InfobipService();
            CLI::write('InfobipService erfolgreich initialisiert', 'green');

            // Test-Telefonnummer aus Parameter oder Default
            $testPhone = $params[0] ?? '+41788708462';
            $testMessage = 'Test SMS von ' . $config->sender . '. Ihr Code: 1234';

            CLI::write('Sende Test-SMS an: ' . $testPhone);
            log_message('info', 'TEST: Sende SMS an ' . $testPhone);

            $result = $infobip->sendSms($testPhone, $testMessage);

            CLI::newLine();
            CLI::write('=== Ergebnis ===', 'yellow');
            CLI::write('Success: ' . ($result['success'] ? 'JA' : 'NEIN'), $result['success'] ? 'green' : 'red');
            CLI::write('Status: ' . ($result['status'] ?? 'N/A'));
            CLI::write('Message ID: ' . ($result['messageId'] ?? 'N/A'));

            if (isset($result['error'])) {
                CLI::error('Fehler: ' . $result['error']);
            }

            CLI::newLine();
            CLI::write('Prüfen Sie die Logs unter: writable/logs/log-' . date('Y-m-d') . '.log');

        } catch (\Throwable $e) {
            CLI::error('EXCEPTION: ' . $e->getMessage());
            CLI::write('Stack Trace:', 'red');
            CLI::write($e->getTraceAsString());
        }
    }
}
