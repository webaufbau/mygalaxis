<?php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class FilterOptions extends BaseConfig
{
    public array $categories = [
        'move',
        'move_cleaning',
        'cleaning',
        'painting',
        'gardening',
    ];

    public array $types = [
        'move' => 'move',
        'cleaning' => 'cleaning',
        'move_cleaning' => 'move_cleaning',
        'painting' => 'painting',
        'gardening' => 'gardening',
        'plumbing' => 'plumbing',
    ];

    public array $languages = [
        'de',
        'en',
        'fr',
        'it',
    ];

    public array $services = [
        'packing',
        'unpacking',
        'furniture_assembly',
        'lamp_disassembly',
    ];
}
