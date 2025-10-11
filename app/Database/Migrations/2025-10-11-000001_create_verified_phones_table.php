<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVerifiedPhonesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Normalisierte Telefonnummer (z.B. +41791234567)',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Email-Adresse des Benutzers (optional)',
            ],
            'verified_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'Zeitpunkt der Verifizierung',
            ],
            'verify_method' => [
                'type'       => 'ENUM',
                'constraint' => ['sms', 'call'],
                'null'       => true,
                'comment'    => 'Methode der Verifizierung (SMS oder Anruf)',
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Platform (z.B. my_offertenschweiz_ch)',
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

        // Index für schnelle Suche nach Telefonnummer
        $this->forge->addKey('phone');

        // Index für Suche nach Email + Telefon (für Multi-Service-Anfragen)
        $this->forge->addKey(['email', 'phone']);

        // Index für Platform-spezifische Suche
        $this->forge->addKey('platform');

        $this->forge->createTable('verified_phones');
    }

    public function down()
    {
        $this->forge->dropTable('verified_phones');
    }
}
