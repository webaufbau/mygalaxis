<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\OfferModel;

/**
 * Tests für die OfferModel Daten-Extraktion
 *
 * Testet die Logik zum Extrahieren von Daten aus Formularfeldern
 */
final class OfferModelTest extends CIUnitTestCase
{
    private OfferModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new OfferModel();
    }

    // ========================================
    // ADRESS-EXTRAKTION TESTS
    // ========================================

    public function testExtractAddressFromDirectFields(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('extractAddressData');
        $method->setAccessible(true);

        $fields = [
            'city' => 'Zürich',
            'zip' => '8001'
        ];

        $result = $method->invoke($this->model, $fields);

        $this->assertEquals('Zürich', $result['city']);
        $this->assertEquals('8001', $result['zip']);
    }

    public function testExtractAddressFromAuszugAdresse(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('extractAddressData');
        $method->setAccessible(true);

        $fields = [
            'auszug_adresse' => [
                'city' => 'Basel',
                'zip' => '4000'
            ]
        ];

        $result = $method->invoke($this->model, $fields);

        $this->assertEquals('Basel', $result['city']);
        $this->assertEquals('4000', $result['zip']);
    }

    public function testExtractAddressFromEinzugAdresse(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('extractAddressData');
        $method->setAccessible(true);

        $fields = [
            'einzug_adresse' => [
                'city' => 'Bern',
                'zip' => '3000'
            ]
        ];

        $result = $method->invoke($this->model, $fields);

        $this->assertEquals('Bern', $result['city']);
        $this->assertEquals('3000', $result['zip']);
    }

    public function testExtractAddressReturnsNullWhenMissing(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('extractAddressData');
        $method->setAccessible(true);

        $fields = [
            'some_other_field' => 'value'
        ];

        $result = $method->invoke($this->model, $fields);

        $this->assertNull($result['city']);
        $this->assertNull($result['zip']);
    }

    public function testExtractAddressPrioritizesDirectFields(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('extractAddressData');
        $method->setAccessible(true);

        // Direkte Felder haben Priorität vor verschachtelten
        $fields = [
            'city' => 'Zürich',
            'zip' => '8001',
            'auszug_adresse' => [
                'city' => 'Basel',
                'zip' => '4000'
            ]
        ];

        $result = $method->invoke($this->model, $fields);

        $this->assertEquals('Zürich', $result['city']);
        $this->assertEquals('8001', $result['zip']);
    }

    // ========================================
    // MOVE FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractMoveFieldsPrivate(): void
    {
        $fields = [
            'auszug_adresse' => ['city' => 'Zürich', 'zip' => '8001'],
            'einzug_adresse' => ['city' => 'Basel', 'zip' => '4000'],
            'auszug_object' => 'Wohnung',
            'einzug_object' => 'Haus',
            'auszug_zimmer' => '3-Zimmer',
            'datetime_1' => '15/03/2025'
        ];

        $result = $this->model->extractMoveFields($fields);

        $this->assertEquals('Zürich', $result['from_city']);
        $this->assertEquals('Basel', $result['to_city']);
        $this->assertEquals('Wohnung', $result['from_object_type']);
        $this->assertEquals('Haus', $result['to_object_type']);
        $this->assertEquals('3-Zimmer', $result['from_room_count']);
        $this->assertEquals('private', $result['customer_type']);
    }

    public function testExtractMoveFieldsCompany(): void
    {
        $fields = [
            'auszug_adresse_firma' => ['city' => 'Zürich', 'zip' => '8001'],
            'einzug_adresse_firma' => ['city' => 'Basel', 'zip' => '4000'],
            'auszug_object_firma' => 'Büro',
            'einzug_object_firma' => 'Lager'
        ];

        $result = $this->model->extractMoveFields($fields);

        $this->assertEquals('Zürich', $result['from_city']);
        $this->assertEquals('Basel', $result['to_city']);
        $this->assertEquals('Büro', $result['from_object_type']);
        $this->assertEquals('Lager', $result['to_object_type']);
        $this->assertEquals('company', $result['customer_type']);
    }

    // ========================================
    // CLEANING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractCleaningFields(): void
    {
        $fields = [
            'address' => ['city' => 'Luzern', 'zip' => '6000'],
            'benutzer' => 'Mieter',
            'wohnung_groesse' => '3-Zimmer',
            'komplett_anzahlzimmer' => 3,
            'reinigungsart' => 'Endreinigung'
        ];

        $result = $this->model->extractCleaningFields($fields);

        $this->assertEquals('Luzern', $result['address_city']);
        $this->assertEquals('Mieter', $result['user_role']);
        $this->assertEquals('3-Zimmer', $result['apartment_size']);
        $this->assertEquals(3, $result['room_count']);
        $this->assertEquals('Endreinigung', $result['cleaning_type']);
    }

    // ========================================
    // PAINTING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractPaintingFields(): void
    {
        $fields = [
            'address' => ['city' => 'Bern', 'zip' => '3000'],
            'art_objekt' => 'Wohnung',
            'art_gewerbe' => 'Büro',
            'malerarbeiten_uebersicht' => ['Innenräume', 'Fassade'],
            'arbeiten_wohnung' => ['Wände', 'Decken']
        ];

        $result = $this->model->extractPaintingFields($fields);

        $this->assertEquals('Bern', $result['address_city']);
        $this->assertEquals('Wohnung', $result['object_type']);
        $this->assertEquals('Büro', $result['business_type']);
        $this->assertEquals(['Innenräume', 'Fassade'], $result['painting_overview']);
        $this->assertEquals(['Wände', 'Decken'], $result['service_details']);
    }

    // ========================================
    // GARDENING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractGardeningFields(): void
    {
        $fields = [
            'address' => ['city' => 'Winterthur', 'zip' => '8400'],
            'garten_benutzer' => 'Eigentümer',
            'garten_anlegen' => ['Rasen', 'Hecken schneiden']
        ];

        $result = $this->model->extractGardeningFields($fields);

        $this->assertEquals('Winterthur', $result['address_city']);
        $this->assertEquals('Eigentümer', $result['user_role']);
        $this->assertEquals(['Rasen', 'Hecken schneiden'], $result['service_details']);
    }

    // ========================================
    // ELECTRICIAN FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractElectricianFields(): void
    {
        $fields = [
            'address' => ['city' => 'St. Gallen', 'zip' => '9000'],
            'art_objekt' => 'Haus',
            'arbeiten_elektriker' => ['Neubau', 'Solaranlage']
        ];

        $result = $this->model->extractElectricianFields($fields);

        $this->assertEquals('St. Gallen', $result['address_city']);
        $this->assertEquals('Haus', $result['object_type']);
        $this->assertEquals(['Neubau', 'Solaranlage'], $result['service_details']);
    }

    // ========================================
    // PLUMBING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractPlumbingFields(): void
    {
        $fields = [
            'address' => ['city' => 'Chur', 'zip' => '7000'],
            'art_objekt' => 'Mehrfamilienhaus',
            'arbeiten_sanitaer' => ['Neubau', 'Bad/WC Sanierung']
        ];

        $result = $this->model->extractPlumbingFields($fields);

        $this->assertEquals('Chur', $result['address_city']);
        $this->assertEquals('Mehrfamilienhaus', $result['object_type']);
        $this->assertEquals(['Neubau', 'Bad/WC Sanierung'], $result['service_details']);
    }

    // ========================================
    // HEATING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractHeatingFields(): void
    {
        $fields = [
            'address' => ['city' => 'Thun', 'zip' => '3600'],
            'art_objekt' => 'Haus',
            'arbeiten_heizung' => ['Neubau', 'Neue el. Wärmepumpe']
        ];

        $result = $this->model->extractHeatingFields($fields);

        $this->assertEquals('Thun', $result['address_city']);
        $this->assertEquals('Haus', $result['object_type']);
        $this->assertEquals(['Neubau', 'Neue el. Wärmepumpe'], $result['service_details']);
    }

    // ========================================
    // TILING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractTilingFields(): void
    {
        $fields = [
            'address' => ['city' => 'Aarau', 'zip' => '5000'],
            'art_objekt' => 'Wohnung',
            'arbeiten_platten' => ['Platten entfernen', 'Platten verlegen']
        ];

        $result = $this->model->extractTilingFields($fields);

        $this->assertEquals('Aarau', $result['address_city']);
        $this->assertEquals('Wohnung', $result['object_type']);
        $this->assertEquals(['Platten entfernen', 'Platten verlegen'], $result['service_details']);
    }

    // ========================================
    // FLOORING FIELDS EXTRAKTION TESTS
    // ========================================

    public function testExtractFlooringFields(): void
    {
        $fields = [
            'address' => ['city' => 'Baden', 'zip' => '5400'],
            'art_objekt' => 'Gewerbe',
            'arbeiten_boden' => ['Belag entfernen', 'Belag verlegen']
        ];

        $result = $this->model->extractFlooringFields($fields);

        $this->assertEquals('Baden', $result['address_city']);
        $this->assertEquals('Gewerbe', $result['object_type']);
        $this->assertEquals(['Belag entfernen', 'Belag verlegen'], $result['service_details']);
    }

    // ========================================
    // EXTRACT FIELDS BY TYPE TESTS
    // ========================================

    public function testExtractFieldsByTypeRouting(): void
    {
        $fields = [
            'address' => ['city' => 'Test', 'zip' => '1234'],
            'art_objekt' => 'Wohnung',
            'arbeiten_elektriker' => ['Test']
        ];

        $result = $this->model->extractFieldsByType('electrician', $fields);

        $this->assertArrayHasKey('object_type', $result);
        $this->assertArrayHasKey('service_details', $result);
    }

    public function testExtractFieldsByTypeUnknownReturnsEmpty(): void
    {
        $result = $this->model->extractFieldsByType('unknown_type', []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ========================================
    // ENRICH DATA TESTS
    // ========================================

    public function testEnrichDataExtractsBasicInfo(): void
    {
        $formFields = [
            'vorname' => 'Hans',
            'nachname' => 'Muster',
            'email' => 'hans@example.com',
            'phone' => '+41791234567',
            'city' => 'Zürich',
            'zip' => '8001',
            'language' => 'de',
            'platform' => 'offertenheld.ch'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('Hans', $result['firstname']);
        $this->assertEquals('Muster', $result['lastname']);
        $this->assertEquals('hans@example.com', $result['email']);
        $this->assertEquals('+41791234567', $result['phone']);
        $this->assertEquals('Zürich', $result['city']);
        $this->assertEquals('8001', $result['zip']);
        $this->assertEquals('de', $result['language']);
    }

    public function testEnrichDataDetectsPrivateCustomer(): void
    {
        $formFields = [
            'vorname' => 'Maria',
            'nachname' => 'Müller'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('privat', $result['customer_type']);
    }

    public function testEnrichDataDetectsCompanyCustomer(): void
    {
        $formFields = [
            'vorname' => 'Hans',
            'nachname' => 'Muster',
            'firmenname' => 'Muster AG'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('firma', $result['customer_type']);
    }

    public function testEnrichDataNormalizesPlatformFromDomain(): void
    {
        $formFields = [
            'platform' => 'offertenheld.ch'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('my_offertenheld_ch', $result['platform']);
    }

    public function testEnrichDataKeepsPlatformIfAlreadyNormalized(): void
    {
        $formFields = [
            'platform' => 'my_offertenheld_ch'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('my_offertenheld_ch', $result['platform']);
    }

    public function testEnrichDataDefaultsLanguageToGerman(): void
    {
        $formFields = [];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('de', $result['language']);
    }

    public function testEnrichDataExtractsLanguageFromLangField(): void
    {
        $formFields = [
            'lang' => 'fr'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('fr', $result['language']);
    }

    public function testEnrichDataGeneratesUuid(): void
    {
        $formFields = [];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertEquals(32, strlen($result['uuid'])); // 16 bytes = 32 hex chars
    }

    public function testEnrichDataDoesNotOverwriteExistingUuid(): void
    {
        $formFields = [];
        $original = ['uuid' => 'existing-uuid-12345'];

        $result = $this->model->enrichDataFromFormFields($formFields, $original);

        $this->assertArrayNotHasKey('uuid', $result);
    }

    public function testEnrichDataFormatsWorkStartDate(): void
    {
        $formFields = [
            'datetime_1' => '25/12/2025'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('2025-12-25', $result['work_start_date']);
    }

    public function testEnrichDataExtractsCompanyName(): void
    {
        $formFields = [
            'firma' => 'Test GmbH'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('Test GmbH', $result['company']);
    }

    public function testEnrichDataExtractsCompanyNameFromFirmenname(): void
    {
        $formFields = [
            'firmenname' => 'Muster AG'
        ];

        $result = $this->model->enrichDataFromFormFields($formFields);

        $this->assertEquals('Muster AG', $result['company']);
    }

    // ========================================
    // MAX PURCHASES CONSTANT TEST
    // ========================================

    public function testMaxPurchasesConstant(): void
    {
        $this->assertEquals(4, OfferModel::MAX_PURCHASES);
    }
}
