<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsAutoPurchaseToOfferPurchases extends Migration
{
    public function up()
    {
        $fields = [
            'is_auto_purchase' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'payment_method'
            ]
        ];

        $this->forge->addColumn('offer_purchases', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('offer_purchases', 'is_auto_purchase');
    }
}
