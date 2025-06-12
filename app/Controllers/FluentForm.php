<?php
namespace App\Controllers;

use Random\RandomException;

class FluentForm extends BaseController
{
    public function submit()
    {
        $session = session();
        $postData = $this->request->getPost();

        // Option 1: In Session speichern
        $session->set('form_step1', $postData);

        // Option 2: Temporär in DB speichern mit Token (falls nötig)

        log_message('debug', 'Webhook POST: ' . print_r($this->request->getPost(), true));

        $data = $this->request->getPost(); // Formulardaten
        $headers = array_map(function ($header) {
            return (string)$header->getValueLine();
        }, $this->request->headers());
        $referer = $this->request->getServer('HTTP_REFERER');

        $formName = $data['form_name'] ?? null;
        unset($data['form_name']);

        // Verifizierungslogik (Beispiel: erwartet 'verified_method' im POST)
        $verifyType = $data['verified_method'] ?? null;
        $verified = in_array($verifyType, ['sms', 'phone']) ? 1 : 0;
        unset($data['verified_method']); // optional aus form_fields entfernen

        $db = \Config\Database::connect();
        $builder = $db->table('requests');

        if(!$builder->insert([
            'form_name'    => $formName,
            'form_fields'  => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers'      => json_encode(array_map(fn($h) => (string)$h, $headers), JSON_UNESCAPED_UNICODE),
            'referer'      => $referer,
            'verified'     => $verified,
            'verify_type'  => $verifyType,
            'created_at'   => date('Y-m-d H:i:s')
        ])) {
            log_message('error', 'Insert failed: ' . print_r($db->error(), true));
        }


        // Weiterleiten zu Schritt 2
        return redirect()->to('https://umzuege.webagentur-forster.ch/#elementor-action:action=popup:open&settings=eyJpZCI6IjE1MCIsInRvZ2dsZSI6ZmFsc2V9');
    }

    /**
     * @throws RandomException
     */
    public function handle()
    {
        $request = service('request');
        $vorname = $request->getPost('names');
        $next_url_action = $request->getGet('next_url_action');
        $next_url = $request->getGet('service_url');
        $uuid = $request->getGet('uuid') ?? bin2hex(random_bytes(8));

        log_message('debug', 'Form Submit Handle GET: ' . print_r($this->request->getGet(), true));

        // Speichern (in Session oder Datenbank)
        session()->set("formdata_$uuid", [
            'vorname' => $vorname,
            'next_url_action' => $next_url_action,
            'next_url' => $next_url,
        ]);

        return redirect()->to($next_url);

        /*
        // Ziel-URL bestimmen
        if ($auswahl === 'Privatumzug') {
            $redirectUrl = "https://mygalaxis.primeno.ch/form/privat?uid=$uid";
        } else {
            $redirectUrl = "https://mygalaxis.primeno.ch/form/firma?uid=$uid";
        }

        // Anweisung an Fluent Form zum Weiterleiten:
        return $this->response->setJSON([
            'redirect_url' => $redirectUrl
        ]);
        */
    }


    public function step2()
    {
        $session = session();
        $step1Data = $session->get('form_step1');

        // Lade Schritt 2 View und gib Daten aus Schritt 1 mit
        return view('form/step2', ['step1' => $step1Data]);
    }
}
