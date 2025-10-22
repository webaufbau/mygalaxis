<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailNotificationPreferences extends Migration
{
    public function up()
    {
        // Add email notification preferences field
        $this->forge->addColumn('users', [
            'email_notifications_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => 'Enable/disable daily update emails (0=disabled, 1=enabled)',
                'after'      => 'welcome_email_sent'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'email_notifications_enabled');
    }
}
