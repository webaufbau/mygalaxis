<?php

/**
 * Test Script für Rabatt-E-Mails
 * Sendet direkt eine Test-Rabatt-E-Mail
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

echo "=== Test Rabatt-E-Mail ===\n\n";

// Finde eine verifizierte Offerte mit Platform
$db = \Config\Database::connect();
$offer = $db->table('offers')
    ->where('verified', 1)
    ->where('price >', 10)
    ->where('platform IS NOT NULL')
    ->orderBy('id', 'DESC')
    ->get(1)
    ->getRowArray();

if (!$offer) {
    die("ERROR: Keine passende Offerte gefunden!\n");
}

echo "✓ Offerte #{$offer['id']} gefunden: {$offer['type']}, {$offer['zip']} {$offer['city']}\n";
echo "  Platform: {$offer['platform']}\n";
echo "  Preis: {$offer['price']} CHF\n\n";

// Finde eine Firma
$userModel = new \App\Models\UserModel();
$companies = $userModel->findAll();
$company = null;
foreach ($companies as $u) {
    if ($u->inGroup('user')) {
        $company = $u;
        break;
    }
}

if (!$company) {
    die("ERROR: Keine Firma gefunden!\n");
}

echo "✓ Firma gefunden: {$company->company_name} ({$company->email})\n\n";

// Simuliere Rabatt
$oldPrice = (float)$offer['price'];
$newPrice = round($oldPrice * 0.3, 2); // 70% Rabatt
$discount = round(($oldPrice - $newPrice) / $oldPrice * 100);

echo "Simulierter Rabatt: {$discount}%\n";
echo "  Alter Preis: {$oldPrice} CHF\n";
echo "  Neuer Preis: {$newPrice} CHF\n\n";

// Lade SiteConfig
$siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($company->platform);

// Dekodiere form_fields als data-Feld
if (isset($offer['form_fields']) && is_string($offer['form_fields'])) {
    $offer['data'] = json_decode($offer['form_fields'], true) ?? [];
} elseif (isset($offer['data']) && is_string($offer['data'])) {
    $offer['data'] = json_decode($offer['data'], true) ?? [];
} else {
    $offer['data'] = [];
}

// Extrahiere Plattform-Domain
$offerPlatformDomain = '';
if (!empty($offer['platform'])) {
    $offerPlatformDomain = str_replace('my_', '', $offer['platform']);
    $offerPlatformDomain = str_replace('_', '.', $offerPlatformDomain);
    $offerPlatformDomain = ucfirst($offerPlatformDomain);
}

echo "Plattform-Domain: {$offerPlatformDomain}\n\n";

// Typ-Mapping
$typeMapping = [
    'move' => 'Umzug',
    'cleaning' => 'Reinigung',
    'move_cleaning' => 'Umzug + Reinigung',
    'painting' => 'Maler/Gipser',
    'gardening' => 'Garten Arbeiten',
    'electrician' => 'Elektriker Arbeiten',
    'plumbing' => 'Sanitär Arbeiten',
    'heating' => 'Heizung Arbeiten',
];
$type = $typeMapping[$offer['type']] ?? ucfirst($offer['type']);

// Betreff
$newPriceFormatted = number_format($newPrice, 0, '.', '\'');
$subject = "{$discount}% Rabatt / Neuer Preis Fr. {$newPriceFormatted}.– für {$type} {$offer['zip']} {$offer['city']} ID {$offer['id']} Anfrage";

echo "Betreff: {$subject}\n\n";

// Rendere E-Mail
$message = view('emails/price_update', [
    'firma' => $company,
    'offer' => $offer,
    'oldPrice' => $oldPrice,
    'newPrice' => $newPrice,
    'discount' => $discount,
    'siteConfig' => $siteConfig,
    'alreadyPurchased' => false,
    'customFieldDisplay' => null,
    'offerPlatformDomain' => $offerPlatformDomain,
]);

$view = \Config\Services::renderer();
$fullEmail = $view->setData([
    'title' => 'Rabatt-Benachrichtigung',
    'content' => $message,
    'siteConfig' => $siteConfig,
])->render('emails/layout');

// Sende E-Mail
helper('email_template');
$email = \Config\Services::email();
$to = $siteConfig->testMode ? $siteConfig->testEmail : $company->email;
$email->setTo($to);
$email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
$email->setSubject($subject);
$email->setMessage($fullEmail);
$email->setMailType('html');

date_default_timezone_set('Europe/Zurich');
$email->setHeader('Date', date('r'));

echo "Sende E-Mail an: {$to}\n";
echo str_repeat("-", 60) . "\n\n";

if ($email->send()) {
    echo "✅ E-Mail erfolgreich gesendet!\n\n";
    echo "Prüfe in MailPit:\n";
    echo "  → https://mygalaxis.ddev.site:8026\n\n";
    echo "Der Plattform-Hinweis sollte erscheinen:\n";
    echo "  \"Diese Anfrage stammt von {$offerPlatformDomain}\"\n";
} else {
    echo "❌ Fehler beim Senden:\n";
    echo $email->printDebugger();
}

echo "\n=== Test abgeschlossen ===\n";
