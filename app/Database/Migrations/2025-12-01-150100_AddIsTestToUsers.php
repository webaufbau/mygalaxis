<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsTestToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_test' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'is_blocked',
                'comment' => 'Testfirma - erhält Testanfragen',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['is_test']);
    }
}
