<?php

namespace App\Controllers;

use App\Config\Infobib;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

use Infobip\Configuration;
use Infobip\ApiException;
use Infobip\Api\SmsApi;
use Infobip\Model\CallsSingleBody;
use Infobip\Model\CallsVoice;
use Infobip\Model\SmsRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsMessage;
use Infobip\Model\SmsTextContent;

use Infobip\Api\VoiceApi;
use Infobip\Model\VoiceSingleTtsRequest;

class Verification extends Controller
{
    public function index()
    {
        $maxWaitTime = 5; // Sekunden
        $waited = 0;

        $uuid = session()->get('uuid');

        while (!$uuid = session()->get('uuid')) {
            sleep(1); // 1 Sekunde warten
            $waited++;

            if ($waited >= $maxWaitTime) {
                log_message('debug', 'Verifikation kann nicht gemacht werden uuid fehlt nach 5 Sekunden' .  print_r($_SESSION, true));

                return redirect()->to(session()->get('next_url') ?? 'https://offertenschweiz.ch/dankesseite-umzug/'); // Fehlerseite oder Hinweis
            }
        }

        $db = \Config\Database::connect();
        $builder = $db->table('offers');

        $maxWaitTime = 18; // Maximal 10 Sekunden warten
        $waited = 0;
        $sleepInterval = 1; // Sekunde

        $row = null;

        while ($waited < $maxWaitTime) {
            $row = $builder->where('uuid', $uuid)->orderBy('created_at', 'DESC')->get()->getRow();

            if ($row) {
                break;
            }

            sleep($sleepInterval);
            $waited += $sleepInterval;
        }

        if (!$row) {
            log_message('debug', 'Verifikation kann nicht gemacht werden kein Datensatz mit der UUID '.$uuid.': ' .  print_r($_SESSION, true));
            log_message('debug', 'Abfrage: ' . $builder->db()->getLastQuery());

            return redirect()->to(session()->get('next_url') ?? 'https://offertenschweiz.ch/dankesseite-umzug/')->with('error', 'Keine Anfrage gefunden.');
        }

        // form_fields ist JSON, decode es:
        $fields = json_decode($row->form_fields, true);
        $phone = $fields['phone'] ?? '';

        $isMobile = false;
        if (preg_match('/^(\+41|0)(75|76|77|78|79)[0-9]{7}$/', str_replace(' ', '', $phone))) {
            $isMobile = true;
        }

        return view('verification_form', [
            'phone' => $phone,
            'isMobile' => $isMobile,
        ]);
    }

    public function processing()
    {
        log_message('debug', 'Verifizierung processing: Warte auf Datensatz');
        return view('processing_request');
    }

    public function checkSession()
    {
        $uuid = session()->get('uuid');
        if (!$uuid) {
            log_message('debug', 'Verifizierung checkSession: waiting');
            return $this->response->setJSON(['status' => 'waiting']);
        }

        // Datenbank prüfen
        $db = \Config\Database::connect();
        $row = $db->table('offers')
            ->where('uuid', $uuid)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRow();

        if ($row) {
            log_message('debug', 'Verifizierung checkSession: ok: ' . $uuid);
            return $this->response->setJSON(['status' => 'ok']);
        }

        log_message('debug', 'Verifizierung checkSession: waiting: ' . $uuid);
        return $this->response->setJSON(['status' => 'waiting']);
    }

    public function send()
    {
        $request = service('request');

        $phone = $request->getPost('phone'); // sollte in prod nicht per form sondern auch wieder über DB gelesen werden, sonst manipulierbar.
        $method = $request->getPost('method');

        if (!$phone) {
            log_message('debug', 'Verifizierung gesendet: Verifizierung Telefonnummer fehlt.');
            return redirect()->back()->with('error', 'Telefonnummer fehlt.');
        }

        // Prüfe, ob Mobilnummer
        $isMobile = $this->isMobileNumber($phone);

        // Wenn kein Mobile, dann nur Anruf zulassen
        if (!$isMobile && $method !== 'phone') {
            log_message('debug', 'Verifizierung gesendet: Bei Festnetznummer ist nur Anruf-Verifizierung möglich.');
            return redirect()->back()->with('error', 'Bei Festnetznummer ist nur Anruf-Verifizierung möglich.');
        }

        if (!$method) {
            log_message('debug', 'Verifizierung gesendet: Bitte Verifizierungsmethode wählen.');
            return redirect()->back()->with('error', 'Bitte Verifizierungsmethode wählen.');
        }

        log_message('debug', 'Verifizierung Methode ' . $method);

        // Simuliere den Versand des Verifizierungscodes
        $verificationCode = rand(100000, 999999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

        log_message('debug', 'Verifizierung Code ' . $verificationCode);

        // Infobip Konfiguration
        $infobib_config = new Infobib();
        $host = $infobib_config->api_host;
        $key  = $infobib_config->api_key;
        $configuration = new Configuration(
            host: $host,
            apiKey: $key,
        );

        try {
            if ($method === 'sms') {
                $smsApi = new SmsApi($configuration);

                $message = new SmsMessage(
                    destinations: [new SmsDestination(to: number_format($phone, "", "", ""))], // Kein + in Telefonnummer
                    content: new SmsTextContent(text: "Ihr Verifizierungscode lautet: $verificationCode"),
                    sender: 'InfoSMS' // muss bei Infobip registriert sein ||| GalaxisGroup
                );

                $smsRequest = new SmsRequest(messages: [$message]);

                $response = $smsApi->sendSmsMessages($smsRequest);

                log_message('info', "SMS Verifizierungscode an $phone gesendet, Nachricht-ID: " .
                    ($response->getMessages()[0]->getMessageId() ?? 'unbekannt'));

            } elseif ($method === 'call') {
                $voiceApi = new VoiceApi($configuration);

                $from = 'InfoCall'; // Bei Infobip registrierter Absender

                $voice = new CallsVoice();
                $voice->setGender('female');
                $voice->setName('de-DE');

                $callRequest = new CallsSingleBody(
                    from: $from,
                    to: number_format($phone, "", "", ""), // Kein + in Telefonnummer
                    audioFileUrl: null,
                    language: 'de-CH',
                    text: "Ihr Verifizierungscode lautet $verificationCode",
                    voice: $voice
                );

                $voiceApi->sendSingleVoiceTts($callRequest);

                log_message('info', "TTS Anruf mit Verifizierungscode an $phone gestartet.");
            }

            return redirect()->to('/verification/confirm');

        } catch (ApiException $e) {
            log_message('error', "Infobip API Fehler bei $method an $phone: " . $e->getMessage());
            return redirect()->back()->with('error', 'Fehler beim Versenden des Verifizierungscodes. Bitte später erneut versuchen.');
        }
    }

    public function confirm()
    {
        $verificationCode = session('verification_code');
        if (!$verificationCode || $verificationCode=='') {
            log_message('debug', 'Verifizierung Confirm verificationCode fehlt.');

            return redirect()->to(session()->get('next_url') ?? 'https://offertenschweiz.ch/dankesseite-umzug/'); // oder Fehlerseite
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
            $builder = $db->table('offers');

            // verified auf 1 setzen für diese uuid
            $builder->where('uuid', $uuid)->update(['verified' => 1, 'verify_type' => $this->request->getPost('method')]);

            session()->remove('verification_code');

            log_message('debug', 'Verifizierung Abgeschlossen: gehe weiter zur URL: ' . (session()->get('next_url') ?? 'https://offertenschweiz.ch/dankesseite-umzug/'));
            return view('verification_success', ['next_url' => session()->get('next_url') ?? 'https://offertenschweiz.ch/dankesseite-umzug/']);
        }

        log_message('debug', 'Verifizierung Confirm: Falscher Code. Bitte erneut versuchen.');
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
