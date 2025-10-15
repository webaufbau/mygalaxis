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

        log_message('debug', '[Handle] Form Submit Handle GET: ' . print_r($getParams, true));
        log_message('debug', '[Handle] UUID: ' . $uuid . ', additional_service: ' . $additional_service);
        log_message('debug', '[Handle] POST vorname: ' . ($vorname ?: 'leer') . ', POST email: ' . ($email ?: 'leer'));

        // Kontaktdaten aus POST-Daten oder Session holen
        if (empty($vorname) || empty($email)) {
            log_message('debug', '[Handle] POST-Daten leer, versuche aus Session zu laden');

            // Zuerst versuchen aus Session zu holen
            $vorname = session()->get('group_vorname') ?? session()->get('vorname') ?? '';
            $nachname = session()->get('group_nachname') ?? session()->get('nachname') ?? '';
            $email = session()->get('group_email') ?? session()->get('email') ?? '';
            $phone = session()->get('group_phone') ?? session()->get('phone') ?? '';

            log_message('debug', '[Handle] Session geladen - vorname: ' . ($vorname ?: 'leer') . ', email: ' . ($email ?: 'leer') . ', phone: ' . ($phone ?: 'leer'));

            // Wenn Session leer ist, dann aus Datenbank holen basierend auf UUID
            if (empty($email) && !empty($uuid)) {
                $offerModel = new OfferModel();

                // WICHTIG: Lade die ERSTE Offerte mit Kontaktdaten, nicht die neueste!
                $lastOffer = $offerModel
                    ->where('uuid', $uuid)
                    ->where('email IS NOT NULL')
                    ->where('phone IS NOT NULL')
                    ->orderBy('created_at', 'ASC')
                    ->first();

                if ($lastOffer) {
                    $formFields = json_decode($lastOffer['form_fields'], true) ?? [];
                    $vorname = $formFields['names'] ?? $formFields['vorname'] ?? '';
                    $nachname = $formFields['nachname'] ?? '';
                    $email = $formFields['email'] ?? '';
                    $phone = $formFields['phone'] ?? '';
                    log_message('debug', '[Handle] Kontaktdaten aus Datenbank geladen (UUID: '.$uuid.', Offer ID: '.$lastOffer['id'].'): vorname='.$vorname.', email='.$email);
                } else {
                    log_message('warning', '[Handle] Keine Offerte mit Kontaktdaten gefunden für UUID: '.$uuid);
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

        log_message('debug', '[Handle] Adressdaten aus Session - Line1: ' . ($addressLine1 ?: 'leer') . ', Zip: ' . ($zip ?: 'leer') . ', City: ' . ($city ?: 'leer'));

        // Wenn Session leer ist, auch diese aus Datenbank holen
        if (empty($addressLine1) && !empty($uuid)) {
            $offerModel = $offerModel ?? new OfferModel();

            // WICHTIG: Lade die ERSTE Offerte mit Kontaktdaten
            $lastOffer = $lastOffer ?? $offerModel
                ->where('uuid', $uuid)
                ->where('email IS NOT NULL')
                ->where('phone IS NOT NULL')
                ->orderBy('created_at', 'ASC')
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
                log_message('debug', '[Handle] Adressdaten aus Datenbank geladen (UUID: '.$uuid.', Offer ID: '.$lastOffer['id'].'): address_line_1='.$addressLine1.', zip='.$zip.', city='.$city);
            } else {
                log_message('debug', '[Handle] Keine Offerte mit Adressdaten in DB gefunden für UUID: ' . $uuid);
            }
        } else {
            log_message('debug', '[Handle] Adressdaten aus Session vorhanden, kein DB-Lookup nötig');
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

                log_message('info', '[Handle] === FINALE KONTAKTDATEN FÜR WEITERLEITUNG ===');
                log_message('info', '[Handle] Vorname: ' . ($vorname ?: 'LEER'));
                log_message('info', '[Handle] Nachname: ' . ($nachname ?: 'LEER'));
                log_message('info', '[Handle] Email: ' . ($email ?: 'LEER'));
                log_message('info', '[Handle] Phone: ' . ($phone ?: 'LEER'));
                log_message('info', '[Handle] Address Line 1: ' . ($addressLine1 ?: 'LEER'));
                log_message('info', '[Handle] Address Line 2: ' . ($addressLine2 ?: 'LEER'));
                log_message('info', '[Handle] ZIP: ' . ($zip ?: 'LEER'));
                log_message('info', '[Handle] City: ' . ($city ?: 'LEER'));
                log_message('info', '[Handle] Erreichbar: ' . ($erreichbar ?: 'LEER'));
                log_message('info', '[Handle] ========================================');
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

        // WICHTIG: Bei zweiter Offerte (skip_kontakt=1) müssen Kontaktdaten geladen werden
        if (!empty($data['skip_kontakt']) && $data['skip_kontakt'] == '1') {
            log_message('debug', '[Webhook] skip_kontakt=1 - Lade Kontaktdaten');

            $uuid = $data['uuid'] ?? $data['uuid_value'] ?? null;
            if ($uuid) {
                $db = \Config\Database::connect();
                $contactData = null;

                // METHODE 1: Versuche aus temp_contact_data zu laden
                $tempData = $db->table('temp_contact_data')
                    ->where('uuid', $uuid)
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->get()
                    ->getRow();

                if ($tempData) {
                    $contactData = json_decode($tempData->contact_data, true);
                    log_message('debug', '[Webhook] Kontaktdaten aus temp_contact_data geladen');

                    // Temp-Daten nach Verwendung löschen
                    $db->table('temp_contact_data')->where('uuid', $uuid)->delete();
                } else {
                    // METHODE 2: Lade aus vorheriger Offerte mit gleicher UUID
                    log_message('debug', '[Webhook] Keine temp_contact_data - lade aus vorheriger Offerte');

                    $previousOffer = $db->table('offers')
                        ->where('uuid', $uuid)
                        ->where('email IS NOT NULL')
                        ->where('phone IS NOT NULL')
                        ->orderBy('created_at', 'ASC')
                        ->get()
                        ->getRow();

                    if ($previousOffer && !empty($previousOffer->form_fields)) {
                        $formFields = json_decode($previousOffer->form_fields, true) ?? [];

                        // Extrahiere Adresse aus verschachteltem Array
                        $address = $formFields['address']
                            ?? $formFields['auszug_adresse']
                            ?? $formFields['auszug_adresse_firma']
                            ?? [];

                        $contactData = [
                            'vorname' => $formFields['names'] ?? $formFields['vorname'] ?? null,
                            'nachname' => $formFields['nachname'] ?? null,
                            'email' => $formFields['email'] ?? $previousOffer->email ?? null,
                            'phone' => $formFields['phone'] ?? $previousOffer->phone ?? null,
                            'erreichbar' => $formFields['erreichbar'] ?? null,
                        ];

                        if (is_array($address)) {
                            $contactData['address_line_1'] = $address['address_line_1'] ?? $address['address'] ?? null;
                            $contactData['address_line_2'] = $address['address_line_2'] ?? null;
                            $contactData['zip'] = $address['zip'] ?? null;
                            $contactData['city'] = $address['city'] ?? null;
                        } else {
                            $contactData['address_line_1'] = $formFields['address_line_1'] ?? null;
                            $contactData['address_line_2'] = $formFields['address_line_2'] ?? null;
                            $contactData['zip'] = $formFields['zip'] ?? $previousOffer->zip ?? null;
                            $contactData['city'] = $formFields['city'] ?? $previousOffer->city ?? null;
                        }

                        log_message('debug', '[Webhook] Kontaktdaten aus vorheriger Offerte geladen (ID: ' . $previousOffer->id . ')');
                    } else {
                        log_message('warning', '[Webhook] skip_kontakt=1 aber keine vorherige Offerte mit Kontaktdaten für UUID: ' . $uuid);
                    }
                }

                // Kontaktdaten in $data einfügen
                if ($contactData) {
                    $contactFields = ['vorname', 'nachname', 'email', 'phone', 'address_line_1', 'address_line_2', 'zip', 'city', 'erreichbar'];
                    foreach ($contactFields as $field) {
                        if (empty($data[$field]) && !empty($contactData[$field])) {
                            $data[$field] = $contactData[$field];
                            log_message('debug', '[Webhook] Übernommen: ' . $field . ' = ' . $contactData[$field]);
                        }
                    }

                    // names Feld
                    if (empty($data['names']) && !empty($contactData['vorname'])) {
                        $data['names'] = $contactData['vorname'];
                    }
                }
            }
        }

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
                log_message('debug', '[Webhook] Erste Offerte - Extrahiere Adressdaten');
                log_message('debug', '[Webhook] Alle $data Keys: ' . implode(', ', array_keys($data)));

                // Zeige alle address-ähnlichen Felder
                foreach ($data as $key => $value) {
                    if (stripos($key, 'address') !== false || stripos($key, 'adresse') !== false ||
                        $key === 'zip' || $key === 'city' || stripos($key, 'stadt') !== false) {
                        log_message('debug', '[Webhook] Adressfeld gefunden: ' . $key . ' = ' .
                            (is_array($value) ? json_encode($value) : $value));
                    }
                }

                // Adresse kann in verschiedenen Formaten vorliegen - extrahiere zuerst
                $address = $data['address']
                    ?? $data['auszug_adresse']
                    ?? $data['auszug_adresse_firma']
                    ?? [];

                log_message('debug', '[Webhook] Extrahiertes $address Array: ' . (is_array($address) ? json_encode($address) : 'kein Array'));

                $addressLine1 = null;
                $addressLine2 = null;
                $zip = null;
                $city = null;

                if (is_array($address) && !empty($address)) {
                    $addressLine1 = $address['address_line_1'] ?? $address['address'] ?? null;
                    $addressLine2 = $address['address_line_2'] ?? null;
                    $zip = $address['zip'] ?? null;
                    $city = $address['city'] ?? null;
                    log_message('debug', '[Webhook] Adresse aus Array extrahiert - Line1: ' . $addressLine1 . ', Zip: ' . $zip . ', City: ' . $city);
                } else {
                    // Fallback: direkt aus $data
                    $addressLine1 = $data['address_line_1'] ?? null;
                    $addressLine2 = $data['address_line_2'] ?? null;
                    $zip = $data['zip'] ?? null;
                    $city = $data['city'] ?? null;
                    log_message('debug', '[Webhook] Adresse direkt aus $data - Line1: ' . $addressLine1 . ', Zip: ' . $zip . ', City: ' . $city);
                }

                // Alle Kontaktdaten für spätere Weiterleitung speichern
                session()->set('group_vorname', $data['names'] ?? $data['vorname'] ?? null);
                session()->set('group_nachname', $data['nachname'] ?? null);
                session()->set('group_email', $data['email'] ?? null);
                session()->set('group_phone', $data['phone'] ?? null);
                session()->set('group_address_line_1', $addressLine1);
                session()->set('group_address_line_2', $addressLine2);
                session()->set('group_zip', $zip);
                session()->set('group_city', $city);
                session()->set('group_erreichbar', $data['erreichbar'] ?? null);
                session()->set('group_uuid', $data['uuid'] ?? $data['uuid_value'] ?? null);
                session()->set('group_additional_service', $data['additional_service'] ?? null);
                session()->set('group_date', time());

                log_message('debug', '[Webhook] Session saved - group_vorname: ' . session()->get('group_vorname'));
                log_message('debug', '[Webhook] Session saved - group_nachname: ' . session()->get('group_nachname'));
                log_message('debug', '[Webhook] Session saved - group_email: ' . session()->get('group_email'));
                log_message('debug', '[Webhook] Session saved - group_phone: ' . session()->get('group_phone'));
                log_message('debug', '[Webhook] Session saved - group_address_line_1: ' . session()->get('group_address_line_1'));
                log_message('debug', '[Webhook] Session saved - group_address_line_2: ' . session()->get('group_address_line_2'));
                log_message('debug', '[Webhook] Session saved - group_zip: ' . session()->get('group_zip'));
                log_message('debug', '[Webhook] Session saved - group_city: ' . session()->get('group_city'));
                log_message('debug', '[Webhook] Session saved - group_erreichbar: ' . session()->get('group_erreichbar'));
                log_message('debug', '[Webhook] Session saved - group_uuid: ' . session()->get('group_uuid'));

                // SICHERHEIT: Kontaktdaten auch in temp_contact_data speichern (für Cross-Domain)
                $contactDataToStore = [
                    'vorname' => $data['names'] ?? $data['vorname'] ?? null,
                    'nachname' => $data['nachname'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address_line_1' => $addressLine1,
                    'address_line_2' => $addressLine2,
                    'zip' => $zip,
                    'city' => $city,
                    'erreichbar' => $data['erreichbar'] ?? null,
                ];

                $uuid = $data['uuid'] ?? $data['uuid_value'] ?? null;
                if ($uuid) {
                    $db = \Config\Database::connect();

                    // Lösche alte Einträge für diese UUID (falls vorhanden)
                    $db->table('temp_contact_data')->where('uuid', $uuid)->delete();

                    // Speichere neue Daten (gültig für 1 Stunde)
                    $db->table('temp_contact_data')->insert([
                        'uuid' => $uuid,
                        'contact_data' => json_encode($contactDataToStore),
                        'created_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                    ]);

                    log_message('debug', '[Webhook] Kontaktdaten in temp_contact_data gespeichert für UUID: ' . $uuid);
                }

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

        // Combo-Logik nur für move und cleaning (wenn aktiviert in SiteConfig)
        if($siteConfig->enableMoveCleaningCombo && isset($data['additional_service']) && $data['additional_service'] == 'Nein') {
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
