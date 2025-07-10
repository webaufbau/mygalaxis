<?php
namespace App\Controllers;

use App\Models\OfferModel;
use CodeIgniter\Controller;

class Offers extends Controller
{
    public function index()
    {
        $model = new OfferModel();

        // Optional: Filter & Suche via GET-Parameter
        $search = $this->request->getGet('search');
        $filter = $this->request->getGet('filter'); // z.B. Statusfilter

        $builder = $model->builder();

        if ($search) {
            $builder->like('form_name', $search);
            // Hier kannst du weitere Felder durchsuchen, z.B. 'form_fields'
        }

        if ($filter) {
            $builder->where('status', $filter);
        }

        $builder->where('verified', 1);

        $offers = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        return view('offers/index', [
            'offers' => $offers,
            'search' => $search,
            'filter' => $filter,
            'title' => 'Angebote'
        ]);
    }

    public function mine()
    {
        helper('auth');
        $user = auth()->user();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Bitte einloggen, um Ihre Anfragen zu sehen.');
        }

        $bookingModel = new \App\Models\BookingModel();
        $bookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->findAll();

        // Buchung nach offer_id indexieren
        $bookingsByOfferId = [];
        foreach ($bookings as $booking) {
            $bookingsByOfferId[$booking['reference_id']] = $booking;
        }

        $offerIds = array_keys($bookingsByOfferId);

        $offerModel = new \App\Models\OfferModel();
        $offers = [];

        if (!empty($offerIds)) {
            $offers = $offerModel
                ->whereIn('id', $offerIds)
                ->findAll();

            foreach ($offers as &$offer) {
                if (isset($bookingsByOfferId[$offer['id']])) {
                    $booking = $bookingsByOfferId[$offer['id']];
                    $offer['purchased_price'] = abs($booking['amount']);
                    $offer['purchased_at'] = $booking['created_at']; // für Sortierung
                }
            }
            unset($offer);

            // Sortieren nach Kaufdatum (neueste zuerst)
            usort($offers, function ($a, $b) {
                return strtotime($b['purchased_at']) <=> strtotime($a['purchased_at']);
            });

        }

        return view('offers/mine', [
            'offers' => $offers,
            'search' => null,
            'filter' => null,
            'title' => 'Meine gekauften Anfragen',
            'isOwnView' => true,
        ]);
    }

    public function buy($id)
    {
        helper('auth');
        $user = auth()->user();

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer || $offer['status'] !== 'available') {
            return redirect()->to('/offers')->with('error', 'Dieses Angebot kann nicht gekauft werden.');
        }

        // Preis berechnen (Rabatt nach 3 Tagen)
        $created = new \DateTime($offer['created_at']);
        $now = new \DateTime();
        $days = $now->diff($created)->days;
        $price = $offer['price'];
        if ($days > 3) {
            $price = $price / 2;
        }

        // Aktuelles Guthaben prüfen
        $bookingModel = new \App\Models\BookingModel();
        $balance = $bookingModel->getUserBalance($user->id);

        if ($balance >= $price) {
            // Aus Guthaben bezahlen
            $this->finalizePurchase($user, $offer, $price);
            return redirect()->to('/offers/mine')->with('message', 'Anfrage erfolgreich gekauft (per Guthaben)!');
        }

        // Prüfen, ob Kreditkarte vorhanden
        $stripeService = new \App\Libraries\StripeService();
        if ($stripeService->hasCardOnFile($user)) {
            try {
                // Direktzahlung per Stripe versuchen
                $stripeService->charge($user, $price, 'Anfrage #' . $id);

                // Buchung erfassen (nicht über Guthaben, sondern via Stripe)
                $bookingModel->insert([
                    'user_id' => $user->id,
                    'type' => 'offer_purchase',
                    'description' => "Anfrage gekauft: #" . $offer['id'],
                    'reference_id' => $offer['id'],
                    'amount' => -$price,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $this->finalizePurchase($user, $offer, $price);
                return redirect()->to('/offers/mine')->with('message', 'Anfrage erfolgreich gekauft (per Kreditkarte)!');
            } catch (\Exception $e) {
                log_message('error', 'Stripe-Zahlung fehlgeschlagen: ' . $e->getMessage());
            }
        }

        // Keine Zahlungsmöglichkeit
        return redirect()->to('/finance/topup')->with('error', 'Nicht genügend Guthaben. Bitte laden Sie Ihr Konto auf oder hinterlegen Sie eine Kreditkarte.');
    }


    /**
     * Finalisiert den Kaufprozess
     */
    private function finalizePurchase($user, $offer, $price)
    {
        // Anfrage buchen
        $bookingModel = new \App\Models\BookingModel();
        $bookingModel->insert([
            'user_id' => $user->id,
            'type' => 'offer_purchase',
            'description' => "Anfrage gekauft: #" . $offer['id'],
            'reference_id' => $offer['id'],
            'amount' => -$price,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Prüfen, wie viele Firmen bereits gekauft haben
        $bookingModel = new \App\Models\BookingModel();
        $salesCount = $bookingModel
            ->where('type', 'offer_purchase')
            ->where('reference_id', $offer['id'])
            ->countAllResults();

        // Anfrage als verkauft markieren
        if ($salesCount >= 3) {
            $offerModel = new \App\Models\OfferModel();
            $offerModel->update($offer['id'], ['status' => 'sold']);
        }


        // Benachrichtigungen senden
        $mailer = new \App\Libraries\OfferMailer();
        $mailer->sendOfferPurchasedToRequester($offer, (array)$user);
    }

    public function confirm($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $hash = $this->request->getGet('hash');
        $expectedHash = hash_hmac('sha256', $offer['id'] . $offer['email'], $_ENV['app.secret.key'] ?? 'topsecret');

        if (!hash_equals($expectedHash, $hash)) {
            return redirect()->to('/')->with('error', 'Ungültiger Link.');
        }

        // Auftrag bestätigen...
    }



}
