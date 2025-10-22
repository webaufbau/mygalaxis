<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldRulesToEmailTemplates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('email_templates', [
            'field_rules' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Field Display Rules als JSON - definiert wie bedingte Felder dargestellt werden',
                'after' => 'body_template',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('email_templates', 'field_rules');
    }
}
