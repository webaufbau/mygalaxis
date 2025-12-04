<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotifiedCompanyIdsToOfferEmailLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offer_email_log', [
            'notified_company_ids' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'company_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offer_email_log', 'notified_company_ids');
    }
}