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
            "1Z", "2Z", "3Z", "4Z", "5Z", "EFH"
        ],

        'move_cleaning' => [
            "1Z", "2Z", "3Z", "4Z", "5Z", "EFH"
        ],

        'cleaning' => [
            "1Z", "2Z", "3Z", "4Z", "5Z", "Andere", "Wiederkehrend", "Nur Fenster", "Nur Fassaden", "Hauswartung"
        ],

        'painting' => [
            "Wohnung Neubau", "Wohnung Renovierung", "Wohnung Andere",
            "Haus Innenräume", "Haus Fassade",
            "Gewerbe Büro/Laden/Lager", "Gewerbe Industrie", "Gewerbe Andere"
        ],

        'gardening' => [
            "Mieter", "Eigentümer", "Verwaltung/Andere",
            "Bodenplatten verlegen", "Kies/Split", "Brüstung/Stutzmauer",
            "Zäune/Geländer", "Holz/WPC Dielen",
            "Teich Arbeiten", "Neuen Teich anlegen", "Neuer Pool",
            "Hecken/Bäume", "Hecke kompl. entfernen", "Hecke neu Pflanzen",
            "Rasen", "Rasen ersetzen", "Rasen neu anlegen", "Sprinkleranlage",
            "Andere"
        ],

        'plumbing' => [
            "Wohnung", "Haus", "MFH", "Gewerbe", "Andere",
            "Neubau", "Renovierung", "Umbau", "Bad/WC Sanierung",
            "Boiler Entkalkung", "Kleinreparaturen"
        ],

        'electrician' => [
            "Wohnung", "Haus", "MFH", "Gewerbe", "Andere",
            "Neubau", "Renovierung", "Umbau", "Kompl. Sanierung",
            "Solaranlage", "Alarmanlage",
            "Internet/Telefonanschluss", "Kleinarbeiten", "Andere"
        ],

        'heating' => [
            "Wohnung", "Haus", "MFH", "Gewerbe", "Andere",
            "Neubau", "Renovierung", "Umbau",
            "Neue Wärmepumpe", "Neue Gasheizung", "Neue Ölheizung", "Neue Erdwärme",
            "Heizkörper Austausch", "Andere"
        ],

        'flooring' => [
            "Wohnung", "Haus", "MFH", "Gewerbe", "Andere",
            "Neubau", "Renovierung", "Umbau",
            "Belag entfernen", "Belag verlegen", "Parkett schleifen", "Parkett lackieren", "Andere"
        ],

        'tiling' => [
            "Wohnung", "Haus", "MFH", "Gewerbe", "Andere",
            "Neubau", "Renovierung", "Umbau",
            "Platten entfernen", "Platten verlegen", "Andere"
        ],
    ];

    public array $discountRules = [
        [
            'hours'    => 8,
            'discount' => 30,   // Prozent
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
