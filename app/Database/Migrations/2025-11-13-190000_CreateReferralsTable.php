<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReferralsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'referrer_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'User ID who made the referral',
            ],
            'referred_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User ID who was referred (null until registration complete)',
            ],
            'referred_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Email of referred user',
            ],
            'referred_company_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Company name of referred user',
            ],
            'referral_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'Affiliate code used for referral',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP address of referred user at registration',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'credited', 'rejected'],
                'default' => 'pending',
                'comment' => 'Status: pending (registered), credited (credit given), rejected (declined)',
            ],
            'credit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 50.00,
                'comment' => 'Credit amount in CHF/EUR',
            ],
            'credited_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When credit was given',
            ],
            'credited_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Admin user ID who gave the credit',
            ],
            'admin_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Admin notes about this referral',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('referrer_user_id');
        $this->forge->addKey('referred_user_id');
        $this->forge->addKey('referral_code');
        $this->forge->addKey('status');

        $this->forge->createTable('referrals');
    }

    public function down()
    {
        $this->forge->dropTable('referrals');
    }
}
