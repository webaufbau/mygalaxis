<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTempContactData extends Migration
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
            'uuid' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'contact_data' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('expires_at');

        $this->forge->createTable('temp_contact_data');
    }

    public function down()
    {
        $this->forge->dropTable('temp_contact_data');
    }
}
