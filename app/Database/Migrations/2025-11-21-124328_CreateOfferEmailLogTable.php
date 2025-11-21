<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfferEmailLogTable extends Migration
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
            'email_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'confirmation, company_notification, discount, reminder, etc.',
            ],
            'recipient_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'recipient_type' => [
                'type' => 'ENUM',
                'constraint' => ['customer', 'company'],
                'null' => false,
                'default' => 'customer',
            ],
            'company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Firma ID wenn an Firma gesendet',
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['sent', 'failed', 'bounced'],
                'null' => false,
                'default' => 'sent',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('offer_id');
        $this->forge->addKey('email_type');
        $this->forge->addKey('recipient_email');
        $this->forge->addKey('sent_at');

        $this->forge->createTable('offer_email_log');
    }

    public function down()
    {
        $this->forge->dropTable('offer_email_log');
    }
}
