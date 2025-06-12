<?php
namespace App\Controllers;

use Random\RandomException;

class FluentForm extends BaseController
{
    // aktiv:
    /**
     * @throws RandomException
     */
    public function handle()
    {
        $request = service('request');

        // POST-Daten
        $vorname = $request->getPost('names');

        // GET-Daten
        $getParams = $request->getGet(); // alle GET-Parameter
        $next_url = $getParams['service_url'] ?? null;
        unset($getParams['service_url']); // entfernen, damit nicht mit übergeben
        $uuid = $getParams['uuid'] ?? bin2hex(random_bytes(8));

        log_message('debug', 'Form Submit Handle GET: ' . print_r($getParams, true));

        // Speichern
        session()->set("formdata_$uuid", [
            'vorname' => $vorname,
            'next_url_action' => $getParams['next_url_action'] ?? '',
            'next_url' => $next_url,
        ]);

        // URL zusammensetzen (alle GET-Parameter anhängen)
        if ($next_url) {
            $query = http_build_query($getParams);
            $redirectUrl = $next_url . (str_contains($next_url, '?') ? '&' : '?') . $query;
            return redirect()->to($redirectUrl);
        }

        return redirect()->to('/'); // Fallback, falls next_url fehlt

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

    // inaktiv:
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


}
