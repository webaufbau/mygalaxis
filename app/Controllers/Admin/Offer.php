<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CreditModel;

class Offer extends BaseController
{
    protected $creditModel;
    protected $companyModel;

    public function __construct()
    {
        $this->creditModel = new CreditModel();
        $this->companyModel = new CompanyModel();
    }

    public function detail($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Angebot nicht gefunden.");
        }

        $data['offer'] = $offer;
        return view('admin/offer_detail', $data);
    }

}
