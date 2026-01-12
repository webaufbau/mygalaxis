<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'slug',
        'name_de',
        'name_en',
        'name_fr',
        'name_it',
        'form_link',
        'color',
        'category_types',
        'sort_order',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'slug' => 'required|alpha_dash|max_length[50]|is_unique[projects.slug,id,{id}]',
        'name_de' => 'required|max_length[100]',
    ];

    /**
     * Alle aktiven Projekte holen, sortiert
     */
    public function getActiveProjects(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('name_de', 'ASC')
                    ->findAll();
    }

    /**
     * Projekte mit übersetztem Namen holen
     */
    public function getActiveProjectsWithNames(?string $locale = null): array
    {
        $locale = $locale ?? service('request')->getLocale();
        $projects = $this->getActiveProjects();

        foreach ($projects as &$project) {
            $project['name'] = $this->getLocalizedName($project, $locale);
        }

        return $projects;
    }

    /**
     * Lokalisierter Name
     */
    public function getLocalizedName(array $project, ?string $locale = null): string
    {
        $locale = $locale ?? service('request')->getLocale();
        $nameField = "name_{$locale}";

        // Fallback zu DE wenn Übersetzung fehlt
        return $project[$nameField] ?? $project['name_de'] ?? $project['slug'];
    }

    /**
     * Projekt nach Slug finden
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Kategorien für ein Projekt holen
     */
    public function getCategoryTypes(array $project): array
    {
        if (empty($project['category_types'])) {
            return [];
        }

        $types = $project['category_types'];
        if (is_string($types)) {
            $types = json_decode($types, true) ?? [];
        }

        return $types;
    }
}
