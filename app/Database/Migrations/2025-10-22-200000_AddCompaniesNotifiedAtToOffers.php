<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompaniesNotifiedAtToOffers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offers', [
            'companies_notified_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Timestamp when interested companies were notified about this offer',
                'after'      => 'confirmation_sent_at'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', 'companies_notified_at');
    }
}
