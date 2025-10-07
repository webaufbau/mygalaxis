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

        // Alle Buchungen zu diesem Angebot zählen
        $allBookings = $bookingModel
            ->where('type', 'offer_purchase')
            ->where('reference_id', $offer['id'])
            ->findAll();

        $buyerIds = array_column($allBookings, 'user_id');
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

        // Benachrichtigung
        //$mailer = new \App\Libraries\OfferMailer();
        //$mailer->sendOfferPurchasedToRequester($offer, (array)$user);
    }

}
