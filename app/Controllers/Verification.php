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
