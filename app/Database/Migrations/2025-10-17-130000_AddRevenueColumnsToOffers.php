<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevenueColumnsToOffers extends Migration
{
    public function up()
    {
        // Add revenue and sales count columns to offers table
        $fields = [
            'sales_normal_price' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'comment' => 'Anzahl Verkäufe Normalpreis'
            ],
            'revenue_normal_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'comment' => 'Umsatz Normalpreis (gesamt)'
            ],
            'sales_discount_1' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'comment' => 'Anzahl Verkäufe Rabatt 1'
            ],
            'revenue_discount_1' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'comment' => 'Umsatz Rabatt 1 <= 20% (gesamt)'
            ],
            'sales_discount_2' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'comment' => 'Anzahl Verkäufe Rabatt 2'
            ],
            'revenue_discount_2' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'comment' => 'Umsatz Rabatt 2 > 20% (gesamt)'
            ],
            'total_revenue' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'comment' => 'Total Umsatz pro Anfrage'
            ]
        ];

        $this->forge->addColumn('offers', $fields);
    }

    public function down()
    {
        // Remove revenue and sales count columns from offers table
        $this->forge->dropColumn('offers', [
            'sales_normal_price',
            'revenue_normal_price',
            'sales_discount_1',
            'revenue_discount_1',
            'sales_discount_2',
            'revenue_discount_2',
            'total_revenue'
        ]);
    }
}
