<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fügt capture_id zu saferpay_transactions hinzu für Refund-Funktionalität
 */
class AddCaptureIdToSaferpayTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('saferpay_transactions', [
            'capture_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'transaction_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('saferpay_transactions', 'capture_id');
    }
}
