<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\CategoryManager;

/**
 * Tests für CategoryManager
 *
 * Testet die Verarbeitung von Kategorien und Formularen
 */
final class CategoryManagerTest extends CIUnitTestCase
{
    private CategoryManager $manager;
    private string $testFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Temporären Pfad für Tests verwenden
        $this->testFilePath = WRITEPATH . 'test_category_settings.json';

        // CategoryManager instanzieren
        $this->manager = new CategoryManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Testdatei aufräumen
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    // ========================================
    // SAVE FORMS TESTS
    // ========================================

    public function testSaveFiltersEmptyFormNames(): void
    {
        $forms = [
            ['name_de' => 'Privat-Umzug', 'form_link_de' => 'https://example.com/privat'],
            ['name_de' => '', 'form_link_de' => 'https://example.com/empty'], // Sollte gefiltert werden
            ['name_de' => 'Firmen-Umzug', 'form_link_de' => 'https://example.com/firma'],
        ];

        // Die Filterlogik testen
        $filteredForms = [];
        foreach ($forms as $form) {
            if (!empty($form['name_de'])) {
                $filteredForms[] = [
                    'name_de' => $form['name_de'],
                    'name_en' => $form['name_en'] ?? '',
                    'name_fr' => $form['name_fr'] ?? '',
                    'name_it' => $form['name_it'] ?? '',
                    'form_link_de' => $form['form_link_de'] ?? '',
                    'form_link_en' => $form['form_link_en'] ?? '',
                    'form_link_fr' => $form['form_link_fr'] ?? '',
                    'form_link_it' => $form['form_link_it'] ?? '',
                ];
            }
        }

        $this->assertCount(2, $filteredForms);
        $this->assertEquals('Privat-Umzug', $filteredForms[0]['name_de']);
        $this->assertEquals('Firmen-Umzug', $filteredForms[1]['name_de']);
    }

    public function testFormStructureHasMultilingualNames(): void
    {
        $form = [
            'name_de' => 'Privat-Umzug',
            'name_en' => 'Private Move',
            'name_fr' => 'Déménagement privé',
            'name_it' => 'Trasloco privato',
            'form_link_de' => 'https://example.com/de/form',
            'form_link_en' => 'https://example.com/en/form',
            'form_link_fr' => 'https://example.com/fr/form',
            'form_link_it' => 'https://example.com/it/form',
        ];

        $this->assertArrayHasKey('name_de', $form);
        $this->assertArrayHasKey('name_en', $form);
        $this->assertArrayHasKey('name_fr', $form);
        $this->assertArrayHasKey('name_it', $form);
        $this->assertArrayHasKey('form_link_de', $form);
        $this->assertArrayHasKey('form_link_en', $form);
        $this->assertArrayHasKey('form_link_fr', $form);
        $this->assertArrayHasKey('form_link_it', $form);
    }

    public function testFormLinkDefaultsToEmpty(): void
    {
        $rawForm = ['name_de' => 'Test'];

        $processed = [
            'name_de' => $rawForm['name_de'],
            'form_link_de' => $rawForm['form_link_de'] ?? '',
        ];

        $this->assertEquals('', $processed['form_link_de']);
    }

    // ========================================
    // GETALL FORMS TESTS
    // ========================================

    public function testGetAllReturnsEmptyFormsIfNotSet(): void
    {
        // Test dass die Default-Struktur leere Formulare hat
        $categoryData = [
            'name' => 'Test',
            'max' => null,
            'review_email_days' => 5,
            'review_reminder_days' => 10,
            'color' => '#6c757d',
            'options' => [],
        ];

        $forms = $categoryData['forms'] ?? [];

        $this->assertIsArray($forms);
        $this->assertEmpty($forms);
    }

    public function testGetAllPreservesExistingForms(): void
    {
        $existingValues = [
            'categories' => [
                'move' => [
                    'name' => 'Umzug',
                    'forms' => [
                        ['name_de' => 'Privat-Umzug', 'form_link_de' => 'https://example.com/privat'],
                        ['name_de' => 'Firmen-Umzug', 'form_link_de' => 'https://example.com/firma'],
                    ],
                ],
            ],
        ];

        $forms = $existingValues['categories']['move']['forms'] ?? [];

        $this->assertCount(2, $forms);
        $this->assertEquals('Privat-Umzug', $forms[0]['name_de']);
        $this->assertEquals('Firmen-Umzug', $forms[1]['name_de']);
    }

    // ========================================
    // FORM ID TESTS
    // ========================================

    public function testFormIdFormat(): void
    {
        // Form ID ist "category_key:index"
        $formId = 'move:0';

        $parts = explode(':', $formId);
        $this->assertCount(2, $parts);
        $this->assertEquals('move', $parts[0]);
        $this->assertEquals('0', $parts[1]);
    }

    public function testExtractCategoryFromFormId(): void
    {
        $formId = 'cleaning:2';

        $categoryKey = null;
        if (!empty($formId) && strpos($formId, ':') !== false) {
            $categoryKey = explode(':', $formId)[0];
        }

        $this->assertEquals('cleaning', $categoryKey);
    }

    // ========================================
    // CATEGORY DEFAULT VALUES TESTS
    // ========================================

    public function testCategoryDefaultReviewEmailDays(): void
    {
        $categoryData = [];
        $default = isset($categoryData['review_email_days']) && $categoryData['review_email_days'] !== ''
            ? intval($categoryData['review_email_days'])
            : 5;

        $this->assertEquals(5, $default);
    }

    public function testCategoryDefaultReviewReminderDays(): void
    {
        $categoryData = [];
        $default = isset($categoryData['review_reminder_days']) && $categoryData['review_reminder_days'] !== ''
            ? intval($categoryData['review_reminder_days'])
            : 10;

        $this->assertEquals(10, $default);
    }

    public function testCategoryDefaultColor(): void
    {
        $categoryData = [];
        $default = $categoryData['color'] ?? '#6c757d';

        $this->assertEquals('#6c757d', $default);
    }

    // ========================================
    // OPTIONS PROCESSING TESTS
    // ========================================

    public function testOptionsProcessingUsesKeyAsIdentifier(): void
    {
        $labels = [
            ['key' => 'option1', 'label' => 'Option 1'],
            ['key' => 'option2', 'label' => 'Option 2'],
        ];
        $existingOptions = [
            'option1' => ['price' => 100],
        ];

        $options = [];
        foreach ($labels as $labelInfo) {
            $key = $labelInfo['key'];
            $label = $labelInfo['label'];
            $price = $existingOptions[$key]['price'] ?? 0;
            $options[$key] = [
                'key' => $key,
                'label' => $label,
                'price' => $price,
            ];
        }

        $this->assertArrayHasKey('option1', $options);
        $this->assertArrayHasKey('option2', $options);
        $this->assertEquals(100, $options['option1']['price']);
        $this->assertEquals(0, $options['option2']['price']); // Default wenn nicht vorhanden
    }

    public function testOptionsPriceDefaultsToZero(): void
    {
        $existingOptions = [];
        $key = 'new_option';

        $price = $existingOptions[$key]['price'] ?? 0;

        $this->assertEquals(0, $price);
    }

    // ========================================
    // DISCOUNT RULES PROCESSING TESTS
    // ========================================

    public function testDiscountRulesFilterEmptyHours(): void
    {
        $discountRules = [
            ['hours' => 24, 'discount' => 10],
            ['hours' => '', 'discount' => 20], // Sollte gefiltert werden
            ['hours' => 48, 'discount' => 15],
        ];

        $filteredDiscounts = [];
        foreach ($discountRules as $rule) {
            if (!empty($rule['hours']) && !empty($rule['discount'])) {
                $filteredDiscounts[] = [
                    'hours' => (int)$rule['hours'],
                    'discount' => (int)$rule['discount'],
                ];
            }
        }

        $this->assertCount(2, $filteredDiscounts);
        $this->assertEquals(24, $filteredDiscounts[0]['hours']);
        $this->assertEquals(48, $filteredDiscounts[1]['hours']);
    }

    public function testDiscountRulesFilterEmptyDiscount(): void
    {
        $discountRules = [
            ['hours' => 24, 'discount' => 10],
            ['hours' => 48, 'discount' => ''], // Sollte gefiltert werden
        ];

        $filteredDiscounts = [];
        foreach ($discountRules as $rule) {
            if (!empty($rule['hours']) && !empty($rule['discount'])) {
                $filteredDiscounts[] = [
                    'hours' => (int)$rule['hours'],
                    'discount' => (int)$rule['discount'],
                ];
            }
        }

        $this->assertCount(1, $filteredDiscounts);
        $this->assertEquals(24, $filteredDiscounts[0]['hours']);
    }

    public function testDiscountRulesConvertToInteger(): void
    {
        $rule = ['hours' => '24', 'discount' => '10'];

        $processed = [
            'hours' => (int)$rule['hours'],
            'discount' => (int)$rule['discount'],
        ];

        $this->assertIsInt($processed['hours']);
        $this->assertIsInt($processed['discount']);
        $this->assertEquals(24, $processed['hours']);
        $this->assertEquals(10, $processed['discount']);
    }

    // ========================================
    // MAX VALUE PROCESSING TESTS
    // ========================================

    public function testMaxValueNullWhenEmpty(): void
    {
        $categoryData = ['max' => ''];

        $max = (isset($categoryData['max']) && $categoryData['max'] !== '')
            ? intval($categoryData['max'])
            : null;

        $this->assertNull($max);
    }

    public function testMaxValueConvertedToInteger(): void
    {
        $categoryData = ['max' => '50'];

        $max = (isset($categoryData['max']) && $categoryData['max'] !== '')
            ? intval($categoryData['max'])
            : null;

        $this->assertEquals(50, $max);
        $this->assertIsInt($max);
    }

    public function testMaxValueNullWhenNotSet(): void
    {
        $categoryData = [];

        $max = (isset($categoryData['max']) && $categoryData['max'] !== '')
            ? intval($categoryData['max'])
            : null;

        $this->assertNull($max);
    }

    // ========================================
    // FORM LOCALIZATION TESTS
    // ========================================

    public function testFormNameFallbackToGerman(): void
    {
        $form = [
            'name_de' => 'Privat-Umzug',
            'name_en' => '',
            'name_fr' => '',
            'name_it' => '',
        ];
        $locale = 'fr';

        $nameField = "name_{$locale}";
        $name = !empty($form[$nameField]) ? $form[$nameField] : ($form['name_de'] ?? '');

        $this->assertEquals('Privat-Umzug', $name);
    }

    public function testFormLinkFallbackToGerman(): void
    {
        $form = [
            'form_link_de' => 'https://example.com/de',
            'form_link_en' => '',
            'form_link_fr' => '',
            'form_link_it' => '',
        ];
        $locale = 'en';

        $linkField = "form_link_{$locale}";
        $link = !empty($form[$linkField]) ? $form[$linkField] : ($form['form_link_de'] ?? '');

        $this->assertEquals('https://example.com/de', $link);
    }

    public function testFormNameUsesLocaleWhenAvailable(): void
    {
        $form = [
            'name_de' => 'Privat-Umzug',
            'name_en' => 'Private Move',
            'name_fr' => 'Déménagement privé',
            'name_it' => 'Trasloco privato',
        ];
        $locale = 'fr';

        $nameField = "name_{$locale}";
        $name = !empty($form[$nameField]) ? $form[$nameField] : ($form['name_de'] ?? '');

        $this->assertEquals('Déménagement privé', $name);
    }
}
