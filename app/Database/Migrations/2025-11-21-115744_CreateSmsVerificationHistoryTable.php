<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmsVerificationHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'offer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'uuid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'verification_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false,
            ],
            'method' => [
                'type' => 'ENUM',
                'constraint' => ['sms', 'call'],
                'null' => false,
                'default' => 'sms',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Infobip/Twilio Status (z.B. PENDING_ACCEPTED, DELIVERED_TO_HANDSET)',
            ],
            'message_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Infobip/Twilio Message ID',
            ],
            'platform' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'verified' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 = Code wurde korrekt eingegeben',
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('offer_id');
        $this->forge->addKey('uuid');
        $this->forge->addKey('phone');
        $this->forge->addKey('created_at');

        $this->forge->createTable('sms_verification_history');
    }

    public function down()
    {
        $this->forge->dropTable('sms_verification_history');
    }
}
