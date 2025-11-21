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
            CLI::write('Group: ' . ($result['group'] ?? 'N/A'));
            CLI::write('Message ID: ' . ($result['messageId'] ?? 'N/A'));

            if (isset($result['error'])) {
                CLI::error('Fehler: ' . $result['error']);
            }

            // Wenn wir eine Message ID haben, prüfe den Delivery Status
            if (!empty($result['messageId'])) {
                CLI::newLine();
                CLI::write('Warte 3 Sekunden und prüfe dann den Zustellstatus...', 'yellow');
                sleep(3);

                CLI::newLine();
                CLI::write('=== Delivery Status Check ===', 'yellow');
                $deliveryStatus = $infobip->checkDeliveryStatus($result['messageId']);

                CLI::write('Delivery Success: ' . ($deliveryStatus['success'] ? 'JA' : 'NEIN'),
                          $deliveryStatus['success'] ? 'green' : 'red');
                CLI::write('Status: ' . ($deliveryStatus['status'] ?? 'N/A'));
                CLI::write('Group: ' . ($deliveryStatus['group'] ?? 'N/A'));
                CLI::write('Description: ' . ($deliveryStatus['description'] ?? 'N/A'));

                if ($deliveryStatus['status'] === 'PENDING_ENROUTE') {
                    CLI::newLine();
                    CLI::write('⚠️  SMS steckt bei PENDING_ENROUTE fest!', 'red');
                    CLI::write('Mögliche Ursachen:', 'yellow');
                    CLI::write('  1. Sender-ID "' . $config->sender . '" ist nicht für Schweiz registriert');
                    CLI::write('  2. Keine SMS-Route für Swisscom/Salt/Sunrise konfiguriert');
                    CLI::write('  3. Account-Guthaben aufgebraucht');
                    CLI::write('  4. Rate Limit erreicht');
                    CLI::write('  5. Sender-ID wurde gesperrt/deaktiviert');
                    CLI::newLine();
                    CLI::write('→ Bitte prüfen Sie Ihr Infobip Dashboard:', 'yellow');
                    CLI::write('  - Account Balance (Guthaben)');
                    CLI::write('  - Sender ID Status');
                    CLI::write('  - SMS Delivery Reports');
                }
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
