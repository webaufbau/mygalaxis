<?php

namespace App\Libraries;

use Twilio\Rest\Client;
use Config\Twilio;

class TwilioService
{
    protected Client $client;
    protected Twilio $config;
    protected bool $testMode;
    protected string $fromSms;
    protected string $fromCaller;

    public function __construct()
    {
        $this->config = config(Twilio::class);
        $this->testMode = $this->config->testMode;

        $sid   = $this->testMode ? $this->config->testAccountSid : $this->config->accountSid;
        $token = $this->testMode ? $this->config->testAuthToken  : $this->config->authToken;

        $this->fromSms    = $this->testMode ? $this->config->testSmsNumber   : $this->config->smsNumber;
        $this->fromCaller = $this->testMode ? $this->config->testCallerId    : $this->config->callerId;

        $this->client = new Client($sid, $token);
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            if ($this->testMode) {
                log_message('info', "[TWILIO TESTMODE] SMS an $to: $message");
                return true;
            }

            $sms = $this->client->messages->create($to, [
                'from' => $this->fromSms,
                'body' => $message
            ]);

            // Prüfung, ob SMS erfolgreich gesendet wurde
            if (isset($sms->sid)) {
                log_message('info', "SMS an $to erfolgreich gesendet. SID: {$sms->sid}");
                return true;
            } else {
                log_message('error', "Twilio SMS fehlgeschlagen an $to: Keine SID erhalten.");
                return false;
            }
        } catch (\Throwable $e) {
            log_message('error', "Twilio SMS Fehler an $to: " . $e->getMessage());
            return false;
        }
    }

    public function sendCall(string $to, string $message, bool $escape = true): bool
    {
        try {
            if ($this->testMode) {
                log_message('info', "[TWILIO TESTMODE] Anruf an $to: $message");
                return true;
            }

            // Nur escapen, wenn explizit gewünscht
            $finalMessage = $escape ? htmlspecialchars($message) : $message;

            $twiml = '
<Response>
    <Say language="de-DE" voice="Polly.Vicki">
        <prosody rate="slow">
            ' . $finalMessage . '
        </prosody>
    </Say>
</Response>
';

            $call = $this->client->calls->create($to, $this->fromCaller, [
                'twiml' => $twiml,
            ]);

            return in_array($call->status, ['queued', 'initiated', 'ringing']);
        } catch (\Throwable $e) {
            log_message('error', "Twilio Call Fehler an $to: " . $e->getMessage());
            return false;
        }
    }


    public function sendCallCode(string $to, string $message, int $code): bool
    {
        $digits = str_split($code);
        $ssmlDigits = '';
        foreach ($digits as $digit) {
            $ssmlDigits .= $digit . '<break time="300ms"/>';
        }

        // Wiederhole den Code zweimal mit etwas Pause dazwischen
        $messageText = $message . ' ' . $ssmlDigits . '<break time="400ms"/> Ich wiederhole: ' . $ssmlDigits;

        return $this->sendCall($to, $messageText, false);
    }




}
