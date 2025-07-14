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


        // Prüfen ob 'delete' Parameter gesetzt ist und User Admin ist
        $deleteId = $request->getGet('delete');
        $user = auth()->user();

        if ($deleteId && $user && in_array('admin', $user->groups ?? [])) {
            // Sicherstellen, dass $deleteId integer ist
            $deleteId = (int)$deleteId;

            // Optional: Check ob das Angebot existiert
            $offer = $offerModel->find($deleteId);
            if ($offer) {
                // Loggen wer gelöscht hat
                log_message('info', sprintf(
                    'Offer ID %d wurde gelöscht von User ID %d (Username: %s)',
                    $deleteId,
                    $user->id,
                    $user->username
                ));

                $offerModel->delete($deleteId);

                return redirect()->to('/dashboard')->with('success', 'Angebot wurde gelöscht.');
            } else {
                return redirect()->to('/dashboard')->with('error', 'Angebot nicht gefunden.');
            }
        }


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

        $filterOptions = new \App\Config\FilterOptions();

        return view('admin/dashboard', [
            'types' => $filterOptions->types,
            'title' => 'Anfragen-Statistik',
            'offers' => $offers,
            'filter_type' => $type,
            'request' => $request->getGet(),
        ]);
    }




    public function index_user()
    {
        if (!isset($_SERVER['DDEV_PROJECT'])) {
            exit(); // deaktiviert aktuell
        }

        $user = auth()->user();

        $bookingModel = new \App\Models\BookingModel();
        $offerModel = new \App\Models\OfferModel();

        // Gekaufte Angebote via Buchungen
        $bookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // IDs der gekauften Angebote
        $purchasedOfferIds = array_column($bookings, 'reference_id');

        // Gekaufte Angebote mit Offer-Infos
        $purchasedOffers = [];
        foreach ($purchasedOfferIds as $offerId) {
            $offer = $offerModel->find($offerId);
            if ($offer) {
                $offer['price_paid'] = $bookingModel
                    ->where('user_id', $user->id)
                    ->where('reference_id', $offerId)
                    ->where('type', 'offer_purchase')
                    ->orderBy('created_at', 'DESC')
                    ->first()['amount'] ?? 0;
                $purchasedOffers[] = $offer;
            }
        }

        $totalPurchased = count($purchasedOffers);

        // Alle verifizierten Offerten (die man hätte kaufen können)
        $allOffers = $offerModel->where('verified', 1)->findAll();

        $totalMissed = count($allOffers) - $totalPurchased;
        if ($totalMissed < 0) {
            $totalMissed = 0; // Sicherheitskorrektur
        }

        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'bookings' => $bookings,
            'purchasedOffers' => $purchasedOffers,
            'totalSpent' => 0,
            'totalPurchased' => $totalPurchased,
            'totalMissed' => $totalMissed,
        ];

        return view('account/dashboard', $data);
    }


}
