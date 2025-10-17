<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvgSalePriceToOffers extends Migration
{
    public function up()
    {
        // Add avg_sale_price column to offers table
        $fields = [
            'avg_sale_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'after' => 'discounted_price',
                'comment' => 'Durchschnittlicher Verkaufspreis pro Verkauf'
            ]
        ];

        $this->forge->addColumn('offers', $fields);
    }

    public function down()
    {
        // Remove avg_sale_price column from offers table
        $this->forge->dropColumn('offers', 'avg_sale_price');
    }
}
