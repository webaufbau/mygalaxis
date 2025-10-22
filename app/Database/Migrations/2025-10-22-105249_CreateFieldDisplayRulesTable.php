<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFieldDisplayRulesTable extends Migration
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
            'rule_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Eindeutiger Schlüssel für die Rule (z.B. bodenplatten_vorplatz_gruppe)',
            ],
            'offer_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'default',
                'comment' => 'Branche/Offer-Type (default, umzug, reinigung, gartenbau, etc.)',
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Anzeige-Label (z.B. "Bodenplatten: Vorplatz / Garage")',
            ],
            'conditions' => [
                'type' => 'JSON',
                'comment' => 'JSON-Array mit Bedingungen: [{"when": {...}, "display": "..."}]',
            ],
            'fields_to_hide' => [
                'type' => 'JSON',
                'comment' => 'JSON-Array mit Feldnamen die versteckt werden sollen',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1 = aktiv, 0 = inaktiv',
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Sortierreihenfolge (niedrigere Zahl = höhere Priorität)',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Interne Notizen für Admins',
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
        $this->forge->addUniqueKey(['rule_key', 'offer_type'], 'unique_rule_per_offer_type');
        $this->forge->addKey('offer_type');
        $this->forge->addKey('is_active');

        $this->forge->createTable('field_display_rules');
    }

    public function down()
    {
        $this->forge->dropTable('field_display_rules');
    }
}
