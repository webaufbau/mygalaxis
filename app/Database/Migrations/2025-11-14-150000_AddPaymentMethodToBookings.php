<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentMethodToBookings extends Migration
{
    public function up()
    {
        // Add payment_method column to bookings table
        $fields = [
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'type',
                'comment'    => 'Payment method: manual_credit, stripe, balance, etc.',
            ],
        ];

        $this->forge->addColumn('bookings', $fields);

        // Migrate existing data:
        // - Set 'manual_credit' for topup/credit bookings with amount > 0 and paid_amount = 0
        // - Set 'balance' for offer_purchase with amount < 0 (paid from balance)
        // - Set 'stripe' for offer_purchase with amount = 0 and paid_amount > 0 (paid by card)
        $db = \Config\Database::connect();

        // Manual credits (topup/credit with no actual payment)
        $db->query("
            UPDATE bookings
            SET payment_method = 'manual_credit'
            WHERE type IN ('topup', 'credit')
            AND amount > 0
            AND paid_amount = 0
        ");

        // Balance payments (deducted from user balance)
        $db->query("
            UPDATE bookings
            SET payment_method = 'balance'
            WHERE type = 'offer_purchase'
            AND amount < 0
        ");

        // Stripe/card payments (paid amount > 0)
        $db->query("
            UPDATE bookings
            SET payment_method = 'stripe'
            WHERE type = 'offer_purchase'
            AND paid_amount > 0
        ");

        // Purchase type without specific payment
        $db->query("
            UPDATE bookings
            SET payment_method = 'stripe'
            WHERE type = 'purchase'
            AND payment_method IS NULL
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', 'payment_method');
    }
}
