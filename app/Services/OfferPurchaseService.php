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

        // Nicht genug Guthaben - Betrag zurückgeben
        $missingAmount = $price - $balance;
        return [
            'success' => false,
            'missing_amount' => $missingAmount,
            'required_amount' => $price,
            'current_balance' => $balance
        ];
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
        //$mailer = new \App\Libraries\OfferMailer();
        //$mailer->sendOfferPurchasedToRequester($offer, (array)$user);
    }

}
