<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Remove legacy credits table and related code
 *
 * The credits table is no longer used - all balance/transaction handling
 * is now done via the bookings table.
 */
class RemoveLegacyCreditsTable extends Migration
{
    public function up()
    {
        // Drop the credits table if it exists
        if ($this->db->tableExists('credits')) {
            $this->forge->dropTable('credits', true);
        }
    }

    public function down()
    {
        // Recreate the credits table structure (for rollback)
        if (!$this->db->tableExists('credits')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                ],
                'type' => [
                    'type' => 'ENUM',
                    'constraint' => ['manual_credit', 'purchase', 'refund', 'auto_charge', 'initial_credit'],
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->createTable('credits');
        }
    }
}
