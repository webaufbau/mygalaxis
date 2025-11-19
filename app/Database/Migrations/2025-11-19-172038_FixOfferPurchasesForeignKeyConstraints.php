<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixOfferPurchasesForeignKeyConstraints extends Migration
{
    public function up()
    {
        // Drop the constraint without CASCADE
        $this->db->query('ALTER TABLE `offer_purchases` DROP FOREIGN KEY `fk_offer_purchases_user`');

        // The other constraint (offer_purchases_ibfk_1) with ON DELETE CASCADE already exists,
        // so we don't need to recreate anything
    }

    public function down()
    {
        // Recreate the constraint without CASCADE (if we need to rollback)
        $this->db->query('
            ALTER TABLE `offer_purchases`
            ADD CONSTRAINT `fk_offer_purchases_user`
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ');
    }
}
