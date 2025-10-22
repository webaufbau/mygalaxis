<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlatformToUserPaymentMethods extends Migration
{
    public function up()
    {
        $fields = [
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Platform where payment method was created (my_offertenschweiz_ch, my_offertenheld_ch, my_renovo24_ch)',
                'after'      => 'payment_method_code'
            ],
        ];

        $this->forge->addColumn('user_payment_methods', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('user_payment_methods', 'platform');
    }
}
