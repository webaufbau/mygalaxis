<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConfirmationSentAtToOffers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offers', [
            'confirmation_sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'verified',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', 'confirmation_sent_at');
    }
}
