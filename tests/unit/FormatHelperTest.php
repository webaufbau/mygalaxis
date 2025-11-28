<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests für die Format-Helper Funktionen
 */
final class FormatHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('format');
    }

    // ========================================
    // TRIM RECURSIVE TESTS
    // ========================================

    public function testTrimRecursiveTrimsString(): void
    {
        $result = trim_recursive('  hello world  ');

        $this->assertEquals('hello world', $result);
    }

    public function testTrimRecursiveTrimsArrayValues(): void
    {
        $input = [
            'name' => '  Hans  ',
            'email' => ' test@example.com ',
        ];

        $result = trim_recursive($input);

        $this->assertEquals('Hans', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function testTrimRecursiveHandlesNestedArrays(): void
    {
        $input = [
            'user' => [
                'name' => '  Maria  ',
                'address' => [
                    'street' => '  Hauptstrasse 1  '
                ]
            ]
        ];

        $result = trim_recursive($input);

        $this->assertEquals('Maria', $result['user']['name']);
        $this->assertEquals('Hauptstrasse 1', $result['user']['address']['street']);
    }

    public function testTrimRecursiveLeavesNonStringsUnchanged(): void
    {
        $input = [
            'count' => 42,
            'active' => true,
            'data' => null
        ];

        $result = trim_recursive($input);

        $this->assertSame(42, $result['count']);
        $this->assertSame(true, $result['active']);
        $this->assertNull($result['data']);
    }

    // ========================================
    // NORMALIZE KEY TESTS
    // ========================================

    public function testNormalizeKeyConvertsUmlauts(): void
    {
        $this->assertEquals('auszug_adresse', normalize_key('Auszug Adresse'));
        $this->assertEquals('groesse', normalize_key('Größe'));
        $this->assertEquals('fuer', normalize_key('Für'));
    }

    public function testNormalizeKeyToLowercase(): void
    {
        $result = normalize_key('GROSS');

        $this->assertEquals('gross', $result);
    }

    public function testNormalizeKeyRemovesSpecialChars(): void
    {
        $result = normalize_key('Name & Vorname!');

        $this->assertEquals('name_vorname', $result);
    }

    public function testNormalizeKeyConvertsSpacesToUnderscores(): void
    {
        $result = normalize_key('auszug zimmer');

        $this->assertEquals('auszug_zimmer', $result);
    }

    public function testNormalizeKeyHandlesMultipleSpaces(): void
    {
        $result = normalize_key('auszug    zimmer');

        $this->assertEquals('auszug_zimmer', $result);
    }

    // ========================================
    // NORMALIZE KEYS RECURSIVE TESTS
    // ========================================

    public function testNormalizeKeysRecursiveNormalizesAllKeys(): void
    {
        $input = [
            'Vor Name' => 'Hans',
            'E-Mail' => 'test@test.ch'
        ];

        $result = normalize_keys_recursive($input);

        $this->assertArrayHasKey('vor_name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertEquals('Hans', $result['vor_name']);
    }

    public function testNormalizeKeysRecursiveHandlesNestedArrays(): void
    {
        $input = [
            'Benutzer Daten' => [
                'Vor Name' => 'Hans',
                'Nach Name' => 'Muster'
            ]
        ];

        $result = normalize_keys_recursive($input);

        $this->assertArrayHasKey('benutzer_daten', $result);
        $this->assertArrayHasKey('vor_name', $result['benutzer_daten']);
        $this->assertArrayHasKey('nach_name', $result['benutzer_daten']);
    }

    // ========================================
    // IS MOBILE NUMBER TESTS
    // ========================================

    public function testIsMobileNumberSwissValid(): void
    {
        // Schweizer Mobilnummern
        $this->assertTrue(is_mobile_number('+41791234567'));
        $this->assertTrue(is_mobile_number('0791234567'));
        $this->assertTrue(is_mobile_number('+41761234567'));
        $this->assertTrue(is_mobile_number('+41781234567'));
        $this->assertTrue(is_mobile_number('+41751234567'));
    }

    public function testIsMobileNumberSwissWithSpaces(): void
    {
        $this->assertTrue(is_mobile_number('+41 79 123 45 67'));
        $this->assertTrue(is_mobile_number('079 123 45 67'));
    }

    public function testIsMobileNumberSwissFixedLineIsFalse(): void
    {
        // Schweizer Festnetznummern sollten false sein
        $this->assertFalse(is_mobile_number('+41441234567')); // Zürich
        $this->assertFalse(is_mobile_number('0441234567'));
        $this->assertFalse(is_mobile_number('+41311234567')); // Bern
    }

    public function testIsMobileNumberGermanValid(): void
    {
        // Deutsche Mobilnummern
        $this->assertTrue(is_mobile_number('+4915012345678'));
        $this->assertTrue(is_mobile_number('+4916012345678'));
        $this->assertTrue(is_mobile_number('+4917012345678'));
        $this->assertTrue(is_mobile_number('015012345678'));
    }

    public function testIsMobileNumberGermanFixedLineIsFalse(): void
    {
        // Deutsche Festnetznummern
        $this->assertFalse(is_mobile_number('+493012345678')); // Berlin
        $this->assertFalse(is_mobile_number('+498912345678')); // München
    }

    public function testIsMobileNumberAustrianValid(): void
    {
        // Österreichische Mobilnummern
        $this->assertTrue(is_mobile_number('+436501234567'));
        $this->assertTrue(is_mobile_number('+436601234567'));
        $this->assertTrue(is_mobile_number('06501234567'));
    }

    public function testIsMobileNumberInvalidFormats(): void
    {
        $this->assertFalse(is_mobile_number('invalid'));
        $this->assertFalse(is_mobile_number('123'));
        $this->assertFalse(is_mobile_number(''));
    }

    public function testIsMobileNumberWithDashesAndParens(): void
    {
        $this->assertTrue(is_mobile_number('+41 (79) 123-45-67'));
        $this->assertTrue(is_mobile_number('(079) 123-45-67'));
    }

    // ========================================
    // CONVERT UMLAUTE TESTS
    // ========================================

    public function testConvertUmlauteConvertsLowercase(): void
    {
        $this->assertEquals('ae', convert_umlaute('ä'));
        $this->assertEquals('oe', convert_umlaute('ö'));
        $this->assertEquals('ue', convert_umlaute('ü'));
        $this->assertEquals('ss', convert_umlaute('ß'));
    }

    public function testConvertUmlauteInText(): void
    {
        $result = convert_umlaute('Größe der Wohnung');

        $this->assertEquals('Groesse der Wohnung', $result);
    }

    public function testConvertUmlauteMultipleUmlauts(): void
    {
        // Die Funktion konvertiert nur Kleinbuchstaben-Umlaute
        $result = convert_umlaute('zürich österreich düsseldorf');

        $this->assertEquals('zuerich oesterreich duesseldorf', $result);
    }

    public function testConvertUmlauteWithoutUmlauts(): void
    {
        $result = convert_umlaute('Hello World');

        $this->assertEquals('Hello World', $result);
    }
}
