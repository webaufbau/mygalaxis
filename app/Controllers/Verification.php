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

        $phone = $request->getPost('phone'); // sollte in prod nicht per form sondern auch wieder über DB gelesen werden, sonst manipulierbar.
        $method = $request->getPost('method');

        if (!$phone) {
            return redirect()->back()->with('error', 'Telefonnummer fehlt.');
        }

        // Prüfe, ob Mobilnummer
        $isMobile = $this->isMobileNumber($phone);

        // Wenn kein Mobile, dann nur Anruf zulassen
        if (!$isMobile && $method !== 'phone') {
            return redirect()->back()->with('error', 'Bei Festnetznummer ist nur Anruf-Verifizierung möglich.');
        }

        if (!$method) {
            return redirect()->back()->with('error', 'Bitte Verifizierungsmethode wählen.');
        }

        // Simuliere den Versand des Verifizierungscodes
        $verificationCode = rand(100000, 999999);
        session()->set('verification_code', $verificationCode);
        session()->set('phone', $phone);
        session()->set('verify_method', $method);

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
            return redirect()->to('/'); // oder Fehlerseite
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
            $builder = $db->table('requests');

            // verified auf 1 setzen für diese uuid
            $builder->where('uuid', $uuid)->update(['verified' => 1, 'verify_type' => $this->request->getPost('method')]);

            session()->remove('verification_code');
            return view('verification_success', ['next_url' => session()->get('next_url') ?? 'https://umzuege.webagentur-forster.ch/danke-umzug/']);
        }

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
