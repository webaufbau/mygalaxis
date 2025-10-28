<?php

namespace App\Controllers;

use App\Models\OfferPurchaseModel;
use CodeIgniter\Controller;
use App\Models\OfferModel;
use App\Models\UserModel;

class Dashboard extends Controller
{
    public function index() {
        //echo base64_encode('API_367271_95535142:Xr9$Y=bF&1+zqF14M');
        //exit();
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        if ($user->inGroup('admin')) {
            return $this->index_admin();
        } elseif ($user->inGroup('user')) {
            // Prüfen, ob der Firmen-User noch keine Filter gesetzt hat
            $hasFilters =
                !empty($user->filter_categories) ||
                !empty($user->filter_cantons) ||
                !empty($user->filter_regions) ||
                !empty($user->min_rooms) ||
                !empty($user->filter_custom_zip);

            if (!$hasFilters) {
                // Weiterleiten zur Filter-Seite mit Erklärung
                return redirect()->to('/filter')->with('warning', 'Bevor wir Ihnen passende Offerten anzeigen können, stellen Sie bitte die Filter so ein, wie sie für Ihre Dienstleistung zutreffen.');
            }

            return $this->index_user();
        }

    }

    /**
     * Löscht eine Anfrage (nur für Admins)
     */
    public function delete($id = null)
    {
        $user = auth()->user();

        // Prüfe ob User Admin ist
        if (!$user || !$user->inGroup('admin')) {
            session()->setFlashdata('error', 'Keine Berechtigung.');
            return redirect()->to('/dashboard');
        }

        if (!$id) {
            session()->setFlashdata('error', 'Keine ID angegeben.');
            return redirect()->to('/dashboard');
        }

        $offerModel = new \App\Models\OfferModel();
        $deleteId = (int)$id;

        // Check ob das Angebot existiert
        $offer = $offerModel->find($deleteId);
        if ($offer) {
            // Loggen wer gelöscht hat
            log_message('info', sprintf(
                'Offer ID %d wird gelöscht von User ID %d (Username: %s)',
                $deleteId,
                $user->id,
                $user->username
            ));

            // Versuche zu löschen
            $deleted = $offerModel->delete($deleteId, true); // true = permanent delete

            if ($deleted) {
                log_message('info', sprintf('Offer ID %d erfolgreich gelöscht', $deleteId));
                session()->setFlashdata('success', 'Angebot #' . $deleteId . ' wurde erfolgreich gelöscht.');
            } else {
                log_message('error', sprintf('Offer ID %d konnte nicht gelöscht werden', $deleteId));
                session()->setFlashdata('error', 'Fehler beim Löschen von Angebot #' . $deleteId);
            }
        } else {
            log_message('warning', sprintf('Offer ID %d nicht gefunden zum Löschen', $deleteId));
            session()->setFlashdata('error', 'Angebot #' . $deleteId . ' nicht gefunden.');
        }

        return redirect()->to('/dashboard');
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

        // Neue globale Filter
        if ($verified = $request->getGet('verified')) {
            if ($verified === 'yes') {
                $builder->where('offers.verified', 1);
            } elseif ($verified === 'no') {
                $builder->where('offers.verified', 0);
            }
        }

        if ($purchases = $request->getGet('purchases')) {
            if ($purchases === 'yes') {
                $builder->where('offers.buyers >', 0);
            } elseif ($purchases === 'no') {
                $builder->where('(offers.buyers IS NULL OR offers.buyers = 0)');
            }
        }

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

        $categoryOptions = new \Config\CategoryOptions();
        $appConfig = new \Config\App();

        return view('admin/dashboard', [
            'types' => $categoryOptions->categoryTypes,
            'title' => 'Anfragen-Statistik',
            'offers' => $offers,
            'filter_type' => $type,
            'request' => $request->getGet(),
        ]);
    }




    public function index_user()
    {
        /*if (!isset($_SERVER['DDEV_PROJECT'])) {
            exit(); // deaktiviert aktuell
        }*/

        $user = auth()->user();

        $bookingModel = new \App\Models\BookingModel();
        $offerModel = new \App\Models\OfferModel();

        // Gekaufte Angebote via Buchungen
        $allBookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Nur Buchungen behalten, deren Angebote noch existieren
        $bookings = [];
        $purchasedOffers = [];

        foreach ($allBookings as $booking) {
            $offer = $offerModel->find($booking['reference_id']);
            if ($offer) {
                // Angebot existiert noch - Buchung behalten
                $bookings[] = $booking;

                // Offer-Info für Statistik hinzufügen
                $offer['price_paid'] = $booking['paid_amount']; // Verwende paid_amount statt amount
                $offer['purchased_at'] = $booking['created_at']; // Add purchase timestamp
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
