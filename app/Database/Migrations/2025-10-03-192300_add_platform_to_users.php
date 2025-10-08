<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlatformToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Platform where user registered (e.g. my_offertenheld_ch, my_offertenschweiz_ch, my_renovo24_ch)',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'platform');
    }
}
