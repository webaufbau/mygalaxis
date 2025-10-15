<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyToOffers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offers', [
            'company' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'lastname',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', 'company');
    }
}
