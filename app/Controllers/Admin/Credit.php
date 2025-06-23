<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CreditModel;

class Credit extends BaseController
{
    protected $creditModel;
    protected $companyModel;

    public function __construct()
    {
        $this->creditModel = new CreditModel();
        $this->companyModel = new CompanyModel();
    }

    public function index()
    {
        $credits = $this->creditModel->orderBy('created_at', 'DESC')->findAll();
        return view('admin/credits/index', ['credits' => $credits]);
    }

    public function create()
    {
        $companies = $this->companyModel->findAll();
        return view('admin/credits/create', ['companies' => $companies]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $this->creditModel->insert([
            'company_id' => $data['company_id'],
            'amount' => $data['amount'],
            'type' => 'manual_credit',
            'description' => $data['description'] ?? 'Gutschrift vom Admin',
        ]);
        return redirect()->to('/admin/credits')->with('success', 'Guthaben erfolgreich vergeben.');
    }
}
