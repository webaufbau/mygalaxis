<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubtypeToEmailTemplates extends Migration
{
    public function up()
    {
        // Add subtype column to email_templates table
        $fields = [
            'subtype' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'offer_type',
                'comment' => 'Optional subtype for specific variations (e.g., umzug_privat, umzug_firma). NULL = applies to all subtypes',
            ],
        ];

        $this->forge->addColumn('email_templates', $fields);

        // Add index for better query performance
        $this->forge->addKey(['offer_type', 'subtype'], false, false, 'idx_offer_type_subtype');
        $this->db->query('CREATE INDEX idx_offer_type_subtype ON email_templates(offer_type, subtype)');
    }

    public function down()
    {
        // Remove index first
        $this->db->query('DROP INDEX IF EXISTS idx_offer_type_subtype ON email_templates');

        // Remove column
        $this->forge->dropColumn('email_templates', 'subtype');
    }
}
