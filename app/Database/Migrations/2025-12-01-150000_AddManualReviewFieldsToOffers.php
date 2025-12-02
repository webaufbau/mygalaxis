<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManualReviewFieldsToOffers extends Migration
{
    public function up()
    {
        // Felder für manuelle Prüfung hinzufügen
        $this->forge->addColumn('offers', [
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'form_fields_combo',
            ],
            'customer_hint' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'admin_notes',
                'comment' => 'Hinweis der dem Kunden angezeigt wird',
            ],
            'custom_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'after' => 'customer_hint',
                'comment' => 'Manuell festgelegter Preis (überschreibt berechneten Preis)',
            ],
            'is_test' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'custom_price',
                'comment' => 'Testanfrage - geht nur an Testfirmen',
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'is_test',
                'comment' => 'Zeitpunkt der Admin-Freigabe',
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'approved_at',
                'comment' => 'Admin User ID der freigebenden Person',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', ['admin_notes', 'customer_hint', 'custom_price', 'is_test', 'approved_at', 'approved_by']);
    }
}
