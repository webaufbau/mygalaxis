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

        // Branchen-Filter (Mehrfachauswahl)
        $selectedTypes = $this->request->getGet('types') ?? [];
        if (!empty($selectedTypes) && is_array($selectedTypes)) {
            $builder->groupStart();
            foreach ($selectedTypes as $type) {
                $builder->orWhere('type', $type);
            }
            $builder->groupEnd();
        }

        $filter = $this->request->getGet('filter');

        // Hole alle gekauften Offer-IDs für diesen User
        $purchasedOfferIds = [];
        $bookingsByOfferId = [];
        if ($userId) {
            $bookingModel = new \App\Models\BookingModel();
            $bookings = $bookingModel
                ->where('user_id', $userId)
                ->where('type', 'offer_purchase')
                ->findAll();
            $purchasedOfferIds = array_column($bookings, 'reference_id');

            // Buchungen nach offer_id indexieren für spätere Verwendung
            foreach ($bookings as $booking) {
                $bookingsByOfferId[$booking['reference_id']] = $booking;
            }
        }

        // Filter anwenden
        if ($filter === 'available') {
            // Nur verfügbare (noch nicht gekaufte) Angebote anzeigen
            if (!empty($purchasedOfferIds)) {
                $builder->whereNotIn('id', $purchasedOfferIds);
            }
            $builder->where('status', 'available');
        } elseif ($filter === 'purchased') {
            // Nur gekaufte Angebote anzeigen
            if (!empty($purchasedOfferIds)) {
                $builder->whereIn('id', $purchasedOfferIds);
            } else {
                // Keine gekauften Angebote -> leere Ergebnisse
                $builder->where('1', '0');
            }
        }

        $builder->orderBy('created_at', 'DESC');

        // Pagination
        $perPage = 25;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;

        // Get total count for pagination
        $totalOffers = $builder->countAllResults(false);

        // Get paginated results
        $offers = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Add purchased_at timestamp to purchased offers
        foreach ($offers as &$offer) {
            if (isset($bookingsByOfferId[$offer['id']])) {
                $booking = $bookingsByOfferId[$offer['id']];
                $offer['purchased_at'] = $booking['created_at'];
                $offer['purchased_price'] = $booking['paid_amount'];
            }
        }
        unset($offer);

        // Create pager manually
        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalOffers);

        // Lade nur die Branchen, die der User in seinen Filtern hat
        $categoryOptions = new \Config\CategoryOptions();
        $allCategoryTypes = $categoryOptions->categoryTypes;

        // Filter nur die Branchen, die der User ausgewählt hat
        // und übersetze sie in die aktuelle Sprache
        $userCategoryTypes = [];
        foreach ($userCategories as $categoryKey) {
            if (isset($allCategoryTypes[$categoryKey])) {
                $userCategoryTypes[$categoryKey] = lang('Offers.type.' . $categoryKey);
            }
        }

        // ============================================================
        // Statistiken berechnen
        // ============================================================

        // Zeitraum-Filter aus GET-Parametern
        $fromMonth = $this->request->getGet('from_month');
        $fromYear = $this->request->getGet('from_year');
        $toMonth = $this->request->getGet('to_month');
        $toYear = $this->request->getGet('to_year');

        // Validierung: Von-Datum darf nicht nach Bis-Datum liegen
        $statsError = null;
        if ($fromYear && $toYear) {
            $fromDate = ($fromYear * 100) + ($fromMonth ?: 1);
            $toDate = ($toYear * 100) + ($toMonth ?: 12);
            if ($fromDate > $toDate) {
                $statsError = lang('Offers.stats.error_invalid_period');
                $fromMonth = $fromYear = $toMonth = $toYear = null; // Reset filter
            }
        }

        // Gefilterte Statistiken (nur für Zeitraum)
        $filteredStats = $this->calculateStatistics($userId, $fromMonth, $fromYear, $toMonth, $toYear);

        // Fix-Statistiken (seit Registrierung)
        $totalStats = $this->calculateStatistics($userId, null, null, null, null);

        // Generiere dynamische Titel für Angebote
        foreach ($offers as &$offer) {
            $offer['dynamic_title'] = $this->generateDynamicTitle($offer);
        }

        return view('offers/index', [
            'offers' => $offers,
            'purchasedOfferIds' => $purchasedOfferIds,
            'selectedTypes' => $selectedTypes,
            'categoryTypes' => $userCategoryTypes,
            'filter' => $filter,
            'pager' => $pager,
            'title' => lang('Offers.title'),
            'filteredStats' => $filteredStats,
            'totalStats' => $totalStats,
            'fromMonth' => $fromMonth,
            'fromYear' => $fromYear,
            'toMonth' => $toMonth,
            'toYear' => $toYear,
            'statsError' => $statsError,
        ]);

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
                // Umzug: Zimmeranzahl aus auszug_zimmer oder einzug_zimmer
                $rooms = $formFields['auszug_zimmer']
                    ?? $formFields['einzug_zimmer']
                    ?? $formFields['zimmer']
                    ?? null;

                if ($rooms) {
                    $parts[] = $rooms . ' ' . lang('Offers.title_rooms');
                }
                break;

            case 'cleaning':
                // Reinigung: Zimmeranzahl aus verschiedenen Quellen
                $rooms = null;

                // Versuche aus wohnung_groesse (z.B. "3-Zimmer", "5-Zimmer")
                if (isset($formFields['wohnung_groesse']) && $formFields['wohnung_groesse'] !== 'Andere') {
                    $rooms = $formFields['wohnung_groesse'];
                }
                // Oder aus komplett_anzahlzimmer
                elseif (isset($formFields['komplett_anzahlzimmer'])) {
                    $rooms = $formFields['komplett_anzahlzimmer'] . ' ' . lang('Offers.title_rooms');
                }
                // Oder aus anderen Zimmerfeldnamen
                elseif (isset($formFields['zimmer'])) {
                    $rooms = $formFields['zimmer'] . ' ' . lang('Offers.title_rooms');
                }
                // Oder wohnung_groesse_andere (wenn "Andere" gewählt)
                elseif (isset($formFields['wohnung_groesse_andere'])) {
                    $rooms = $formFields['wohnung_groesse_andere'] . ' ' . lang('Offers.title_rooms');
                }

                if ($rooms) {
                    $parts[] = $rooms;
                }

                // Objektart (z.B. "EFH", "Einfamilienhaus")
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                }
                break;

            case 'painting':
            case 'painter':
                // Malerarbeiten: Objektart oder Neubau-Status
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                } elseif (isset($formFields['neubau']) && $formFields['neubau'] === 'Ja') {
                    $parts[] = lang('Offers.title_neubau');
                }
                break;

            case 'gardening':
            case 'gardener':
                // Gartenpflege: Dienstleistungen
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
                // Sanitär: Objektart
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
                // Andere Branchen: Objektart falls vorhanden
                if (isset($formFields['objektart']) && !empty($formFields['objektart'])) {
                    $parts[] = $formFields['objektart'];
                }
                break;
        }

        // Verbinde mit " - " wenn mehrere Teile vorhanden
        return !empty($parts) ? '- ' . implode(' - ', $parts) : '';
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
            $offer['purchased_price'] = $booking['paid_amount']; // Verwende paid_amount statt amount
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
                    $offer['purchased_price'] = $booking['paid_amount']; // Verwende paid_amount statt amount
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
            // Zahlung aus Guthaben oder automatische Kreditkartenzahlung fehlgeschlagen
            // -> Direktkauf via Saferpay (egal ob bereits eine Zahlungsmethode gespeichert ist)
            return $this->buyDirect($id, $offer);
        }

        return redirect()->to('/offers')->with('error', lang('Offers.errors.purchase_failed'));
    }

    /**
     * Direktkauf via Saferpay (wenn Guthaben nicht ausreicht oder automatische Zahlung fehlschlägt)
     */
    private function buyDirect($offerId, $offer)
    {
        $user = auth()->user();
        $price = $offer['discounted_price'] > 0 ? $offer['discounted_price'] : $offer['price'];
        $amountInCents = (int)($price * 100);
        $refno = 'offer_direct_' . $offerId . '_' . uniqid();

        $successUrl = site_url("offers/buyDirectSuccess?refno=$refno&offer_id=$offerId");
        $failUrl    = site_url("offers/buyDirectFail?offer_id=$offerId");
        $notifyUrl  = site_url("webhook/saferpay/notify");

        try {
            $saferpay = new \App\Services\SaferpayService();
            $response = $saferpay->initTransactionWithAlias($successUrl, $failUrl, $amountInCents, $refno, $notifyUrl);
            return redirect()->to($response['RedirectUrl']);
        } catch (\Exception $e) {
            log_message('error', 'Saferpay-Direktkauf fehlgeschlagen: ' . $e->getMessage());
            return redirect()->to('/offers')->with('error', lang('Offers.errors.payment_failed'));
        }
    }

    /**
     * Success-Handler für Direktkauf via Saferpay
     */
    public function buyDirectSuccess()
    {
        $refno = $this->request->getGet('refno');
        $offerId = $this->request->getGet('offer_id');
        $user = auth()->user();

        if (!$user || !$offerId) {
            return redirect()->to('/offers')->with('error', lang('Offers.errors.purchase_failed'));
        }

        // Token holen
        $saferpay = new \App\Services\SaferpayService();
        $token = $saferpay->getTokenByRefno($refno);

        if (!$token) {
            return redirect()->to('/offers')->with('error', lang('Offers.errors.transaction_not_found'));
        }

        try {
            // Transaktion prüfen
            $response = $saferpay->assertTransaction($token);

            if (isset($response['Transaction']) && $response['Transaction']['Status'] === 'AUTHORIZED') {
                $transactionId = $response['Transaction']['Id'];

                // Capture durchführen
                $captureResponse = $saferpay->captureTransaction($transactionId);
                log_message('info', 'Saferpay Direktkauf Capture erfolgreich: ' . json_encode($captureResponse));

                // Alias speichern (wie bei topupSuccess)
                if (isset($response['RegistrationResult']['Alias']['Id'])) {
                    $aliasId = $response['RegistrationResult']['Alias']['Id'];
                    $aliasLifetime = $response['RegistrationResult']['Alias']['Lifetime'] ?? null;
                    $paymentMeans = $response['PaymentMeans'] ?? [];
                    $card = $paymentMeans['Card'] ?? [];

                    $paymentMethodModel = new \App\Models\UserPaymentMethodModel();
                    $paymentMethodModel->save([
                        'user_id' => $user->id,
                        'payment_method_code' => 'saferpay',
                        'provider_data' => json_encode([
                            'alias_id' => $aliasId,
                            'alias_lifetime' => $aliasLifetime,
                            'card_masked' => $paymentMeans['DisplayText'] ?? null,
                            'card_brand' => $paymentMeans['Brand']['Name'] ?? null,
                            'card_exp_month' => $card['ExpMonth'] ?? null,
                            'card_exp_year' => $card['ExpYear'] ?? null,
                        ]),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    log_message('info', "Alias gespeichert beim Direktkauf für User #{$user->id}");
                }

                // Kauf finalisieren (via OfferPurchaseService)
                $offerModel = new \App\Models\OfferModel();
                $offer = $offerModel->find($offerId);

                if ($offer) {
                    $price = $offer['discounted_price'] > 0 ? $offer['discounted_price'] : $offer['price'];
                    $purchaseService = new \App\Services\OfferPurchaseService();

                    // Zahlungsmethode aus Response extrahieren
                    $paymentMethodName = $response['PaymentMeans']['Brand']['Name'] ?? 'Kreditkarte';

                    // Direkt finalize aufrufen (da Zahlung bereits erfolgt)
                    $reflection = new \ReflectionClass($purchaseService);
                    $finalizeMethod = $reflection->getMethod('finalize');
                    $finalizeMethod->setAccessible(true);
                    $finalizeMethod->invoke($purchaseService, $user, $offer, $price, 'credit_card', false, $paymentMethodName);

                    return redirect()->to('/offers/' . $offerId)->with('message', lang('Offers.messages.purchase_success'));
                }

                return redirect()->to('/offers')->with('error', lang('Offers.errors.offer_not_found'));
            }

            return redirect()->to('/offers')->with('error', lang('Offers.errors.payment_not_authorized'));
        } catch (\Exception $e) {
            log_message('error', 'Direktkauf Success-Handler fehlgeschlagen: ' . $e->getMessage());
            return redirect()->to('/offers')->with('error', lang('Offers.errors.purchase_failed'));
        }
    }

    /**
     * Fail-Handler für Direktkauf via Saferpay
     */
    public function buyDirectFail()
    {
        $offerId = $this->request->getGet('offer_id');
        return redirect()->to('/offers/' . $offerId)->with('error', lang('Offers.errors.payment_cancelled'));
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
        if ($salesCount >= \App\Models\OfferModel::MAX_PURCHASES) {
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

    /**
     * Berechnet Statistiken für gekaufte und nicht gekaufte Anfragen
     *
     * @param int $userId User ID
     * @param string|null $fromMonth Monat von (1-12)
     * @param string|null $fromYear Jahr von
     * @param string|null $toMonth Monat bis (1-12)
     * @param string|null $toYear Jahr bis
     * @return array Statistiken
     */
    private function calculateStatistics($userId, $fromMonth = null, $fromYear = null, $toMonth = null, $toYear = null)
    {
        $bookingModel = new \App\Models\BookingModel();
        $offerModel = new \App\Models\OfferModel();

        // Zeitraum-Filter erstellen
        $fromDate = null;
        $toDate = null;

        // Von-Datum: Wenn Jahr angegeben, nutze Jan 1 oder spezifischen Monat
        if ($fromYear) {
            $month = $fromMonth ?: 1; // Falls kein Monat, nehme Januar
            $fromDate = sprintf('%04d-%02d-01 00:00:00', $fromYear, $month);
        }

        // Bis-Datum: Wenn Jahr angegeben, nutze Dez 31 oder spezifischen Monat
        if ($toYear) {
            $month = $toMonth ?: 12; // Falls kein Monat, nehme Dezember
            $toDate = sprintf('%04d-%02d-%02d 23:59:59', $toYear, $month, cal_days_in_month(CAL_GREGORIAN, $month, $toYear));
        }

        // Gekaufte Anfragen
        $purchasedQuery = $bookingModel->builder();
        $purchasedQuery->where('user_id', $userId);
        $purchasedQuery->where('type', 'offer_purchase');

        if (!empty($fromDate)) {
            $purchasedQuery->where('created_at >=', $fromDate);
        }
        if (!empty($toDate)) {
            $purchasedQuery->where('created_at <=', $toDate);
        }

        $purchasedBookings = $purchasedQuery->get()->getResultArray();
        $purchasedCount = count($purchasedBookings);
        $purchasedTotal = abs(array_sum(array_column($purchasedBookings, 'paid_amount')));
        $purchasedAvg = $purchasedCount > 0 ? $purchasedTotal / $purchasedCount : 0;

        // Nicht gekaufte Anfragen (alle verfügbaren Angebote die der User sehen kann)
        $purchasedOfferIds = array_column($purchasedBookings, 'reference_id');

        // Hier müssen wir die gleiche Logik wie in index() verwenden
        $user = auth()->user();
        $userCantons = $user->filter_cantons ?? [];
        $userRegions = $user->filter_regions ?? [];
        $userCategories = $user->filter_categories ?? [];
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
        $userCustomZips = array_filter(array_map('trim', explode(',', $userCustomZips)));

        $zipcodeService = new \App\Libraries\ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($userCantons, $userRegions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $userCustomZips));

        $notPurchasedQuery = $offerModel->builder();
        $notPurchasedQuery->where('verified', 1);
        $notPurchasedQuery->where('price >', 0);

        if (!empty($purchasedOfferIds)) {
            $notPurchasedQuery->whereNotIn('id', $purchasedOfferIds);
        }

        if (!empty($allZips)) {
            $notPurchasedQuery->whereIn('zip', $allZips);
        }

        if (!empty($userCategories)) {
            $notPurchasedQuery->groupStart();
            foreach ($userCategories as $category) {
                $notPurchasedQuery->orWhere('type', $category);
            }
            $notPurchasedQuery->groupEnd();
        }

        if (!empty($fromDate)) {
            $notPurchasedQuery->where('created_at >=', $fromDate);
        }
        if (!empty($toDate)) {
            $notPurchasedQuery->where('created_at <=', $toDate);
        }

        $notPurchased = $notPurchasedQuery->get()->getResultArray();
        $notPurchasedCount = count($notPurchased);
        $notPurchasedTotal = array_sum(array_column($notPurchased, 'price'));
        $notPurchasedAvg = $notPurchasedCount > 0 ? $notPurchasedTotal / $notPurchasedCount : 0;

        return [
            'purchased' => [
                'count' => $purchasedCount,
                'total' => $purchasedTotal,
                'avg' => $purchasedAvg,
            ],
            'not_purchased' => [
                'count' => $notPurchasedCount,
                'total' => $notPurchasedTotal,
                'avg' => $notPurchasedAvg,
            ],
        ];
    }

}
