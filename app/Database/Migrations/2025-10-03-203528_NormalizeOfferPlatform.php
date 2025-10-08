<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeOfferPlatform extends Migration
{
    public function up()
    {
        // Konvertiere Domain-Format zu Ordner-Format
        // offertenheld.ch -> my_offertenheld_ch
        // renovoscout24.de -> my_renovoscout24_de
        // etc.

        // Alle Offers mit Platform, die NICHT mit 'my_' beginnen
        $query = $this->db->query("SELECT id, platform FROM offers WHERE platform IS NOT NULL AND platform NOT LIKE 'my_%'");

        foreach ($query->getResultArray() as $row) {
            $oldPlatform = $row['platform'];
            // Domain-Format: Punkte durch Underscores ersetzen und my_ voranstellen
            $newPlatform = 'my_' . str_replace('.', '_', $oldPlatform);

            $this->db->query(
                "UPDATE offers SET platform = ? WHERE id = ?",
                [$newPlatform, $row['id']]
            );
        }
    }

    public function down()
    {
        // Rückgängig: Ordner-Format zu Domain-Format
        // my_offertenheld_ch -> offertenheld.ch
        // my_renovoscout24_de -> renovoscout24.de
        // etc.

        $query = $this->db->query("SELECT id, platform FROM offers WHERE platform IS NOT NULL AND platform LIKE 'my_%'");

        foreach ($query->getResultArray() as $row) {
            $oldPlatform = $row['platform'];
            // Entferne 'my_' und ersetze Underscores mit Punkten
            $newPlatform = str_replace('_', '.', substr($oldPlatform, 3));

            $this->db->query(
                "UPDATE offers SET platform = ? WHERE id = ?",
                [$newPlatform, $row['id']]
            );
        }
    }
}
