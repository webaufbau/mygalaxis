<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserNotesTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'admin_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Admin der die Notiz erstellt hat',
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['phone', 'email'],
                'default' => 'phone',
            ],
            'note_text' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('admin_user_id');
        $this->forge->addKey('created_at');

        $this->forge->createTable('user_notes');
    }

    public function down()
    {
        $this->forge->dropTable('user_notes');
    }
}
