<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedProjectsData extends Migration
{
    public function up()
    {
        $projects = [
            ['slug' => 'new_build', 'name_de' => 'Neubau', 'name_en' => 'New Build', 'name_fr' => 'Construction neuve', 'name_it' => 'Nuova costruzione', 'sort_order' => 1],
            ['slug' => 'renovation', 'name_de' => 'Umbau', 'name_en' => 'Renovation', 'name_fr' => 'Rénovation', 'name_it' => 'Ristrutturazione', 'sort_order' => 2],
            ['slug' => 'bathroom', 'name_de' => 'Bad/WC Sanierung', 'name_en' => 'Bathroom Renovation', 'name_fr' => 'Rénovation salle de bain', 'name_it' => 'Ristrutturazione bagno', 'sort_order' => 3],
            ['slug' => 'sauna', 'name_de' => 'Sauna', 'name_en' => 'Sauna', 'name_fr' => 'Sauna', 'name_it' => 'Sauna', 'sort_order' => 4],
            ['slug' => 'hvac', 'name_de' => 'Luft/Klima Heizungsanlage', 'name_en' => 'HVAC System', 'name_fr' => 'Système CVC', 'name_it' => 'Sistema HVAC', 'sort_order' => 5],
            ['slug' => 'stairs', 'name_de' => 'Treppe/Geländer', 'name_en' => 'Stairs/Railings', 'name_fr' => 'Escalier/Garde-corps', 'name_it' => 'Scale/Ringhiere', 'sort_order' => 6],
            ['slug' => 'alarm', 'name_de' => 'Alarmanlage', 'name_en' => 'Alarm System', 'name_fr' => 'Système d\'alarme', 'name_it' => 'Sistema d\'allarme', 'sort_order' => 7],
            ['slug' => 'ev_charger', 'name_de' => 'E-Ladestation', 'name_en' => 'EV Charging Station', 'name_fr' => 'Borne de recharge', 'name_it' => 'Stazione di ricarica EV', 'sort_order' => 8],
            ['slug' => 'smart_home', 'name_de' => 'Smart Home Komplettsystem', 'name_en' => 'Smart Home System', 'name_fr' => 'Système domotique', 'name_it' => 'Sistema domotico', 'sort_order' => 9],
            ['slug' => 'solar', 'name_de' => 'Solaranlage', 'name_en' => 'Solar System', 'name_fr' => 'Installation solaire', 'name_it' => 'Impianto solare', 'sort_order' => 10],
            ['slug' => 'doors', 'name_de' => 'Innen/Aussentüren', 'name_en' => 'Interior/Exterior Doors', 'name_fr' => 'Portes intérieures/extérieures', 'name_it' => 'Porte interne/esterne', 'sort_order' => 11],
            ['slug' => 'blinds', 'name_de' => 'Storen/Rolläden', 'name_en' => 'Blinds/Shutters', 'name_fr' => 'Stores/Volets', 'name_it' => 'Tapparelle/Persiane', 'sort_order' => 12],
            ['slug' => 'shading', 'name_de' => 'Beschattung', 'name_en' => 'Shading', 'name_fr' => 'Protection solaire', 'name_it' => 'Ombreggiatura', 'sort_order' => 13],
            ['slug' => 'insect_screen', 'name_de' => 'Fliegengitter/Insektenschutz', 'name_en' => 'Insect Screen', 'name_fr' => 'Moustiquaire', 'name_it' => 'Zanzariera', 'sort_order' => 14],
            ['slug' => 'winter_garden', 'name_de' => 'Wintergarten', 'name_en' => 'Winter Garden', 'name_fr' => 'Jardin d\'hiver', 'name_it' => 'Giardino d\'inverno', 'sort_order' => 15],
            ['slug' => 'pool', 'name_de' => 'Poolanlage', 'name_en' => 'Pool', 'name_fr' => 'Piscine', 'name_it' => 'Piscina', 'sort_order' => 16],
            ['slug' => 'mold', 'name_de' => 'Schimmel Entfernung', 'name_en' => 'Mold Removal', 'name_fr' => 'Élimination des moisissures', 'name_it' => 'Rimozione muffa', 'sort_order' => 17],
            ['slug' => 'locks', 'name_de' => 'Schlüssel/Schliessanlage', 'name_en' => 'Locks/Security Systems', 'name_fr' => 'Serrures/Systèmes de sécurité', 'name_it' => 'Serrature/Sistemi di sicurezza', 'sort_order' => 18],
            ['slug' => 'carport', 'name_de' => 'Carport', 'name_en' => 'Carport', 'name_fr' => 'Carport', 'name_it' => 'Tettoia auto', 'sort_order' => 19],
            ['slug' => 'garage', 'name_de' => 'Garagenbox', 'name_en' => 'Garage Box', 'name_fr' => 'Box garage', 'name_it' => 'Box garage', 'sort_order' => 20],
            ['slug' => 'insulation', 'name_de' => 'Dämmung/Isolation', 'name_en' => 'Insulation', 'name_fr' => 'Isolation', 'name_it' => 'Isolamento', 'sort_order' => 21],
            ['slug' => 'facade', 'name_de' => 'Fassade', 'name_en' => 'Facade', 'name_fr' => 'Façade', 'name_it' => 'Facciata', 'sort_order' => 22],
            ['slug' => 'roof_renovation', 'name_de' => 'Dachsanierung', 'name_en' => 'Roof Renovation', 'name_fr' => 'Rénovation de toiture', 'name_it' => 'Ristrutturazione tetto', 'sort_order' => 23],
            ['slug' => 'balcony', 'name_de' => 'Balkon Anbau', 'name_en' => 'Balcony Extension', 'name_fr' => 'Extension balcon', 'name_it' => 'Estensione balcone', 'sort_order' => 24],
            ['slug' => 'real_estate', 'name_de' => 'Immobilien-Makler Immobilienschätzung', 'name_en' => 'Real Estate Agent/Valuation', 'name_fr' => 'Agent immobilier/Estimation', 'name_it' => 'Agente immobiliare/Valutazione', 'sort_order' => 25],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($projects as $project) {
            // Check if already exists
            $exists = $this->db->table('projects')
                               ->where('slug', $project['slug'])
                               ->countAllResults();

            if ($exists === 0) {
                $project['is_active'] = 1;
                $project['created_at'] = $now;
                $project['updated_at'] = $now;
                $this->db->table('projects')->insert($project);
            }
        }
    }

    public function down()
    {
        // Delete all seeded projects
        $slugs = [
            'new_build', 'renovation', 'bathroom', 'sauna', 'hvac', 'stairs',
            'alarm', 'ev_charger', 'smart_home', 'solar', 'doors', 'blinds',
            'shading', 'insect_screen', 'winter_garden', 'pool', 'mold', 'locks',
            'carport', 'garage', 'insulation', 'facade', 'roof_renovation',
            'balcony', 'real_estate'
        ];

        $this->db->table('projects')->whereIn('slug', $slugs)->delete();
    }
}
