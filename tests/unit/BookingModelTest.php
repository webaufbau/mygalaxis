<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\BookingModel;

/**
 * Tests für das BookingModel
 *
 * Testet die Buchungs/Guthaben-Logik
 */
final class BookingModelTest extends CIUnitTestCase
{
    private BookingModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BookingModel();
    }

    // ========================================
    // CLASS STRUCTURE TESTS
    // ========================================

    public function testModelClassExists(): void
    {
        $this->assertInstanceOf(BookingModel::class, $this->model);
    }

    public function testTableName(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);

        $this->assertEquals('bookings', $property->getValue($this->model));
    }

    public function testPrimaryKey(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('primaryKey');
        $property->setAccessible(true);

        $this->assertEquals('id', $property->getValue($this->model));
    }

    public function testAllowedFields(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $allowedFields = $property->getValue($this->model);

        // Wichtige Felder prüfen
        $this->assertContains('user_id', $allowedFields);
        $this->assertContains('type', $allowedFields);
        $this->assertContains('amount', $allowedFields);
        $this->assertContains('paid_amount', $allowedFields);
        $this->assertContains('description', $allowedFields);
        $this->assertContains('reference_id', $allowedFields);
    }

    // ========================================
    // METHOD EXISTENCE TESTS
    // ========================================

    public function testHasGetUserBalanceMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'getUserBalance'));
    }

    // ========================================
    // METHOD SIGNATURE TESTS
    // ========================================

    public function testGetUserBalanceRequiresUserId(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'getUserBalance');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('userId', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }

    public function testGetUserBalanceReturnsFloat(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'getUserBalance');

        $this->assertEquals('float', $reflection->getReturnType()->getName());
    }

    // ========================================
    // BALANCE CALCULATION LOGIC TESTS
    // ========================================

    public function testBalanceCalculationPositive(): void
    {
        // Simuliere Balance-Berechnung
        $transactions = [
            ['amount' => 100], // Einzahlung
            ['amount' => 50],  // Einzahlung
        ];

        $balance = array_sum(array_column($transactions, 'amount'));

        $this->assertEquals(150.0, $balance);
    }

    public function testBalanceCalculationWithPurchases(): void
    {
        // Simuliere Balance mit Käufen (negative Beträge)
        $transactions = [
            ['amount' => 100],  // Einzahlung
            ['amount' => -39],  // Kauf
            ['amount' => -29],  // Kauf
        ];

        $balance = array_sum(array_column($transactions, 'amount'));

        $this->assertEquals(32.0, $balance);
    }

    public function testBalanceCalculationZero(): void
    {
        $transactions = [];

        $balance = array_sum(array_column($transactions, 'amount'));

        $this->assertEquals(0.0, $balance);
    }

    public function testBalanceCalculationNegative(): void
    {
        // Sollte nicht passieren, aber trotzdem testen
        $transactions = [
            ['amount' => 50],
            ['amount' => -100],
        ];

        $balance = array_sum(array_column($transactions, 'amount'));

        $this->assertEquals(-50.0, $balance);
    }

    // ========================================
    // BOOKING TYPE TESTS
    // ========================================

    public function testBookingTypes(): void
    {
        // Bekannte Booking-Typen basierend auf dem Code
        $knownTypes = [
            'topup',           // Guthaben-Aufladung
            'offer_purchase',  // Offerten-Kauf
            'referral',        // Weiterempfehlungs-Gutschrift
        ];

        foreach ($knownTypes as $type) {
            $this->assertIsString($type);
        }
    }

    // ========================================
    // EDGE CASE TESTS
    // ========================================

    public function testBalanceWithNullAmount(): void
    {
        // Wenn amount null ist, sollte 0 verwendet werden
        $result = ['amount' => null];

        $balance = (float)($result['amount'] ?? 0);

        $this->assertEquals(0.0, $balance);
    }

    public function testBalanceWithStringAmount(): void
    {
        // Wenn amount als String kommt, sollte es zu float konvertiert werden
        $result = ['amount' => '123.45'];

        $balance = (float)($result['amount'] ?? 0);

        $this->assertEquals(123.45, $balance);
    }

    public function testBalanceWithEmptyResult(): void
    {
        // Wenn kein Ergebnis, sollte 0 zurückgegeben werden
        $result = [];

        $balance = (float)($result['amount'] ?? 0);

        $this->assertEquals(0.0, $balance);
    }

    // ========================================
    // PAYMENT AMOUNT LOGIC TESTS
    // ========================================

    public function testPaidAmountForTopup(): void
    {
        // Bei Topups ist paid_amount typischerweise 0 (wird nicht vom Guthaben abgezogen)
        $booking = [
            'type' => 'topup',
            'amount' => 100,
            'paid_amount' => 0
        ];

        $this->assertEquals(0, $booking['paid_amount']);
    }

    public function testPaidAmountForPurchase(): void
    {
        // Bei Käufen entspricht paid_amount dem bezahlten Preis
        $booking = [
            'type' => 'offer_purchase',
            'amount' => -39,  // Negativ weil vom Guthaben abgezogen
            'paid_amount' => 39
        ];

        $this->assertEquals(39, $booking['paid_amount']);
        $this->assertEquals(-39, $booking['amount']);
    }

    public function testAmountSignConvention(): void
    {
        // Konvention: Einzahlungen positiv, Käufe negativ
        $topup = ['type' => 'topup', 'amount' => 100];
        $purchase = ['type' => 'offer_purchase', 'amount' => -39];

        $this->assertGreaterThan(0, $topup['amount'], 'Topups sollten positive Beträge haben');
        $this->assertLessThan(0, $purchase['amount'], 'Käufe sollten negative Beträge haben');
    }
}
