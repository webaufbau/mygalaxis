<?php
namespace App\Controllers;

use Random\RandomException;
use App\Models\OfferModel;

class FluentForm extends BaseController
{

    // Diese Methode ist die Action von Fluent Form und leitet es gemäss Optionen auf die entsprechende URL weiter.
    // Wenn die Ziel URL bzw. die next_url_action = 'nein' ist dann muss die Verifikation ausgeführt werden.
    // additional_service={inputs.additional_service}&refurl={wp.site_url}&service_url={inputs.service_url}&uuid={inputs.uuid}

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
        $additional_service = $getParams['additional_service'] ?? 'Nein';
        unset($getParams['service_url']); // entfernen, damit nicht mit übergeben
        $uuid = $getParams['uuid'] ?? bin2hex(random_bytes(8));

        log_message('debug', 'Form Submit Handle GET: ' . print_r($getParams, true));

        // Speichern
        session()->set('uuid', $uuid);
        session()->set('next_url', $next_url);
        session()->set('additional_service', $additional_service);
        session()->set("formdata_$uuid", [
            'vorname' => $vorname,
            'additional_service' => $additional_service,
            'next_url' => $next_url,
        ]);

        if($additional_service == 'Nein') {
            log_message('debug', 'Weiterleitung zur Verifikation mit UUID '.$uuid.' ' .  print_r($_SESSION, true));
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
        $type = $offerData['type'] ?? $this->detectType($data);

        // Typ-spezifische Speicherung:
        switch ($type) {
            case 'move':
                $moveModel = new \App\Models\OfferMoveModel();
                $moveModel->insert([
                    'offer_id'        => $offerId,
                    'room_size'       => $data['auszug_flaeche'] ?? null,
                    'move_date'       => isset($data['datetime_1']) ? date('Y-m-d', strtotime(str_replace('/', '.', $data['datetime_1']))) : null,
                    'from_city'       => $data['auszug_adresse']['city'] ?? null,
                    'to_city'         => $data['einzug_adresse']['city'] ?? null,
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


        $this->sendOfferNotificationEmail($data, $type, $uuid, $verifyType);



        return $this->response->setJSON(['success' => true]);
    }

    protected function detectType(array $fields): string
    {
        $source = $fields['service_url'] ?? '';

        if (str_contains($source, 'umzuege')) return 'move';
        if (str_contains($source, 'reinigung')) return 'cleaning';
        if (str_contains($source, 'maler')) return 'painter';
        if (str_contains($source, 'garten')) return 'gardener';
        if (str_contains($source, 'sanitaer')) return 'plumbing';

        return 'unknown';
    }

    protected function sendOfferNotificationEmail(array $data, string $formName, string $uuid, ?string $verifyType = null): void
    {
        helper('text'); // für esc()

        // Admins
        $adminEmails = ['support@galaxisgroup.ch', 'info@webaufbau.ch', 'info@webagentur-forster.ch'];
        $bccString = implode(',', $adminEmails);

        // Formularverfasser
        $userEmail = $data['email'] ?? null;

        $formular_page = null;
        if(isset($data['_wp_http_referer'])) {
            $formular_page = $data['_wp_http_referer'];
            $formular_page = str_replace('-', ' ', $formular_page);
            $formular_page = str_replace('/', ' ', $formular_page);
            $formular_page = ucwords($formular_page);
            $formular_page = trim($formular_page);
        }

        // Technische Felder rausfiltern
        $filteredFields = array_filter($data, function ($key) {
            // feste Keys ausschließen
            $excludeKeys = ['__submission', '__fluent_form_embded_post_id', '_wp_http_referer', 'form_name', 'uuid', 'service_url', 'uuid_value', 'verified_method'];

            // prüfen, ob key in festen Keys ist
            if (in_array($key, $excludeKeys)) {
                return false;
            }

            // dynamische Keys ausschließen, z.B. _fluentform_{id}_fluentformnonce
            if (preg_match('/^_fluentform_\d+_fluentformnonce$/', $key)) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);

        // Maildaten für View
        $emailData = [
            'formName'       => $formName,
            'formular_page' => $formular_page,
            'uuid'           => $uuid,
            'verifyType'     => $verifyType,
            'filteredFields' => $filteredFields,
            'data'           => $data,
        ];

        // HTML-Ansicht generieren
        $htmlMessage = view('emails/offer_notification', $emailData);

        // Maildienst starten
        $email = \Config\Services::email();

        $email->setFrom('anfrage@offertenschweiz.ch', 'OffertenSchweiz.ch');
        $email->setTo($userEmail);            // Kunde als To
        $email->setBCC($bccString);         // Admins als BCC
        $email->setSubject('Wir bestätigen Dir deine Anfrage/Offerte');
        $email->setMessage($htmlMessage);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Mail senden fehlgeschlagen: ' . print_r($email->printDebugger(['headers']), true));
        }

    }


}
