<?php
namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\OfferModel;
use App\Models\ReviewModel;
use App\Models\UserModel;

class PublicController extends BaseController
{
    // Show interested companies (with public hash)
    public function interestedCompanies(string $offerHash)
    {
        $offerModel = new OfferModel();
        $bookingModel = new BookingModel();
        $userModel = new UserModel();
        $reviewModel = new ReviewModel();

        $offer = $offerModel->where('access_hash', $offerHash)->first();

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('Offers.offerNotFound'));
        }

        $bookings = $bookingModel
            ->where('reference_id', $offer['id'])
            ->where('type', 'offer_purchase')
            ->findAll();

        $companies = [];
        foreach ($bookings as $booking) {
            $company = $userModel->find($booking['user_id']); // je nach Struktur evtl. 'company_id'
            if ($company) {
                // Durchschnittliche Bewertung
                $rating = $reviewModel->where('recipient_id', $company->id)->selectAvg('rating')->first();
                $company->average_rating = round($rating->rating ?? 0, 1);

                // Einzelne Bewertungen
                $company->reviews = $reviewModel
                    ->where('recipient_id', $company->id)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();

                $companies[] = $company;
            }
        }

        $alreadyRated = $reviewModel
            ->where('offer_id', $offer['id'])
            ->first();

        return view('public/interested_companies', [
            'siteConfig' => $this->siteConfig,
            'offer' => $offer,
            'companies' => $companies,
            'alreadyRated' => !is_null($alreadyRated),
        ]);
    }

    // Show rating form
    public function showRatingForm(string $companyHash)
    {
        $companyModel = new UserModel();
        $company = $companyModel->where('public_hash', $companyHash)->first();

        if (!$company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('Offers.companyNotFound'));
        }

        return view('rating/write', [
            'company' => $company,
        ]);
    }

    // Save submitted rating

    /**
     * @throws \ReflectionException
     */
    public function submitRating()
    {
        $offerModel = new OfferModel();
        $bookingModel = new BookingModel();
        $userModel = new UserModel();
        $reviewModel = new ReviewModel();

        $data = $this->request->getPost();

        $offer_access_hash = $data['offer_token'];
        $offer = $offerModel->where('access_hash', $offer_access_hash)->first();
        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('Offers.offerNotFound'));
        }

        if (!isset($data['recipient_id']) || empty($data['rating'])) {
            return redirect()->back()->with('error', lang('General.fillAllFields'));
        }

        // Validierung
        if (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return redirect()->back()->with('error', lang('Offers.invalidRating'));
        }

        // Prüfen ob recipient_id die Offerte auch tatsächlich gekauft hat
        $recipient_user_has_bought_offer = $bookingModel
            ->where('reference_id', $offer['id'])
            ->where('user_id', $data['recipient_id'])
            ->where('type', 'offer_purchase')
            ->first();

        if (!$recipient_user_has_bought_offer) {
            return redirect()->back()->with('error', lang('Offers.companyNotEligibleForRating'));
        }

        // Prüfe, ob der Anfragesteller die Firma bewerten darf
        // Hat er die Offerte bereits bewertet? Nur eine Bewertung je Offerte
        $alreadyRated = $reviewModel
            ->where('offer_id', $offer['id'])
            ->first();

        if ($alreadyRated) {
            return redirect()->back()->with('error', lang('Offers.alreadyRated'));
        }

        $reviewModel->insert([
            'recipient_id' => $data['recipient_id'], // USER ID
            'offer_id' => $offer['id'], // USER ID
            'rating' => (int) $data['rating'],
            'comment' => strip_tags($data['comment'] ?? ''),
            'created_at' => date('Y-m-d H:i:s') ?? '',
            'created_by_email' => $offer['email'] ?? '',
            'created_by_firstname' => $offer['firstname'] ?? '',
            'created_by_lastname' => $offer['lastname'] ?? '',
            'created_by_zip' => $offer['zip'] ?? '',
            'created_by_city' => $offer['city'] ?? '',
            'created_by_country' => $offer['country'] ?? '',
        ]);

        return redirect()->to('/offer/interested/' . $offer['access_hash'])->with('success', lang('Offers.thankYouForRating'));
    }
}
