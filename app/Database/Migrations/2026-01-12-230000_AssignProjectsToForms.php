<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssignProjectsToForms extends Migration
{
    public function up()
    {
        // Elektriker-Projekte
        $this->db->query("UPDATE projects SET form_id = 'electrician:0', category_type = 'electrician' WHERE slug IN ('alarm', 'ev_charger', 'smart_home', 'solar', 'locks')");

        // Garten-Projekte (Andere Gartenarbeiten)
        $this->db->query("UPDATE projects SET form_id = 'gardening:3', category_type = 'gardening' WHERE slug IN ('pool', 'carport', 'garage', 'balcony')");

        // Heizung-Projekte
        $this->db->query("UPDATE projects SET form_id = 'heating:0', category_type = 'heating' WHERE slug = 'hvac'");

        // Maler Haus-Projekte
        $this->db->query("UPDATE projects SET form_id = 'painting:1', category_type = 'painting' WHERE slug IN ('stairs', 'doors', 'blinds', 'shading', 'insect_screen', 'winter_garden', 'mold', 'insulation', 'facade', 'roof_renovation')");

        // Sanitär-Projekte
        $this->db->query("UPDATE projects SET form_id = 'plumbing:0', category_type = 'plumbing' WHERE slug IN ('new_build', 'renovation', 'bathroom', 'sauna')");
    }

    public function down()
    {
        // Alle Zuweisungen zurücksetzen
        $this->db->query("UPDATE projects SET form_id = NULL, category_type = NULL");
    }
}
