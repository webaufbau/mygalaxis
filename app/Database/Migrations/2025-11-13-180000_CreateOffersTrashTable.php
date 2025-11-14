<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOffersTrashTable extends Migration
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
            'original_offer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Original offer ID from offers table',
            ],
            'original_table' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'Original table name (e.g., offers_plumbing)',
            ],

            // All fields from offers table
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'original_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'sub_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'discounted_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'buyers' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'bought_by' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of user IDs who bought this offer',
            ],
            'language' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'de',
            ],
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'company' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'work_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'additional_service' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'service_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'uuid' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'zip' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'platform' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'form_fields' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON of form fields',
            ],
            'form_fields_combo' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'headers' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'referer' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'verify_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'from_campaign' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'checked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reminder_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verification_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'form_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'group_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'access_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],

            // Type-specific data stored as JSON
            'type_specific_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON of type-specific fields from offers_plumbing, offers_cleaning, etc.',
            ],

            // Deletion tracking fields
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'deleted_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User ID who deleted this offer',
            ],
            'deletion_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Optional reason for deletion',
            ],

            // Original timestamps
            'original_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'original_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('original_offer_id');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('type');
        $this->forge->addKey('platform');

        $this->forge->createTable('offers_trash');
    }

    public function down()
    {
        $this->forge->dropTable('offers_trash');
    }
}
