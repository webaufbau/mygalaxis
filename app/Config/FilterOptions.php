<?php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class FilterOptions extends BaseConfig
{
    public array $categories = [
        'Umzug',
        'Umzug + Reinigung',
        'Reinigung',
        'Maler',
        'Gärtner',
    ];

    public array $types = [
        'move' => 'Umzug',
        'cleaning' => 'Reinigung',
        'move_cleaning' => 'Umzug + Reinigung',
        'painting' => 'Maler',
        'gardening' => 'Gartenpflege',
        'plumbing' => 'Sanitär',
    ];

    public array $languages = [
        'Deutsch',
        'Englisch',
        'Französisch',
        'Italienisch',
    ];

    public array $services = [
        'Hausrat einpacken',
        'Hausrat anpacken',
        'Möbel Aufbau',
        'Lampen demontieren',
    ];
}
