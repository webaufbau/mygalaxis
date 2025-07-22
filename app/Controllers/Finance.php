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

        $builder = $bookingModel->where('user_id', $user->id);
        if ($year) {
            $builder->where('YEAR(created_at)', $year);
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

        $currentMonth = date('m');
        $currentYear = date('Y');
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
            'currentYear' => $year,
            'monthlyTurnover' => $monthlyTurnover,
        ]);
    }


    public function topup()
    {
        $amount = (int)(floatval($this->request->getPost('amount') ?? 20) * 100); // CHF → Rappen
        $refno = uniqid('topup_');

        $successUrl = site_url("finance/topupSuccess?refno=$refno");
        $failUrl    = site_url("finance/topupFail");

        try {
            $response = $this->saferpay->initTransactionWithAlias($successUrl, $failUrl, $amount, $refno);
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
                session()->setFlashdata('error', 'Ihre Adresse ist unvollständig oder ungültig. Bitte korrigieren Sie diese, um Ihr Guthaben aufzuladen.');

                // Weiterleitung zur Profilseite
                return redirect()->to('/profile');
            }

            return $this->response->setStatusCode(500)->setBody("Zahlung fehlgeschlagen: " . $e->getMessage());
        }
    }

    public function topupSuccess()
    {
        $refno = $this->request->getGet('refno');
        $user = auth()->user();

        // 1. Token anhand refno holen (aus DB oder Speicher)
        $token = $this->saferpay->getTokenByRefno($refno);  // Du brauchst diese Methode, um den Token zu laden

        if (!$token) {
            return redirect()->to('/finance/topupFail')->with('error', 'Transaktion nicht gefunden.');
        }

        try {
            $saferpay = new \App\Services\SaferpayService();
            $response = $saferpay->assertTransaction($token);

            // 2. Prüfen, ob die Transaktion autorisiert ist
            if (isset($response['Transaction']) && $response['Transaction']['Status'] === 'AUTHORIZED') {
                $amount = $response['Transaction']['Amount']['Value']; // in Rappen
                $currency = $response['Transaction']['Amount']['CurrencyCode'];

                // 3. Guthaben gutschreiben (eigene Logik)
                // Guthaben gutschreiben
                $bookingModel = new BookingModel();
                $booking_id = $bookingModel->insert([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'description' => "Guthabenaufladung via " . ($response['Transaction']['AcquirerName'] ?? 'Online-Zahlung'),
                    'amount' => $amount / 100,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // 4. Alias sichern, falls vorhanden
                if (isset($response['Transaction']['PaymentMeans']['Alias'])) {
                    $aliasId = $response['Transaction']['PaymentMeans']['Alias']['Id'];
                    $paymentMethodModel = new \App\Models\UserPaymentMethodModel();
                    $paymentMethodModel->save([
                        'user_id' => $user->id,
                        'payment_method_code' => 'saferpay',
                        'provider_data' => json_encode(['alias_id' => $aliasId]),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // 5. Transaktion updaten
                if (isset($response['Transaction'])) {
                    $transaction = $response['Transaction'];

                    $transaction_data = [
                        'transaction_id' => $transaction['Id'] ?? 0,
                        'status'         => $transaction['Status'] ?? '',
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




                return redirect()->to('/finance')->with('message', 'Zahlung erfolgreich.');
            }

            return redirect()->to('/finance/topupFail')->with('error', 'Zahlung nicht autorisiert.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Loggen
            log_message('error', 'Saferpay API Fehler: ' . $errorMessage);

            // Benutzerfreundliche Meldung mit genauer Erklärung
            $userMessage = 'Fehler bei der Zahlungsprüfung. ';

            if (strpos($errorMessage, 'AUTHORIZATION_AMOUNT_EXCEEDED') !== false) {
                $userMessage .= 'Die Zahlung wurde abgelehnt, da das verfügbare Guthaben oder Kreditlimit nicht ausreicht.';
            } else {
                $userMessage .= 'Bitte versuchen Sie es später erneut oder kontaktieren Sie den Support.';
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
     * Startet den Redirect zu Datatrans mit Tokenization
     */
    /*public function topup()
    {
        $amount = (int)(floatval($this->request->getPost('amount')) * 100);
        $refno = uniqid('topup_');

        // URLs für Redirect
        $successUrl = site_url("finance/topupSuccess?refno=$refno");
        $cancelUrl  = site_url('finance/topupCancel');
        $errorUrl   = site_url('finance/topupError');

        try {
            $response = $this->datatrans->initTransactionWithAlias($successUrl, $cancelUrl, $errorUrl, $amount, $refno);
            $redirectUrl = str_replace('{transactionId}', $response['transactionId'], $this->datatrans->config->redirectUrlTemplate);

            return redirect()->to($redirectUrl);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setBody("Fehler beim Starten der Zahlung: " . $e->getMessage());
        }
    }*/


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



    /*public function topup()
    {
        $user = auth()->user();

        $userPaymentMethodModel = new \App\Models\UserPaymentMethodModel();
        $paymentMethodModel = new \App\Models\PaymentMethodModel();

        // Eigene Zahlungsmethoden holen
        $userMethods = $userPaymentMethodModel->where('user_id', $user->id)->findAll();

        // Optional: Namen & Details aus payment_methods ergänzen
        $myPaymentMethods = [];
        foreach ($userMethods as $method) {
            $baseMethod = $paymentMethodModel->where('code', $method['payment_method_code'])->first();
            if ($baseMethod) {
                $myPaymentMethods[] = [
                    'id' => $method['id'],
                    'code' => $method['payment_method_code'],
                    'name' => $baseMethod['name'],
                    'provider_data' => $method['provider_data'], // JSON string
                ];
            }
        }

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $amount = floatval($post['amount'] ?? 0);
            $paymentMethod = $post['payment_method'] ?? '';
            $mode = $post['mode'] ?? 'once';

            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Bitte geben Sie einen gültigen Betrag ein.');
            }
            if (!in_array($paymentMethod, array_column($myPaymentMethods, 'code'))) {
                return redirect()->back()->with('error', 'Bitte wählen Sie eine gültige Zahlungsmethode.');
            }

            $bookingModel = new BookingModel();
            $bookingModel->insert([
                'user_id' => $user->id,
                'type' => 'topup',
                'description' => "Guthabenaufladung via $paymentMethod ($mode)",
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/finance/topup')->with('message', 'Zahlung erfolgreich verarbeitet.');
        }


        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->where('active', 1)->findAll();


        return view('account/finance_topup', [
            'myPaymentMethods' => $myPaymentMethods,
            'paymentMethods' => $paymentMethods,
            'session' => session(),
        ]);
    }*/



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
            return redirect()->to('/finance/userpaymentmethods')->with('message', 'Zahlungsmethode gespeichert.');
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

        return redirect()->back()->with('error', 'Zahlungsseite konnte nicht erstellt werden.');
    }

    public function paymentSuccess()
    {
        $user = auth()->user();
        $reference = $this->request->getGet('reference');

        $payrexx = new \App\Libraries\PayrexxService();
        $response = $payrexx->request('Transaction', ['referenceId' => $reference]);

        if (!isset($response['data'][0]['token'])) {
            return redirect()->to('/finance/userpaymentmethods')->with('error', 'Kein Token erhalten.');
        }

        $token = $response['data'][0]['token'];

        // Speichern (verschlüsselt)
        $model = new \App\Models\UserPaymentMethodModel();
        $model->saveEncryptedToken($user->id, 'creditcard', $token);

        return redirect()->to('/finance/userpaymentmethods')->with('message', 'Zahlungsmethode gespeichert.');
    }

    public function deleteUserPaymentMethod($id)
    {
        $model = new UserPaymentMethodModel();
        $method = $model->find($id);

        if ($method && $method['user_id'] == auth()->user()->id) {
            $model->delete($id);
            return redirect()->back()->with('message', 'Zahlungsmethode gelöscht.');
        }

        return redirect()->back()->with('error', 'Nicht gefunden oder Zugriff verweigert.');
    }

}
