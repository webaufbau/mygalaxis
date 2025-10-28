<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Libraries\OfferNotificationSender;

class TestNewOfferEmail extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'mail:test-new-offer';
    protected $description = 'Sendet eine Test-E-Mail für "Neue passende Offerte"';

    public function run(array $params)
    {
        $offerId = (int)($params[0] ?? 0);
        $userId = (int)($params[1] ?? 0);

        if ($offerId === 0 || $userId === 0) {
            CLI::error('❌ Bitte gib sowohl Offer-ID als auch User-ID an.');
            CLI::write('Verwendung: php spark mail:test-new-offer [offer_id] [user_id]');
            CLI::write('Beispiel: php spark mail:test-new-offer 447 11');
            return;
        }

        CLI::write('📧 Test: Neue passende Offerte E-Mail', 'yellow');
        CLI::write(str_repeat('=', 50));

        // Lade Offerte
        $offerModel = new OfferModel();
        $offer = $offerModel->asArray()->find($offerId);

        if (!$offer) {
            CLI::error("❌ Offerte mit ID {$offerId} nicht gefunden!");
            return;
        }

        CLI::write("✅ Offerte geladen: #{$offer['id']} - {$offer['title']}", 'green');
        CLI::write("   Typ: {$offer['type']}");
        CLI::write("   Ort: {$offer['zip']} {$offer['city']}");
        CLI::write("   Verifiziert: " . ($offer['verified'] ? 'Ja' : 'Nein'));

        // Lade User
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            CLI::error("❌ Benutzer mit ID {$userId} nicht gefunden!");
            return;
        }

        CLI::write("✅ Benutzer geladen: {$user->getEmail()}", 'green');
        CLI::write("   Firma: " . ($user->company_name ?? 'N/A'));
        CLI::write("   Kontakt: " . ($user->contact_person ?? 'N/A'));
        CLI::write("   Platform: " . ($user->platform ?? 'Standard'));

        // Sende Test-E-Mail
        CLI::newLine();
        CLI::write('📬 Sende E-Mail...', 'yellow');

        try {
            $notificationSender = new OfferNotificationSender();

            // Verwende ReflectionClass um protected sendOfferEmail Methode aufzurufen
            $reflection = new \ReflectionClass($notificationSender);
            $method = $reflection->getMethod('sendOfferEmail');
            $method->setAccessible(true);

            $method->invoke($notificationSender, $user, $offer);

            CLI::newLine();
            CLI::write('✅ E-Mail erfolgreich versendet!', 'green');
            CLI::newLine();
            CLI::write('📨 E-Mail Details:', 'yellow');
            CLI::write("   An: {$user->getEmail()}");
            CLI::write("   Betreff: Neue passende Offerte #{$offer['id']}");
            CLI::write("   Template: emails/offer_new_detailed.php");
            CLI::newLine();
            CLI::write('📬 MailHog öffnen: https://mygalaxis.ddev.site:8026', 'blue');

        } catch (\Exception $e) {
            CLI::error("❌ Fehler beim Senden: " . $e->getMessage());
            CLI::write("   Stack Trace:", 'red');
            CLI::write($e->getTraceAsString());
            return;
        }

        CLI::newLine();
        CLI::write(str_repeat('=', 50));
        CLI::write('✅ Test abgeschlossen!', 'green');
    }
}
