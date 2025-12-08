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
        $offerTrashModel = new \App\Models\OfferTrashModel();
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

            // Zuerst archivieren in Papierkorb
            $offerType = $offer['type'] ?? 'unknown';
            $archived = $offerTrashModel->archiveOffer($deleteId, $offerType, $user->id, 'Admin deletion');

            if ($archived) {
                // Dann löschen
                $deleted = $offerModel->delete($deleteId, true); // true = permanent delete

                if ($deleted) {
                    log_message('info', sprintf('Offer ID %d erfolgreich gelöscht und archiviert', $deleteId));
                    session()->setFlashdata('success', 'Angebot #' . $deleteId . ' wurde gelöscht und in den Papierkorb verschoben.');
                } else {
                    log_message('error', sprintf('Offer ID %d konnte nicht gelöscht werden (aber archiviert)', $deleteId));
                    session()->setFlashdata('error', 'Fehler beim Löschen von Angebot #' . $deleteId . ' (aber im Papierkorb gesichert).');
                }
            } else {
                log_message('error', sprintf('Offer ID %d konnte nicht archiviert werden - Löschung abgebrochen', $deleteId));
                session()->setFlashdata('error', 'Fehler beim Archivieren von Angebot #' . $deleteId . ' - Löschung abgebrochen.');
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

        // Aggregiere offer_purchases Daten pro Offer
        $offerPurchaseModel = new OfferPurchaseModel();
        $db = \Config\Database::connect();

        // Hole alle relevanten offer_purchases aggregiert nach offer_id und discount_type
        $purchaseStats = $db->query("
            SELECT
                offer_id,
                discount_type,
                COUNT(*) as sales_count,
                SUM(price_paid) as revenue
            FROM offer_purchases
            WHERE status = 'paid'
            GROUP BY offer_id, discount_type
        ")->getResultArray();

        // Erstelle ein Lookup-Array für schnellen Zugriff
        $purchaseLookup = [];
        foreach ($purchaseStats as $stat) {
            $offerId = $stat['offer_id'];
            if (!isset($purchaseLookup[$offerId])) {
                $purchaseLookup[$offerId] = [
                    'total_revenue' => 0,
                    'sales_normal' => 0,
                    'revenue_normal' => 0,
                    'sales_discount_1' => 0,
                    'revenue_discount_1' => 0,
                    'sales_discount_2' => 0,
                    'revenue_discount_2' => 0,
                ];
            }

            $purchaseLookup[$offerId]['total_revenue'] += (float)$stat['revenue'];

            switch ($stat['discount_type']) {
                case 'normal':
                    $purchaseLookup[$offerId]['sales_normal'] = (int)$stat['sales_count'];
                    $purchaseLookup[$offerId]['revenue_normal'] = (float)$stat['revenue'];
                    break;
                case 'discount_1':
                    $purchaseLookup[$offerId]['sales_discount_1'] = (int)$stat['sales_count'];
                    $purchaseLookup[$offerId]['revenue_discount_1'] = (float)$stat['revenue'];
                    break;
                case 'discount_2':
                    $purchaseLookup[$offerId]['sales_discount_2'] = (int)$stat['sales_count'];
                    $purchaseLookup[$offerId]['revenue_discount_2'] = (float)$stat['revenue'];
                    break;
            }
        }

        // Füge die aggregierten Daten zu jedem Offer hinzu
        foreach ($offers as &$offer) {
            $offerId = $offer['id'];
            if (isset($purchaseLookup[$offerId])) {
                $offer['purchase_stats'] = $purchaseLookup[$offerId];
            } else {
                $offer['purchase_stats'] = [
                    'total_revenue' => 0,
                    'sales_normal' => 0,
                    'revenue_normal' => 0,
                    'sales_discount_1' => 0,
                    'revenue_discount_1' => 0,
                    'sales_discount_2' => 0,
                    'revenue_discount_2' => 0,
                ];
            }
        }
        unset($offer); // Reference freigeben

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

                // Generiere dynamischen Titel
                $offer['dynamic_title'] = $this->generateDynamicTitle($offer);

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

    /**
     * Generiert einen dynamischen, übersetzten Titel für ein Angebot
     */
    private function generateDynamicTitle($offer): string
    {
        // Typ übersetzen
        $typeKey = $offer['type'];
        $translatedType = lang('Offers.type.' . $typeKey);

        // Stadt
        $city = $offer['city'] ?? '';

        // Form fields parsen
        $formFields = [];
        if (!empty($offer['form_fields'])) {
            $formFields = json_decode($offer['form_fields'], true) ?? [];
        }

        // Branchenspezifische Titelgenerierung
        $titleParts = [$translatedType];

        if (!empty($city)) {
            // "move" verwendet "von", alle anderen "in"
            $preposition = ($typeKey === 'move' || $typeKey === 'move_cleaning')
                ? lang('Offers.title_from')
                : lang('Offers.title_in');

            $titleParts[] = $preposition;
            $titleParts[] = $city;
        }

        // Zusätzliche Details je nach Branche
        $additionalInfo = $this->extractAdditionalTitleInfo($typeKey, $formFields);
        if (!empty($additionalInfo)) {
            $titleParts[] = $additionalInfo;
        }

        return implode(' ', $titleParts);
    }

    /**
     * Extrahiert branchenspezifische Zusatzinformationen für den Titel
     */
    private function extractAdditionalTitleInfo(string $typeKey, array $formFields): string
    {
        $parts = [];

        switch ($typeKey) {
            case 'move':
            case 'move_cleaning':
                $rooms = $formFields['auszug_zimmer']
                    ?? $formFields['einzug_zimmer']
                    ?? $formFields['zimmer']
                    ?? null;

                if ($rooms) {
                    $parts[] = $rooms . ' ' . lang('Offers.title_rooms');
                }
                break;

            case 'cleaning':
                $rooms = null;

                if (isset($formFields['wohnung_groesse']) && $formFields['wohnung_groesse'] !== 'Andere') {
                    if (preg_match('/^(\d+)-Zimmer$/', $formFields['wohnung_groesse'], $matches)) {
                        $rooms = $matches[1] . '-' . lang('Offers.title_rooms');
                    } else {
                        $rooms = $formFields['wohnung_groesse'];
                    }
                }
                elseif (isset($formFields['komplett_anzahlzimmer'])) {
                    $rooms = $formFields['komplett_anzahlzimmer'] . ' ' . lang('Offers.title_rooms');
                }
                elseif (isset($formFields['zimmer'])) {
                    $rooms = $formFields['zimmer'] . ' ' . lang('Offers.title_rooms');
                }
                elseif (isset($formFields['wohnung_groesse_andere'])) {
                    $rooms = $formFields['wohnung_groesse_andere'] . ' ' . lang('Offers.title_rooms');
                }

                if ($rooms) {
                    $parts[] = $rooms;
                }

                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                }
                break;

            case 'painting':
            case 'painter':
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                } elseif (isset($formFields['neubau']) && $formFields['neubau'] === 'Ja') {
                    $parts[] = lang('Offers.title_neubau');
                }
                break;

            case 'gardening':
            case 'gardener':
                $services = [];
                if (isset($formFields['holz_wpc_dielen']) && $formFields['holz_wpc_dielen'] === 'Ja') {
                    $services[] = lang('Offers.title_gardening_decking');
                }
                if (isset($formFields['teich_arbeiten']) && $formFields['teich_arbeiten'] === 'Ja') {
                    $services[] = lang('Offers.title_gardening_pond');
                }
                if (isset($formFields['hecken_baeume']) && $formFields['hecken_baeume'] === 'Ja') {
                    $services[] = lang('Offers.title_gardening_hedges');
                }
                if (isset($formFields['rasen']) && $formFields['rasen'] === 'Ja') {
                    $services[] = lang('Offers.title_gardening_lawn');
                }

                if (!empty($services)) {
                    $parts[] = implode(', ', $services);
                }
                break;

            case 'plumbing':
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                } elseif (isset($formFields['property_type']) && !empty($formFields['property_type'])) {
                    $parts[] = $formFields['property_type'];
                }
                break;

            case 'electrician':
            case 'flooring':
            case 'heating':
            case 'tiling':
            case 'furniture_assembly':
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                }
                break;
        }

        return !empty($parts) ? '- ' . implode(' - ', $parts) : '';
    }

}
