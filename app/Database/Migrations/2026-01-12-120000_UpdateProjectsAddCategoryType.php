<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateProjectsAddCategoryType extends Migration
{
    public function up()
    {
        // category_type Spalte hinzufügen
        $this->forge->addColumn('projects', [
            'category_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'name_it',
            ],
        ]);

        // form_link und color Spalten entfernen (optional, nur wenn gewünscht)
        // $this->forge->dropColumn('projects', 'form_link');
        // $this->forge->dropColumn('projects', 'color');
    }

    public function down()
    {
        $this->forge->dropColumn('projects', 'category_type');
    }
}
