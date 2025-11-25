<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsBlockedToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_blocked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_blocked');
    }
}
