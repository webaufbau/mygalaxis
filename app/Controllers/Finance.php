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
        $paymentMethods = [];
        foreach ($userMethods as $method) {
            $baseMethod = $paymentMethodModel->where('code', $method['payment_method_code'])->first();
            if ($baseMethod) {
                $paymentMethods[] = [
                    'id' => $method['id'],
                    'code' => $method['payment_method_code'],
                    'name' => $baseMethod['name'],
                    'provider_data' => $method['provider_data'], // JSON string
                ];
            }
        }

        if ($this->request->getMethod() === 'post') {
            $post = $this->request->getPost();
            $amount = floatval($post['amount'] ?? 0);
            $paymentMethod = $post['payment_method'] ?? '';
            $mode = $post['mode'] ?? 'once';

            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Bitte geben Sie einen gültigen Betrag ein.');
            }
            if (!in_array($paymentMethod, array_column($paymentMethods, 'code'))) {
                return redirect()->back()->with('error', 'Bitte wählen Sie eine gültige Zahlungsmethode.');
            }

            // TODO: Payment abwickeln (z.B. Stripe, PayPal, TWINT)

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

        return view('account/finance_topup', [
            'paymentMethods' => $paymentMethods,
            'session' => session(),
        ]);
    }



    public function userPaymentMethods()
    {
        $userId = auth()->user()->id;
        $model = new UserPaymentMethodModel();
        $methods = $model->where('user_id', $userId)->findAll();

        return view('finance/user_payment_methods', ['methods' => $methods]);
    }

    public function addUserPaymentMethod()
    {
        $userId = auth()->user()->id;
        $model = new UserPaymentMethodModel();

        if ($this->request->getMethod() === 'post') {
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

    public function deleteUserPaymentMethod($id)
    {
        $model = new UserPaymentMethodModel();
        $method = $model->find($id);

        if ($method && $method['user_id'] === auth()->user()->id) {
            $model->delete($id);
            return redirect()->back()->with('message', 'Zahlungsmethode gelöscht.');
        }

        return redirect()->back()->with('error', 'Nicht gefunden oder Zugriff verweigert.');
    }

}
