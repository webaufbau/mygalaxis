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

        // Session speichern (Fallback)
        session()->set('uuid', $uuid);
        session()->set('next_url', $next_url);
        session()->set('additional_service', $additional_service);
        session()->set("formdata_$uuid", [
            'vorname' => $vorname,
            'additional_service' => $additional_service,
            'next_url' => $next_url,
        ]);

        log_message('debug', 'Session Daten sichern: ' .  print_r($_SESSION, true));

        if($additional_service == 'Nein') {
            log_message('debug', 'Weiterleitung zur Verifikation mit UUID '.$uuid.' ' .  print_r($_SESSION, true));
            return redirect()->to('processing?uuid=' . urlencode($uuid));
        }

        // Aktuelle Sprache aus Helper holen
        $locale = getCurrentLocale();

        // Basis-URI für Weiterleitung (z.B. "processing")
        $baseUri = 'processing';

        // URI mit Sprachsegment (sofern nicht 'de')
        $redirectUri = changeLocaleInUri($baseUri, $locale);

        // URL zusammensetzen (alle GET-Parameter anhängen)
        if ($next_url) {
            $query = http_build_query($getParams);
            $redirectUrl = $next_url . (str_contains($next_url, '?') ? '&' : '?') . $query;
            return redirect()->to($redirectUrl);
        }

        return redirect()->to('/'); // Fallback, falls next_url fehlt
    }

    // Dies wird nach dem Senden der Formulare ausgeführt:

    /**
     * @throws RandomException
     * @throws \ReflectionException
     */
    public function webhook()
    {
        log_message('debug', 'Webhook called!');
        log_message('debug', 'Webhook POST: ' . print_r($this->request->getPost(), true));

        $data = $this->request->getPost();
        $data = trim_recursive($data);

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

        $isCampaign = 0;
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $utmKey) {
            if (!empty($data[$utmKey])) {
                $isCampaign = 1;
                break;
            }
        }

        // save for groups
        $groupId = null;
        if(isset($data['additional_service'])) {
            log_message('debug', 'matching additional_service|'.$data['additional_service'].'|');

            if ($data['additional_service'] !== 'Nein') {
                session()->set('group_email', $data['email'] ?? null);
                session()->set('group_uuid', $data['uuid'] ?? null);
                session()->set('group_additional_service', $data['additional_service'] ?? null);
                session()->set('group_date', time());

                log_message('debug', 'additional_service group_email ' . session()->get('group_email'));
                log_message('debug', 'additional_service group_uuid ' . session()->get('group_uuid'));
                log_message('debug', 'additional_service group_additional_service ' . session()->get('group_additional_service'));
                log_message('debug', 'additional_service group_date ' . session()->get('group_date'));

            } else {
                log_message('debug', 'matching Nein');

                // Nein
                $offerModel = new OfferModel();
                $matchingOffers = $offerModel
                    ->where('email', $data['email'] ?? session()->get('group_email'))
                    // ->where('uuid', session()->get('group_uuid'))
                    ->where('group_id IS NULL') // noch nicht gruppiert
                    //->where('created_at >=', date('Y-m-d H:i:s', strtotime('-15 minutes')))
                    ->orderBy('created_at', 'DESC')
                    ->findAll(1); // nur das letzte holen
                log_message('debug', 'matching query ' . $offerModel->db->getLastQuery());

                if (!empty($matchingOffers)) {
                    log_message('debug', 'matching offers ' . print_r($matchingOffers, true));

                    $groupId = bin2hex(random_bytes(6));
                    log_message('debug', 'matching groupId ' . $groupId);

                    // Update vorheriges Angebot mit group_id
                    $offerModel->update($matchingOffers[0]['id'], ['group_id' => $groupId]);
                }

            }
        }


        $siteConfig = siteconfig();


        $offerModel = new OfferModel();
        $enriched = $offerModel->enrichDataFromFormFields($data, ['uuid' => $uuid]);

        $type = $enriched['type'] ?? $data['type'] ?? 'unknown';

        $categoryManager = new \App\Libraries\CategoryManager();
        $categories = $categoryManager->getAll();
        $category_option = $categories[$type] ?? null;

        $insertData = array_merge([
            'form_name'     => $formName,
            'form_fields'   => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers'       => json_encode($headers, JSON_UNESCAPED_UNICODE),
            'referer'       => $referer,
            'verified'      => $verified,
            'verify_type'   => $verifyType,
            'uuid'          => $uuid,
            //'created_at'    => date('Y-m-d H:i:s'),
            'status'        => 'available',
            'price'         => $category_option['price'] ?? 0,
            'buyers'        => 0,
            'bought_by'     => json_encode([]),
            'from_campaign' => $isCampaign,
            'group_id'      => $groupId,
            'type'          => $type,
            'country'       => $siteConfig->siteCountry ?? null,
        ], $enriched);

        $host = $_SERVER['HTTP_HOST'] ?? $headers['Host'] ?? 'unknown';
        $parts = explode('.', $host);
        $partsCount = count($parts);
        if ($partsCount > 2) {
            $domain = $parts[$partsCount - 2] . '.' . $parts[$partsCount - 1];
        } else {
            $domain = $host;
        }
        $insertData['platform'] = $domain;

        if(isset($data['additional_service']) && $data['additional_service'] == 'Nein') {
            $other_type_has_to_be = $enriched['type'] == 'move' ? 'cleaning' : 'move';
            $userEmail = $data['email'] ?? null;
            $offerFindModel = new OfferModel();
            $matchingOffers = $offerFindModel
                ->where('email', $data['email'] ?? $userEmail)
                ->where('type', $other_type_has_to_be)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-3600 minutes')))
                ->orderBy('created_at', 'DESC')
                ->findAll(1); // nur das letzte holen

            if (!empty($matchingOffers)) {
                $previousOffer = $matchingOffers[0];
                $previousFormFields = json_decode($previousOffer['form_fields'], true) ?? [];

                // Neuen Typ setzen
                $type = 'move_cleaning';

                $categoryManager = new \App\Libraries\CategoryManager();
                $categories = $categoryManager->getAll();
                $category_option = $categories[$type] ?? null;

                $insertData['type'] = $type;
                $insertData['price'] = $category_option['price'] ?? 0;
                $insertData['form_fields_combo'] = json_encode($previousFormFields, JSON_UNESCAPED_UNICODE);

                // vorige Anfrage $matchingOffers löschen
                $offerModel->delete($previousOffer['id']);
            }
        }

        log_message('debug', 'insertdata: ' . print_r($insertData, true));


        // Speichern
        if (!$offerModel->insert($insertData)) {
            log_message('error', 'Offer insert failed: ' . print_r($offerModel->errors(), true));
        }

        $offerId = $offerModel->getInsertID();
        $formFields = $data;


        // Typ-spezifische Speicherung:
        $typeModelMap = [
            'move'          => \App\Models\OfferMoveModel::class,
            'cleaning'      => \App\Models\OfferCleaningModel::class,
            'move_cleaning' => \App\Models\OfferMoveCleaningModel::class,
            'painting'      => \App\Models\OfferPaintingModel::class,
            'gardening'     => \App\Models\OfferGardeningModel::class,
            'plumbing'      => \App\Models\OfferPlumbingModel::class,
            'electrician'   => \App\Models\OfferElectricianModel::class,
            'flooring'      => \App\Models\OfferFlooringModel::class,
            'heating'       => \App\Models\OfferHeatingModel::class,
            'tiling'        => \App\Models\OfferTilingModel::class,
        ];

        $typeExtractorMap = [
            'move'        => 'extractMoveFields',
            'cleaning'    => 'extractCleaningFields',
            'painting'    => 'extractPaintingFields',
            'gardening'   => 'extractGardeningFields',
            'plumbing'    => 'extractPlumbingFields',
            'electrician' => 'extractElectricianFields',
            'flooring'    => 'extractFlooringFields',
            'heating'     => 'extractHeatingFields',
            'tiling'      => 'extractTilingFields',
        ];

        if (isset($typeModelMap[$type], $typeExtractorMap[$type])) {
            $modelClass = $typeModelMap[$type];
            $extractMethod = $typeExtractorMap[$type];

            $typeModel = new $modelClass();
            $typeData = $offerModel->$extractMethod($formFields);
            $typeData['offer_id'] = $offerId;

            // Arrays zu Strings konvertieren
            foreach ($typeData as $key => $val) {
                if (is_array($val)) {
                    $typeData[$key] = implode(', ', $val);
                }
            }

            $typeModel->insert($typeData);
        }

        //$this->sendOfferNotificationEmail($data, $type, $uuid, $verifyType); später senden erst nach Verifikation

        return $this->response->setJSON(['success' => true]);
    }


}
