<?php

namespace App\Services;

use App\Models\OfferModel;
use App\Models\BookingModel;
use App\Services\SaferpayService;
use DateTime;

class OfferPurchaseService
{
    /**
     * @throws \DateMalformedStringException
     */
    public function purchase($user, $offerId, bool $isAuto = false): bool|array
    {
        $offerModel = new OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer || $offer['status'] !== 'available') {
            return false;
        }

        // Rabatt nach 3 Tagen
        $created = new DateTime($offer['created_at']);
        $now = new DateTime();
        $days = $now->diff($created)->days;
        $price = $offer['price'];
        if ($offer['discounted_price'] > 0) {
            $price = $offer['discounted_price'];
        }


        $bookingModel = new BookingModel();
        $balance = $bookingModel->getUserBalance($user->id);

        if ($balance >= $price) {
            // Aus Guthaben bezahlen
            $this->finalize($user, $offer, $price, 'wallet');
            return true;
        }

        // Nicht genug Guthaben
        $missingAmount = $price - $balance;

        // Bei Auto-Kauf: Versuche automatisch von Kreditkarte abzubuchen
        if ($isAuto) {
            $charged = $this->tryAutoChargeFromCard($user, $missingAmount, $offer['id']);

            if ($charged) {
                // Erfolgreich von Karte abgebucht - jetzt kaufen
                $this->finalize($user, $offer, $price, 'auto_charge');
                return true;
            }

            // Auto-Charge fehlgeschlagen
            log_message('warning', "Auto-Charge fehlgeschlagen für User #{$user->id}, Offer #{$offerId}. Fehlender Betrag: {$missingAmount}");
            return false;
        }

        // Manueller Kauf ohne genug Guthaben - Betrag zurückgeben
        return [
            'success' => false,
            'missing_amount' => $missingAmount,
            'required_amount' => $price,
            'current_balance' => $balance
        ];
    }

    /**
     * Versucht automatisch den fehlenden Betrag von der hinterlegten Kreditkarte abzubuchen
     *
     * @param object $user Der Benutzer
     * @param float $amount Der Betrag der abgebucht werden soll
     * @param int $offerId Die Offer-ID (für Referenz)
     * @return bool True wenn erfolgreich abgebucht, false sonst
     */
    protected function tryAutoChargeFromCard($user, float $amount, int $offerId): bool
    {
        try {
            // Hole gespeicherte Zahlungsmethode
            $paymentMethodModel = new \App\Models\UserPaymentMethodModel();
            $paymentMethod = $paymentMethodModel
                ->where('user_id', $user->id)
                ->where('payment_method_code', 'saferpay')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$paymentMethod) {
                log_message('info', "Keine Zahlungsmethode für User #{$user->id} gefunden");
                return false;
            }

            // Hole Alias-ID
            $providerData = json_decode($paymentMethod['provider_data'], true);
            $aliasId = $providerData['alias_id'] ?? null;

            if (!$aliasId) {
                log_message('warning', "Alias-ID fehlt für User #{$user->id}");
                return false;
            }

            // Saferpay Charge durchführen
            $saferpayService = new SaferpayService();
            $amountInCents = (int)($amount * 100);
            $refno = 'auto_charge_' . $offerId . '_' . uniqid();

            $response = $saferpayService->authorizeWithAlias($aliasId, $amountInCents, $refno);

            // Prüfe ob erfolgreich
            if (!isset($response['Transaction']) || $response['Transaction']['Status'] !== 'AUTHORIZED') {
                log_message('error', "Saferpay Authorization fehlgeschlagen für User #{$user->id}: " . json_encode($response));
                return false;
            }

            // Guthaben aufladen
            $bookingModel = new BookingModel();
            $bookingModel->insert([
                'user_id' => $user->id,
                'type' => 'topup',
                'description' => 'Automatische Aufladung für Offertenkauf #' . $offerId,
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s'),
                'meta' => json_encode([
                    'source' => 'auto_charge',
                    'offer_id' => $offerId,
                    'transaction_id' => $response['Transaction']['Id'] ?? null,
                    'refno' => $refno
                ])
            ]);

            log_message('info', "Auto-Charge erfolgreich: User #{$user->id}, Betrag: {$amount}, Offer: #{$offerId}");
            return true;

        } catch (\Exception $e) {
            log_message('error', "Auto-Charge Exception für User #{$user->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function finalize($user, $offer, $price, $source = 'wallet')
    {
        $bookingModel = new BookingModel();

        // Buchung eintragen
        $bookingModel->insert([
            'user_id' => $user->id,
            'type' => 'offer_purchase',
            'description' => lang('Offers.buy.offer_purchased') . " #" . $offer['id'],
            'reference_id' => $offer['id'],
            'amount' => -$price,
            'created_at' => date('Y-m-d H:i:s'),
            'meta' => json_encode(['source' => $source]),
        ]);

        // Berechne discount_type basierend auf dem Rabatt-Prozentsatz
        $discountType = 'normal';
        $originalPrice = (float)$offer['price'];

        if ($originalPrice > 0 && $price < $originalPrice) {
            $discountPercent = (($originalPrice - $price) / $originalPrice) * 100;

            if ($discountPercent > 20) {
                $discountType = 'discount_2'; // > 20%
            } else {
                $discountType = 'discount_1'; // <= 20%
            }
        }

        // Eintrag in offer_purchases erstellen (wichtig für "gekauft"-Status!)
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
        $offerPurchaseModel->insert([
            'user_id' => $user->id,
            'offer_id' => $offer['id'],
            'price' => $offer['price'],
            'price_paid' => $price,
            'discount_type' => $discountType,
            'payment_method' => $source,
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Alle PAID Purchases zu diesem Angebot zählen (aus offer_purchases Tabelle!)
        $allPurchases = $offerPurchaseModel
            ->where('offer_id', $offer['id'])
            ->where('status', 'paid')
            ->findAll();

        $buyerIds = array_column($allPurchases, 'user_id');
        $buyerCount = count($buyerIds);
        $status = $buyerCount >= 4 ? 'out_of_stock' : 'available';

        // Angebot aktualisieren
        $offerModel = new OfferModel();
        $offerModel->update($offer['id'], [
            'buyers' => $buyerCount,
            'bought_by' => json_encode($buyerIds),
            'status' => $status,
            'purchased_by' => $user->id,
            'purchased_at' => date('Y-m-d H:i:s'),
        ]);

        // E-Mails SOFORT nach dem Kauf versenden
        $this->sendPurchaseNotifications($user, $offer, $bookingModel);
    }

    /**
     * Sendet E-Mail-Benachrichtigungen nach dem Kauf
     */
    protected function sendPurchaseNotifications($user, array $offer, BookingModel $bookingModel)
    {
        try {
            // Lade SiteConfig basierend auf User-Platform
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

            // Sprache setzen
            $language = $user->language ?? $offer['language'] ?? 'de';
            service('language')->setLocale($language);

            // 1. E-Mail an Firma (Käufer) - mit Kundendaten
            $this->sendEmailToCompany($user, $offer, $siteConfig);

            // 2. E-Mail an Kunde - mit Firmendaten
            $this->sendEmailToCustomer($user, $offer, $siteConfig);

            // Booking als "Benachrichtigung versendet" markieren
            $booking = $bookingModel
                ->where('user_id', $user->id)
                ->where('type', 'offer_purchase')
                ->where('reference_id', $offer['id'])
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($booking) {
                $bookingModel->update($booking['id'], [
                    'offer_notification_sent_at' => date('Y-m-d H:i:s'),
                ]);
            }

            log_message('info', "Purchase notifications sent for Offer #{$offer['id']} to User #{$user->id}");
        } catch (\Exception $e) {
            log_message('error', "Failed to send purchase notifications for Offer #{$offer['id']}: " . $e->getMessage());
        }
    }

    /**
     * Sendet E-Mail an die Firma (Käufer)
     */
    protected function sendEmailToCompany($company, array $offer, $siteConfig)
    {
        $customerData = [
            'firstname' => $offer['firstname'] ?? '',
            'lastname'  => $offer['lastname'] ?? '',
            'email'     => $offer['email'] ?? '',
            'phone'     => $offer['phone'] ?? '',
        ];

        $company_backend_offer_link = rtrim($siteConfig->backendUrl, '/') . '/offers/mine#detailsview-' . $offer['id'];

        $data = [
            'siteConfig' => $siteConfig,
            'kunde'      => $customerData,
            'firma'      => $company,
            'offer'      => $offer,
            'company_backend_offer_link' => $company_backend_offer_link,
        ];

        $subject = lang('Email.offerPurchasedCompanySubject', [$offer['title']]);
        $message = view('emails/offer_purchase_to_company', $data);

        $this->sendEmail($company->email, $subject, $message, $siteConfig);
    }

    /**
     * Sendet E-Mail an den Kunden
     */
    protected function sendEmailToCustomer($company, array $offer, $siteConfig)
    {
        $customerData = [
            'firstname' => $offer['firstname'] ?? '',
            'lastname'  => $offer['lastname'] ?? '',
            'email'     => $offer['email'] ?? '',
            'phone'     => $offer['phone'] ?? '',
        ];

        // Access Hash generieren falls noch nicht vorhanden
        if (empty($offer['access_hash'])) {
            $accessHash = bin2hex(random_bytes(16));
            $offerModel = new OfferModel();
            $offerModel->update($offer['id'], ['access_hash' => $accessHash]);
            $offer['access_hash'] = $accessHash;
        }

        $interessentenLink = rtrim($siteConfig->backendUrl, '/') . '/offer/interested/' . $offer['access_hash'];

        $data = [
            'siteConfig'        => $siteConfig,
            'kunde'             => $customerData,
            'firma'             => $company,
            'offer'             => $offer,
            'interessentenLink' => $interessentenLink,
        ];

        $subject = lang('Email.offerPurchasedSubject', [$offer['title']]);
        $message = view('emails/offer_purchase_to_customer', $data);

        $originalEmail = $customerData['email'];

        // Testmodus-Prüfung
        if ($siteConfig->testMode) {
            $emailTo = $siteConfig->testEmail;
            $subject = 'TEST EMAIL – NICHT AN ECHTEN BENUTZER! (eigentlich an: ' . $originalEmail . ') – ' . $subject;
        } else {
            $emailTo = $originalEmail;
        }

        $this->sendEmail($emailTo, $subject, $message, $siteConfig);
    }

    /**
     * Hilfsmethode zum E-Mail-Versand
     */
    protected function sendEmail(string $to, string $subject, string $message, $siteConfig): bool
    {
        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'      => $subject,
            'content'    => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Failed to send email to ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

}
