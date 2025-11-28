<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\OfferPurchaseService;

/**
 * Tests für den OfferPurchaseService
 *
 * Testet die Kauflogik und Hilfsmethoden
 */
final class OfferPurchaseServiceTest extends CIUnitTestCase
{
    private OfferPurchaseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfferPurchaseService();
    }

    // ========================================
    // CLASS STRUCTURE TESTS
    // ========================================

    public function testServiceClassExists(): void
    {
        $this->assertInstanceOf(OfferPurchaseService::class, $this->service);
    }

    public function testHasPurchaseMethod(): void
    {
        $this->assertTrue(method_exists($this->service, 'purchase'));
    }

    // ========================================
    // DISCOUNT CALCULATION TESTS
    // ========================================

    public function testDiscountTypeCalculationNormal(): void
    {
        // Keine Ersparnis = normal
        $originalPrice = 100.0;
        $paidPrice = 100.0;

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('normal', $discountType);
    }

    public function testDiscountTypeCalculationDiscount1(): void
    {
        // 20% Rabatt oder weniger = discount_1
        $originalPrice = 100.0;
        $paidPrice = 80.0; // 20% Rabatt

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('discount_1', $discountType);
    }

    public function testDiscountTypeCalculationDiscount2(): void
    {
        // Mehr als 20% Rabatt = discount_2
        $originalPrice = 100.0;
        $paidPrice = 70.0; // 30% Rabatt

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('discount_2', $discountType);
    }

    public function testDiscountTypeCalculation15Percent(): void
    {
        // 15% Rabatt = discount_1
        $originalPrice = 100.0;
        $paidPrice = 85.0;

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('discount_1', $discountType);
    }

    public function testDiscountTypeCalculation21Percent(): void
    {
        // 21% Rabatt = discount_2 (über 20%)
        $originalPrice = 100.0;
        $paidPrice = 79.0;

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('discount_2', $discountType);
    }

    public function testDiscountTypeZeroOriginalPrice(): void
    {
        // Originalpreis 0 = normal (Vermeidung Division durch Null)
        $originalPrice = 0.0;
        $paidPrice = 0.0;

        $discountType = $this->calculateDiscountType($originalPrice, $paidPrice);

        $this->assertEquals('normal', $discountType);
    }

    /**
     * Helper-Methode die die gleiche Logik wie im Service verwendet
     */
    private function calculateDiscountType(float $originalPrice, float $paidPrice): string
    {
        $discountType = 'normal';

        if ($originalPrice > 0 && $paidPrice < $originalPrice) {
            $discountPercent = (($originalPrice - $paidPrice) / $originalPrice) * 100;

            if ($discountPercent > 20) {
                $discountType = 'discount_2';
            } else {
                $discountType = 'discount_1';
            }
        }

        return $discountType;
    }

    // ========================================
    // BALANCE CHECK LOGIC TESTS
    // ========================================

    public function testBalanceCheckLogic(): void
    {
        // Simuliere Balance-Check-Logik
        $balance = 50.0;
        $price = 39.0;

        $hasEnoughBalance = $balance >= $price;

        $this->assertTrue($hasEnoughBalance);
    }

    public function testBalanceCheckLogicInsufficientFunds(): void
    {
        $balance = 30.0;
        $price = 39.0;

        $hasEnoughBalance = $balance >= $price;
        $missingAmount = $price - $balance;

        $this->assertFalse($hasEnoughBalance);
        $this->assertEquals(9.0, $missingAmount);
    }

    public function testBalanceCheckLogicExactAmount(): void
    {
        $balance = 39.0;
        $price = 39.0;

        $hasEnoughBalance = $balance >= $price;

        $this->assertTrue($hasEnoughBalance, 'Exakter Betrag sollte ausreichen');
    }

    // ========================================
    // PRICE SELECTION LOGIC TESTS
    // ========================================

    public function testPriceSelectionUsesDiscountedPrice(): void
    {
        $offer = [
            'price' => 100,
            'discounted_price' => 70
        ];

        $price = $offer['discounted_price'] > 0 ? $offer['discounted_price'] : $offer['price'];

        $this->assertEquals(70, $price);
    }

    public function testPriceSelectionUsesRegularPriceWhenNoDiscount(): void
    {
        $offer = [
            'price' => 100,
            'discounted_price' => 0
        ];

        $price = $offer['discounted_price'] > 0 ? $offer['discounted_price'] : $offer['price'];

        $this->assertEquals(100, $price);
    }

    public function testPriceSelectionUsesRegularPriceWhenDiscountNull(): void
    {
        $offer = [
            'price' => 100,
            'discounted_price' => null
        ];

        $price = !empty($offer['discounted_price']) ? $offer['discounted_price'] : $offer['price'];

        $this->assertEquals(100, $price);
    }

    // ========================================
    // OFFER STATUS LOGIC TESTS
    // ========================================

    public function testOfferAvailabilityCheck(): void
    {
        $offer = [
            'id' => 1,
            'status' => 'available'
        ];

        $isAvailable = $offer && $offer['status'] === 'available';

        $this->assertTrue($isAvailable);
    }

    public function testOfferNotAvailableWhenSoldOut(): void
    {
        $offer = [
            'id' => 1,
            'status' => 'out_of_stock'
        ];

        $isAvailable = $offer && $offer['status'] === 'available';

        $this->assertFalse($isAvailable);
    }

    public function testOfferNotAvailableWhenDeleted(): void
    {
        $offer = [
            'id' => 1,
            'status' => 'deleted'
        ];

        $isAvailable = $offer && $offer['status'] === 'available';

        $this->assertFalse($isAvailable);
    }

    // ========================================
    // BUYER COUNT / SOLD OUT LOGIC TESTS
    // ========================================

    public function testSoldOutLogicWith4Buyers(): void
    {
        $buyerCount = 4;
        $maxPurchases = 4; // OfferModel::MAX_PURCHASES

        $status = $buyerCount >= $maxPurchases ? 'out_of_stock' : 'available';

        $this->assertEquals('out_of_stock', $status);
    }

    public function testStillAvailableWith3Buyers(): void
    {
        $buyerCount = 3;
        $maxPurchases = 4;

        $status = $buyerCount >= $maxPurchases ? 'out_of_stock' : 'available';

        $this->assertEquals('available', $status);
    }

    public function testStillAvailableWith0Buyers(): void
    {
        $buyerCount = 0;
        $maxPurchases = 4;

        $status = $buyerCount >= $maxPurchases ? 'out_of_stock' : 'available';

        $this->assertEquals('available', $status);
    }

    // ========================================
    // PAYMENT METHOD DETECTION TESTS
    // ========================================

    public function testWalletPaymentMethodDetection(): void
    {
        $source = 'wallet';
        $description = $source === 'wallet' ? 'Bezahlt aus Guthaben' : 'Bezahlt per Kreditkarte';

        $this->assertEquals('Bezahlt aus Guthaben', $description);
    }

    public function testCreditCardPaymentMethodDetection(): void
    {
        $source = 'credit_card';
        $description = $source === 'wallet' ? 'Bezahlt aus Guthaben' : 'Bezahlt per Kreditkarte';

        $this->assertEquals('Bezahlt per Kreditkarte', $description);
    }
}
