<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMonthlyInvoicesTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'invoice_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'z.B. MRECH-2025-11-123',
            ],
            'period' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'comment' => 'Format: YYYY-MM',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Bruttobetrag inkl. MWST',
            ],
            'purchase_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Anzahl Käufe in diesem Monat',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('period');
        $this->forge->addUniqueKey(['user_id', 'period'], 'unique_user_period');

        $this->forge->createTable('monthly_invoices');
    }

    public function down()
    {
        $this->forge->dropTable('monthly_invoices');
    }
}
