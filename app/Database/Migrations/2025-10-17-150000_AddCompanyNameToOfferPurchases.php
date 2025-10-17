<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyNameToOfferPurchases extends Migration
{
    public function up()
    {
        $fields = [
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'user_id'
            ],
            'external_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'user_id'
            ]
        ];

        $this->forge->addColumn('offer_purchases', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('offer_purchases', ['company_name', 'external_user_id']);
    }
}
