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
        $language = $this->request->getGet('lang');
        if($language) {
            service('request')->setLocale($language);
        }

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

            $languages = Config('App')->supportedLocales ?? [];

            $jsTypes = [];
            $jsLang = [];

            // Für jede Kategorie und jede Sprache die Übersetzung holen
            foreach ($categories as $cat_key => $cat_arr) {
                $types[] = $cat_key;
                foreach ($languages as $tmp_lang) {
                    // Sprachdatei temporär setzen
                    service('language')->setLocale($tmp_lang);
                    $jsTypes[$cat_key][$tmp_lang] = lang('Offers.type.' . $cat_key);
                    $jsLang[$tmp_lang] = [
                        'just_now'    => lang('LiveTicker.just_now'),
                        'seconds_ago' => lang('LiveTicker.seconds_ago'),
                        'minutes_ago' => lang('LiveTicker.minutes_ago'),
                        'hours_ago'   => lang('LiveTicker.hours_ago'),
                        'days_ago'    => lang('LiveTicker.days_ago'),
                    ];
                }
            }

            service('language')->setLocale($language);


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
            for ($i=0; $i<10; $i++) {
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
            $cache->save($cacheKey, ['offers'=>$offers, 'totalOffers'=>$totalOffers, 'jsTypes' => $jsTypes, 'jsLang' => $jsLang], 3600);
        } else {
            $offers = $cache->get($cacheKey);

            $totalOffers = $offers['totalOffers'];
            $jsTypes = $offers['jsTypes'] ?? [];
            $jsLang = $offers['jsLang'] ?? [];
            $offers = $offers['offers'];

            // Auch aus dem Cache sortieren
            usort($offers, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }


        // JS ausgeben
        header('Content-Type: application/javascript; charset=utf-8');

        // Cache verhindern
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // schon abgelaufen

        $cssUrl = base_url('css/live-ticker.css');

        echo "
var lang = '".($language ?? 'de')."';
window.OffersTypes = ".json_encode($jsTypes ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP).";
window.LangStrings = ".json_encode($jsLang ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP).";

function t(key, value = null) {
    let str = window.LangStrings[lang][key] || key;
    if (value !== null) {
        str = str.replace('{0}', value);
    }
    return str;
}

if (!document.getElementById('live-ticker-css')) {
    var link = document.createElement('link');
    link.id = 'live-ticker-css';
    link.rel = 'stylesheet';
    link.href = '$cssUrl';
    document.head.appendChild(link);
}

document.write(`<div class='live-ticker' id='live-ticker'>
    <div class='title'>".lang('LiveTicker.latest_requests')."</div>`);";

foreach ($offers as $offer) {
    $timestamp = strtotime($offer['created_at']);
    $fromTo = $offer['city'] ?? '';
    $type = htmlspecialchars($offer['type']);
    $cat_key = $offer['type'];

    echo "document.write(`<div class='offer-item' data-timestamp='{$timestamp}'>
        <span class='offer-type'>` + (window.OffersTypes['{$cat_key}'] ? window.OffersTypes['{$cat_key}'][lang] : '{$cat_key}') + `</span>
        <span class='offer-city'>{$fromTo}</span>
        <span class='offer-time'></span>
    </div>`);";
}

echo "document.write(` <div class='total-offers'>{$totalOffers} " . lang('LiveTicker.requests_last_24h') . "</div>`);";

// Live TimeAgo Script einfügen
echo "
(function(){
    function updateTimeAgo() {
        const now = Date.now()/1000;
        const hour = new Date().getHours();
    
        document.querySelectorAll('.offer-item').forEach(item => {
            const ts = parseInt(item.getAttribute('data-timestamp'), 10);
            const diff = Math.floor(now - ts);
    
            if (diff >= 3600) {
                item.style.display = 'none';
                return;
            }
    
            let text = '';
            if (diff < 60) {
                text = t('seconds_ago', diff);
            } else {
                text = t('minutes_ago', Math.floor(diff/60));
            }
    
            item.querySelector('.offer-time').textContent = text;
            item.style.display = 'block';
        });
    }
    
    updateTimeAgo();
    
    setInterval(updateTimeAgo, 60*1000);
    
    document.querySelector('.live-ticker').addEventListener('click', function() {
        this.style.display = 'none';
    });

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

        if ($diff < 60) return str_replace("{0}", $diff, lang('LiveTicker.seconds_ago'));
        if ($diff < 3600) return 'vor ' . floor($diff / 60) . ' Minuten';
        if ($diff < 86400) return 'vor ' . floor($diff / 3600) . ' Stunden';
        return 'vor ' . floor($diff / 86400) . ' Tagen';
    }
}
