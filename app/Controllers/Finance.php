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

        $year = $this->request->getGet('year');
        $month = $this->request->getGet('month');

        $builder = $bookingModel->where('user_id', $user->id);

        if ($year) {
            $builder->where('YEAR(created_at)', $year);
        }
        if ($month) {
            $builder->where('MONTH(created_at)', $month);
        }

        $bookings = $builder->orderBy('created_at', 'DESC')->paginate(15);
        $pager = $bookingModel->pager;

        $years = $bookingModel->select("YEAR(created_at) as year")
            ->where('user_id', $user->id)
            ->groupBy('year')
            ->orderBy('year', 'DESC')
            ->findAll();

        $balance = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->first()['amount'] ?? 0;

        // Falls kein Filter gesetzt, Standardwerte verwenden
        $currentYear = $year ?: date('Y');
        $currentMonth = $month ?: ''; // leer bedeutet "Alle Monate"

        $monthlyTurnover = $bookingModel->selectSum('amount')
            ->where('user_id', $user->id)
            ->where('MONTH(created_at)', $currentMonth)
            ->where('YEAR(created_at)', $currentYear)
            ->first()['amount'] ?? 0;

        return view('account/finance', [
            'title' => 'Finanzen',
            'bookings' => $bookings,
            'pager' => $pager,
            'balance' => $balance,
            'years' => $years,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'monthlyTurnover' => $monthlyTurnover,
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

            // Hole gespeicherte Zahlungsmethode
            $paymentMethodModel = new UserPaymentMethodModel();
            $paymentMethod = $paymentMethodModel
                ->where('user_id', $user->id)
                ->where('payment_method_code', 'saferpay')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$paymentMethod) {
                log_message('warning', "[TOPUP AUTO] Keine gespeicherte Zahlungsmethode für User #{$user->id} - Weiterleitung zu Saferpay");
                return false;
            }

            // Hole Alias-ID aus provider_data JSON
            $providerData = json_decode($paymentMethod['provider_data'], true);
            $aliasId = $providerData['alias_id'] ?? null;
            $cardMasked = $providerData['card_masked'] ?? 'unbekannt';
            $platform = $paymentMethod['platform'] ?? 'unbekannt';

            log_message('info', "[TOPUP AUTO] Gefundene Zahlungsmethode für User #{$user->id}: Alias {$aliasId}, Karte: {$cardMasked}, Platform: {$platform}");

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
                try {
                    $captureResponse = $saferpay->captureTransaction($transactionId);
                    log_message('info', 'Saferpay Capture erfolgreich: ' . json_encode($captureResponse));

                    // Status auf CAPTURED aktualisieren
                    $captureStatus = $captureResponse['Status'] ?? 'CAPTURED';
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

                            $paymentMethodModel->save([
                                'user_id' => $user->id,
                                'payment_method_code' => 'saferpay',
                                'platform' => $platform,
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

        $bookings = $builder->orderBy('created_at', 'DESC')->findAll();

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
            ->where('type', 'offer_purchase')
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



}
