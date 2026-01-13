<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequestSessionIdToOffers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offers', [
            'request_session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'group_id',
            ],
        ]);

        // Index für schnellere Suche
        $this->db->query('CREATE INDEX idx_offers_request_session_id ON offers(request_session_id)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_offers_request_session_id ON offers');
        $this->forge->dropColumn('offers', 'request_session_id');
    }
}
