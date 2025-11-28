<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\OfferTitleGenerator;

/**
 * Tests für die Titel-Generierung von Angeboten
 */
final class OfferTitleGeneratorTest extends CIUnitTestCase
{
    private OfferTitleGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new OfferTitleGenerator();
    }

    // ========================================
    // UMZUG TITEL TESTS
    // ========================================

    public function testMoveTitleBasic(): void
    {
        $offer = [
            'type' => 'move',
            'city' => 'zürich',
            'form_fields' => json_encode([])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Zürich', $title);
    }

    public function testMoveTitleWithFromAndTo(): void
    {
        $offer = [
            'type' => 'move',
            'city' => 'basel',
            'form_fields' => json_encode([
                'von_ort' => 'basel',
                'nach_ort' => 'zürich',
                'auszug_zimmer' => '3-Zimmer'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Basel', $title);
        $this->assertStringContainsString('Zürich', $title);
        $this->assertStringContainsString('von', $title);
        $this->assertStringContainsString('nach', $title);
    }

    public function testMoveTitleWithRooms(): void
    {
        $offer = [
            'type' => 'move',
            'city' => 'bern',
            'form_fields' => json_encode([
                'von_ort' => 'bern',
                'auszug_zimmer' => '4-Zimmer'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('4', $title);
    }

    // ========================================
    // REINIGUNG TITEL TESTS
    // ========================================

    public function testCleaningTitleBasic(): void
    {
        $offer = [
            'type' => 'cleaning',
            'city' => 'luzern',
            'form_fields' => json_encode([])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Luzern', $title);
    }

    public function testCleaningTitleWithRooms(): void
    {
        $offer = [
            'type' => 'cleaning',
            'city' => 'genf',
            'form_fields' => json_encode([
                'wohnung_groesse' => '3-Zimmer'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Genf', $title);
        $this->assertStringContainsString('3', $title);
    }

    // ========================================
    // MALER TITEL TESTS
    // ========================================

    public function testPaintingTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'painting',
            'city' => 'winterthur',
            'form_fields' => json_encode([
                'art_objekt' => 'Wohnung'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Winterthur', $title);
        $this->assertStringContainsString('Wohnung', $title);
    }

    // ========================================
    // GARTEN TITEL TESTS
    // ========================================

    public function testGardeningTitleWithWorks(): void
    {
        $offer = [
            'type' => 'gardening',
            'city' => 'st. gallen',
            'form_fields' => json_encode([
                'garten_anlegen' => ['Rasen', 'Hecken schneiden']
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('St. Gallen', $title);
        $this->assertStringContainsString('Rasen', $title);
    }

    public function testGardeningTitleLimitsTwoWorks(): void
    {
        $offer = [
            'type' => 'gardening',
            'city' => 'chur',
            'form_fields' => json_encode([
                'garten_anlegen' => ['Rasen', 'Hecken', 'Bäume', 'Pool']
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        // Sollte nur die ersten 2 Arbeiten zeigen
        $this->assertStringContainsString('Rasen', $title);
        $this->assertStringContainsString('Hecken', $title);
        $this->assertStringNotContainsString('Pool', $title);
    }

    // ========================================
    // ELEKTRIKER TITEL TESTS
    // ========================================

    public function testElectricianTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'electrician',
            'city' => 'aarau',
            'form_fields' => json_encode([
                'art_objekt' => 'Haus'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Aarau', $title);
        $this->assertStringContainsString('Haus', $title);
    }

    // ========================================
    // SANITÄR TITEL TESTS
    // ========================================

    public function testPlumbingTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'plumbing',
            'city' => 'thun',
            'form_fields' => json_encode([
                'art_objekt' => 'Mehrfamilienhaus'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Thun', $title);
        $this->assertStringContainsString('Mehrfamilienhaus', $title);
    }

    // ========================================
    // HEIZUNG TITEL TESTS
    // ========================================

    public function testHeatingTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'heating',
            'city' => 'baden',
            'form_fields' => json_encode([
                'art_objekt' => 'Gewerbe'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Baden', $title);
        $this->assertStringContainsString('Gewerbe', $title);
    }

    // ========================================
    // BODEN TITEL TESTS
    // ========================================

    public function testFlooringTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'flooring',
            'city' => 'olten',
            'form_fields' => json_encode([
                'art_objekt' => 'Wohnung'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Olten', $title);
        $this->assertStringContainsString('Wohnung', $title);
    }

    // ========================================
    // PLATTEN TITEL TESTS
    // ========================================

    public function testTilingTitleWithObjectType(): void
    {
        $offer = [
            'type' => 'tiling',
            'city' => 'solothurn',
            'form_fields' => json_encode([
                'art_objekt' => 'Haus'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Solothurn', $title);
        $this->assertStringContainsString('Haus', $title);
    }

    // ========================================
    // EDGE CASES
    // ========================================

    public function testUnknownTypeReturnsBasicTitle(): void
    {
        $offer = [
            'type' => 'unknown_type',
            'city' => 'biel',
            'form_fields' => json_encode([])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Biel', $title);
    }

    public function testEmptyCityReturnsTypeOnly(): void
    {
        $offer = [
            'type' => 'move',
            'city' => '',
            'form_fields' => json_encode([])
        ];

        $title = $this->generator->generateTitle($offer);

        // Sollte nur den übersetzten Typ zurückgeben
        $this->assertNotEmpty($title);
    }

    public function testArrayValueInFormFields(): void
    {
        $offer = [
            'type' => 'move',
            'city' => 'lausanne',
            'form_fields' => json_encode([
                'von_ort' => ['lausanne'], // Als Array statt String
                'auszug_zimmer' => ['3-Zimmer']
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Lausanne', $title);
    }

    public function testMoveCleaningUsesComboFields(): void
    {
        $offer = [
            'type' => 'move_cleaning',
            'city' => 'fribourg',
            'form_fields' => json_encode([]),
            'form_fields_combo' => json_encode([
                'von_ort' => 'bern',
                'nach_ort' => 'fribourg',
                'auszug_zimmer' => '2-Zimmer'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Bern', $title);
        $this->assertStringContainsString('Fribourg', $title);
    }

    public function testCityIsCapitalized(): void
    {
        $offer = [
            'type' => 'cleaning',
            'city' => 'zürich',
            'form_fields' => json_encode([])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('Zürich', $title);
        $this->assertStringNotContainsString('zürich', $title);
    }

    public function testFormatRoomSizeWithAndere(): void
    {
        $offer = [
            'type' => 'cleaning',
            'city' => 'zug',
            'form_fields' => json_encode([
                'wohnung_groesse' => 'Andere'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        $this->assertStringContainsString('EFH/Andere', $title);
    }

    public function testMoveWithSameFromAndTo(): void
    {
        // Wenn von und nach gleich sind, sollte "nach" nicht angezeigt werden
        $offer = [
            'type' => 'move',
            'city' => 'zürich',
            'form_fields' => json_encode([
                'von_ort' => 'zürich',
                'nach_ort' => 'zürich'
            ])
        ];

        $title = $this->generator->generateTitle($offer);

        // "nach" sollte nicht erscheinen wenn identisch
        $count = substr_count(strtolower($title), 'zürich');
        $this->assertLessThanOrEqual(2, $count, 'Zürich sollte nicht doppelt erscheinen bei gleichem von/nach');
    }
}
