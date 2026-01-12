<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateProjectsAddFormId extends Migration
{
    public function up()
    {
        // form_id Spalte hinzufügen (z.B. "move:0", "cleaning:2")
        $this->forge->addColumn('projects', [
            'form_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'category_type',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('projects', 'form_id');
    }
}
