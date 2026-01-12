<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaitlistToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'on_waitlist' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'auto_purchase_activated_at'
            ],
            'waitlist_joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'on_waitlist'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['on_waitlist', 'waitlist_joined_at']);
    }
}
