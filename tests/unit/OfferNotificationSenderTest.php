<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\OfferNotificationSender;

/**
 * Tests für die Offer Notification Sender Logik
 *
 * Testet hauptsächlich die Hilfsmethoden (getOfferTypeForSubject, extractFieldsForTemplate)
 * ohne tatsächlich E-Mails zu versenden
 */
final class OfferNotificationSenderTest extends CIUnitTestCase
{
    private OfferNotificationSender $sender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = new OfferNotificationSender();
    }

    // ========================================
    // GET OFFER TYPE FOR SUBJECT TESTS
    // ========================================

    public function testGetOfferTypeForSubjectMove(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'move');

        $this->assertEquals('Umzug', $result);
    }

    public function testGetOfferTypeForSubjectCleaning(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'cleaning');

        $this->assertEquals('Reinigung', $result);
    }

    public function testGetOfferTypeForSubjectMoveCleaning(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'move_cleaning');

        $this->assertEquals('Umzug + Reinigung', $result);
    }

    public function testGetOfferTypeForSubjectPainting(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'painting');

        $this->assertEquals('Maler/Gipser', $result);
    }

    public function testGetOfferTypeForSubjectGardening(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'gardening');

        $this->assertEquals('Garten Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectElectrician(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'electrician');

        $this->assertEquals('Elektriker Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectPlumbing(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'plumbing');

        $this->assertEquals('Sanitär Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectHeating(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'heating');

        $this->assertEquals('Heizung Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectTiling(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'tiling');

        $this->assertEquals('Platten Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectFlooring(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'flooring');

        $this->assertEquals('Boden Arbeiten', $result);
    }

    public function testGetOfferTypeForSubjectUnknown(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('getOfferTypeForSubject');
        $method->setAccessible(true);

        $result = $method->invoke($this->sender, 'some_unknown_type');

        // Sollte formatiert werden: underscores zu Leerzeichen, erster Buchstabe gross
        $this->assertEquals('Some unknown type', $result);
    }

    // ========================================
    // EXTRACT FIELDS FOR TEMPLATE TESTS
    // ========================================

    public function testExtractFieldsBasicInfo(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Zürich',
            'zip' => '8001',
            'country' => 'CH',
            'data' => []
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Zürich', $result['city']);
        $this->assertEquals('8001', $result['zip']);
        $this->assertEquals('CH', $result['country']);
    }

    public function testExtractFieldsFromAddressArray(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Zürich',
            'zip' => '8001',
            'data' => [
                'address' => [
                    'address_line_1' => 'Bahnhofstrasse',
                    'address_line_2' => '42',
                    'zip' => '8001',
                    'city' => 'Zürich'
                ]
            ]
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Bahnhofstrasse', $result['address_street']);
        $this->assertEquals('42', $result['address_number']);
        $this->assertEquals('8001', $result['address_zip']);
        $this->assertEquals('Zürich', $result['address_city']);
    }

    public function testExtractFieldsFromAuszugAdresse(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        // Verwende type=cleaning um DB-Zugriff auf offers_move zu vermeiden
        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Basel',
            'zip' => '4000',
            'data' => [
                'auszug_adresse' => [
                    'address_line_1' => 'Marktplatz',
                    'address_line_2' => '1',
                    'zip' => '4000',
                    'city' => 'Basel'
                ]
            ]
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Marktplatz', $result['auszug_street']);
        $this->assertEquals('1', $result['auszug_number']);
        $this->assertEquals('4000', $result['auszug_zip']);
        $this->assertEquals('Basel', $result['auszug_city']);
    }

    public function testExtractFieldsFromEinzugAdresse(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        // Verwende type=cleaning um DB-Zugriff auf offers_move zu vermeiden
        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Basel',
            'zip' => '4000',
            'data' => [
                'einzug_adresse' => [
                    'address_line_1' => 'Bundesplatz',
                    'address_line_2' => '5',
                    'zip' => '3000',
                    'city' => 'Bern'
                ]
            ]
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Bundesplatz', $result['einzug_street']);
        $this->assertEquals('5', $result['einzug_number']);
        $this->assertEquals('3000', $result['einzug_zip']);
        $this->assertEquals('Bern', $result['einzug_city']);
    }

    public function testExtractFieldsSimpleFields(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Zürich',
            'zip' => '8001',
            'data' => [
                'vorname' => 'Hans',
                'nachname' => 'Muster',
                'email' => 'hans@example.com',
                'phone' => '+41791234567',
                'details_hinweise' => 'Bitte nachmittags'
            ]
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Hans', $result['vorname']);
        $this->assertEquals('Muster', $result['nachname']);
        $this->assertEquals('hans@example.com', $result['email']);
        $this->assertEquals('+41791234567', $result['phone']);
        $this->assertEquals('Bitte nachmittags', $result['details_hinweise']);
    }

    public function testExtractFieldsIgnoresArrayFields(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Zürich',
            'zip' => '8001',
            'data' => [
                'vorname' => 'Hans',
                'some_complex_field' => ['value1', 'value2'] // Array sollte ignoriert werden
            ]
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Hans', $result['vorname']);
        $this->assertArrayNotHasKey('some_complex_field', $result);
    }

    public function testExtractFieldsFromStringData(): void
    {
        $reflection = new \ReflectionClass($this->sender);
        $method = $reflection->getMethod('extractFieldsForTemplate');
        $method->setAccessible(true);

        $offer = [
            'id' => 1,
            'type' => 'cleaning',
            'city' => 'Zürich',
            'zip' => '8001',
            'data' => json_encode([
                'vorname' => 'Maria',
                'nachname' => 'Müller'
            ])
        ];

        $result = $method->invoke($this->sender, $offer);

        $this->assertEquals('Maria', $result['vorname']);
        $this->assertEquals('Müller', $result['nachname']);
    }

    // ========================================
    // NOTIFY MATCHING USERS - SECURITY TESTS
    // ========================================

    public function testNotifyRejectsUnverifiedOffer(): void
    {
        $offer = [
            'id' => 999,
            'type' => 'cleaning',
            'verified' => 0, // Nicht verifiziert
            'city' => 'Zürich',
            'zip' => '8001'
        ];

        $result = $this->sender->notifyMatchingUsers($offer);

        $this->assertEquals(0, $result, 'Unverifizierte Offerten sollten keine E-Mails auslösen');
    }

    public function testNotifyRejectsOfferWithEmptyVerified(): void
    {
        $offer = [
            'id' => 999,
            'type' => 'cleaning',
            'verified' => null, // Nicht gesetzt
            'city' => 'Zürich',
            'zip' => '8001'
        ];

        $result = $this->sender->notifyMatchingUsers($offer);

        $this->assertEquals(0, $result, 'Offerten ohne verified-Flag sollten keine E-Mails auslösen');
    }
}
