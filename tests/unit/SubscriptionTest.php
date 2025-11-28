<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\Subscription;

/**
 * Tests für die Subscription Library
 *
 * Testet die Subscription-Logik ohne Datenbankzugriff
 * (Status-Update-Methoden werden über Reflection getestet)
 */
final class SubscriptionTest extends CIUnitTestCase
{
    private Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscription = new Subscription();
    }

    // ========================================
    // BASIC FUNCTIONALITY TESTS
    // ========================================

    public function testSubscriptionClassExists(): void
    {
        $this->assertInstanceOf(Subscription::class, $this->subscription);
    }

    public function testHasGetActiveUserSubscriptionsMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getActiveUserSubscriptions'));
    }

    public function testHasHasAValidUserSubscriptionMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'hasAValidUserSubscription'));
    }

    public function testHasGetUpcomingSubscriptionRenewalsMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getUpcomingSubscriptionRenewals'));
    }

    public function testHasGetSubscriptionTypesMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getSubscriptionTypes'));
    }

    public function testHasGetSubscriptionTypeOptionsMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getSubscriptionTypeOptions'));
    }

    public function testHasGetActiveSubscriptionMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getActiveSubscription'));
    }

    public function testHasRenewMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'renew'));
    }

    public function testHasFailedMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'failed'));
    }

    public function testHasCancelMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'cancel'));
    }

    public function testHasNoticeMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'notice'));
    }

    public function testHasPaidMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'paid'));
    }

    public function testHasRefundMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'refund'));
    }

    public function testHasPartiallyRefundMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'partiallyRefund'));
    }

    public function testHasChargebackMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'chargeback'));
    }

    public function testHasUncapturedMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'uncaptured'));
    }

    public function testHasHasOncePurchasedASubscriptionMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'hasOncePurchasedASubscription'));
    }

    public function testHasGetSubscriptionUntilMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'getSubscriptionUntil'));
    }

    public function testHasCountUserSubscriptionsByTypeMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'countUserSubscriptionsByType'));
    }

    public function testHasHasUserBookedSubscriptionTypeMethod(): void
    {
        $this->assertTrue(method_exists($this->subscription, 'hasUserBookedSubscriptionType'));
    }

    // ========================================
    // METHOD PARAMETER TESTS
    // ========================================

    public function testGetActiveUserSubscriptionsRequiresUserId(): void
    {
        $reflection = new \ReflectionMethod($this->subscription, 'getActiveUserSubscriptions');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('userId', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }

    public function testHasAValidUserSubscriptionRequiresUserIdAndCategory(): void
    {
        $reflection = new \ReflectionMethod($this->subscription, 'hasAValidUserSubscription');
        $params = $reflection->getParameters();

        $this->assertCount(2, $params);
        $this->assertEquals('userId', $params[0]->getName());
        $this->assertEquals('category_name', $params[1]->getName());
    }

    public function testCountUserSubscriptionsByTypeReturnsInt(): void
    {
        $reflection = new \ReflectionMethod($this->subscription, 'countUserSubscriptionsByType');

        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    public function testHasUserBookedSubscriptionTypeReturnsBool(): void
    {
        $reflection = new \ReflectionMethod($this->subscription, 'hasUserBookedSubscriptionType');

        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }
}
