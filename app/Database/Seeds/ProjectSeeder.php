<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        $projects = [
            [
                'slug' => 'new_build',
                'name_de' => 'Neubau',
                'name_en' => 'New Build',
                'name_fr' => 'Construction neuve',
                'name_it' => 'Nuova costruzione',
                'color' => '#28a745',
                'sort_order' => 1,
            ],
            [
                'slug' => 'renovation',
                'name_de' => 'Umbau',
                'name_en' => 'Renovation',
                'name_fr' => 'Rénovation',
                'name_it' => 'Ristrutturazione',
                'color' => '#17a2b8',
                'sort_order' => 2,
            ],
            [
                'slug' => 'bathroom',
                'name_de' => 'Bad/WC Sanierung',
                'name_en' => 'Bathroom Renovation',
                'name_fr' => 'Rénovation salle de bain',
                'name_it' => 'Ristrutturazione bagno',
                'color' => '#007bff',
                'sort_order' => 3,
            ],
            [
                'slug' => 'sauna',
                'name_de' => 'Sauna',
                'name_en' => 'Sauna',
                'name_fr' => 'Sauna',
                'name_it' => 'Sauna',
                'color' => '#dc3545',
                'sort_order' => 4,
            ],
            [
                'slug' => 'hvac',
                'name_de' => 'Luft/Klima Heizungsanlage',
                'name_en' => 'HVAC System',
                'name_fr' => 'Système CVC',
                'name_it' => 'Sistema HVAC',
                'color' => '#6c757d',
                'sort_order' => 5,
            ],
            [
                'slug' => 'stairs',
                'name_de' => 'Treppe/Geländer',
                'name_en' => 'Stairs/Railings',
                'name_fr' => 'Escalier/Garde-corps',
                'name_it' => 'Scale/Ringhiere',
                'color' => '#795548',
                'sort_order' => 6,
            ],
            [
                'slug' => 'alarm',
                'name_de' => 'Alarmanlage',
                'name_en' => 'Alarm System',
                'name_fr' => 'Système d\'alarme',
                'name_it' => 'Sistema d\'allarme',
                'color' => '#e91e63',
                'sort_order' => 7,
            ],
            [
                'slug' => 'ev_charger',
                'name_de' => 'E-Ladestation',
                'name_en' => 'EV Charging Station',
                'name_fr' => 'Borne de recharge',
                'name_it' => 'Stazione di ricarica EV',
                'color' => '#4caf50',
                'sort_order' => 8,
            ],
            [
                'slug' => 'smart_home',
                'name_de' => 'Smart Home Komplettsystem',
                'name_en' => 'Smart Home System',
                'name_fr' => 'Système domotique',
                'name_it' => 'Sistema domotico',
                'color' => '#9c27b0',
                'sort_order' => 9,
            ],
            [
                'slug' => 'solar',
                'name_de' => 'Solaranlage',
                'name_en' => 'Solar System',
                'name_fr' => 'Installation solaire',
                'name_it' => 'Impianto solare',
                'color' => '#ff9800',
                'sort_order' => 10,
            ],
            [
                'slug' => 'doors',
                'name_de' => 'Innen/Aussentüren',
                'name_en' => 'Interior/Exterior Doors',
                'name_fr' => 'Portes intérieures/extérieures',
                'name_it' => 'Porte interne/esterne',
                'color' => '#8d6e63',
                'sort_order' => 11,
            ],
            [
                'slug' => 'blinds',
                'name_de' => 'Storen/Rolläden',
                'name_en' => 'Blinds/Shutters',
                'name_fr' => 'Stores/Volets',
                'name_it' => 'Tapparelle/Persiane',
                'color' => '#607d8b',
                'sort_order' => 12,
            ],
            [
                'slug' => 'shading',
                'name_de' => 'Beschattung',
                'name_en' => 'Shading',
                'name_fr' => 'Protection solaire',
                'name_it' => 'Ombreggiatura',
                'color' => '#795548',
                'sort_order' => 13,
            ],
            [
                'slug' => 'insect_screen',
                'name_de' => 'Fliegengitter/Insektenschutz',
                'name_en' => 'Insect Screen',
                'name_fr' => 'Moustiquaire',
                'name_it' => 'Zanzariera',
                'color' => '#8bc34a',
                'sort_order' => 14,
            ],
            [
                'slug' => 'winter_garden',
                'name_de' => 'Wintergarten',
                'name_en' => 'Winter Garden',
                'name_fr' => 'Jardin d\'hiver',
                'name_it' => 'Giardino d\'inverno',
                'color' => '#4caf50',
                'sort_order' => 15,
            ],
            [
                'slug' => 'pool',
                'name_de' => 'Poolanlage',
                'name_en' => 'Pool',
                'name_fr' => 'Piscine',
                'name_it' => 'Piscina',
                'color' => '#00bcd4',
                'sort_order' => 16,
            ],
            [
                'slug' => 'mold',
                'name_de' => 'Schimmel Entfernung',
                'name_en' => 'Mold Removal',
                'name_fr' => 'Élimination des moisissures',
                'name_it' => 'Rimozione muffa',
                'color' => '#9e9e9e',
                'sort_order' => 17,
            ],
            [
                'slug' => 'locks',
                'name_de' => 'Schlüssel/Schliessanlage',
                'name_en' => 'Locks/Security Systems',
                'name_fr' => 'Serrures/Systèmes de sécurité',
                'name_it' => 'Serrature/Sistemi di sicurezza',
                'color' => '#ffc107',
                'sort_order' => 18,
            ],
            [
                'slug' => 'carport',
                'name_de' => 'Carport',
                'name_en' => 'Carport',
                'name_fr' => 'Carport',
                'name_it' => 'Tettoia auto',
                'color' => '#795548',
                'sort_order' => 19,
            ],
            [
                'slug' => 'garage',
                'name_de' => 'Garagenbox',
                'name_en' => 'Garage Box',
                'name_fr' => 'Box garage',
                'name_it' => 'Box garage',
                'color' => '#607d8b',
                'sort_order' => 20,
            ],
            [
                'slug' => 'insulation',
                'name_de' => 'Dämmung/Isolation',
                'name_en' => 'Insulation',
                'name_fr' => 'Isolation',
                'name_it' => 'Isolamento',
                'color' => '#ff5722',
                'sort_order' => 21,
            ],
            [
                'slug' => 'facade',
                'name_de' => 'Fassade',
                'name_en' => 'Facade',
                'name_fr' => 'Façade',
                'name_it' => 'Facciata',
                'color' => '#9e9e9e',
                'sort_order' => 22,
            ],
            [
                'slug' => 'roof_renovation',
                'name_de' => 'Dachsanierung',
                'name_en' => 'Roof Renovation',
                'name_fr' => 'Rénovation de toiture',
                'name_it' => 'Ristrutturazione tetto',
                'color' => '#d32f2f',
                'sort_order' => 23,
            ],
            [
                'slug' => 'balcony',
                'name_de' => 'Balkon Anbau',
                'name_en' => 'Balcony Extension',
                'name_fr' => 'Extension balcon',
                'name_it' => 'Estensione balcone',
                'color' => '#03a9f4',
                'sort_order' => 24,
            ],
            [
                'slug' => 'real_estate',
                'name_de' => 'Immobilien-Makler Immobilienschätzung',
                'name_en' => 'Real Estate Agent/Valuation',
                'name_fr' => 'Agent immobilier/Estimation',
                'name_it' => 'Agente immobiliare/Valutazione',
                'color' => '#673ab7',
                'sort_order' => 25,
            ],
        ];

        $db = \Config\Database::connect();

        foreach ($projects as $project) {
            // Prüfen ob bereits vorhanden
            $exists = $db->table('projects')
                         ->where('slug', $project['slug'])
                         ->countAllResults();

            if ($exists === 0) {
                $project['is_active'] = 1;
                $project['created_at'] = date('Y-m-d H:i:s');
                $project['updated_at'] = date('Y-m-d H:i:s');
                $db->table('projects')->insert($project);
            }
        }
    }
}
