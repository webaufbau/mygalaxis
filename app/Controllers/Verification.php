<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

class Verification extends Controller
{
    public function index()
    {
        $uuid = session()->get('uuid');
        if (!$uuid) {
            return redirect()->to('/'); // oder Fehlerseite
        }

        $db = \Config\Database::connect();
        $builder = $db->table('requests');
        $row = $builder->where('uuid', $uuid)->orderBy('created_at', 'DESC')->get()->getRow();

        if (!$row) {
            return redirect()->to('/')->with('error', 'Keine Anfrage gefunden.');
        }

        // form_fields ist JSON, decode es:
        $fields = json_decode($row->form_fields, true);
        $phone = $fields['phone'] ?? '';

        return view('verification_form', ['phone' => $phone]);
    }

    public function send()
    {
        $request = service('request');

        $phone = $request->getPost('phone'); // sollte in prod nicht per form sondern auch wieder über DB gelesen werden, sonst manipulierbar.
        $method = $request->getPost('method');

        if (!$phone) {
            return redirect()->back()->with('error', 'Telefonnummer fehlt.');
        }

        // Prüfe, ob Mobilnummer
        $isMobile = $this->isMobileNumber($phone);

        // Wenn kein Mobile, dann nur Anruf zulassen
        if (!$isMobile && $method !== 'phone') {
            return redirect()->back()->with('error', 'Bei Festnetznummer ist nur Anruf-Verifizierung möglich.');
        }

        if (!$method) {
            return redirect()->back()->with('error', 'Bitte Verifizierungsmethode wählen.');
        }

        // Simuliere den Versand des Verifizierungscodes
        $verificationCode = rand(100000, 999999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        return redirect()->to('/verification/confirm');
    }

    public function confirm()
    {
        $verificationCode = session('verification_code');
        if (!$verificationCode || $verificationCode=='') {
            return redirect()->to('/'); // oder Fehlerseite
        }

        return view('verification_confirm', ['verification_code' => session()->get('verification_code')]);
    }

    public function verify()
    {
        $request = service('request');

        $enteredCode = $request->getPost('code');
        $sessionCode = session()->get('verification_code');

        $uuid = session()->get('uuid');
        if ($enteredCode == session()->get('verification_code')) {

            $db = \Config\Database::connect();
            $builder = $db->table('requests');

            // verified auf 1 setzen für diese uuid
            $builder->where('uuid', $uuid)->update(['verified' => 1, 'verify_type' => $this->request->getPost('method')]);

            session()->remove('verification_code');
            return view('verification_success', ['next_url' => session()->get('next_url') ?? 'https://umzuege.webagentur-forster.ch/danke-umzug/']);
        }

        return redirect()->back()->with('error', 'Falscher Code. Bitte erneut versuchen.');
    }

    private function isMobileNumber(string $phone): bool
    {
        // Schweiz +41 Mobilnummern beginnen mit +4175, +4176, +4177, +4178, +4179
        // Beispiel: +41781234567

        $mobilePrefixes = ['+4175', '+4176', '+4177', '+4178', '+4179'];

        foreach ($mobilePrefixes as $prefix) {
            if (str_starts_with($phone, $prefix)) {
                return true;
            }
        }

        return false;
    }

}
