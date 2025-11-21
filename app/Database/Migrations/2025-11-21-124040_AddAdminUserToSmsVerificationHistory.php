<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminUserToSmsVerificationHistory extends Migration
{
    public function up()
    {
        $this->forge->addColumn('sms_verification_history', [
            'admin_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'platform',
                'comment' => 'Admin User ID bei manueller Freigabe',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('sms_verification_history', 'admin_user_id');
    }
}
