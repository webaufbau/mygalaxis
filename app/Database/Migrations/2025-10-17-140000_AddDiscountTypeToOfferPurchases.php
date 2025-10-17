<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountTypeToOfferPurchases extends Migration
{
    public function up()
    {
        // Add discount_type column to offer_purchases table
        $fields = [
            'discount_type' => [
                'type' => 'ENUM',
                'constraint' => ['normal', 'discount_1', 'discount_2'],
                'null' => false,
                'default' => 'normal',
                'after' => 'price_paid',
                'comment' => 'Preistyp: normal (0%), discount_1 (<=20%), discount_2 (>20%)'
            ]
        ];

        $this->forge->addColumn('offer_purchases', $fields);
    }

    public function down()
    {
        // Remove discount_type column from offer_purchases table
        $this->forge->dropColumn('offer_purchases', 'discount_type');
    }
}
