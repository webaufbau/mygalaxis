<?php
namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\PaymentMethodModel;
use App\Models\UserPaymentMethodModel;
use App\Services\DatatransService;
use App\Services\SaferpayService;

class Finance extends BaseController
{
    protected $datatrans;
    protected SaferpayService $saferpay;

    public function __construct()
    {
        $this->datatrans = new DatatransService();
        $this->saferpay = new \App\Services\SaferpayService();
    }

    public function index()
    {
        $user = auth()->user();

        $bookingModel = new BookingModel();
        $paymentMethodModel = new PaymentMethodModel();
        $userPaymentMethodModel = new UserPaymentMethodModel();
        $monthlyInvoiceModel = new \App\Models\MonthlyInvoiceModel();

        $year = $this->request->getGet('year');
        $month = $this->request->getGet('month');

        // 1) Gekaufte Anfragen (offer_purchase und refund_purchase für Stornierungen)
        $purchasesBuilder = $bookingModel->where('user_id', $user->id)
            ->whereIn('type', ['offer_purchase', 'refund_purchase']);

        if ($year) {
            $purchasesBuilder->where('YEAR(created_at)', $year);
        }
        if ($month) {
            $purchasesBuilder->where('MONTH(created_at)', $month);
        }

        $purchases = $purchasesBuilder->orderBy('created_at', 'DESC')->findAll();

        // 2) Gutschriften (topup, refund für Rückerstattungen, admin_credit)
        $creditsBuilder = $bookingModel->where('user_id', $user->id)
            ->whereIn('type', ['topup', 'refund', 'admin_credit']);

        if ($year) {
            $creditsBuilder->where('YEAR(created_at)', $year);
        }
        if ($month) {
            $creditsBuilder->where('MONTH(created_at)', $month);
        }

        $credits = $creditsBuilder->orderBy('created_at', 'ASC')->findAll();

        // Berechne laufenden Saldo für Gutschriften (chronologisch aufsteigend)
        $runningBalance = 0;
        $creditsWithBalance = [];
        foreach ($credits as $credit) {
            $runningBalance += $credit['amount'];
            $credit['running_balance'] = $runningBalance;
            $creditsWithBalance[] = $credit;
        }

        // Danach wieder absteigend sortieren für die Anzeige
        $credits = array_reverse($creditsWithBalance);

        // 3) Monatsrechnungen dynamisch berechnen (statt aus monthly_invoices Tabelle)
        $monthlyInvoices = $this->generateMonthlyInvoicesForUser($user, $year, $month);

        // Alte $bookings Variable für Kompatibilität (wird nicht mehr verwendet)
        $bookings = [];
        $pager = null;

        $years = $bookingModel->select("YEAR(created_at) as year")
            ->where('user_id', $user->id)
            ->groupBy('year')
            ->orderBy('year', 'DESC')
            ->findAll();

        $balance = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->first()['amount'] ?? 0;

        // Guthaben-Aufschlüsselung berechnen
        // Einzahlungen = alle positiven Beträge (topup, refund_purchase, admin_credit, etc.)
        $topups = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->where('amount >', 0)
            ->first()['amount'] ?? 0;

        // Ausgaben = alle negativen Beträge (offer_purchase, refund, etc.)
        $expenses = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->where('amount <', 0)
            ->first()['amount'] ?? 0;

        // Lade gespeicherte Zahlungsmethoden des Users (sortiert: Primary zuerst)
        $userPaymentMethods = $userPaymentMethodModel->getUserCards($user->id);
        $hasSavedCard = !empty($userPaymentMethods);

        // Auto-Fix: Wenn Saferpay-Karten vorhanden aber keine als Primary markiert, setze älteste als Primary
        $saferpayCards = array_filter($userPaymentMethods, fn($c) => $c['payment_method_code'] === 'saferpay');
        if (!empty($saferpayCards)) {
            $hasPrimary = false;
            foreach ($saferpayCards as $card) {
                if ($card['is_primary'] == 1) {
                    $hasPrimary = true;
                    break;
                }
            }

            if (!$hasPrimary) {
                // Setze die älteste Karte als Primary
                $oldestCard = array_values($saferpayCards)[0]; // bereits nach created_at sortiert
                $userPaymentMethodModel->update($oldestCard['id'], ['is_primary' => 1]);
                log_message('info', "Auto-fixed: Set card #{$oldestCard['id']} as primary for user #{$user->id}");

                // Reload nach Update
                $userPaymentMethods = $userPaymentMethodModel->getUserCards($user->id);
            }
        }

        // Hole Primary und Secondary Karte
        $primaryCard = $userPaymentMethodModel->getPrimaryCard($user->id);
        $secondaryCard = $userPaymentMethodModel->getSecondaryCard($user->id);

        // Extrahiere Zahlungsmittel-Details für Anzeige (Legacy für alte Views)
        $cardBrand = $primaryCard['card_brand'] ?? null;
        $cardMasked = $primaryCard['card_last4'] ?? null;

        // Falls kein Filter gesetzt, Standardwerte verwenden
        $currentYear = $year ?: date('Y');
        $currentMonth = $month ?: ''; // leer bedeutet "Alle Monate"

        $monthlyTurnover = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->where('MONTH(created_at)', $currentMonth)
            ->where('YEAR(created_at)', $currentYear)
            ->first()['amount'] ?? 0;

        // Weiterempfehlungs-Daten
        $referralModel = new \App\Models\ReferralModel();

        // Generiere affiliate_code falls noch nicht vorhanden
        if (empty($user->affiliate_code)) {
            $affiliateCode = $this->generateAffiliateCode();

            // Direkt über Query Builder updaten (Shield UserModel unterstützt kein normales update())
            $db = \Config\Database::connect();
            $db->table('users')->where('id', $user->id)->update(['affiliate_code' => $affiliateCode]);

            $user->affiliate_code = $affiliateCode;
        }

        $userReferrals = $referralModel->getReferralsByUser($user->id);
        $referralStats = $referralModel->getUserStats($user->id);
        $affiliateCode = $user->affiliate_code;
        $affiliateLink = site_url('register?ref=' . $affiliateCode);

        return view('account/finance', [
            'title' => 'Finanzen',
            'user' => $user,
            'bookings' => $bookings, // Veraltet, für Kompatibilität
            'pager' => $pager,
            'purchases' => $purchases, // NEU: Nur Käufe
            'credits' => $credits, // NEU: Nur Gutschriften mit Saldo
            'monthlyInvoices' => $monthlyInvoices, // NEU: Monatsrechnungen
            'balance' => $balance,
            'topups' => $topups,
            'expenses' => $expenses,
            'years' => $years,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'monthlyTurnover' => $monthlyTurnover,
            'userPaymentMethods' => $userPaymentMethods,
            'hasSavedCard' => $hasSavedCard,
            'cardBrand' => $cardBrand,
            'userReferrals' => $userReferrals,
            'referralStats' => $referralStats,
            'affiliateCode' => $affiliateCode,
            'affiliateLink' => $affiliateLink,
            'cardMasked' => $cardMasked,
        ]);
    }


    public function topupPage()
    {
        $user = auth()->user();
        $bookingModel = new BookingModel();

        // Hole Daten aus Session
        $missingAmount = session()->get('topup_amount') ?? 20;
        $reason = session()->get('topup_reason');
        $offerId = session()->get('topup_offer_id');

        // Berechne aktuelles Guthaben
        $currentBalance = $bookingModel->getUserBalance($user->id);

        // Falls aus Offer-Kauf kommend, berechne Required Amount
        $requiredAmount = $missingAmount;
        if ($reason === 'offer_purchase' && $offerId) {
            $offerModel = new \App\Models\OfferModel();
            $offer = $offerModel->find($offerId);
            if ($offer) {
                $requiredAmount = $offer['discounted_price'] > 0 ? $offer['discounted_price'] : $offer['price'];
            }
        }

        return view('finance/topup_page', [
            'title' => lang('Finance.topupTitle'),
            'missingAmount' => $missingAmount,
            'requiredAmount' => $requiredAmount,
            'currentBalance' => $currentBalance,
        ]);
    }

    public function topup()
    {
        $user = auth()->user();
        $amountInChf = floatval($this->request->getPost('amount') ?? 20);

        // Hole aktuelles Guthaben
        $bookingModel = new BookingModel();
        $currentBalance = $bookingModel->getUserBalance($user->id);

        // Validierung: Gesamtguthaben darf 3000 CHF nicht überschreiten
        if (($currentBalance + $amountInChf) > 3000) {
            $maxAllowed = 3000 - $currentBalance;
            return redirect()->back()
                ->with('error', sprintf(lang('Finance.errorMaximumBalanceExceeded'), number_format($maxAllowed, 2, '.', '\'')))
                ->withInput();
        }

        $amountInCents = (int)($amountInChf * 100); // CHF → Rappen

        // Versuche direkt von gespeicherter Kreditkarte abzubuchen
        $charged = $this->tryChargeTopupFromCard($user, $amountInChf);

        if ($charged) {
            // Erfolgreich von Karte abgebucht - direkt zur Finance-Seite
            return redirect()->to('/finance')->with('message', lang('Finance.messageTopupSuccess'));
        }

        // Keine gespeicherte Zahlungsmethode oder Abbuchung fehlgeschlagen
        // -> Gehe zu Saferpay für manuelle Zahlung
        $refno = uniqid('topup_');

        $successUrl = site_url("finance/topupSuccess?refno=$refno");
        $failUrl    = site_url("finance/topupFail");
        $notifyUrl  = site_url("webhook/saferpay/notify"); // Server-to-Server Benachrichtigung

        try {
            $response = $this->saferpay->initTransactionWithAlias($successUrl, $failUrl, $amountInCents, $refno, $notifyUrl);
            return redirect()->to($response['RedirectUrl']);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            log_message('error', 'Saferpay-Zahlung fehlgeschlagen: ' . $e->getMessage());

            // Prüfe, ob es ein VALIDATION_FAILED wegen Adresse ist
            if (str_contains($errorMessage, 'VALIDATION_FAILED') &&
                (str_contains($errorMessage, 'BillingAddress.Street') ||
                    str_contains($errorMessage, 'BillingAddress.Zip') ||
                    str_contains($errorMessage, 'BillingAddress.City'))) {

                // Fehler-Message für den Nutzer setzen (Session-Flash)
                session()->setFlashdata('error', lang('Finance.errorIncompleteAddress'));

                // Weiterleitung zur Profilseite
                return redirect()->to('/profile');
            }

            return $this->response->setStatusCode(500)
                ->setBody(lang('Finance.errorPaymentFailed') . ': ' . $errorMessage);
        }
    }

    /**
     * Versucht Guthaben-Aufladung direkt von der Kreditkarte abzubuchen
     *
     * @param object $user Der Benutzer
     * @param float $amount Der Betrag der abgebucht werden soll (in CHF)
     * @return bool True wenn erfolgreich abgebucht, false sonst
     */
    protected function tryChargeTopupFromCard($user, float $amount): bool
    {
        try {
            log_message('info', "[TOPUP AUTO] Versuche automatische Abbuchung für User #{$user->id}, Betrag: CHF {$amount}");

            // Hole beste verfügbare Zahlungsmethode (Primary mit Fallback auf Secondary)
            $paymentMethodModel = new UserPaymentMethodModel();
            $paymentMethod = $paymentMethodModel->getBestAvailableCard($user->id);

            if (!$paymentMethod) {
                log_message('warning', "[TOPUP AUTO] Keine gültige gespeicherte Zahlungsmethode für User #{$user->id} - Weiterleitung zu Saferpay");
                return false;
            }

            // Hole Alias-ID aus provider_data JSON
            $providerData = json_decode($paymentMethod['provider_data'], true);
            $aliasId = $providerData['alias_id'] ?? null;
            $cardMasked = $providerData['card_masked'] ?? 'unbekannt';
            $cardBrand = $paymentMethod['card_brand'] ?? 'Kreditkarte';
            $cardLast4 = $paymentMethod['card_last4'] ?? '';
            $isPrimary = $paymentMethod['is_primary'] == 1;
            $platform = $paymentMethod['platform'] ?? 'unbekannt';

            log_message('info', "[TOPUP AUTO] Gefundene Zahlungsmethode für User #{$user->id}: " . ($isPrimary ? 'PRIMARY' : 'SECONDARY') . " Karte - {$cardBrand} ••{$cardLast4}, Alias {$aliasId}, Platform: {$platform}");

            if (!$aliasId) {
                log_message('error', "[TOPUP AUTO] Alias-ID fehlt in provider_data für User #{$user->id} - Weiterleitung zu Saferpay");
                return false;
            }
            $amountInCents = (int)($amount * 100);

            // Saferpay Service
            $saferpay = new SaferpayService();

            // Transaction initialisieren MIT Alias (ohne Redirect)
            $refno = 'topup_auto_' . uniqid();

            log_message('info', "[TOPUP AUTO] Starte authorizeWithAlias für User #{$user->id}, Alias: {$aliasId}, Betrag: {$amountInCents} Rappen, Refno: {$refno}");

            $transactionResponse = $saferpay->authorizeWithAlias(
                $aliasId,
                $amountInCents,
                $refno,
                $user
            );

            log_message('info', "[TOPUP AUTO] authorizeWithAlias Response für User #{$user->id}: " . json_encode($transactionResponse));

            // Prüfe ob erfolgreich
            if (!isset($transactionResponse['Transaction']) || $transactionResponse['Transaction']['Status'] !== 'AUTHORIZED') {
                log_message('error', "[TOPUP AUTO] Authorization fehlgeschlagen für User #{$user->id}, Status: " . ($transactionResponse['Transaction']['Status'] ?? 'unbekannt') . " - Weiterleitung zu Saferpay. Full Response: " . json_encode($transactionResponse));
                return false;
            }

            $transactionId = $transactionResponse['Transaction']['Id'];

            // Hole Zahlungsmethode (VISA, Mastercard, TWINT, etc.)
            $paymentMethodName = $transactionResponse['PaymentMeans']['Brand']['Name'] ?? 'Kreditkarte';

            // Transaktion capturen (Geld tatsächlich abbuchen)
            log_message('info', "[TOPUP AUTO] Starte Capture für User #{$user->id}, Transaction ID: {$transactionId}");
            $captureResponse = $saferpay->captureTransaction($transactionId);

            log_message('info', "[TOPUP AUTO] Capture Response für User #{$user->id}: " . json_encode($captureResponse));

            if (!isset($captureResponse['Status']) || $captureResponse['Status'] !== 'CAPTURED') {
                log_message('error', "[TOPUP AUTO] Capture fehlgeschlagen für User #{$user->id}, Transaction #{$transactionId}, Status: " . ($captureResponse['Status'] ?? 'unbekannt'));
                return false;
            }

            // Guthaben gutschreiben
            $bookingModel = new BookingModel();
            $bookingModel->insert([
                'user_id' => $user->id,
                'type' => 'topup',
                'description' => lang('Finance.topupDescription') . " - " . number_format($amount, 2, '.', '') . " CHF per " . $paymentMethodName . " bezahlt",
                'amount' => $amount,
                'paid_amount' => 0.00, // Topups haben kein paid_amount (nur für Käufe relevant)
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', "[TOPUP AUTO] ✓ Erfolgreich abgeschlossen für User #{$user->id}, Betrag: CHF {$amount}, Alias: {$aliasId}");
            return true;

        } catch (\Exception $e) {
            log_message('error', "[TOPUP AUTO] ✗ Exception für User #{$user->id}: " . $e->getMessage() . "\nStacktrace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function topupSuccess()
    {
        $refno = $this->request->getGet('refno');
        $user = auth()->user();

        // 1. Token anhand refno holen (aus DB oder Speicher)
        $token = $this->saferpay->getTokenByRefno($refno);  // Du brauchst diese Methode, um den Token zu laden

        if (!$token) {
            return redirect()->to('/finance/topupFail')->with('error', lang('Finance.messageTransactionNotFound'));
        }

        try {
            $saferpay = new \App\Services\SaferpayService();
            $response = $saferpay->assertTransaction($token);

            // 2. Prüfen, ob die Transaktion autorisiert ist
            if (isset($response['Transaction']) && $response['Transaction']['Status'] === 'AUTHORIZED') {
                $amount = $response['Transaction']['Amount']['Value']; // in Rappen
                $currency = $response['Transaction']['Amount']['CurrencyCode'];
                $transactionId = $response['Transaction']['Id'];

                // 2.1 WICHTIG: Transaktion verbuchen (Capture)
                // Dies zieht das Geld tatsächlich von der Karte ab
                $captureId = null;
                try {
                    $captureResponse = $saferpay->captureTransaction($transactionId);
                    log_message('info', 'Saferpay Capture erfolgreich: ' . json_encode($captureResponse));

                    // Status auf CAPTURED aktualisieren
                    $captureStatus = $captureResponse['Status'] ?? 'CAPTURED';
                    // CaptureId für spätere Refunds speichern
                    $captureId = $captureResponse['CaptureId'] ?? null;
                } catch (\Exception $captureError) {
                    log_message('error', 'Saferpay Capture fehlgeschlagen: ' . $captureError->getMessage());
                    // Weiter mit AUTHORIZED Status, aber loggen
                    $captureStatus = 'AUTHORIZED';
                }

                // 3. Guthaben gutschreiben (eigene Logik)
                // Hole Zahlungsmethode (VISA, Mastercard, TWINT, etc.)
                $paymentMethodName = $response['PaymentMeans']['Brand']['Name'] ?? null;

                // Wenn keine Brand Name vorhanden, nutze AcquirerName als Fallback
                if (!$paymentMethodName) {
                    $paymentMethodName = $response['Transaction']['AcquirerName'] ?? lang('Finance.onlinePayment');
                }

                $bookingModel = new BookingModel();
                $amountInChf = $amount / 100;
                $booking_id = $bookingModel->insert([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'description' => lang('Finance.topupDescription') . " - " . number_format($amountInChf, 2, '.', '') . " CHF per " . $paymentMethodName . " bezahlt",
                    'amount' => $amountInChf,
                    'paid_amount' => 0.00, // Topups haben kein paid_amount (nur für Käufe relevant)
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // 4. Alias sichern, falls vorhanden
                // Der Alias ist in RegistrationResult.Alias, nicht in Transaction.PaymentMeans.Alias
                log_message('info', 'Saferpay Response - Full Response: ' . json_encode($response));

                if (isset($response['RegistrationResult']['Alias']['Id'])) {
                    $aliasId = $response['RegistrationResult']['Alias']['Id'];
                    $aliasLifetime = $response['RegistrationResult']['Alias']['Lifetime'] ?? null;

                    log_message('info', "Saferpay Alias gefunden: $aliasId (Lifetime: $aliasLifetime Tage) für User #{$user->id}");

                    try {
                        // Prüfe ob dieser Alias bereits gespeichert ist
                        $paymentMethodModel = new \App\Models\UserPaymentMethodModel();
                        $existingAlias = $paymentMethodModel
                            ->where('user_id', $user->id)
                            ->where('payment_method_code', 'saferpay')
                            ->like('provider_data', $aliasId)
                            ->first();

                        if ($existingAlias) {
                            log_message('info', "Saferpay Alias $aliasId bereits gespeichert für User #{$user->id}, wird nicht erneut hinzugefügt");
                        } else {
                            // Speichere auch PaymentMeans für bessere Anzeige
                            $paymentMeans = $response['PaymentMeans'] ?? [];
                            $card = $paymentMeans['Card'] ?? [];

                            // Hole Platform aus .env oder bestimme sie anhand der Domain
                            $platform = env('app.platform', null);
                            if (!$platform) {
                                // Fallback: Bestimme Platform anhand der aktuellen Domain
                                $request = service('request');
                                $host = $request->getServer('HTTP_HOST');
                                if (str_contains($host, 'offertenheld')) {
                                    $platform = 'my_offertenheld_ch';
                                } elseif (str_contains($host, 'renovo24')) {
                                    $platform = 'my_renovo24_ch';
                                } else {
                                    $platform = 'my_offertenschweiz_ch';
                                }
                            }

                            // Extrahiere Kartendetails
                            $cardBrand = $paymentMeans['Brand']['Name'] ?? null;
                            $cardExpMonth = $card['ExpMonth'] ?? null;
                            $cardExpYear = $card['ExpYear'] ?? null;
                            $cardMasked = $paymentMeans['DisplayText'] ?? null;

                            // Last 4 Ziffern extrahieren aus DisplayText (z.B. "9000 xxxx xxxx 0006" -> "0006")
                            $cardLast4 = null;
                            if ($cardMasked && preg_match('/(\d{4})\s*$/', $cardMasked, $matches)) {
                                $cardLast4 = $matches[1];
                            }

                            // Expiry formatieren als MM/YYYY
                            $cardExpiry = null;
                            if ($cardExpMonth && $cardExpYear) {
                                $cardExpiry = sprintf('%02d/%04d', $cardExpMonth, $cardExpYear);
                            }

                            // Prüfe ob dies die erste Karte des Users ist -> dann als Primary setzen
                            $existingCards = $paymentMethodModel->where('user_id', $user->id)->findAll();
                            $isPrimary = count($existingCards) === 0 ? 1 : 0;

                            $paymentMethodModel->saveCard([
                                'user_id' => $user->id,
                                'payment_method_code' => 'saferpay',
                                'is_primary' => $isPrimary,
                                'card_last4' => $cardLast4,
                                'card_brand' => $cardBrand,
                                'card_expiry' => $cardExpiry,
                                'platform' => $platform,
                                'provider_data' => json_encode([
                                    'alias_id' => $aliasId,
                                    'alias_lifetime' => $aliasLifetime,
                                    'card_masked' => $cardMasked,
                                    'card_brand' => $cardBrand,
                                    'card_exp_month' => $cardExpMonth,
                                    'card_exp_year' => $cardExpYear,
                                ]),
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                            log_message('info', "Saferpay Alias $aliasId erfolgreich gespeichert für User #{$user->id} auf Platform: {$platform}");
                        }
                    } catch (\Exception $aliasError) {
                        // Fehler beim Speichern des Alias loggen, aber nicht die gesamte Zahlung abbrechen
                        log_message('error', "Fehler beim Speichern des Saferpay Alias für User #{$user->id}: " . $aliasError->getMessage());
                    }
                } else {
                    log_message('warning', "Kein Saferpay Alias in Response gefunden für User #{$user->id}");
                }

                // 5. Transaktion updaten
                if (isset($response['Transaction'])) {
                    $transaction = $response['Transaction'];

                    $transaction_data = [
                        'transaction_id' => $transaction['Id'] ?? 0,
                        'capture_id'     => $captureId, // Für spätere Refunds
                        'status'         => $captureStatus, // CAPTURED statt AUTHORIZED
                        'amount'         => $transaction['Amount']['Value'] ?? '',
                        'currency'       => $transaction['Amount']['CurrencyCode'] ?? '',
                    ];

                    // Alle weiteren Daten als JSON
                    $extra_data = $transaction;
                    unset($extra_data['Id'], $extra_data['Status'], $extra_data['Amount']); // diese sind bereits einzeln gespeichert

                    $transaction_data['transaction_data'] = json_encode($extra_data);

                    // Transaktion speichern
                    $this->saferpay->updateTransaction($token, $transaction_data);
                }




                return redirect()->to('/finance')->with('message', lang('Finance.messagePaymentSuccess'));
            }

            return redirect()->to('/finance/topupFail')->with('error', lang('Finance.errorPaymentNotAuthorized'));
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Loggen
            log_message('error', 'Saferpay API Fehler: ' . $errorMessage);

            // Benutzerfreundliche Meldung mit genauer Erklärung
            if (strpos($errorMessage, 'AUTHORIZATION_AMOUNT_EXCEEDED') !== false) {
                $userMessage = lang('Finance.errorAmountExceeded');
            } else {
                $userMessage = lang('Finance.errorPaymentCheck');
            }

            // Weiterleitung mit Flash-Message (wenn dein Framework das unterstützt)
            return redirect()->to('/finance/topupFail')->with('error', $userMessage);
        }
    }


    public function topupFail()
    {
        return view('finance/topup_fail'); // oder redirect mit Fehlermeldung
    }


    /**
     * Zahlung mit gespeichertem Token ausführen
     */
    public function chargeAlias()
    {
        $alias = 'AAABcH0Bq92s3kgAESIAAbGj5NIsAHWC'; // Aus Datenbank laden!
        $amount = (int)(floatval($this->request->getPost('amount')) * 100);
        $refno = uniqid('charge_');

        try {
            $response = $this->datatrans->authorizeWithAlias($alias, $amount, $refno, 12, 2025); // expiryMonth/Year ggf. aus DB holen
            return "Alias-Zahlung erfolgreich!";
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setBody("Alias-Zahlung fehlgeschlagen: " . $e->getMessage());
        }
    }

    public function userPaymentMethods()
    {
        $userId = auth()->user()->id;
        $model = new UserPaymentMethodModel();
        $methods = $model->where('user_id', $userId)->findAll();


        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->where('active', 1)->findAll();


        return view('finance/user_payment_methods', ['methods' => $methods,
            'paymentMethods' => $paymentMethods,]);
    }

    public function addUserPaymentMethod()
    {
        $userId = auth()->user()->id;
        $model = new UserPaymentMethodModel();

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'user_id' => $userId,
                'payment_method_code' => $this->request->getPost('payment_method_code'),
                'provider_data' => json_encode($this->request->getPost('provider_data')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $model->insert($data);
            return redirect()->to('/finance/userpaymentmethods')->with('message', lang('Finance.messagePaymentMethodSaved'));
        }

        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->where('active', 1)->findAll();

        return view('finance/add_user_payment_method', ['paymentMethods' => $paymentMethods]);
    }

    public function startAddPaymentMethod()
    {
        $user = auth()->user();

        $payrexx = new \App\Libraries\PayrexxService();

        $successUrl = site_url('finance/paymentSuccess');
        $cancelUrl  = site_url('finance/paymentCancel');

        $response = $payrexx->createTokenCheckout($user, $successUrl, $cancelUrl);

        if (is_array($response) && !empty($response['data']['link'])) {
            return redirect()->to($response['data']['link']);
        } elseif (filter_var($response, FILTER_VALIDATE_URL)) {
            return redirect()->to($response);
        }

        return redirect()->back()->with('error', lang('Finance.errorPaymentPageNotCreated'));
    }

    public function paymentSuccess()
    {
        $user = auth()->user();
        $reference = $this->request->getGet('reference');

        $payrexx = new \App\Libraries\PayrexxService();
        $response = $payrexx->request('Transaction', ['referenceId' => $reference]);

        if (!isset($response['data'][0]['token'])) {
            return redirect()->to('/finance/userpaymentmethods')->with('error', lang('Finance.errorNoTokenReceived'));
        }

        $token = $response['data'][0]['token'];

        // Speichern (verschlüsselt)
        $model = new \App\Models\UserPaymentMethodModel();
        $model->saveEncryptedToken($user->id, 'creditcard', $token);

        return redirect()->to('/finance/userpaymentmethods')->with('message', lang('Finance.messagePaymentMethodSaved'));
    }

    public function deleteUserPaymentMethod($id)
    {
        $model = new UserPaymentMethodModel();
        $method = $model->find($id);

        if ($method && $method['user_id'] == auth()->user()->id) {
            $model->delete($id);
            return redirect()->to('/finance/userpaymentmethods')->with('message', lang('Finance.messagePaymentMethodSaved'));
        }

        return redirect()->back()->with('error', lang('Finance.errorNotFoundOrDenied'));
    }

    /**
     * Zahlungsmittel hinterlegen/ändern - OHNE Guthaben aufzuladen
     * Verwendet Saferpay Payment Page mit 1 CHF Autorisierung (kein Capture!)
     */
    public function registerPaymentMethod()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // Hole gewählten Type (card oder twint)
        $type = $this->request->getGet('type') ?? 'card';

        // Hole replace Parameter (falls User eine Karte ersetzen möchte)
        $replaceCardId = $this->request->getGet('replace') ?? null;

        try {
            // Bei Payment Page wird der Token NICHT in der URL ersetzt
            // Stattdessen speichern wir ihn in der Session
            $successUrl = base_url('finance/register-payment-method/success');
            $failUrl = base_url('finance/register-payment-method/fail');

            $response = $this->saferpay->insertAliasOnly($successUrl, $failUrl, $type);

            // Token + replace ID in Session speichern für späteren Assert
            session()->set('alias_insert_token', $response['Token']);
            if ($replaceCardId) {
                session()->set('replace_card_id', $replaceCardId);
            }

            log_message('info', 'Alias Insert Token gespeichert in Session für User #' . $user->id . ': ' . $response['Token']);

            // Weiterleitung zu Saferpay
            return redirect()->to($response['RedirectUrl']);

        } catch (\Exception $e) {
            log_message('error', 'Alias Insert fehlgeschlagen für User #' . $user->id . ': ' . $e->getMessage());
            return redirect()->to('/finance')->with('error', 'Zahlungsmittel konnte nicht registriert werden: ' . $e->getMessage());
        }
    }

    /**
     * Callback nach erfolgreicher Zahlungsmittel-Registrierung
     */
    public function registerPaymentMethodSuccess()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        // Saferpay ersetzt {TOKEN} in der ReturnUrl - hole aus GET Parameter
        $token = $this->request->getGet('token');

        // Falls nicht in URL, versuche aus Session (Fallback)
        if (!$token) {
            $token = session()->get('alias_insert_token');
        }

        // Debug logging
        log_message('info', 'Alias Registration Success Callback - User #' . $user->id . ', Token aus URL: ' . ($this->request->getGet('token') ?? 'null') . ', Token aus Session: ' . (session()->get('alias_insert_token') ?? 'null'));
        log_message('info', 'Verwendeter Token: ' . ($token ?? 'null'));
        log_message('info', 'Alle GET Parameter: ' . json_encode($this->request->getGet()));

        if (!$token) {
            log_message('error', 'Kein Token für Alias Assert gefunden - User #' . $user->id);
            return redirect()->to('/finance')->with('error', lang('Finance.errorPaymentMethodRegistrationNoToken'));
        }

        try {
            // Assert Transaction (Payment Page) - hole die Karteninformationen + Alias
            // Versuche zuerst mit RetryIndicator = 0
            try {
                $response = $this->saferpay->assertTransaction($token, 0);
                log_message('info', 'Payment Page Assert erfolgreich (Retry=0) für Alias Registration - User #' . $user->id . ': ' . json_encode($response));
            } catch (\Exception $e) {
                // Falls fehlgeschlagen, versuche mit RetryIndicator = 1
                log_message('warning', 'Payment Page Assert fehlgeschlagen mit Retry=0, versuche Retry=1 für User #' . $user->id . ': ' . $e->getMessage());
                $response = $this->saferpay->assertTransaction($token, 1);
                log_message('info', 'Payment Page Assert erfolgreich (Retry=1) für Alias Registration - User #' . $user->id . ': ' . json_encode($response));
            }

            // Extrahiere Alias-Informationen (aus Payment Page Assert)
            $registrationResult = $response['RegistrationResult'] ?? null;

            if (!$registrationResult || !isset($registrationResult['Alias']['Id'])) {
                throw new \Exception('Keine Alias-ID in Response gefunden');
            }

            $alias = $registrationResult['Alias'];
            $aliasId = $alias['Id'];
            $aliasLifetime = $alias['Lifetime'] ?? null;

            // Extrahiere Karten-Details
            $paymentMeans = $response['PaymentMeans'] ?? [];
            $card = $paymentMeans['Card'] ?? [];
            $brand = $paymentMeans['Brand'] ?? [];

            // WICHTIG: Wir machen KEIN Capture! Die Autorisierung verfällt automatisch.
            // Es wird KEINE Zahlung durchgeführt - nur der Alias wird gespeichert.

            $paymentMethodModel = new UserPaymentMethodModel();

            // Extrahiere Kartendetails ZUERST (vor dem Löschen/Prüfen)
            $cardBrand = $brand['Name'] ?? null;
            $cardExpMonth = $card['ExpMonth'] ?? null;
            $cardExpYear = $card['ExpYear'] ?? null;
            $cardMasked = $paymentMeans['DisplayText'] ?? null;

            // Last 4 Ziffern extrahieren
            $cardLast4 = null;
            if ($cardMasked && preg_match('/(\d{4})\s*$/', $cardMasked, $matches)) {
                $cardLast4 = $matches[1];
            }

            // Expiry formatieren als MM/YYYY
            $cardExpiry = null;
            if ($cardExpMonth && $cardExpYear) {
                $cardExpiry = sprintf('%02d/%04d', $cardExpMonth, $cardExpYear);
            }

            // Prüfe wie viele Saferpay-Karten bereits vorhanden sind
            $existingCards = $paymentMethodModel
                ->where('user_id', $user->id)
                ->where('payment_method_code', 'saferpay')
                ->findAll();

            // DUPLIKAT-PRÜFUNG: Prüfe ob diese Karte (Brand + Last4 + ExpYear) bereits existiert
            foreach ($existingCards as $existingCard) {
                $existingLast4 = $existingCard['card_last4'];
                $existingExpiry = $existingCard['card_expiry'];
                $existingBrand = $existingCard['card_brand'];

                // Falls Daten in provider_data gespeichert sind
                if (empty($existingLast4) || empty($existingExpiry) || empty($existingBrand)) {
                    $providerData = json_decode($existingCard['provider_data'], true);
                    if (empty($existingLast4) && !empty($providerData['card_masked'])) {
                        if (preg_match('/(\d{4})\s*$/', $providerData['card_masked'], $m)) {
                            $existingLast4 = $m[1];
                        }
                    }
                    if (empty($existingExpiry) && isset($providerData['card_exp_year'])) {
                        $existingExpiry = sprintf('%02d/%04d', $providerData['card_exp_month'], $providerData['card_exp_year']);
                    }
                    if (empty($existingBrand) && !empty($providerData['card_brand'])) {
                        $existingBrand = $providerData['card_brand'];
                    }
                }

                // Vergleiche Brand, Last4 und Expiry (alle drei müssen übereinstimmen)
                if ($existingBrand === $cardBrand && $existingLast4 === $cardLast4 && $existingExpiry === $cardExpiry) {
                    log_message('warning', 'Duplikat erkannt: Karte ' . $cardBrand . ' ' . $cardLast4 . ' (' . $cardExpiry . ') existiert bereits für User #' . $user->id);
                    return redirect()->to('/finance')->with('error', lang('Finance.errorDuplicateCard'));
                }
            }

            // REPLACE-LOGIK: Prüfe ob User eine Karte ersetzen möchte
            $replaceCardId = session()->get('replace_card_id');
            $wasPrimary = false;

            if ($replaceCardId) {
                // Finde die zu ersetzende Karte
                $cardToReplace = null;
                foreach ($existingCards as $card) {
                    if ($card['id'] == $replaceCardId && $card['user_id'] == $user->id) {
                        $cardToReplace = $card;
                        $wasPrimary = ($card['is_primary'] == 1);
                        break;
                    }
                }

                if ($cardToReplace) {
                    $paymentMethodModel->delete($replaceCardId);
                    log_message('info', 'Karte (ID: ' . $replaceCardId . ') ersetzt für User #' . $user->id);

                    // Aktualisiere existingCards nach Löschung
                    $existingCards = array_filter($existingCards, fn($c) => $c['id'] != $replaceCardId);
                }

                // Lösche replace_card_id aus Session
                session()->remove('replace_card_id');
            }

            // Normale Logik: Wenn bereits 2 Karten vorhanden, lösche älteste Secondary
            if (count($existingCards) >= 2) {
                // Finde die älteste Secondary-Karte zum Löschen
                $cardToDelete = null;
                foreach ($existingCards as $card) {
                    if ($card['is_primary'] == 0) {
                        if (!$cardToDelete || $card['created_at'] < $cardToDelete['created_at']) {
                            $cardToDelete = $card;
                        }
                    }
                }

                // Wenn alle Primary sind (sollte nicht passieren), nimm die älteste
                if (!$cardToDelete) {
                    $cardToDelete = $existingCards[0];
                    foreach ($existingCards as $card) {
                        if ($card['created_at'] < $cardToDelete['created_at']) {
                            $cardToDelete = $card;
                        }
                    }
                }

                $paymentMethodModel->delete($cardToDelete['id']);
                log_message('info', 'Älteste Karte (ID: ' . $cardToDelete['id'] . ') gelöscht für User #' . $user->id . ' (Limit: 2 Karten)');

                // Aktualisiere existingCards nach Löschung
                $existingCards = array_filter($existingCards, fn($c) => $c['id'] != $cardToDelete['id']);
            }

            // Speichere neue Zahlungsmethode
            $platform = $user->platform ?? 'mygalaxis';

            // Bestimme is_primary: Wenn Replace und war Primary ODER erste Karte
            $isPrimary = ($wasPrimary || count($existingCards) === 0) ? 1 : 0;

            $paymentMethodModel->saveCard([
                'user_id' => $user->id,
                'payment_method_code' => 'saferpay',
                'is_primary' => $isPrimary,
                'card_last4' => $cardLast4,
                'card_brand' => $cardBrand,
                'card_expiry' => $cardExpiry,
                'platform' => $platform,
                'provider_data' => json_encode([
                    'alias_id' => $aliasId,
                    'alias_lifetime' => $aliasLifetime,
                    'card_masked' => $cardMasked,
                    'card_brand' => $cardBrand,
                    'card_exp_month' => $cardExpMonth,
                    'card_exp_year' => $cardExpYear,
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', 'Neue Zahlungsmethode gespeichert für User #' . $user->id . ', Alias: ' . $aliasId);

            // Token aus Session entfernen
            session()->remove('alias_insert_token');

            return redirect()->to('/finance')->with('success', lang('Finance.messagePaymentMethodRegistered'));

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            log_message('error', 'Alias Assert fehlgeschlagen für User #' . $user->id . ': ' . $errorMessage);

            // Prüfe auf spezifische Fehlertypen
            if (str_contains($errorMessage, '3DS_AUTHENTICATION_FAILED') || str_contains($errorMessage, '3D-Secure authentication failed')) {
                return redirect()->to('/finance')->with('error', lang('Finance.errorPaymentMethodAuthFailed'));
            }

            // Generische benutzerfreundliche Fehlermeldung
            return redirect()->to('/finance')->with('error', lang('Finance.errorPaymentMethodRegistration'));
        }
    }

    /**
     * Callback bei fehlgeschlagener Zahlungsmittel-Registrierung
     */
    public function registerPaymentMethodFail()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        log_message('warning', 'Zahlungsmittel-Registrierung abgebrochen für User #' . $user->id);

        // Token aus Session entfernen
        session()->remove('alias_insert_token');

        return redirect()->to('/finance')->with('error', 'Zahlungsmittel-Registrierung wurde abgebrochen.');
    }

    public function pdf()
    {
        $user = auth()->user();
        $year = $this->request->getGet('year');
        $month = $this->request->getGet('month');

        $bookingModel = new BookingModel();
        $builder = $bookingModel->where('user_id', $user->id);

        if ($year) {
            $builder->where('YEAR(created_at)', $year);
        }
        if ($month) {
            $builder->where('MONTH(created_at)', $month);
        }

        $bookings = $builder->orderBy('created_at', 'ASC')->findAll(); // Neueste unten

        // HTML für PDF generieren
        $html = view('account/pdf_finance', [
            'bookings' => $bookings,
            'year' => $year,
            'month' => $month
        ]);

        // Bootstrap 5 CSS lokal laden (z.B. im public/css-Verzeichnis)
        $bootstrapCss = file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        //$bootstrapCss = file_get_contents(ROOTPATH . 'public/css/bootstrap.min.css');

        // mPDF initialisieren mit besserer Schrift
        $mpdf = new \Mpdf\Mpdf([
            'default_font' => 'helvetica',
        ]);

        // CSS und HTML einfügen
        $mpdf->WriteHTML($bootstrapCss, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html);

        // PDF-Ausgabe: Stream (im Browser anzeigen)
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    public function invoice($id)
    {
        $user = auth()->user();
        $bookingModel = new BookingModel();

        $booking = $bookingModel
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereIn('type', ['offer_purchase', 'refund_purchase'])
            ->first();

        if (!$booking) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Bei Kreditkartenzahlungen (amount = 0) den echten Betrag aus offer_purchases holen
        if ($booking['amount'] == 0 && $booking['reference_id']) {
            $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
            $purchase = $offerPurchaseModel
                ->where('offer_id', $booking['reference_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($purchase) {
                $booking['amount'] = -$purchase['price_paid']; // Negativ für Rechnung
                $booking['payment_method_info'] = 'Kreditkarte';
            }
        }

        // Rechnungsnummer: RE{LAND}{ID} z.B. RECH123
        $invoice_name = 'RE' . strtoupper(siteconfig()->siteCountry) . $id;

        // Land aus User-Platform extrahieren (z.B. my_offertenheld_ch -> CH)
        $country = '';
        if (!empty($user->platform)) {
            // my_offertenheld_ch -> ch
            $parts = explode('_', $user->platform);
            $countryCode = strtoupper(end($parts)); // CH, DE, etc.
            $country = $countryCode;
        }

        $html = view('account/pdf_invoice', [
            'user' => $user,
            'booking' => $booking,
            'invoice_name' => $invoice_name,
            'country' => $country
        ]);

        $mpdf = new \Mpdf\Mpdf(['default_font' => 'helvetica']);
        $mpdf->WriteHTML($html);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output($invoice_name.".pdf", 'S'));
    }

    public function monthlyInvoice($year, $month)
    {
        $user = auth()->user();
        $bookingModel = new BookingModel();

        // Alle offer_purchase Bookings des Monats holen
        $bookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->where('YEAR(created_at)', $year)
            ->where('MONTH(created_at)', $month)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($bookings)) {
            return redirect()->back()->with('error', lang('Finance.noBookingsForMonth'));
        }

        // Rechnungsnummer für Monatrechnung: RE{LAND}M-{JAHR}-{MONAT} z.B. RECHM-2024-03
        $invoice_name = 'RE' . strtoupper(siteconfig()->siteCountry) . 'M-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Land aus User-Platform extrahieren
        $country = '';
        if (!empty($user->platform)) {
            $parts = explode('_', $user->platform);
            $countryCode = strtoupper(end($parts));
            $country = $countryCode;
        }

        // Gesamtbetrag berechnen
        $total = 0;
        foreach ($bookings as $booking) {
            $total += abs($booking['amount']);
        }

        $html = view('account/pdf_monthly_invoice', [
            'user' => $user,
            'bookings' => $bookings,
            'invoice_name' => $invoice_name,
            'country' => $country,
            'year' => $year,
            'month' => $month,
            'total' => $total
        ]);

        $mpdf = new \Mpdf\Mpdf(['default_font' => 'helvetica']);
        $mpdf->WriteHTML($html);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output($invoice_name . ".pdf", 'S'));
    }

    /**
     * Generiere Monatsrechnungen dynamisch für einen Benutzer
     */
    protected function generateMonthlyInvoicesForUser($user, $filterYear = null, $filterMonth = null): array
    {
        $db = \Config\Database::connect();

        // Hole erste Transaktion des Benutzers
        $firstTransaction = $db->table('bookings')
            ->select('MIN(created_at) as first_transaction')
            ->where('user_id', $user->id)
            ->get()
            ->getRow();

        if (!$firstTransaction || empty($firstTransaction->first_transaction)) {
            return [];
        }

        $invoices = [];
        $firstTransactionDate = new \DateTime($firstTransaction->first_transaction);
        $startPeriod = $firstTransactionDate->format('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $period = $startPeriod;
        while ($period <= $lastMonth) {
            // Filter nach Jahr/Monat wenn angegeben
            $periodYear = substr($period, 0, 4);
            $periodMonth = substr($period, 5, 2);

            if ($filterYear && $periodYear != $filterYear) {
                $period = date('Y-m', strtotime($period . '-01 +1 month'));
                continue;
            }
            if ($filterMonth && $periodMonth != str_pad($filterMonth, 2, '0', STR_PAD_LEFT)) {
                $period = date('Y-m', strtotime($period . '-01 +1 month'));
                continue;
            }

            $startDate = $period . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $purchases = $db->table('bookings')
                ->select('id, created_at, paid_amount, amount, type')
                ->where('user_id', $user->id)
                ->whereIn('type', ['offer_purchase', 'refund_purchase'])
                ->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate)
                ->get()
                ->getResultArray();

            $totalAmount = 0;
            $purchaseCount = 0;
            $refundCount = 0;
            foreach ($purchases as $purchase) {
                if ($purchase['type'] === 'offer_purchase') {
                    $totalAmount += abs($purchase['paid_amount'] ?? $purchase['amount']);
                    $purchaseCount++;
                } else {
                    $totalAmount -= abs($purchase['amount']);
                    $refundCount++;
                }
            }

            // Nur Monate mit Aktivität hinzufügen
            if ($purchaseCount > 0 || $refundCount > 0) {
                $year = substr($period, 0, 4);
                $month = substr($period, 5, 2);

                $platformParts = explode('_', $user->platform ?? '');
                $countryCode = end($platformParts);
                $country = strtoupper($countryCode === 'ch' ? 'CH' :
                          ($countryCode === 'de' ? 'DE' :
                          ($countryCode === 'at' ? 'AT' : 'CH')));

                $currency = ($country === 'CH') ? 'CHF' : 'EUR';
                $invoiceDate = date('Y-m-01', strtotime($period . '-01 +1 month'));

                $invoices[] = [
                    'id' => null, // Dynamisch generiert
                    'user_id' => $user->id,
                    'period' => $period,
                    'purchase_count' => $purchaseCount,
                    'refund_count' => $refundCount,
                    'amount' => $totalAmount,
                    'currency' => $currency,
                    'created_at' => $invoiceDate,
                    'invoice_number' => "M{$country}-{$year}{$month}-{$user->id}",
                ];
            }

            $period = date('Y-m', strtotime($period . '-01 +1 month'));
        }

        // Sortiere nach Periode absteigend (neueste zuerst)
        usort($invoices, fn($a, $b) => strcmp($b['period'], $a['period']));

        return $invoices;
    }

    /**
     * Generiert einen eindeutigen Affiliate-Code
     */
    private function generateAffiliateCode(): string
    {
        $userModel = new \App\Models\UserModel();

        do {
            // Generiere einen 8-stelligen Code aus Buchstaben und Zahlen
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            // Prüfe ob Code bereits existiert
            $exists = $userModel->where('affiliate_code', $code)->first();
        } while ($exists);

        return $code;
    }

    /**
     * Monatsrechnung über monthly_invoices Tabelle generieren
     * URL: /finance/monthly-invoice-pdf/{period} z.B. /finance/monthly-invoice-pdf/2025-11
     */
    public function monthlyInvoicePdf($period)
    {
        $user = auth()->user();
        $monthlyInvoiceModel = new \App\Models\MonthlyInvoiceModel();

        // Hole oder erstelle Monatsrechnung
        $invoice = $monthlyInvoiceModel->getOrCreateForPeriod($user->id, $period);

        if (!$invoice || $invoice['amount'] == 0) {
            return redirect()->back()->with('error', 'Keine Käufe in diesem Monat vorhanden.');
        }

        // Hole alle Käufe für diesen Monat
        $bookingModel = new BookingModel();
        $year = substr($period, 0, 4);
        $month = substr($period, 5, 2);

        $startDate = $period . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $bookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Land aus User-Platform extrahieren
        $country = strtoupper(siteconfig()->siteCountry ?? 'CH');

        $html = view('account/pdf_monthly_invoice', [
            'user' => $user,
            'bookings' => $bookings,
            'invoice' => $invoice,
            'invoice_name' => $invoice['invoice_number'],
            'country' => $country,
            'year' => $year,
            'month' => $month,
            'total' => $invoice['amount']
        ]);

        $mpdf = new \Mpdf\Mpdf(['default_font' => 'helvetica']);
        $mpdf->WriteHTML($html);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output($invoice['invoice_number'] . ".pdf", 'S'));
    }

    /**
     * Update Auto-Purchase Einstellungen
     */
    public function updateSettings()
    {
        $user = auth()->user();
        $autoPurchase = $this->request->getPost('auto_purchase') ? 1 : 0;

        // Hole aktuellen Status
        $db = \Config\Database::connect();
        $currentUser = $db->table('users')->where('id', $user->id)->get()->getRow();

        // Wenn Auto-Purchase aktiviert wird und noch kein Aktivierungsdatum gesetzt ist
        if ($autoPurchase == 1 && empty($currentUser->auto_purchase_activated_at)) {
            $db->table('users')->where('id', $user->id)->update([
                'auto_purchase' => 1,
                'auto_purchase_activated_at' => date('Y-m-d H:i:s')
            ]);
        }
        // Wenn Auto-Purchase deaktiviert wird
        elseif ($autoPurchase == 0) {
            $db->table('users')->where('id', $user->id)->update([
                'auto_purchase' => 0,
                // Aktivierungsdatum NICHT löschen - bleibt erhalten für History
            ]);
        }
        // Wenn bereits aktiviert und wieder aktiviert wird
        else {
            $db->table('users')->where('id', $user->id)->update([
                'auto_purchase' => 1
            ]);
        }

        return redirect()->to('/finance')->with('success', 'Einstellungen erfolgreich gespeichert');
    }

    /**
     * Setzt eine Karte als Primary
     */
    public function setPrimaryCard($cardId)
    {
        $user = auth()->user();
        $paymentMethodModel = new UserPaymentMethodModel();

        // Prüfe ob Karte dem User gehört
        $card = $paymentMethodModel->find($cardId);
        if (!$card || $card['user_id'] != $user->id) {
            return redirect()->to('/finance')->with('error', 'Ungültige Karte');
        }

        // Extrahiere Kartenname für Erfolgsmeldung
        $cardBrand = $card['card_brand'];
        $cardLast4 = $card['card_last4'];

        // Fallback zu provider_data falls Felder leer
        if (empty($cardBrand) || empty($cardLast4)) {
            $providerData = !empty($card['provider_data']) ? json_decode($card['provider_data'], true) : [];
            if (empty($cardBrand)) {
                $cardBrand = $providerData['card_brand'] ?? 'Karte';
            }
            if (empty($cardLast4) && !empty($providerData['card_masked'])) {
                if (preg_match('/(\d{4})\s*$/', $providerData['card_masked'], $matches)) {
                    $cardLast4 = $matches[1];
                }
            }
        }

        // Setze als Primary
        $paymentMethodModel->setPrimary($user->id, $cardId);

        // Erstelle personalisierte Erfolgsmeldung
        $cardName = $cardBrand;
        if ($cardLast4) {
            $cardName .= ' •••• ' . $cardLast4;
        }
        $successMessage = sprintf(lang('Finance.primaryCardSetSuccess'), $cardName);

        return redirect()->to('/finance')->with('success', $successMessage);
    }

    /**
     * Entfernt eine Karte
     */
    public function removeCard($cardId)
    {
        $user = auth()->user();
        $paymentMethodModel = new UserPaymentMethodModel();

        // Prüfe ob Karte dem User gehört
        $card = $paymentMethodModel->find($cardId);
        if (!$card || $card['user_id'] != $user->id) {
            return redirect()->to('/finance')->with('error', 'Ungültige Karte');
        }

        // Prüfe ob User mindestens 2 Karten hat, wenn dies Primary ist
        if ($card['is_primary']) {
            $allCards = $paymentMethodModel->getUserCards($user->id);
            if (count($allCards) > 1) {
                // Setze die nächste Karte als Primary
                foreach ($allCards as $c) {
                    if ($c['id'] != $cardId) {
                        $paymentMethodModel->setPrimary($user->id, $c['id']);
                        break;
                    }
                }
            }
        }

        // Lösche Karte
        $paymentMethodModel->delete($cardId);

        return redirect()->to('/finance')->with('success', lang('Finance.cardRemoved'));
    }

}


