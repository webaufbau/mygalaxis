<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewEmailFields extends Migration
{
    public function up()
    {
        $fields = [
            'review_email_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'review_reminder_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ];

        $this->forge->addColumn('offers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('offers', ['review_email_sent_at', 'review_reminder_sent_at']);
    }
}
