<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        // Projekte-Tabelle erstellen
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'name_de' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'name_en' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'name_fr' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'name_it' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'form_link' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#6c757d',
            ],
            'category_types' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('projects');

        // Filter-Projekte-Spalte zu users hinzufügen
        $this->forge->addColumn('users', [
            'filter_projects' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'filter_categories',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('projects');
        $this->forge->dropColumn('users', 'filter_projects');
    }
}
