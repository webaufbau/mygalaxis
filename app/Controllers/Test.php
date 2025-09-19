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
