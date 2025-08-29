<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class CategoryOptions extends BaseConfig
{
    // Die festen Typen
    public array $categoryTypes = [
        'move'           => 'Umzug',
        'move_cleaning'  => 'Umzug + Reinigung',
        'cleaning'       => 'Reinigung',
        'painting'       => 'Maler',
        'gardening'      => 'Gartenpflege',
        'plumbing'       => 'Sanitär',
        'electrician'    => 'Elektriker',
        'flooring'       => 'Boden',
        'heating'        => 'Heizung',
        'tiling'         => 'Platten',
    ];

    // Alle möglichen Optionen je Kategorie (Labels fix)
    public array $categoryOptions = [

        'move' => [
            ['key' => '1', 'label' => '1Z', 'price' => 0],
            ['key' => '2', 'label' => '2Z', 'price' => 0],
            ['key' => '3', 'label' => '3Z', 'price' => 0],
            ['key' => '4', 'label' => '4Z', 'price' => 0],
            ['key' => '5', 'label' => '5Z', 'price' => 0],
            ['key' => '6', 'label' => 'EFH', 'price' => 0],
        ],

        'move_cleaning' => [
            ['key' => '1', 'label' => '1Z', 'price' => 0],
            ['key' => '2', 'label' => '2Z', 'price' => 0],
            ['key' => '3', 'label' => '3Z', 'price' => 0],
            ['key' => '4', 'label' => '4Z', 'price' => 0],
            ['key' => '5', 'label' => '5Z', 'price' => 0],
            ['key' => '6', 'label' => 'EFH', 'price' => 0],
        ],

        'cleaning' => [
            ['key' => '1', 'label' => '1Z', 'price' => 19],
            ['key' => '2', 'label' => '2Z', 'price' => 25],
            ['key' => '3', 'label' => '3Z', 'price' => 29],
            ['key' => '4', 'label' => '4Z', 'price' => 35],
            ['key' => '5', 'label' => '5Z', 'price' => 39],
            ['key' => 'andere', 'label' => 'Andere', 'price' => 39],
            ['key' => 'wiederkehrend', 'label' => 'Wiederkehrend', 'price' => 20],  // Aufpreis
            ['key' => 'nur_fenster', 'label' => 'Nur Fenster', 'price' => 19],
            ['key' => 'nur_fassaden', 'label' => 'Nur Fassaden', 'price' => 39],
            ['key' => 'hauswartung', 'label' => 'Hauswartung', 'price' => 79],
        ],

        'painting' => [
            // Grundtypen
            ['key' => 'neubau_renovierung_andere', 'label' => 'Neubau / Renovierung / Andere', 'price' => 19],

            // Arbeiten (Aufschlag)
            ['key' => 'waende', 'label' => 'Wände', 'price' => 19],
            ['key' => 'decken', 'label' => 'Decken', 'price' => 9],
            ['key' => 'fensterlaeden', 'label' => 'Fensterläden', 'price' => 5],
            ['key' => 'fenster', 'label' => 'Fenster', 'price' => 9],
            ['key' => 'tueren', 'label' => 'Türen', 'price' => 5],
            ['key' => 'treppengelaender', 'label' => 'Treppengeländer', 'price' => 9],
            ['key' => 'garagentor', 'label' => 'Garagentor', 'price' => 5],
            ['key' => 'heizkoerper', 'label' => 'Heizkörper', 'price' => 5],
            ['key' => 'zaungelaender', 'label' => 'Zaungeländer', 'price' => 5],
            ['key' => 'andere_arbeit', 'label' => 'Andere Arbeit', 'price' => 5],

            // Zimmergröße (Aufschlag)
            ['key' => '1', 'label' => '1Z', 'price' => 5],
            ['key' => '2', 'label' => '2Z', 'price' => 10],
            ['key' => '3', 'label' => '3Z', 'price' => 15],
            ['key' => '4', 'label' => '4Z', 'price' => 20],
            ['key' => '5', 'label' => '5Z', 'price' => 25],
            ['key' => 'andere_zimmer', 'label' => 'Andere', 'price' => 10],

            // Trennwände
            ['key' => 'trennwaende', 'label' => 'Trennwände', 'price' => 15],

            // Gewerbe-Spezialfälle
            ['key' => 'gewerbe_buero_laden_lager_industrie', 'label' => 'Gewerbe Büro/Laden/Lager/Industrie', 'price' => 19],
            ['key' => 'gewerbe_andere', 'label' => 'Gewerbe Andere', 'price' => 39],

            // Innenräume / Fassade extra bei Haus
            ['key' => 'arbeiten_innenraeume', 'label' => 'Malerarbeiten Innenräume', 'price' => 19],
            ['key' => 'arbeiten_fassade', 'label' => 'Malerarbeiten Fassade', 'price' => 39],
            ['key' => 'arbeiten_andere', 'label' => 'Malerarbeiten Andere', 'price' => 39],
        ],

        'gardening' => [
            ['key' => 'mieter_eigentuemer_verwaltung_andere', 'label' => 'Mieter / Eigentümer / Verwaltung / Andere', 'price' => 0],
            ['key' => 'bodenplatten_verlegen', 'label' => 'Bodenplatten verlegen', 'price' => 0],
            ['key' => 'kies_split_flaechen',   'label' => 'Kies/Split Flächen', 'price' => 0],
            ['key' => 'bruestung_stuetzmauer', 'label' => 'Brüstung / Stützmauer', 'price' => 0],
            ['key' => 'zaeune_gelaender',      'label' => 'Zäune / Geländer', 'price' => 0],
            ['key' => 'holz_wpc_dielen',       'label' => 'Holz / WPC Dielen', 'price' => 0],
            ['key' => 'teich_arbeiten',        'label' => 'Teich-Arbeiten', 'price' => 0],
            ['key' => 'neuer_tech',            'label' => 'Neuer Teich anlegen', 'price' => 0],
            ['key' => 'neuer_pool',            'label' => 'Neuer Pool', 'price' => 0],
            ['key' => 'hecken_baeume',         'label' => 'Hecken / Bäume', 'price' => 0],
            ['key' => 'hecke_kompl_entfernen', 'label' => 'Hecke kompl. entfernen', 'price' => 0],
            ['key' => 'hecke_neu_pflanzen',    'label' => 'Hecke neu Pflanzen', 'price' => 0],
            ['key' => 'rasen',                 'label' => 'Rasen', 'price' => 0],
            ['key' => 'rasen_ersetzen',        'label' => 'Rasen ersetzen', 'price' => 0],
            ['key' => 'rasen_neu_anlegen',     'label' => 'Rasen neu anlegen', 'price' => 0],
            ['key' => 'sprinkleranlage',       'label' => 'Sprinkleranlage', 'price' => 0],
            ['key' => 'andere',                'label' => 'Andere', 'price' => 0],
            ['key' => 'wiederkehrend', 'label' => 'Wiederkehrend', 'price' => 0],
        ],

        'electrician' => [
            // --- Art Objekt ---
            ['key' => 'wohnung',             'label' => 'Wohnung', 'price' => 29],
            ['key' => 'haus',                'label' => 'Haus', 'price' => 29],
            ['key' => 'mehrfamilienhaus',    'label' => 'Mehrfamilienhaus', 'price' => 29],
            ['key' => 'gewerbe',             'label' => 'Gewerbe', 'price' => 29],
            ['key' => 'andere',              'label' => 'Andere', 'price' => 29],

            // --- Arbeiten ---
            ['key' => 'neubau',              'label' => 'Neubau', 'price' => 49],
            ['key' => 'renovierung',         'label' => 'Renovierung', 'price' => 49],
            ['key' => 'umbau',               'label' => 'Umbau', 'price' => 49],
            ['key' => 'kompl_sanierung',     'label' => 'Kompl. Sanierung', 'price' => 49],
            ['key' => 'solaranlage',         'label' => 'Solaranlage', 'price' => 49],
            ['key' => 'alarmanlage',         'label' => 'Alarmanlage', 'price' => 49],

            ['key' => 'internet_tel_anschluss', 'label' => 'Internet / Tel. Anschluss', 'price' => 19],
            ['key' => 'kleinere_arbeiten',      'label' => 'Kleinere Arbeiten', 'price' => 19],
            ['key' => 'andere_arbeiten',        'label' => 'Andere', 'price' => 19],
        ],

        'plumbing' => [
            // Art Objekt
            ['key' => 'wohnung', 'label' => 'Wohnung', 'price' => 29],
            ['key' => 'haus', 'label' => 'Haus', 'price' => 29],
            ['key' => 'mehrfamilienhaus', 'label' => 'Mehrfamilienhaus', 'price' => 29],
            ['key' => 'gewerbe', 'label' => 'Gewerbe', 'price' => 29],
            ['key' => 'andere', 'label' => 'Andere', 'price' => 29],

            // Arbeiten
            ['key' => 'neubau',             'label' => 'Neubau', 'price' => 59],
            ['key' => 'renovierung',        'label' => 'Renovierung', 'price' => 39],
            ['key' => 'umbau',              'label' => 'Umbau', 'price' => 39],
            ['key' => 'bad_wc_sanierung',   'label' => 'Bad/WC Sanierung', 'price' => 39],
            ['key' => 'anpassung_neue_kueche', 'label' => 'Anpassung neue Küche', 'price' => 39],
            ['key' => 'boiler_entkalkung',  'label' => 'Boiler Entkalkung', 'price' => 19],
            ['key' => 'kleine_reparatur',   'label' => 'Kleine Reparatur', 'price' => 19],
            ['key' => 'andere_arbeiten',    'label' => 'Andere', 'price' => 19],
        ],

        'heating' => [
            // Art Objekt
            ['key' => 'wohnung', 'label' => 'Wohnung', 'price' => 29],
            ['key' => 'haus', 'label' => 'Haus', 'price' => 29],
            ['key' => 'mehrfamilienhaus', 'label' => 'Mehrfamilienhaus', 'price' => 29],
            ['key' => 'gewerbe', 'label' => 'Gewerbe', 'price' => 29],
            ['key' => 'andere', 'label' => 'Andere', 'price' => 29],

            // Step 2 Arbeiten
            ['key' => 'neubau', 'label' => 'Neubau', 'price' => 59],
            ['key' => 'renovierung', 'label' => 'Renovierung', 'price' => 39],
            ['key' => 'umbau', 'label' => 'Umbau', 'price' => 59],

            // Neue Anlagen (Step 3) – 69,-
            ['key' => 'neue_waermepumpe', 'label' => 'Neue el. Wärmepumpe', 'price' => 69],
            ['key' => 'neue_gasheizung', 'label' => 'Neue Gasheizung', 'price' => 69],
            ['key' => 'neue_oelheizung', 'label' => 'Neue Öl-Heizung', 'price' => 69],
            ['key' => 'neue_erdwaerme', 'label' => 'Neue Erdwärme', 'price' => 69],

            // Heizkörper Austausch / Andere – 29,-
            ['key' => 'heizkoerper_austausch', 'label' => 'Heizkörper Austausch', 'price' => 29],
            ['key' => 'andere_arbeiten', 'label' => 'Andere', 'price' => 29],
        ],

        'flooring' => [
            // Art Objekt
            ['key' => 'wohnung', 'label' => 'Wohnung', 'price' => 29],
            ['key' => 'haus', 'label' => 'Haus', 'price' => 29],
            ['key' => 'mehrfamilienhaus', 'label' => 'Mehrfamilienhaus', 'price' => 29],
            ['key' => 'gewerbe', 'label' => 'Gewerbe', 'price' => 29],
            ['key' => 'andere', 'label' => 'Andere', 'price' => 29],

            // Step 2 Arbeiten
            ['key' => 'neubau', 'label' => 'Neubau', 'price' => 39],
            ['key' => 'renovierung', 'label' => 'Renovierung', 'price' => 29],
            ['key' => 'umbau', 'label' => 'Umbau', 'price' => 29],

            // Step 3 Arbeiten / Beläge
            ['key' => 'belag_entfernen', 'label' => 'Belag entfernen', 'price' => 19],
            ['key' => 'belag_verlegen', 'label' => 'Belag verlegen', 'price' => 19],
            ['key' => 'parkett_schleifen', 'label' => 'Parkett schleifen', 'price' => 9],
            ['key' => 'parkett_lackieren', 'label' => 'Parkett lackieren', 'price' => 9],
            ['key' => 'andere_arbeiten', 'label' => 'Andere', 'price' => 9],
        ],

        'tiling' => [
            // --- Art Objekt ---
            ['key' => 'wohnung', 'label' => 'Wohnung', 'price' => 29],
            ['key' => 'haus', 'label' => 'Haus', 'price' => 29],
            ['key' => 'mehrfamilienhaus', 'label' => 'Mehrfamilienhaus', 'price' => 29],
            ['key' => 'gewerbe', 'label' => 'Gewerbe', 'price' => 29],
            ['key' => 'andere', 'label' => 'Andere', 'price' => 29],

            // --- Arbeiten Platten ---
            ['key' => 'neubau', 'label' => 'Neubau', 'price' => 39],
            ['key' => 'renovierung', 'label' => 'Renovierung', 'price' => 29],
            ['key' => 'umbau', 'label' => 'Umbau', 'price' => 29],
            ['key' => 'platten_entfernen', 'label' => 'Platten entfernen', 'price' => 19],
            ['key' => 'platten_verlegen', 'label' => 'Platten verlegen', 'price' => 19],
            ['key' => 'andere_arbeiten', 'label' => 'Andere', 'price' => 19],
        ],

    ];


    public array $discountRules = [
        [
            'hours'    => 8,
            'discount' => 30,
        ],
        [
            'hours'    => 14,
            'discount' => 50,
        ],
        [
            'hours'    => 24,
            'discount' => 70,
        ]
    ];

    // Pfad zur JSON-Datei mit Preisen
    public string $storagePath = WRITEPATH . 'config/category_settings.json';
}
