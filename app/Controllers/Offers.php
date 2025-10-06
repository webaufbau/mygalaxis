<?php
namespace App\Controllers;

use App\Models\OfferModel;
use CodeIgniter\Controller;

class Offers extends BaseController
{
    public function index()
    {
        if(auth()->user()->inGroup('admin')) {
            return redirect()->back()->with('error', lang('Offers.errors.admin_view_only'));
        }

        $user = auth()->user();
        $userId = $user->id ?? null;


        // Prüfen, ob der User noch keine Filter gesetzt hat
        $hasFilters =
            !empty($user->filter_categories) ||
            !empty($user->filter_cantons) ||
            !empty($user->filter_regions) ||
            !empty($user->min_rooms) ||
            !empty($user->filter_custom_zip);

        if (!$hasFilters) {
            // Weiterleiten zur Filter-Seite
            return redirect()->to('/filter')->with('warning', lang('Offers.errors.filter_view_only'));
        }


        $userCantons = $user->filter_cantons ?? [];
        $userRegions = $user->filter_regions ?? [];
        $userCategories = $user->filter_categories ?? [];
        $userLanguages = $user->filter_languages ?? [];
        $userAbsences = $user->filter_absences ?? [];
        $userCustomZips = $user->filter_custom_zip ?? '';

        if (is_string($userCantons)) {
            $userCantons = array_filter(array_map('trim', explode(',', $userCantons)));
        }
        if (is_string($userRegions)) {
            $userRegions = array_filter(array_map('trim', explode(',', $userRegions)));
        }
        if (is_string($userCategories)) {
            $userCategories = array_filter(array_map('trim', explode(',', $userCategories)));
        }
        if (is_string($userLanguages)) {
            // JSON decode if JSON, else explode comma
            $decoded = json_decode($userLanguages, true);
            $userLanguages = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $userLanguages)));
        }
        if (is_string($userAbsences)) {
            $decoded = json_decode($userAbsences, true);
            $userAbsences = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $userAbsences)));
        }

        $userCustomZips = array_filter(array_map('trim', explode(',', $userCustomZips)));


        $offerModel = new \App\Models\OfferModel();
        $builder = $offerModel->builder();
        $builder->where('verified', 1);
        // Nur Angebote mit gültigem Preis anzeigen
        $builder->where('price >', 0);

        // PLZ aus Kantonen & Regionen
        $zipcodeService = new \App\Libraries\ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($userCantons, $userRegions, $siteCountry);

        $allZips = array_merge($relevantZips, $userCustomZips);
        $allZips = array_unique($allZips);

        if (!empty($allZips)) {
            $builder->groupStart();
            $builder->whereIn('zip', $allZips);
            $builder->groupEnd();
        }

        // Kategorien: entspricht dem Feld 'type' in Offers (z.B. 'move', 'gardening')
        if (!empty($userCategories)) {
            $builder->groupStart();
            foreach ($userCategories as $category) {
                $builder->orWhere('type', $category);
            }
            $builder->groupEnd();
        }

        // Sprachfilter, wenn Feld 'language' oder ähnliches in offers existiert
        if (!empty($userLanguages)) {
            // Beispiel, wenn 'language' ein CSV-Feld ist, dann:
            foreach ($userLanguages as $lang) {
                $builder->like('language', $lang);
            }
        }

        // Services (filter_absences) - je nach DB-Struktur anpassen
        // Beispiel: wenn 'services' JSON in offers:
        if (!empty($userAbsences)) {
            foreach ($userAbsences as $service) {
                $builder->like('services', $service);
            }
        }

        // Suche & Status (optional)
        $search = $this->request->getGet('search');
        if ($search) {
            $builder->groupStart();
            $builder->like('title', $search);
            $builder->orLike('form_fields', $search);
            $builder->groupEnd();
        }

        $filter = $this->request->getGet('filter');
        if ($filter && $filter !== 'purchased') {
            $builder->where('status', $filter);
        }

        $offers = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        $purchasedOfferIds = [];

        if ($userId) {
            $offerIds = array_column($offers, 'id');
            if (!empty($offerIds)) {
                $bookingModel = new \App\Models\BookingModel();
                $bookings = $bookingModel
                    ->where('user_id', $userId)
                    ->where('type', 'offer_purchase')
                    ->whereIn('reference_id', $offerIds)
                    ->findAll();

                $purchasedOfferIds = array_column($bookings, 'reference_id');
            }
        }

        // Filter für gekaufte Angebote
        if ($filter === 'purchased') {
            $offers = array_filter($offers, function($offer) use ($purchasedOfferIds) {
                return in_array($offer['id'], $purchasedOfferIds);
            });
        }


        return view('offers/index', [
            'offers' => $offers,
            'purchasedOfferIds' => $purchasedOfferIds,
            'search' => $search,
            'filter' => $filter,
            'title' => 'Angebote'
        ]);

    }

    public function show($id)
    {
        helper('auth');
        $user = auth()->user();

        if (!$user) {
            return redirect()->to('/login')->with('error', lang('Offers.errors.login_required'));
        }

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Prüfen ob User das Angebot gekauft hat
        $bookingModel = new \App\Models\BookingModel();
        $booking = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->where('reference_id', $id)
            ->first();

        $isPurchased = !empty($booking);

        if ($isPurchased && $booking) {
            $offer['purchased_price'] = abs($booking['amount']);
            $offer['purchased_at'] = $booking['created_at'];
        }

        return view('offers/show', [
            'offer' => $offer,
            'isPurchased' => $isPurchased,
            'title' => $offer['title']
        ]);
    }

    public function mine()
    {
        helper('auth');
        $user = auth()->user();

        if (!$user) {
            return redirect()->to('/login')->with('error', lang('Offers.errors.login_required'));
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
            'isOwnView' => true,
        ]);
    }

    public function buy($id)
    {
        helper('auth');
        $user = auth()->user();

        // Prüfe ob Angebot gültigen Preis hat
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer || $offer['price'] <= 0) {
            return redirect()->to('/offers')->with('error', lang('Offers.errors.invalid_price'));
        }

        $purchaseService = new \App\Services\OfferPurchaseService();

        $result = $purchaseService->purchase($user, $id);

        if ($result === true) {
            return redirect()->to('/offers/' . $id)->with('message', lang('Offers.messages.purchase_success'));
        }

        if (is_array($result) && !$result['success']) {
            // Betrag in Session speichern und zur Auflade-Seite weiterleiten
            session()->set('topup_amount', $result['missing_amount']);
            session()->set('topup_reason', 'offer_purchase');
            session()->set('topup_offer_id', $id);
            return redirect()->to('/finance/topup-page');
        }

        return redirect()->to('/offers')->with('error', lang('Offers.errors.purchase_failed'));
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
            'description' => lang('Offers.buy.offer_purchased') . ": #" . $offer['id'],
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
        if ($salesCount >= 4) {
            $offerModel = new \App\Models\OfferModel();
            $offerModel->update($offer['id'], ['status' => 'sold']);
        }


        // Benachrichtigungen senden
        //$mailer = new \App\Libraries\OfferMailer();
        //$mailer->sendOfferPurchasedToRequester($offer, (array)$user);
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
            return redirect()->to('/')->with('error', lang('Offers.errors.invalid_link'));
        }

        // Auftrag bestätigen...
    }



}
