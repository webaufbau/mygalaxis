<?php

namespace App\Libraries;

use Config\Infobip as InfobipConfig;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsMessage;
use Infobip\Api\SmsApi;
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

    public function sendSms(string $to, string $text): array
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
            $response_message  = $response->getMessages()[0] ?? null;
            if ($response_message) {
                $statusName  = $response_message->getStatus()?->getName();
                $statusGroup = $response_message->getStatus()?->getGroupName();
                $messageId   = $response_message->getMessageId();

                log_message('info', "SMS gesendet an $to – Status: $statusName – ID: $messageId");

                return [
                    'success'    => in_array($statusName, ['PENDING_ENROUTE', 'DELIVERED_TO_HANDSET']),
                    'status'     => $statusName,
                    'group'      => $statusGroup,
                    'messageId'  => $messageId,
                ];
            }

            // Falls keine Nachricht zurückkommt (sehr selten)
            log_message('warning', "SMS-Versand: Keine Rückmeldung für $to");
            return [
                'success'    => false,
                'status'     => 'NO_RESPONSE',
                'messageId'  => null,
            ];

        } catch (ApiException $apiException) {
            // HANDLE THE EXCEPTION
            log_message('error', "Fehler beim SMS-Versand API-Exception CODE an $to: " . $apiException->getCode());
            log_message('error', "Fehler beim SMS-Versand API-Exception HEADERS an $to: " . print_r($apiException->getResponseHeaders(), true));
            log_message('error', "Fehler beim SMS-Versand API-Exception BODY an $to: " . $apiException->getResponseBody());
            log_message('error', "Fehler beim SMS-Versand API-Exception OBJECT an $to: " . print_r($apiException->getResponseObject(), true));

            return [
                'success'    => false,
                'status'     => 'EXCEPTION',
                'error'      => $apiException->getResponseBody(),
                'messageId'  => null,
            ];

        } catch (\Throwable $e) {
            log_message('error', "Fehler beim SMS-Versand an $to: " . $e->getMessage());
            return [
                'success'    => false,
                'status'     => 'EXCEPTION',
                'error'      => $e->getMessage(),
                'messageId'  => null,
            ];
        }
    }

    public function checkDeliveryStatus(string $messageId): array
    {
        try {
            $response = $this->smsApi->getOutboundSmsMessageDeliveryReports(messageId: $messageId);

            $result = $response->getResults()[0] ?? null;

            if ($result) {
                $status = $result->getStatus();
                return [
                    'success'     => $status->getGroupName() === 'DELIVERED',
                    'status'      => $status->getName(),
                    'description' => $status->getDescription(),
                    'group'       => $status->getGroupName(),
                    'to'          => $result->getTo(),
                    'messageId'   => $result->getMessageId(),
                ];
            }

            return [
                'success' => false,
                'status' => 'NO_RESULT',
                'description' => 'Keine Zustellinformationen gefunden.',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Infobip Delivery Report Fehler: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'ERROR',
                'description' => $e->getMessage(),
            ];
        }
    }

}
