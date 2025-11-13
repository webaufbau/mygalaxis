<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PopulateUserPlatforms extends Migration
{
    public function up()
    {
        // 1. Update Benutzer mit eindeutiger Plattform aus ihren gekauften Angeboten
        $sql = "
            UPDATE users u
            INNER JOIN (
                SELECT
                    users.id as user_id,
                    offers.platform as offer_platform
                FROM users
                LEFT JOIN bookings ON users.id = bookings.user_id AND bookings.type = 'offer_purchase'
                LEFT JOIN offers ON bookings.reference_id = offers.id
                WHERE offers.platform IS NOT NULL
                  AND (users.platform IS NULL OR users.platform = '')
                GROUP BY users.id
                HAVING COUNT(DISTINCT offers.platform) = 1
            ) as user_platforms ON u.id = user_platforms.user_id
            SET u.platform = user_platforms.offer_platform
        ";
        $this->db->query($sql);

        // 2. Setze Standard-Plattform für alle verbleibenden Benutzer ohne Plattform
        $sql = "
            UPDATE users
            SET platform = 'my_offertenschweiz_ch'
            WHERE platform IS NULL OR platform = ''
        ";
        $this->db->query($sql);
    }

    public function down()
    {
        // Optional: Plattformen wieder auf NULL setzen (nur für Entwicklung)
        // $this->db->query("UPDATE users SET platform = NULL");
    }
}
