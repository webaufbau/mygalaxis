<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendUserPaymentMethodsForMultiCard extends Migration
{
    public function up()
    {
        // Felder für Multi-Card Support hinzufügen
        $fields = [
            'is_primary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'payment_method_code',
                'comment' => 'Primary card for auto-purchase and default payments'
            ],
            'card_last4' => [
                'type' => 'VARCHAR',
                'constraint' => 4,
                'null' => true,
                'after' => 'is_primary',
                'comment' => 'Last 4 digits of card number'
            ],
            'card_brand' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'card_last4',
                'comment' => 'Card brand (Visa, Mastercard, TWINT, etc.)'
            ],
            'card_expiry' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
                'after' => 'card_brand',
                'comment' => 'Card expiry in format MM/YYYY'
            ],
        ];

        $this->forge->addColumn('user_payment_methods', $fields);

        // Index für is_primary hinzufügen
        $this->forge->addKey(['user_id', 'is_primary'], false, false, 'idx_user_primary');
        $this->db->query('ALTER TABLE `user_payment_methods` ADD INDEX `idx_user_primary` (`user_id`, `is_primary`)');
    }

    public function down()
    {
        // Index entfernen
        $this->db->query('ALTER TABLE `user_payment_methods` DROP INDEX `idx_user_primary`');

        // Felder entfernen
        $this->forge->dropColumn('user_payment_methods', ['is_primary', 'card_last4', 'card_brand', 'card_expiry']);
    }
}
