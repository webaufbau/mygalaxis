<?php

namespace App\Controllers;

use App\Libraries\CategoryManager;
use CodeIgniter\Controller;
use Config\Services;

class LiveTicker extends Controller
{
    public function js()
    {
        helper('text');

        $country = $this->request->getGet('country') ?? 'CH';

        // CI4 Cache Service
        $cache = Services::cache();

        // Aktuelle Stunde als Key
        $currentHour = date('Y-m-d-H');
        $cacheKey = 'live_ticker_offers_' . $country . $currentHour;

        // Prüfen ob Cache existiert
        if (!$offers = $cache->get($cacheKey)) {

            // Fiktive Daten generieren ...
            $offers = [];
            $cities = []; // ['Neuenkirch','Zürich','Bern','Basel','Luzern'];
            $types = []; // ['Malerarbeiten','Reinigung','Gartenpflege','Umzug','Elektriker'];

            $db = db_connect();

            // 1. Branchen (Config/Library)
            $categoryManager = new CategoryManager();
            $categories = $categoryManager->getAll();
            foreach($categories as $cat_key=>$cat_arr) {
                $types[] = lang('Offers.type.' . $cat_key);
            }

            // 2. Orte (aus DB)
            $rows = $db->table('zipcodes')
                ->select('community')
                ->where('country_code', $country)
                ->orderBy('RAND()')
                ->limit(50)
                ->get()
                ->getResultArray();

            foreach($rows as $row) {
                $cities[] = $row['community'];
            }

            // Standard-Orte, falls DB keine liefert
            if (empty($cities)) {
                switch ($country) {
                    case 'AT':
                        $cities = ['Wien', 'Graz', 'Linz', 'Salzburg', 'Innsbruck', 'Klagenfurt', 'Villach', 'Wels', 'Sankt Pölten', 'Dornbirn'];
                        break;
                    case 'DE':
                        $cities = ['Berlin', 'Hamburg', 'München', 'Köln', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Dortmund', 'Essen', 'Leipzig'];
                        break;
                    case 'CH':
                    default:
                        $cities = ['Zürich', 'Genf', 'Basel', 'Bern', 'Lausanne', 'Winterthur', 'St. Gallen', 'Luzern', 'Lugano', 'Biel/Bienne'];
                        break;
                }
            }

            // 3. Zufalls-Kombinationen erzeugen
            for ($i=0; $i<5; $i++) {
                $cat = $types[array_rand($types)];
                $from = $cities[array_rand($cities)];
                $to   = rand(0,1) ? $cities[array_rand($cities)] : null;
                $minutes = rand(1,59);
                $minutesAgo = rand(5, 60);
                $createdAt = date('Y-m-d H:i:s', strtotime("-$minutesAgo minutes"));

                $offers[] = [
                    'title'   => $cat,
                    'from'    => $from,
                    'to'      => $to,
                    'minutes' => $minutes,

                    'type' => $types[array_rand($types)],
                    'city' => $cities[array_rand($cities)],
                    'created_at' => $createdAt
                ];
            }

            // Nach Datum absteigend sortieren
            usort($offers, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            // Gesamtanzahl letzte 24 Stunden fiktiv (z.B. 50-100)
            $totalOffers = rand(50, 100);

            // Cache speichern
            $cache->save($cacheKey, ['offers'=>$offers, 'totalOffers'=>$totalOffers], 3600);
        } else {
            $offers = $cache->get($cacheKey);

            $totalOffers = $offers['totalOffers'];
            $offers = $offers['offers'];

            // Auch aus dem Cache sortieren
            usort($offers, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }


        // JS ausgeben
        header('Content-Type: application/javascript');

        // Cache verhindern
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // schon abgelaufen

        $cssUrl = base_url('css/live-ticker.css');

        echo "
if (!document.getElementById('live-ticker-css')) {
    var link = document.createElement('link');
    link.id = 'live-ticker-css';
    link.rel = 'stylesheet';
    link.href = '$cssUrl';
    document.head.appendChild(link);
}

document.write(`<div class='live-ticker' id='live-ticker'>
    <div class='title'>Neueste Anfragen</div>`);";

foreach ($offers as $offer) {
    $timestamp = strtotime($offer['created_at']);
    $fromTo = $offer['city'] ?? '';
    $type = htmlspecialchars($offer['type']);
    echo "document.write(`<div class='offer-item' data-timestamp='{$timestamp}'>
        <span class='offer-type'>$type</span>
        <span class='offer-city'>$fromTo</span>
        <span class='offer-time'></span>
    </div>`);";
}

echo "document.write(` <div class='total-offers'>{$totalOffers} " . lang('LiveTicker.requests_last_24h') . "</div>`);";

// Live TimeAgo Script einfügen
echo "
(function(){
    function updateTimeAgo() {
        const now = Date.now()/1000; // aktueller Timestamp in Sekunden
        const hour = new Date().getHours();
    
        document.querySelectorAll('.offer-item').forEach(item => {
            const ts = parseInt(item.getAttribute('data-timestamp'), 10);
            const diff = Math.floor(now - ts);
    
            // Angebote älter als 60 Minuten ausblenden
            if (diff >= 3600) {
                item.style.display = 'none';
                return;
            }
    
            let text = '';
            if (diff < 60) text = 'vor ' + diff + ' Sekunden';
            else text = 'vor ' + Math.floor(diff/60) + ' Minuten';
    
            item.querySelector('.offer-time').textContent = text;
            item.style.display = 'block';
        });
    }
    
    // sofort ausführen
    updateTimeAgo();
    
    // jede Minute aktualisieren
    setInterval(updateTimeAgo, 60*1000);
    
    // Box ausblenden bei Klick irgendwo auf die Box
    document.querySelector('.live-ticker').addEventListener('click', function() {
        this.style.display = 'none';
    });


    // Box per Klick ausblenden
    const ticker = document.getElementById('live-ticker');
    ticker.addEventListener('click', function() {
        ticker.style.display = 'none';
    });
})();
";

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
