<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAffiliateCodeToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'affiliate_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'unique' => true,
                'comment' => 'Unique affiliate/referral code for this user',
            ],
        ];

        $this->forge->addColumn('users', $fields);

        // Create unique index
        $this->db->query('CREATE UNIQUE INDEX idx_affiliate_code ON users(affiliate_code)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_affiliate_code ON users');
        $this->forge->dropColumn('users', 'affiliate_code');
    }
}
