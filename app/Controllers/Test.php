<?php
namespace App\Controllers;

use App\Libraries\TwilioService;

class Test extends BaseController
{
    public function testtwilio() {
        $twilio = new TwilioService();

        $normalizedPhone = $this->normalizePhone('+4366565973028');
        $message = 'Ihr Code lautet: ' . rand(100000, 999999);

        $success = $twilio->sendSms($normalizedPhone, $message);

        dd($success);

    }

    public function testVerification($uuid) {
        // Prüfe ob Offer existiert
        $db = \Config\Database::connect();
        $offer = $db->table('offers')
            ->where('uuid', $uuid)
            ->get()
            ->getRow();

        if (!$offer) {
            return "Offer mit UUID {$uuid} nicht gefunden.";
        }

        // Session setzen
        session()->set('uuid', $uuid);
        session()->set('next_url', '/dashboard');

        echo "✓ Session gesetzt für UUID: {$uuid}<br>";
        echo "✓ Verified Status: " . ($offer->verified ? 'Ja' : 'Nein') . "<br>";
        echo "✓ Telefon: " . (json_decode($offer->form_fields, true)['phone'] ?? 'N/A') . "<br><br>";
        echo "<a href='/verification'>→ Zum Verification-Prozess</a>";
    }

    public function smsTest($phone = null)
    {
        // Standard-Nummer falls keine angegeben
        $phone = $phone ?? '+41788708462';

        // Nummer normalisieren
        $phone = $this->normalizePhone($phone);

        // Session setzen
        session()->set('phone', $phone);
        session()->set('verify_method', 'sms');
        session()->set('uuid', 'test-' . time());
        session()->set('next_url', '/test/smsTestResult');

        log_message('info', "TEST: SMS-Test gestartet für {$phone}");
        log_message('info', "TEST: Session phone = " . session()->get('phone'));
        log_message('info', "TEST: Session verify_method = " . session()->get('verify_method'));

        // Debug ausgabe
        echo "<h2>Session Debug</h2>";
        echo "<p>Phone in Session: " . session()->get('phone') . "</p>";
        echo "<p>Method in Session: " . session()->get('verify_method') . "</p>";
        echo "<p>UUID in Session: " . session()->get('uuid') . "</p>";
        echo "<br><br>";
        echo "<p><a href='/verification/send'>Weiter zu /verification/send</a></p>";
        echo "<p>Oder warte 3 Sekunden für Auto-Redirect...</p>";

        echo "<script>setTimeout(function() { window.location.href = '/verification/send'; }, 3000);</script>";
    }

    public function smsTestResult()
    {
        $code = session('verification_code');
        $phone = session('phone');
        $status = session('sms_sent_status');
        $messageId = session('sms_message_id');

        echo "<h2>SMS Test Ergebnis</h2>";
        echo "<p><strong>Telefon:</strong> {$phone}</p>";
        echo "<p><strong>Code:</strong> {$code}</p>";
        echo "<p><strong>Status:</strong> {$status}</p>";
        echo "<p><strong>Message ID:</strong> {$messageId}</p>";
        echo "<br><a href='/test/smsTest'>Neuer Test</a>";
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone); // Nur Zahlen
        if (str_starts_with($phone, '0')) {
            $phone = '+41' . substr($phone, 1); // 0781234512 → +41781234512
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

}
