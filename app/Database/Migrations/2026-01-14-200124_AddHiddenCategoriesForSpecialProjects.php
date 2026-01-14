<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fügt versteckte Branchen für spezielle Projekte hinzu:
 * - blinds (Storen/Rolläden)
 * - shading (Beschattung)
 * - insect_screen (Fliegengitter/Insektenschutz)
 * - winter_garden (Wintergarten)
 * - pool (Poolanlage)
 *
 * Diese Branchen sind versteckt (is_hidden = true) und erscheinen nicht
 * in der Dienstleistungs-Auswahl, können aber für Projekte verwendet werden.
 */
class AddHiddenCategoriesForSpecialProjects extends Migration
{
    private array $newCategories = [
        'blinds' => [
            'name' => 'Storen/Rolläden',
            'name_de' => 'Storen/Rolläden',
            'name_en' => 'Blinds/Shutters',
            'name_fr' => 'Stores/Volets',
            'name_it' => 'Tapparelle/Persiane',
            'color' => '#8B4513',
            'base_price' => 29,
        ],
        'shading' => [
            'name' => 'Beschattung',
            'name_de' => 'Beschattung',
            'name_en' => 'Shading',
            'name_fr' => 'Ombrage',
            'name_it' => 'Ombreggiatura',
            'color' => '#DAA520',
            'base_price' => 29,
        ],
        'insect_screen' => [
            'name' => 'Fliegengitter/Insektenschutz',
            'name_de' => 'Fliegengitter/Insektenschutz',
            'name_en' => 'Insect Screen',
            'name_fr' => 'Moustiquaire',
            'name_it' => 'Zanzariera',
            'color' => '#556B2F',
            'base_price' => 29,
        ],
        'winter_garden' => [
            'name' => 'Wintergarten',
            'name_de' => 'Wintergarten',
            'name_en' => 'Winter Garden',
            'name_fr' => 'Jardin d\'hiver',
            'name_it' => 'Giardino d\'inverno',
            'color' => '#2E8B57',
            'base_price' => 49,
        ],
        'pool' => [
            'name' => 'Poolanlage',
            'name_de' => 'Poolanlage',
            'name_en' => 'Pool Installation',
            'name_fr' => 'Installation piscine',
            'name_it' => 'Impianto piscina',
            'color' => '#4169E1',
            'base_price' => 49,
        ],
    ];

    public function up()
    {
        $configPath = WRITEPATH . 'config/category_settings.json';

        if (!file_exists($configPath)) {
            throw new \RuntimeException('category_settings.json not found');
        }

        $config = json_decode(file_get_contents($configPath), true);

        foreach ($this->newCategories as $key => $data) {
            // Prüfen ob Kategorie bereits existiert
            if (isset($config[$key])) {
                continue;
            }

            $config[$key] = [
                'name' => $data['name'],
                'name_de' => $data['name_de'],
                'name_en' => $data['name_en'],
                'name_fr' => $data['name_fr'],
                'name_it' => $data['name_it'],
                'color' => $data['color'],
                'base_price' => $data['base_price'],
                'options' => [],
                'forms' => [],
                'hidden' => true,
                'review_email_days' => 14,
                'review_reminder_days' => 7,
            ];
        }

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function down()
    {
        $configPath = WRITEPATH . 'config/category_settings.json';

        if (!file_exists($configPath)) {
            return;
        }

        $config = json_decode(file_get_contents($configPath), true);

        foreach (array_keys($this->newCategories) as $key) {
            unset($config[$key]);
        }

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
