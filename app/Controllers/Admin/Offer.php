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

        // Preisberechnung
        $calculator = new \App\Libraries\OfferPriceCalculator();
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);
        $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true);

        $calculatedPrice = $calculator->calculatePrice(
            $offer['type'] ?? '',
            $offer['original_type'] ?? '',
            $formFields,
            $formFieldsCombo
        );

        // Debug-Informationen holen
        $priceDebugInfo = $calculator->getDebugInfo();

        // Preiskomponenten holen
        $priceComponents = $calculator->getPriceComponents();

        // Maximalpreis-Cap Informationen holen
        $maxPriceCapInfo = $calculator->getMaxPriceCapInfo();

        // Rabatt berechnen
        $createdAt = \CodeIgniter\I18n\Time::parse($offer['created_at'], 'UTC')->setTimezone(app_timezone());
        $now = \CodeIgniter\I18n\Time::now(app_timezone());
        $hoursDiff = $createdAt->diff($now)->h + ($createdAt->diff($now)->days * 24);
        $discountedPrice = $calculator->applyDiscount($calculatedPrice, $hoursDiff);

        // Rabatt-Prozentsatz berechnen
        $discountPercent = 0;
        if ($discountedPrice < $calculatedPrice) {
            $discountPercent = round((($calculatedPrice - $discountedPrice) / $calculatedPrice) * 100);
        }

        // Anzahl Verkäufe ermitteln
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchaseCount = $purchaseModel->where('offer_id', $offer['id'])->countAllResults();

        // Käufer-Informationen holen
        $bookingModel = new \App\Models\BookingModel();
        $purchases = $bookingModel
            ->select('bookings.*, users.username, users.contact_person')
            ->join('users', 'users.id = bookings.user_id')
            ->where('bookings.type', 'offer_purchase')
            ->where('bookings.reference_id', $offer['id'])
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll();

        // Berechnungsdetails sammeln
        $calculationDetails = $this->getCalculationDetails($offer, $formFields, $formFieldsCombo, $calculatedPrice);

        $data['offer'] = $offer;
        $data['calculatedPrice'] = $calculatedPrice;
        $data['discountedPrice'] = $discountedPrice;
        $data['discountPercent'] = $discountPercent;
        $data['hoursDiff'] = $hoursDiff;
        $data['purchaseCount'] = $purchaseCount;
        $data['purchases'] = $purchases;
        $data['calculationDetails'] = $calculationDetails;
        $data['formFields'] = $formFields;
        $data['formFieldsCombo'] = $formFieldsCombo;
        $data['priceDebugInfo'] = $priceDebugInfo;
        $data['priceComponents'] = $priceComponents;
        $data['maxPriceCapInfo'] = $maxPriceCapInfo;

        return view('admin/offer_detail', $data);
    }

    private function getCalculationDetails($offer, $formFields, $formFieldsCombo, $price)
    {
        $details = [];
        $details['type'] = $offer['type'];
        $details['original_type'] = $offer['original_type'];
        $details['price'] = $price;

        switch ($offer['type']) {
            case 'cleaning':
                if (in_array($offer['original_type'], ['reinigung_nur_fenster', 'reinigung_fassaden', 'reinigung_hauswartung', 'reinigung_andere'])) {
                    $details['base'] = $offer['original_type'];
                } else {
                    $details['wohnung_groesse'] = $formFields['wohnung_groesse'] ?? null;
                    $details['komplett_anzahlzimmer'] = $formFields['komplett_anzahlzimmer'] ?? null;
                    $details['wiederkehrend'] = $formFields['reinigungsart_wiederkehrend'] ?? null;
                    $details['fensterreinigung'] = $formFields['fensterreinigung'] ?? null;
                    $details['aussenfassade'] = $formFields['aussenfassade'] ?? null;
                }
                break;

            case 'move':
            case 'move_cleaning':
                $details['auszug_zimmer'] = $formFields['auszug_zimmer'] ?? null;
                $details['auszug_arbeitsplatz_firma'] = $formFields['auszug_arbeitsplatz_firma'] ?? null;
                $details['auszug_flaeche_firma'] = $formFields['auszug_flaeche_firma'] ?? null;
                break;
        }

        return $details;
    }

}
