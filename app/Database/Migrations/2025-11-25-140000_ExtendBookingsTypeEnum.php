<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Erweitert das type ENUM in bookings um neue Typen für Refunds und Admin-Aktionen
 */
class ExtendBookingsTypeEnum extends Migration
{
    public function up()
    {
        // ENUM erweitern um neue Typen
        $this->db->query("ALTER TABLE bookings MODIFY COLUMN type ENUM('purchase', 'offer_purchase', 'topup', 'credit', 'subscription', 'refund', 'refund_purchase', 'adjustment', 'admin_credit') NOT NULL");
    }

    public function down()
    {
        // Zurück zum alten ENUM (Achtung: Daten mit neuen Typen gehen verloren!)
        $this->db->query("ALTER TABLE bookings MODIFY COLUMN type ENUM('purchase', 'offer_purchase', 'topup', 'credit', 'subscription') NOT NULL");
    }
}
