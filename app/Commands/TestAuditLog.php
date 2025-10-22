<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAuditLog extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'test:audit-log';
    protected $description = 'Testet das Audit Log System';

    public function run(array $params)
    {
        helper('audit');

        CLI::write('Teste Audit Log System...', 'yellow');

        // Test 1: Formular Submit
        auditLog(
            'form_submit_test',
            'Test: Formular wurde ausgefüllt - Typ: Umzug',
            [
                'uuid' => 'test-uuid-12345',
                'email' => 'test@example.com',
                'phone' => '+41791234567',
            ],
            [
                'form_data' => ['type' => 'move', 'zip' => '8000'],
                'additional_service' => 'Ja',
            ]
        );
        CLI::write('✓ Test 1: Form Submit geloggt', 'green');

        // Test 2: Weiterleitung
        auditLog(
            'redirect_to_next_form',
            'Test: Weiterleitung zu nächstem Formular (offertenschweiz.ch/reinigung)',
            [
                'uuid' => 'test-uuid-12345',
                'group_id' => 'test-group-abc',
            ],
            [
                'redirect_url' => 'https://offertenschweiz.ch/reinigung',
                'get_params' => ['uuid' => 'test-uuid-12345', 'vorname' => 'Max'],
            ]
        );
        CLI::write('✓ Test 2: Redirect geloggt', 'green');

        // Test 3: Verifikation
        auditLog(
            'verification_sms_sent',
            'Test: SMS-Verifikation gesendet an +41791234567',
            [
                'uuid' => 'test-uuid-12345',
                'phone' => '+41791234567',
            ],
            [
                'method' => 'sms',
                'provider' => 'infobip',
                'code' => '1234',
            ]
        );
        CLI::write('✓ Test 3: Verification SMS geloggt', 'green');

        // Test 4: Email
        auditLog(
            'email_confirmation_sent',
            'Test: Bestätigungsmail gesendet an test@example.com',
            [
                'uuid' => 'test-uuid-12345',
                'offer_id' => 999,
                'email' => 'test@example.com',
                'platform' => 'my_offertenschweiz_ch',
            ],
            [
                'subject' => 'Ihre Anfrage wurde erhalten',
                'template_id' => 5,
            ]
        );
        CLI::write('✓ Test 4: Email Confirmation geloggt', 'green');

        // Logs abrufen
        CLI::newLine();
        CLI::write('Gespeicherte Logs abrufen...', 'cyan');

        $auditModel = new \App\Models\FormAuditLogModel();
        $logs = $auditModel->getLogsByUuid('test-uuid-12345');

        CLI::write('Gefundene Logs: ' . count($logs), 'yellow');
        foreach ($logs as $log) {
            CLI::write("  [{$log['event_type']}] {$log['message']}", 'white');
        }

        CLI::newLine();
        CLI::write('Audit Log System funktioniert!', 'green');
    }
}
