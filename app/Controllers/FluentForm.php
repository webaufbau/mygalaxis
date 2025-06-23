<?php
namespace App\Controllers;

use Random\RandomException;
use App\Models\OfferModel;

class FluentForm extends BaseController
{

    // Diese Methode ist die Action von Fluent Form und leitet es gemäss Optionen auf die entsprechende URL weiter.
    // Wenn die Ziel URL bzw. die next_url_action = 'nein' ist dann muss die Verifikation ausgeführt werden.

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
        $next_url_action = $getParams['next_url_action'] ?? 'nein';
        unset($getParams['service_url']); // entfernen, damit nicht mit übergeben
        $uuid = $getParams['uuid'] ?? bin2hex(random_bytes(8));

        log_message('debug', 'Form Submit Handle GET: ' . print_r($getParams, true));

        // Speichern
        session()->set('uuid', $uuid);
        session()->set('next_url', $next_url);
        session()->set("formdata_$uuid", [
            'vorname' => $vorname,
            'next_url_action' => $next_url_action,
            'next_url' => $next_url,
        ]);

        if($next_url_action == 'nein') {
            return redirect()->to('verification');
        }

        // URL zusammensetzen (alle GET-Parameter anhängen)
        if ($next_url) {
            $query = http_build_query($getParams);
            $redirectUrl = $next_url . (str_contains($next_url, '?') ? '&' : '?') . $query;
            return redirect()->to($redirectUrl);
        }

        return redirect()->to('/'); // Fallback, falls next_url fehlt
    }

    // Dies wird nach dem Senden der Formulare ausgeführt:
    public function webhook()
    {
        log_message('debug', 'Webhook called!');
        log_message('debug', 'Webhook POST: ' . print_r($this->request->getPost(), true));

        $data = $this->request->getPost();
        $headers = array_map(function ($header) {
            return (string)$header->getValueLine();
        }, $this->request->headers());
        $referer = $this->request->getServer('HTTP_REFERER');

        log_message('debug', 'Webhook HEADERS: ' . print_r($headers, true));

        $formName = $data['form_name'] ?? null;
        unset($data['form_name']);

        $uuid = $data['uuid'] ?? bin2hex(random_bytes(8)); // fallback falls nicht mitgeliefert

        $verifyType = $data['verified_method'] ?? null;
        $verified = in_array($verifyType, ['sms', 'phone']) ? 1 : 0;
        unset($data['verified_method']);

        $offerModel = new OfferModel();

        if (!$offerModel->insert([
            'form_name'    => $formName,
            'form_fields'  => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers'      => json_encode($headers, JSON_UNESCAPED_UNICODE),
            'referer'      => $referer,
            'verified'     => $verified,
            'verify_type'  => $verifyType,
            'uuid'         => $uuid,
            'created_at'   => date('Y-m-d H:i:s'),
            'status'       => 'new',
            'price'        => 0.00,
            'buyers'       => 0,
            'bought_by'    => json_encode([]),
        ])) {
            log_message('error', 'Offer insert failed: ' . print_r($offerModel->errors(), true));
        }

        $offerId = $offerModel->getInsertID();
        $type = $offerData['type'] ?? 'unknown';

        // Typ-spezifische Speicherung:
        switch ($type) {
            case 'move':
                $moveModel = new \App\Models\OfferMoveModel();
                $moveModel->insert([
                    'offer_id'        => $offerId,
                    'room_size'       => $data['auszug_flaeche_firma'] ?? null,
                    'move_date'       => isset($data['datetime_1']) ? date('Y-m-d', strtotime(str_replace('/', '.', $data['datetime_1']))) : null,
                    'from_city'       => $data['auszug_adresse_firma']['city'] ?? null,
                    'to_city'         => $data['einzug_adresse_firma']['city'] ?? null,
                    'has_lift'        => $data['auszug_lift_firma'] ?? null,
                    'customer_type'   => isset($data['firmenname']) ? 'firma' : 'privat',
                ]);
                break;

            case 'cleaning':
                $cleaningModel = new \App\Models\OfferCleaningModel();
                $cleaningModel->insert([
                    'offer_id'       => $offerId,
                    'object_size'    => $data['objektgroesse'] ?? null,
                    'cleaning_type'  => $data['reinigungsart'] ?? null,
                ]);
                break;

            case 'painter':
                $painterModel = new \App\Models\OfferPaintingModel();
                $painterModel->insert([
                    'offer_id'       => $offerId,
                    'area'           => $data['malerflaeche'] ?? null,
                    'indoor_outdoor' => $data['malerart'] ?? null,
                ]);
                break;

            case 'gardener':
                $gardenerModel = new \App\Models\OfferGardeningModel();
                $gardenerModel->insert([
                    'offer_id'       => $offerId,
                    'garden_size'    => $data['gartenflaeche'] ?? null,
                    'work_type'      => $data['gartenarbeit'] ?? null,
                ]);
                break;

            case 'plumbing':
                $plumbingModel = new \App\Models\OfferPlumbingModel();
                $plumbingModel->insert([
                    'offer_id'       => $offerId,
                    'problem_type'   => $data['sanitaer_typ'] ?? null,
                    'urgency'        => $data['dringlichkeit'] ?? null,
                ]);
                break;
        }


        return $this->response->setJSON(['success' => true]);
    }

}
