<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEditTokensTable extends Migration
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
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'offer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'form_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'user',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('token');
        $this->forge->addKey('offer_id');
        $this->forge->addKey('expires_at');

        $this->forge->createTable('edit_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('edit_tokens');
    }
}
