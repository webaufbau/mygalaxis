<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldDisplayTemplateToEmailTemplates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('email_templates', [
            'field_display_template' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Template für Feld-Darstellung mit [if...] Bedingungen - wiederverwendbar für Email, Firmen-Ansicht, PDF, etc.',
                'after' => 'body_template',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('email_templates', 'field_display_template');
    }
}
