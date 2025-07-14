<?php

namespace App\Services;

use App\Models\OfferModel;
use App\Models\BookingModel;
use App\Libraries\StripeService;
use DateTime;

class OfferPurchaseService
{
    /**
     * @throws \DateMalformedStringException
     */
    public function purchase($user, $offerId, bool $isAuto = false): bool
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
        if ($days > 3) {
            $price = $price / 2;
        }

        $bookingModel = new BookingModel();
        $balance = $bookingModel->getUserBalance($user->id);

        if ($balance >= $price) {
            // Aus Guthaben bezahlen
            $this->finalize($user, $offer, $price, 'wallet');
            return true;
        }

        // Stripe Fallback
        $stripeService = new StripeService();
        if ($stripeService->hasCardOnFile($user)) {
            try {
                $stripeService->charge($user, $price, 'Anfrage #' . $offerId);
                $this->finalize($user, $offer, $price, 'stripe');
                return true;
            } catch (\Exception $e) {
                log_message('error', 'Stripe-Zahlung fehlgeschlagen: ' . $e->getMessage());
            }
        }

        // Keine Zahlungsmöglichkeit
        return false;
    }

    protected function finalize($user, $offer, $price, $source = 'wallet')
    {
        $bookingModel = new BookingModel();

        // Buchung eintragen
        $bookingModel->insert([
            'user_id' => $user->id,
            'type' => 'offer_purchase',
            'description' => "Anfrage gekauft: #" . $offer['id'],
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
        $status = $buyerCount >= 3 ? 'out_of_stock' : 'available';

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
        $mailer = new \App\Libraries\OfferMailer();
        $mailer->sendOfferPurchasedToRequester($offer, (array)$user);
    }

}
