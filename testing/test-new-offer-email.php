<?php

/**
 * Test-Skript für "Neue passende Offerte" E-Mail
 *
 * Verwendung: php test-new-offer-email.php [offer_id] [user_id]
 * Beispiel: php test-new-offer-email.php 447 11
 */

// Define constants
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Load the paths config file
$pathsConfig = FCPATH . '../app/Config/Paths.php';
$paths = require realpath($pathsConfig) ?: $pathsConfig;

// Location of the framework bootstrap file
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Load environment-specific settings
$app = \Config\Services::codeigniter();
$app->initialize();

// --- Parameter auslesen ---
$offerId = (int)($argv[1] ?? 0);
$userId = (int)($argv[2] ?? 0);

if ($offerId === 0 || $userId === 0) {
    echo "❌ Bitte gib sowohl Offer-ID als auch User-ID an.\n";
    echo "Verwendung: php test-new-offer-email.php [offer_id] [user_id]\n";
    echo "Beispiel: php test-new-offer-email.php 447 11\n";
    exit(1);
}

echo "📧 Test: Neue passende Offerte E-Mail\n";
echo str_repeat('=', 50) . "\n";

// Lade Offerte
$offerModel = new OfferModel();
$offer = $offerModel->asArray()->find($offerId);

if (!$offer) {
    echo "❌ Offerte mit ID {$offerId} nicht gefunden!\n";
    exit(1);
}

echo "✅ Offerte geladen: #{$offer['id']} - {$offer['title']}\n";
echo "   Typ: {$offer['type']}\n";
echo "   Ort: {$offer['zip']} {$offer['city']}\n";
echo "   Verifiziert: " . ($offer['verified'] ? 'Ja' : 'Nein') . "\n";

// Lade User
$userModel = new UserModel();
$user = $userModel->find($userId);

if (!$user) {
    echo "❌ Benutzer mit ID {$userId} nicht gefunden!\n";
    exit(1);
}

echo "✅ Benutzer geladen: {$user->getEmail()}\n";
echo "   Firma: {$user->company_name}\n";
echo "   Kontakt: {$user->contact_person}\n";
echo "   Platform: " . ($user->platform ?? 'Standard') . "\n";

// Sende Test-E-Mail
echo "\n📬 Sende E-Mail...\n";

try {
    $notificationSender = new OfferNotificationSender();

    // Verwende die sendOfferEmail Methode direkt (Protected, daher ReflectionClass)
    $reflection = new ReflectionClass($notificationSender);
    $method = $reflection->getMethod('sendOfferEmail');
    $method->setAccessible(true);

    $method->invoke($notificationSender, $user, $offer);

    echo "✅ E-Mail erfolgreich versendet!\n";
    echo "\n📨 E-Mail Details:\n";
    echo "   An: {$user->getEmail()}\n";
    echo "   Betreff: Neue passende Offerte #{$offer['id']}\n";
    echo "   Template: emails/offer_new_detailed.php\n";

} catch (Exception $e) {
    echo "❌ Fehler beim Senden: " . $e->getMessage() . "\n";
    echo "   Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "✅ Test abgeschlossen!\n";
