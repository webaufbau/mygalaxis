<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Audit Log für Einstellungsänderungen und andere Admin-Aktionen
 */
class CreateAuditLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'z.B. settings_update, user_delete, offer_refund',
            ],
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'z.B. settings, user, booking',
            ],
            'entity_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Alte Werte vor der Änderung',
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Neue Werte nach der Änderung',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('entity_type');
        $this->forge->addKey('created_at');

        $this->forge->createTable('audit_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
    }
}
