<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWelcomeEmailSentToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'welcome_email_sent' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Timestamp when welcome email was sent to company',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'welcome_email_sent');
    }
}
