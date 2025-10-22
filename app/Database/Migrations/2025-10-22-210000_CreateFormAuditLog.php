<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFormAuditLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'UUID der Offerte/Anfrage',
            ],
            'group_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Group ID bei mehreren Offerten',
            ],
            'offer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Offer ID wenn bereits erstellt',
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'z.B. form_submit, redirect, verification_send, verification_confirm, email_sent',
            ],
            'event_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'z.B. form, verification, email',
            ],
            'message' => [
                'type'       => 'TEXT',
                'null'       => false,
                'comment'    => 'Lesbare Beschreibung des Events',
            ],
            'details' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'Zusätzliche strukturierte Daten (GET/POST params, URLs, etc.)',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Telefonnummer wenn relevant',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'E-Mail wenn relevant',
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Platform (z.B. my_offertenschweiz_ch)',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP Adresse des Users',
            ],
            'user_agent' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Browser User Agent',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('uuid');
        $this->forge->addKey('group_id');
        $this->forge->addKey('offer_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('event_category');
        $this->forge->addKey('created_at');

        $this->forge->createTable('form_audit_log');
    }

    public function down()
    {
        $this->forge->dropTable('form_audit_log');
    }
}
