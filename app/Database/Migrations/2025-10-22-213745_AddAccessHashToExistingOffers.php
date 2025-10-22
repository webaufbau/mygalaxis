<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccessHashToExistingOffers extends Migration
{
    public function up()
    {
        // Generiere access_hash für alle Offers die noch keinen haben
        $db = \Config\Database::connect();

        // Finde alle Offers ohne access_hash
        $offers = $db->table('offers')
            ->select('id')
            ->where('access_hash IS NULL OR access_hash = ""')
            ->get()
            ->getResultArray();

        foreach ($offers as $offer) {
            // Generiere einen eindeutigen Hash
            $hash = md5($offer['id'] . uniqid() . time() . rand(1000, 9999));

            $db->table('offers')
                ->where('id', $offer['id'])
                ->update(['access_hash' => $hash]);
        }

        log_message('info', 'Access Hash Migration: ' . count($offers) . ' Offers aktualisiert');
    }

    public function down()
    {
        // Nicht rückgängig machen, da access_hash wichtig ist
    }
}
