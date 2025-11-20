<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\OfferNotificationSender;
use App\Libraries\OfferPriceUpdater;
use App\Models\OfferModel;

class TestUnverifiedBlock extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'test:unverified-block';
    protected $description = 'Testet ob unverifizierte Offerten keine E-Mails auslösen';

    public function run(array $params)
    {
        $offerModel = new OfferModel();

        // Finde eine unverifizierte Offerte
        $unverifiedOffer = $offerModel
            ->where('verified', 0)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$unverifiedOffer) {
            CLI::write('Keine unverifizierte Offerte gefunden zum Testen', 'yellow');
            return;
        }

        CLI::write("=== Test: Unverifizierte Offerte ID {$unverifiedOffer['id']} ===", 'cyan');
        CLI::write("Titel: {$unverifiedOffer['title']}", 'white');
        CLI::write("Verified: " . ($unverifiedOffer['verified'] ? 'JA' : 'NEIN'), 'white');
        CLI::newLine();

        // Test 1: OfferNotificationSender
        CLI::write('Test 1: OfferNotificationSender->notifyMatchingUsers()', 'yellow');
        $notifier = new OfferNotificationSender();
        $sentCount = $notifier->notifyMatchingUsers($unverifiedOffer);

        if ($sentCount === 0) {
            CLI::write('✅ PASS: Keine E-Mails gesendet (erwartet: 0)', 'green');
        } else {
            CLI::write("❌ FAIL: {$sentCount} E-Mails gesendet (erwartet: 0)", 'red');
        }
        CLI::newLine();

        // Test 2: OfferPriceUpdater
        CLI::write('Test 2: OfferPriceUpdater->updateOfferAndNotify()', 'yellow');
        $updater = new OfferPriceUpdater();
        $result = $updater->updateOfferAndNotify($unverifiedOffer);

        if ($result === false) {
            CLI::write('✅ PASS: Funktion gab FALSE zurück (geblockt)', 'green');
        } else {
            CLI::write('❌ FAIL: Funktion gab TRUE zurück (nicht geblockt)', 'red');
        }
        CLI::newLine();

        CLI::write('=== Test abgeschlossen ===', 'cyan');
        CLI::write('Prüfe Logs für Warning-Meldungen:', 'white');
        CLI::write('ddev exec tail -30 /var/www/html/writable/logs/log-' . date('Y-m-d') . '.log', 'white');
    }
}
