<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class LiveTicker extends Controller
{
    public function js()
    {
        helper('text');

        // CI4 Cache Service
        $cache = Services::cache();

        // Aktuelle Stunde als Key
        $currentHour = date('Y-m-d-H');
        $cacheKey = 'live_ticker_offers_' . $currentHour;

        // Prüfen ob Cache existiert
        if (!$offers = $cache->get($cacheKey)) {

            // Fiktive Daten für 10 Angebote
            $offers = [];
            $cities = ['Neuenkirch','Zürich','Bern','Basel','Luzern'];
            $types = ['Malerarbeiten','Reinigung','Gartenpflege','Umzug','Elektriker'];

            for ($i=0; $i<10; $i++) {
                $createdAt = date('Y-m-d H:i:s', strtotime('-'.rand(0,23).' hours -'.rand(0,59).' minutes'));
                $offers[] = [
                    'type' => $types[array_rand($types)],
                    'city' => $cities[array_rand($cities)],
                    'created_at' => $createdAt
                ];
            }

            // Gesamtanzahl letzte 24 Stunden fiktiv (z.B. 50-100)
            $totalOffers = rand(50,100);

            // Cache speichern für 1 Stunde
            $cache->save($cacheKey, ['offers'=>$offers, 'totalOffers'=>$totalOffers], 3600);
        } else {
            $totalOffers = $offers['totalOffers'];
            $offers = $offers['offers'];
        }

        // JS ausgeben
        header('Content-Type: application/javascript');

        $cssUrl = base_url('css/live-ticker.css'); // Pfad zu deiner CSS-Datei

        echo "
        if (!document.getElementById('live-ticker-css')) {
            var link = document.createElement('link');
            link.id = 'live-ticker-css';
            link.rel = 'stylesheet';
            link.href = '$cssUrl';
            document.head.appendChild(link);
        }

        document.write(`";
        echo '<div class="live-ticker">';
        echo '<div class="title">Neueste Anfragen</div>';
        foreach ($offers as $offer) {
            $timeAgo = $this->timeAgo($offer['created_at']);
            $fromTo = $offer['city'] ?? '';
            $type = htmlspecialchars($offer['type']);
            echo "<div class='offer-item'><span class='offer-type'>$type</span> <span class='offer-city'>$fromTo</span> <span class='offer-time'>$timeAgo</span></div>";
        }
        echo "<div class='total-offers'>{$totalOffers} Anfragen in den letzten 24 Stunden</div>";
        echo "</div>";
        echo "`);";
        exit();
    }

    protected function timeAgo(string $datetime): string
    {
        $ts = strtotime($datetime);
        $diff = time() - $ts;

        $hour = date('H', $ts);
        if ($hour < 6 || $hour > 22) {
            return 'vor kurzem';
        }

        if ($diff < 60) return 'vor ' . $diff . ' Sekunden';
        if ($diff < 3600) return 'vor ' . floor($diff / 60) . ' Minuten';
        if ($diff < 86400) return 'vor ' . floor($diff / 3600) . ' Stunden';
        return 'vor ' . floor($diff / 86400) . ' Tagen';
    }
}
