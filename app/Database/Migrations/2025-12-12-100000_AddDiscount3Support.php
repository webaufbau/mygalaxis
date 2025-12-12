<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscount3Support extends Migration
{
    public function up()
    {
        // 1. Add discount_3 columns to offers table
        $offerFields = [
            'sales_discount_3' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'after' => 'revenue_discount_2',
                'comment' => 'Anzahl Verkäufe Rabatt 3'
            ],
            'revenue_discount_3' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0,
                'after' => 'sales_discount_3',
                'comment' => 'Umsatz Rabatt 3 > 35% (gesamt)'
            ],
        ];

        $this->forge->addColumn('offers', $offerFields);

        // 2. Modify ENUM for offer_purchases to include discount_3
        // Note: CodeIgniter's forge doesn't support modifying ENUM directly,
        // so we use raw SQL
        $this->db->query("ALTER TABLE offer_purchases MODIFY COLUMN discount_type ENUM('normal', 'discount_1', 'discount_2', 'discount_3') NOT NULL DEFAULT 'normal' COMMENT 'Preistyp: normal (0%), discount_1 (<=20%), discount_2 (21-35%), discount_3 (>35%)'");
    }

    public function down()
    {
        // Remove discount_3 columns from offers
        $this->forge->dropColumn('offers', ['sales_discount_3', 'revenue_discount_3']);

        // Revert ENUM (set discount_3 values to discount_2 first)
        $this->db->query("UPDATE offer_purchases SET discount_type = 'discount_2' WHERE discount_type = 'discount_3'");
        $this->db->query("ALTER TABLE offer_purchases MODIFY COLUMN discount_type ENUM('normal', 'discount_1', 'discount_2') NOT NULL DEFAULT 'normal' COMMENT 'Preistyp: normal (0%), discount_1 (<=20%), discount_2 (>20%)'");
    }
}
