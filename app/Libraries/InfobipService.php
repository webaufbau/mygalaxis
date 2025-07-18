<?php

namespace App\Libraries;

use Config\Infobip as InfobipConfig;
use Infobip\Configuration;
use Infobip\Api\SmsApi;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsMessage;
use Infobip\Model\SmsRequest;
use Infobip\Model\SmsTextContent;

class InfobipService
{
    protected SmsApi $smsApi;
    protected string $sender;

    public function __construct()
    {
        $config = config(InfobipConfig::class);

        $configuration = new Configuration(
            host: rtrim($config->api_host, '/'),
            apiKey: $config->api_key
        );

        $this->smsApi = new SmsApi($configuration);
        $this->sender = $config->sender;
    }

    public function sendSms(string $to, string $text): bool
    {
        $destination = new SmsDestination(to: $to);
        $content     = new SmsTextContent(text: $text);
        $message     = new SmsMessage(
            destinations: [$destination],
            content: $content,
            sender: $this->sender
        );

        $smsRequest = new SmsRequest(messages: [$message]);

        try {
            $response = $this->smsApi->sendSmsMessages($smsRequest);

            $messageId = $response->getMessages()[0]->getMessageId() ?? 'unbekannt';
            log_message('info', "SMS gesendet an $to, Nachricht-ID: $messageId");
            return true;

        } catch (\Throwable $e) {
            log_message('error', "Fehler beim SMS-Versand an $to: " . $e->getMessage());
            return false;
        }
    }
}
