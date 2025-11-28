<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ReferralModel;

/**
 * Tests für das ReferralModel
 *
 * Testet die Referral/Affiliate-Logik
 */
final class ReferralModelTest extends CIUnitTestCase
{
    private ReferralModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ReferralModel();
    }

    // ========================================
    // CLASS STRUCTURE TESTS
    // ========================================

    public function testModelClassExists(): void
    {
        $this->assertInstanceOf(ReferralModel::class, $this->model);
    }

    public function testTableName(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);

        $this->assertEquals('referrals', $property->getValue($this->model));
    }

    public function testPrimaryKey(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('primaryKey');
        $property->setAccessible(true);

        $this->assertEquals('id', $property->getValue($this->model));
    }

    public function testUsesTimestamps(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('useTimestamps');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($this->model));
    }

    public function testAllowedFields(): void
    {
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $allowedFields = $property->getValue($this->model);

        // Wichtige Felder prüfen
        $this->assertContains('referrer_user_id', $allowedFields);
        $this->assertContains('referred_user_id', $allowedFields);
        $this->assertContains('referred_email', $allowedFields);
        $this->assertContains('referral_code', $allowedFields);
        $this->assertContains('status', $allowedFields);
        $this->assertContains('credit_amount', $allowedFields);
    }

    // ========================================
    // METHOD EXISTENCE TESTS
    // ========================================

    public function testHasCreateReferralMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'createReferral'));
    }

    public function testHasUpdateReferredUserIdMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'updateReferredUserId'));
    }

    public function testHasGetReferralsByUserMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'getReferralsByUser'));
    }

    public function testHasGetAllReferralsMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'getAllReferrals'));
    }

    public function testHasGiveCreditMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'giveCredit'));
    }

    public function testHasRejectReferralMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'rejectReferral'));
    }

    public function testHasGetUserStatsMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'getUserStats'));
    }

    public function testHasGetUserIdByCodeMethod(): void
    {
        $this->assertTrue(method_exists($this->model, 'getUserIdByCode'));
    }

    // ========================================
    // METHOD SIGNATURE TESTS
    // ========================================

    public function testCreateReferralParameters(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'createReferral');
        $params = $reflection->getParameters();

        $this->assertGreaterThanOrEqual(3, count($params));
        $this->assertEquals('referrerUserId', $params[0]->getName());
        $this->assertEquals('referralCode', $params[1]->getName());
        $this->assertEquals('email', $params[2]->getName());
    }

    public function testGetReferralsByUserReturnsArray(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'getReferralsByUser');

        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    public function testGetUserStatsReturnsArray(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'getUserStats');

        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    public function testUpdateReferredUserIdReturnsBool(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'updateReferredUserId');

        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    public function testRejectReferralReturnsBool(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'rejectReferral');

        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    public function testGiveCreditReturnsBool(): void
    {
        $reflection = new \ReflectionMethod($this->model, 'giveCredit');

        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    // ========================================
    // DEFAULT VALUES TESTS
    // ========================================

    public function testDefaultCreditAmount(): void
    {
        // Die createReferral-Methode setzt 50.00 als Default-Betrag
        $reflection = new \ReflectionMethod($this->model, 'createReferral');

        // Lese den Quellcode der Methode
        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        // Prüfe ob 50.00 als Default-Wert gesetzt wird
        $this->assertStringContainsString("'credit_amount' => 50.00", $methodCode);
    }

    public function testDefaultStatus(): void
    {
        // Die createReferral-Methode setzt 'pending' als Default-Status
        $reflection = new \ReflectionMethod($this->model, 'createReferral');

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString("'status' => 'pending'", $methodCode);
    }

    // ========================================
    // REFERRAL STATUS VALUES TESTS
    // ========================================

    public function testReferralStatusValues(): void
    {
        // Die erlaubten Status-Werte basierend auf den Methoden
        $expectedStatuses = ['pending', 'credited', 'rejected'];

        // Lese giveCredit-Methode um 'credited' zu verifizieren
        $reflection = new \ReflectionMethod($this->model, 'giveCredit');
        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString("'status' => 'credited'", $methodCode);

        // Lese rejectReferral-Methode um 'rejected' zu verifizieren
        $reflection = new \ReflectionMethod($this->model, 'rejectReferral');
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString("'status' => 'rejected'", $methodCode);
    }

    // ========================================
    // STATS STRUCTURE TESTS
    // ========================================

    public function testGetUserStatsReturnsExpectedStructure(): void
    {
        // Die getUserStats-Methode sollte ein Array mit bestimmten Keys zurückgeben
        $reflection = new \ReflectionMethod($this->model, 'getUserStats');

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        // Prüfe ob die erwarteten Keys im Code definiert werden
        $this->assertStringContainsString("'total'", $methodCode);
        $this->assertStringContainsString("'pending'", $methodCode);
        $this->assertStringContainsString("'credited'", $methodCode);
        $this->assertStringContainsString("'rejected'", $methodCode);
        $this->assertStringContainsString("'total_earned'", $methodCode);
    }

    // ========================================
    // CREDIT VALIDATION TESTS
    // ========================================

    public function testGiveCreditChecksForAlreadyCredited(): void
    {
        // Die giveCredit-Methode sollte prüfen ob bereits credited
        $reflection = new \ReflectionMethod($this->model, 'giveCredit');

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        // Prüfe ob die Methode auf 'credited' Status prüft
        $this->assertStringContainsString("=== 'credited'", $methodCode);
    }

    public function testGiveCreditHasRollbackLogic(): void
    {
        // Die giveCredit-Methode sollte Rollback-Logik haben
        $reflection = new \ReflectionMethod($this->model, 'giveCredit');

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $file = file($filename);
        $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));

        // Prüfe ob Rollback zu 'pending' existiert
        $this->assertStringContainsString("'status' => 'pending'", $methodCode);
    }
}
