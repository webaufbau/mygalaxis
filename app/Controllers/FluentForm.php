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

        // POST-Daten (meist leer bei Fluent Form Action URLs)
        $vorname = $request->getPost('names');
        $nachname = $request->getPost('nachname');
        $email = $request->getPost('email');
        $phone = $request->getPost('phone');

        // GET-Daten
        $getParams = $request->getGet(); // alle GET-Parameter
        $next_url = $getParams['service_url'] ?? null;
        $additional_service = $getParams['additional_service'] ?? 'Nein';
        unset($getParams['service_url']); // entfernen, damit nicht mit übergeben
        $uuid = $getParams['uuid'] ?? bin2hex(random_bytes(8));

        log_message('debug', 'Form Submit Handle GET: ' . print_r($getParams, true));

        // Kontaktdaten aus POST-Daten oder Session holen
        if (empty($vorname) || empty($email)) {
            // Zuerst versuchen aus Session zu holen
            $vorname = session()->get('group_vorname') ?? session()->get('vorname') ?? '';
            $nachname = session()->get('group_nachname') ?? session()->get('nachname') ?? '';
            $email = session()->get('group_email') ?? session()->get('email') ?? '';
            $phone = session()->get('group_phone') ?? session()->get('phone') ?? '';

            // Wenn Session leer ist, dann aus Datenbank holen basierend auf UUID
            if (empty($email) && !empty($uuid)) {
                $offerModel = new OfferModel();
                $lastOffer = $offerModel
                    ->where('uuid', $uuid)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if ($lastOffer) {
                    $formFields = json_decode($lastOffer['form_fields'], true) ?? [];
                    $vorname = $formFields['names'] ?? $formFields['vorname'] ?? '';
                    $nachname = $formFields['nachname'] ?? '';
                    $email = $formFields['email'] ?? '';
                    $phone = $formFields['phone'] ?? '';
                    log_message('debug', 'Kontaktdaten aus Datenbank geladen (UUID: '.$uuid.'): vorname='.$vorname.', email='.$email);
                }
            } else {
                log_message('debug', 'Kontaktdaten aus Session geladen: vorname='.$vorname.', email='.$email);
            }
        }

        // Weitere Kontaktdaten aus Session oder Datenbank holen
        $addressLine1 = session()->get('group_address_line_1') ?? session()->get('address_line_1') ?? '';
        $addressLine2 = session()->get('group_address_line_2') ?? session()->get('address_line_2') ?? '';
        $zip = session()->get('group_zip') ?? session()->get('zip') ?? '';
        $city = session()->get('group_city') ?? session()->get('city') ?? '';
        $erreichbar = session()->get('group_erreichbar') ?? session()->get('erreichbar') ?? '';

        // Wenn Session leer ist, auch diese aus Datenbank holen
        if (empty($addressLine1) && !empty($uuid)) {
            $offerModel = $offerModel ?? new OfferModel();
            $lastOffer = $lastOffer ?? $offerModel
                ->where('uuid', $uuid)
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($lastOffer) {
                $formFields = $formFields ?? json_decode($lastOffer['form_fields'], true) ?? [];

                // Adresse kann in verschiedenen Formaten vorliegen
                $address = $formFields['address']
                    ?? $formFields['auszug_adresse']
                    ?? $formFields['auszug_adresse_firma']
                    ?? [];

                // Wenn address ein Array ist, extrahiere die Felder
                if (is_array($address)) {
                    $addressLine1 = $address['address_line_1'] ?? $address['address'] ?? '';
                    $addressLine2 = $address['address_line_2'] ?? '';
                    $zip = $address['zip'] ?? '';
                    $city = $address['city'] ?? '';
                } else {
                    // Fallback: direkt aus formFields
                    $addressLine1 = $formFields['address_line_1'] ?? '';
                    $addressLine2 = $formFields['address_line_2'] ?? '';
                    $zip = $formFields['zip'] ?? $lastOffer['zip'] ?? '';
                    $city = $formFields['city'] ?? $lastOffer['city'] ?? '';
                }

                $erreichbar = $formFields['erreichbar'] ?? $formFields['erreichbarkeit'] ?? '';
                log_message('debug', 'Adressdaten aus Datenbank geladen (UUID: '.$uuid.'): address_line_1='.$addressLine1.', zip='.$zip.', city='.$city);
            }
        }

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
            // Frontend-Domain aus SiteConfig holen
            $frontendDomain = parse_url($this->siteConfig->frontendUrl, PHP_URL_HOST);

            // Kontaktdaten als GET-Parameter hinzufügen (WordPress Plugin kann diese abfangen)
            if ($frontendDomain && str_contains($next_url, $frontendDomain)) {
                log_message('info', "Weiterleitung zu WordPress mit Kontaktdaten: $next_url");

                // Kontaktdaten zu GET-Parametern hinzufügen
                $getParams['vorname'] = $vorname ?? '';
                $getParams['nachname'] = $nachname ?? '';
                $getParams['email'] = $email ?? '';
                $getParams['phone'] = $phone ?? '';
                $getParams['address_line_1'] = $addressLine1 ?? '';
                $getParams['address_line_2'] = $addressLine2 ?? '';
                $getParams['zip'] = $zip ?? '';
                $getParams['city'] = $city ?? '';
                $getParams['erreichbar'] = $erreichbar ?? '';
                $getParams['skip_kontakt'] = '1';

                log_message('debug', 'Kontaktdaten für Weiterleitung: ' . print_r([
                    'vorname' => $vorname,
                    'nachname' => $nachname,
                    'email' => $email,
                    'phone' => $phone,
                    'address_line_1' => $addressLine1,
                    'address_line_2' => $addressLine2,
                    'zip' => $zip,
                    'city' => $city,
                    'erreichbar' => $erreichbar
                ], true));
            }

            // Weiterleitung mit allen GET-Parametern
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
    /**
     * API Endpoint für FluentForm um Session-Daten abzurufen
     * Gibt zurück ob User bereits Daten ausgefüllt hat
     */
    public function sessionData()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Content-Type', 'application/json');

        // Session-Daten prüfen
        $sessionData = [
            'has_session' => false,
            'email' => null,
            'phone' => null,
            'group_email' => session()->get('group_email'),
            'group_uuid' => session()->get('group_uuid'),
            'group_additional_service' => session()->get('group_additional_service'),
        ];

        // Prüfen ob relevante Session-Daten existieren
        if (!empty($sessionData['group_email'])) {
            $sessionData['has_session'] = true;
            $sessionData['email'] = $sessionData['group_email'];
        }

        log_message('debug', 'Session Data API called: ' . print_r($sessionData, true));

        return $this->response->setJSON($sessionData);
    }

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

        $uuid = $data['uuid'] ?? $data['uuid_value'] ?? bin2hex(random_bytes(8)); // fallback falls nicht mitgeliefert

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
                // Alle Kontaktdaten für spätere Weiterleitung speichern
                session()->set('group_vorname', $data['names'] ?? $data['vorname'] ?? null);
                session()->set('group_nachname', $data['nachname'] ?? null);
                session()->set('group_email', $data['email'] ?? null);
                session()->set('group_phone', $data['phone'] ?? null);
                session()->set('group_address_line_1', $data['address_line_1'] ?? null);
                session()->set('group_address_line_2', $data['address_line_2'] ?? null);
                session()->set('group_zip', $data['zip'] ?? null);
                session()->set('group_city', $data['city'] ?? null);
                session()->set('group_erreichbar', $data['erreichbar'] ?? null);
                session()->set('group_uuid', $data['uuid'] ?? $data['uuid_value'] ?? null);
                session()->set('group_additional_service', $data['additional_service'] ?? null);
                session()->set('group_date', time());

                log_message('debug', 'additional_service group_vorname ' . session()->get('group_vorname'));
                log_message('debug', 'additional_service group_nachname ' . session()->get('group_nachname'));
                log_message('debug', 'additional_service group_email ' . session()->get('group_email'));
                log_message('debug', 'additional_service group_phone ' . session()->get('group_phone'));
                log_message('debug', 'additional_service group_address_line_1 ' . session()->get('group_address_line_1'));
                log_message('debug', 'additional_service group_address_line_2 ' . session()->get('group_address_line_2'));
                log_message('debug', 'additional_service group_zip ' . session()->get('group_zip'));
                log_message('debug', 'additional_service group_city ' . session()->get('group_city'));
                log_message('debug', 'additional_service group_erreichbar ' . session()->get('group_erreichbar'));
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
        $originalType = $enriched['original_type'] ?? $type;

        // Preis mit OfferPriceCalculator berechnen (konsistent mit Cronjob)
        $priceCalculator = new \App\Libraries\OfferPriceCalculator();
        $calculatedPrice = $priceCalculator->calculatePrice(
            $type,
            $originalType,
            $data,
            [] // form_fields_combo ist leer bei neuen Angeboten
        );

        // Fallback auf CategoryManager nur wenn OfferPriceCalculator 0 zurückgibt
        if ($calculatedPrice === 0) {
            $categoryManager = new \App\Libraries\CategoryManager();
            $categories = $categoryManager->getAll();
            $category_option = $categories[$type] ?? null;
            $calculatedPrice = $category_option['price'] ?? 0;
        }

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
            'price'         => $calculatedPrice,
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
        // Platform normalisieren: Domain-Format zu Ordner-Format
        // z.B. offertenschweiz.ch -> my_offertenschweiz_ch
        $insertData['platform'] = 'my_' . str_replace(['.', '-'], '_', $domain);

        // Combo-Logik nur für move und cleaning
        if(isset($data['additional_service']) && $data['additional_service'] == 'Nein') {
            $currentType = $enriched['type'] ?? $type;

            // Nur fortfahren wenn aktueller Typ 'move' oder 'cleaning' ist
            if (in_array($currentType, ['move', 'cleaning'])) {
                $other_type_has_to_be = $currentType == 'move' ? 'cleaning' : 'move';
                $userEmail = $data['email'] ?? $data['email_firma'] ?? null;
                $offerFindModel = new OfferModel();
                $matchingOffers = $offerFindModel
                    ->where('email', $data['email'] ?? $data['email_firma'] ?? $userEmail)
                    ->where('type', $other_type_has_to_be)
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-3600 minutes')))
                    ->orderBy('created_at', 'DESC')
                    ->findAll(1); // nur das letzte holen

                if (!empty($matchingOffers)) {
                    $previousOffer = $matchingOffers[0];
                    $previousFormFields = json_decode($previousOffer['form_fields'], true) ?? [];

                    // Neuen Typ setzen
                    $type = 'move_cleaning';

                    // Preis mit OfferPriceCalculator berechnen für move_cleaning
                    $comboPrice = $priceCalculator->calculatePrice(
                        $type,
                        $type,
                        $data,
                        $previousFormFields
                    );

                    // Fallback auf CategoryManager
                    if ($comboPrice === 0) {
                        $categoryManager = new \App\Libraries\CategoryManager();
                        $categories = $categoryManager->getAll();
                        $category_option = $categories[$type] ?? null;
                        $comboPrice = $category_option['price'] ?? 0;
                    }

                    $insertData['type'] = $type;
                    $insertData['price'] = $comboPrice;
                    $insertData['form_fields_combo'] = json_encode($previousFormFields, JSON_UNESCAPED_UNICODE);

                    // vorige Anfrage $matchingOffers löschen
                    $offerModel->delete($previousOffer['id']);

                    log_message('info', "Combo offer created: move_cleaning from {$currentType} + {$other_type_has_to_be}, deleted offer #{$previousOffer['id']}");
                }
            } else {
                log_message('debug', "Combo logic skipped: current type '{$currentType}' is not 'move' or 'cleaning'");
            }
        }

        // Warnung loggen wenn Preis 0 ist
        if ($insertData['price'] === 0 || $insertData['price'] === '0') {
            log_message('warning', 'Offer created with price 0! Type: ' . $type . ', OriginalType: ' . $originalType . ', Data: ' . json_encode($data));
        }

        log_message('debug', 'insertdata: ' . print_r($insertData, true));


        // Speichern
        if (!$offerModel->insert($insertData)) {
            log_message('error', 'Offer insert failed: ' . print_r($offerModel->errors(), true));
        }

        $offerId = $offerModel->getInsertID();
        $formFields = $data;

        // Titel generieren nach dem Insert (damit wir die ID haben)
        $savedOffer = $offerModel->find($offerId);
        if ($savedOffer) {
            $titleGenerator = new \App\Libraries\OfferTitleGenerator();
            $generatedTitle = $titleGenerator->generateTitle($savedOffer);
            $offerModel->update($offerId, ['title' => $generatedTitle]);
        }

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
