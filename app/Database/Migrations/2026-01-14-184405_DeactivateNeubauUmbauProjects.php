<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeactivateNeubauUmbauProjects extends Migration
{
    public function up()
    {
        // Neubau und Umbau deaktivieren - sind nur Verlinkungen von der Website
        $this->db->table('projects')
            ->whereIn('slug', ['new_build', 'renovation'])
            ->update(['is_active' => 0]);
    }

    public function down()
    {
        // Wieder aktivieren
        $this->db->table('projects')
            ->whereIn('slug', ['new_build', 'renovation'])
            ->update(['is_active' => 1]);
    }
}
