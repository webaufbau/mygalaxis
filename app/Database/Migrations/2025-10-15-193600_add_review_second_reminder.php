<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewSecondReminder extends Migration
{
    public function up()
    {
        $fields = [
            'review_second_reminder_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ];

        $this->forge->addColumn('bookings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', ['review_second_reminder_sent_at']);
    }
}
