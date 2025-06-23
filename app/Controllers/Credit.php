<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CreditModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Credit extends BaseController
{
    protected $creditModel;

    public function __construct()
    {
        $this->creditModel = new CreditModel();
    }

    // Zeigt aktuelle Guthabenübersicht (Mein Konto)
    public function index()
    {
        $companyId = auth()->id(); // Shield-Login

        $transactions = $this->creditModel
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $balance = $this->creditModel->getBalance($companyId);

        return view('credits/index', [
            'transactions' => $transactions,
            'balance' => $balance,
        ]);
    }

    // Seite zum Aufladen von Guthaben (z.B. mit Test-Formular)
    public function add()
    {
        return view('credits/add');
    }

    // POST: Guthaben aufladen (Testform, später Stripe etc.)
    public function store()
    {
        $amount = (float) $this->request->getPost('amount');
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Ungültiger Betrag.');
        }

        $this->creditModel->insert([
            'company_id' => auth()->id(),
            'amount' => $amount,
            'type' => 'manual_credit',
            'description' => 'Manuelle Aufladung',
        ]);

        return redirect()->to('/credits')->with('success', 'Guthaben erfolgreich aufgeladen.');
    }
}
