<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ProjectModel;

/**
 * Tests für ProjectModel
 *
 * Testet Validierung und Lokalisierung
 */
final class ProjectModelTest extends CIUnitTestCase
{
    private ProjectModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ProjectModel();
    }

    // ========================================
    // LOCALIZED NAME TESTS
    // ========================================

    public function testGetLocalizedNameReturnsGerman(): void
    {
        $project = [
            'slug' => 'test-project',
            'name_de' => 'Testprojekt',
            'name_en' => 'Test Project',
            'name_fr' => 'Projet de test',
            'name_it' => 'Progetto di test',
        ];

        $result = $this->model->getLocalizedName($project, 'de');

        $this->assertEquals('Testprojekt', $result);
    }

    public function testGetLocalizedNameReturnsEnglish(): void
    {
        $project = [
            'slug' => 'test-project',
            'name_de' => 'Testprojekt',
            'name_en' => 'Test Project',
            'name_fr' => 'Projet de test',
            'name_it' => 'Progetto di test',
        ];

        $result = $this->model->getLocalizedName($project, 'en');

        $this->assertEquals('Test Project', $result);
    }

    public function testGetLocalizedNameReturnsFrench(): void
    {
        $project = [
            'slug' => 'test-project',
            'name_de' => 'Testprojekt',
            'name_en' => 'Test Project',
            'name_fr' => 'Projet de test',
            'name_it' => 'Progetto di test',
        ];

        $result = $this->model->getLocalizedName($project, 'fr');

        $this->assertEquals('Projet de test', $result);
    }

    public function testGetLocalizedNameReturnsItalian(): void
    {
        $project = [
            'slug' => 'test-project',
            'name_de' => 'Testprojekt',
            'name_en' => 'Test Project',
            'name_fr' => 'Projet de test',
            'name_it' => 'Progetto di test',
        ];

        $result = $this->model->getLocalizedName($project, 'it');

        $this->assertEquals('Progetto di test', $result);
    }

    public function testGetLocalizedNameFallsBackToGerman(): void
    {
        $project = [
            'slug' => 'test-project',
            'name_de' => 'Testprojekt',
            'name_en' => null,
            'name_fr' => null,
            'name_it' => null,
        ];

        $result = $this->model->getLocalizedName($project, 'fr');

        $this->assertEquals('Testprojekt', $result);
    }

    public function testGetLocalizedNameFallsBackToSlug(): void
    {
        $project = [
            'slug' => 'test-project',
        ];

        $result = $this->model->getLocalizedName($project, 'de');

        $this->assertEquals('test-project', $result);
    }

    // ========================================
    // CATEGORY TYPES TESTS
    // ========================================

    public function testGetCategoryTypesReturnsEmptyForNullCategoryTypes(): void
    {
        $project = [
            'slug' => 'test',
            'category_types' => null,
        ];

        $result = $this->model->getCategoryTypes($project);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetCategoryTypesReturnsEmptyForEmptyString(): void
    {
        $project = [
            'slug' => 'test',
            'category_types' => '',
        ];

        $result = $this->model->getCategoryTypes($project);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetCategoryTypesDecodesJsonString(): void
    {
        $project = [
            'slug' => 'test',
            'category_types' => '["umzug","reinigung","maler"]',
        ];

        $result = $this->model->getCategoryTypes($project);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(['umzug', 'reinigung', 'maler'], $result);
    }

    public function testGetCategoryTypesReturnsArrayDirectly(): void
    {
        $project = [
            'slug' => 'test',
            'category_types' => ['umzug', 'reinigung'],
        ];

        $result = $this->model->getCategoryTypes($project);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['umzug', 'reinigung'], $result);
    }

    public function testGetCategoryTypesHandlesInvalidJson(): void
    {
        $project = [
            'slug' => 'test',
            'category_types' => 'invalid-json{',
        ];

        $result = $this->model->getCategoryTypes($project);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ========================================
    // VALIDATION RULES TESTS
    // ========================================

    public function testValidationRulesExist(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('validationRules');
        $property->setAccessible(true);
        $rules = $property->getValue($this->model);

        $this->assertArrayHasKey('slug', $rules);
        $this->assertArrayHasKey('name_de', $rules);
        $this->assertArrayHasKey('id', $rules);
    }

    public function testSlugValidationIncludesUniqueness(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('validationRules');
        $property->setAccessible(true);
        $rules = $property->getValue($this->model);

        $this->assertStringContainsString('is_unique[projects.slug,id,{id}]', $rules['slug']);
    }

    public function testSlugValidationRequiresAlphaDash(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('validationRules');
        $property->setAccessible(true);
        $rules = $property->getValue($this->model);

        $this->assertStringContainsString('alpha_dash', $rules['slug']);
    }

    public function testNameDeIsRequired(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('validationRules');
        $property->setAccessible(true);
        $rules = $property->getValue($this->model);

        $this->assertStringContainsString('required', $rules['name_de']);
    }

    // ========================================
    // ALLOWED FIELDS TESTS
    // ========================================

    public function testAllowedFieldsContainsCategoryType(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $fields = $property->getValue($this->model);

        $this->assertContains('category_type', $fields);
    }

    public function testAllowedFieldsContainsAllLanguages(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $fields = $property->getValue($this->model);

        $this->assertContains('name_de', $fields);
        $this->assertContains('name_en', $fields);
        $this->assertContains('name_fr', $fields);
        $this->assertContains('name_it', $fields);
    }

    public function testAllowedFieldsContainsSortOrder(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $fields = $property->getValue($this->model);

        $this->assertContains('sort_order', $fields);
    }

    public function testAllowedFieldsContainsIsActive(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $fields = $property->getValue($this->model);

        $this->assertContains('is_active', $fields);
    }
}
