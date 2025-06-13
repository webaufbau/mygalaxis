<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

class Verification extends Controller
{
    public function index()
    {
        return view('verification_form');
    }

    public function send()
    {
        $request = service('request');

        $phone = $request->getPost('phone');
        $method = $request->getPost('method');

        if (!$phone || !$method) {
            return redirect()->back()->with('error', 'Bitte alle Felder ausfüllen.');
        }

        // Simuliere den Versand des Verifizierungscodes
        $verificationCode = rand(100000, 999999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);

        return redirect()->to('/verification/confirm');
    }

    public function confirm()
    {
        return view('verification_confirm');
    }

    public function verify()
    {
        $request = service('request');

        $enteredCode = $request->getPost('code');
        $sessionCode = session()->get('verification_code');

        if ($enteredCode == $sessionCode) {
            // Markiere als bestätigt, z. B. in der Datenbank
            session()->remove('verification_code');
            return view('verification_success');
        }

        return redirect()->back()->with('error', 'Falscher Code. Bitte erneut versuchen.');
    }
}
