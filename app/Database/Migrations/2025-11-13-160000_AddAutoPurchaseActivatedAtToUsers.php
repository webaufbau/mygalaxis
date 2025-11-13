<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAutoPurchaseActivatedAtToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'auto_purchase_activated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'auto_purchase',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'auto_purchase_activated_at');
    }
}
