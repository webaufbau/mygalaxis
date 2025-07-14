<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class CategoryOptions extends BaseConfig
{
    // Die festen Typen
    public array $categoryTypes = [
        'move' => 'Umzug',
        'cleaning' => 'Reinigung',
        'move_cleaning' => 'Umzug + Reinigung',
        'painting' => 'Maler',
        'gardening' => 'Gartenpflege',
        'plumbing' => 'Sanitär',
    ];

    // Pfad zur JSON-Datei mit Preisen & Labels
    public string $storagePath = WRITEPATH . 'config/category_settings.json';
}
