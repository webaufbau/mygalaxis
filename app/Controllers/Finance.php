<?php
namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\PaymentMethodModel;
use App\Models\UserPaymentMethodModel;

class Finance extends BaseController
{
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
