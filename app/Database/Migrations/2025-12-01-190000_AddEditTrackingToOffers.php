<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEditTrackingToOffers extends Migration
{
    public function up()
    {
        // Bearbeitungs-Tracking Felder
        $this->forge->addColumn('offers', [
            'edited_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by',
            ],
            'edited_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'edited_at',
            ],
            // Ausgewählte Firmen für diese Offerte (JSON Array von User-IDs)
            'selected_companies' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'edited_by',
            ],
            // Ausgewählte Akquise-Firmen (JSON Array von E-Mails/Daten)
            'acquisition_companies' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'selected_companies',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', ['edited_at', 'edited_by', 'selected_companies', 'acquisition_companies']);
    }
}
