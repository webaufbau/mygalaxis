<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaidAmountToBookings extends Migration
{
    public function up()
    {
        // Neues Feld paid_amount hinzufügen
        $fields = [
            'paid_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'amount',
            ],
        ];

        $this->forge->addColumn('bookings', $fields);

        // Bestehende Daten migrieren: paid_amount = abs(amount) für Guthaben-Käufe
        $db = \Config\Database::connect();

        // Für Guthaben-Käufe (amount < 0): paid_amount = abs(amount)
        $db->query("UPDATE bookings SET paid_amount = ABS(amount) WHERE type = 'offer_purchase' AND amount < 0");

        // Für Kreditkarten-Käufe (amount = 0): Preis aus description extrahieren
        $bookings = $db->query("SELECT id, description FROM bookings WHERE type = 'offer_purchase' AND amount = 0")->getResultArray();

        foreach ($bookings as $booking) {
            // Extrahiere Preis aus "Anfrage gekauft #223 - 21.00 CHF per Kreditkarte bezahlt"
            if (preg_match('/- ([\d.]+) CHF/', $booking['description'], $matches)) {
                $price = (float) $matches[1];
                $db->query("UPDATE bookings SET paid_amount = ? WHERE id = ?", [$price, $booking['id']]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', 'paid_amount');
    }
}
