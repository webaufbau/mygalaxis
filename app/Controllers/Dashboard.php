<?php

namespace App\Controllers;

use App\Models\OfferPurchaseModel;
use CodeIgniter\Controller;
use App\Models\OfferModel;
use App\Models\UserModel;

class Dashboard extends Controller
{
    public function index() {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        if ($user->inGroup('admin')) {
            return $this->index_admin();
        } elseif ($user->inGroup('user')) {
            return $this->index_user();
        }

    }

    public function index_admin()
    {
        $request = service('request');
        $offerModel = new \App\Models\OfferModel();
        $builder = $offerModel->builder()->select('offers.*');

        // Basisfilter
        if ($from = $request->getGet('from')) {
            $builder->where('offers.created_at >=', $from);
        }
        if ($to = $request->getGet('to')) {
            $builder->where('offers.created_at <=', $to);
        }
        if ($type = $request->getGet('type')) {
            $builder->where('offers.type', $type);
        }

        $type = $request->getGet('type');

        // Typ-spezifische Filter
        switch ($type) {
            case 'move':
                $builder->join('offers_move', 'offers_move.offer_id = offers.id');
                if ($roomSize = $request->getGet('room_size')) {
                    $builder->where('offers_move.apartment_size', $roomSize);
                }
                if ($moveDate = $request->getGet('move_date')) {
                    $builder->where('offers_move.move_date', $moveDate);
                }
                break;

            case 'cleaning':
                $builder->join('offers_cleaning', 'offers_cleaning.offer_id = offers.id');
                if ($cleaningType = $request->getGet('cleaning_type')) {
                    $builder->where('offers_cleaning.cleaning_type', $cleaningType);
                }
                break;

            case 'painting':
                $builder->join('offers_painting', 'offers_painting.offer_id = offers.id');
                if ($area = $request->getGet('area')) {
                    $builder->where('offers_painting.area_m2', $area);
                }
                break;

            case 'gardening':
                $builder->join('offers_gardening', 'offers_gardening.offer_id = offers.id');
                if ($workType = $request->getGet('work_type')) {
                    $builder->where('offers_gardening.work_type', $workType);
                }
                if ($area = $request->getGet('area_m2')) {
                    $builder->where('offers_gardening.area_m2', $area);
                }
                break;

            case 'plumbing':
                $builder->join('offers_plumbing', 'offers_plumbing.offer_id = offers.id');
                if ($urgency = $request->getGet('urgency_level')) {
                    $builder->where('offers_plumbing.urgency_level', $urgency);
                }
                if ($room = $request->getGet('affected_rooms')) {
                    $builder->like('offers_plumbing.affected_rooms', $room);
                }
                break;

            case 'furniture_assembly':
                // Noch keine eigene Tabelle, aber Filterung über form_fields oder Zusatzlogik denkbar
                if ($pieces = $request->getGet('pieces')) {
                    $builder->like('form_fields', '"pieces":' . (int)$pieces);
                }
                break;

            default:
                // kein Join
                break;
        }

        $offers = $builder->orderBy('offers.created_at', 'desc')->get()->getResultArray();



        return view('admin/dashboard', [
            'title' => 'Anfragen-Statistik',
            'offers' => $offers,
            'filter_type' => $type,
            'request' => $request->getGet(),
        ]);
    }




    public function index_user() {
        exit(); // deaktiviert aktuell

        $user = auth()->user();

        $purchasedOfferModel = new OfferPurchaseModel();
        $offerModel = new OfferModel();

// Gekaufte Angebote
        $purchasedOffers = $purchasedOfferModel
            ->where('user_id', $user->id)
            ->join('offers', 'offers.id = offer_purchases.offer_id')
            ->select('offer_purchases.*, offers.form_name, offers.status as offer_status')
            ->orderBy('created_at', 'DESC')
            ->findAll();

// Statistik berechnen
        $totalPurchased = count($purchasedOffers);
        $totalSpent = array_sum(array_column($purchasedOffers, 'price_paid'));

// Alle Angebote, die potenziell gekauft werden konnten
        $allOffers = (new OfferModel())
            ->orderBy('created_at', 'DESC')
            ->findAll();

// IDs der gekauften Angebote durch diesen User
        $purchasedOfferIds = array_column($purchasedOffers, 'offer_id');

// Verpasste = Angebote, die der User hätte kaufen können, aber nicht gekauft hat
        $missedOffers = array_filter($allOffers, function ($offer) use ($user, $purchasedOfferIds) {
            // Wenn bereits gekauft → nicht verpasst
            if (in_array($offer['id'], $purchasedOfferIds)) return false;

            // Wenn 'buyers' leer oder enthält User-ID → Angebot war für User sichtbar
            $buyers = json_decode($offer['buyers'], true);
            if (is_array($buyers) && in_array($user->id, $buyers)) {
                return true;
            }

            return false;
        });

        $totalMissed = count($missedOffers);
        $totalMissedCHF = array_sum(array_column($missedOffers, 'price'));

        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'purchasedOffers' => $purchasedOffers,
            'totalPurchased' => $totalPurchased,
            'totalSpent' => $totalSpent,
            'totalMissed' => $totalMissed,
            'totalMissedCHF' => $totalMissedCHF,
        ];


        return view('account/dashboard', $data);
    }
}
