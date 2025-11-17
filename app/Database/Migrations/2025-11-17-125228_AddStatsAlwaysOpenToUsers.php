<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatsAlwaysOpenToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'stats_always_open' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'null' => false,
                'after' => 'email_notifications_enabled'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'stats_always_open');
    }
}
