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
