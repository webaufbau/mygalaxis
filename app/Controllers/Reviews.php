<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Reviews extends BaseController {
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        $reviewModel = new \App\Models\ReviewModel(); // erstelle gleich
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();

        // Pagination (10 pro Seite)
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // Bewertungen mit Paginierung
        $reviews = $reviewModel
            ->where('recipient_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        $totalReviews = $reviewModel->where('recipient_id', $user->id)->countAllResults();

        $avgReviewEntity = $reviewModel->where('recipient_id', $user->id)->selectAvg('rating')->first();
        $avgReview = $avgReviewEntity ? $avgReviewEntity->rating : null;


        $bookingModel = new \App\Models\BookingModel();
        $offerModel = new \App\Models\OfferModel();

        // Gekaufte Angebote via Buchungen
        $bookings = $bookingModel
            ->where('user_id', $user->id)
            ->where('type', 'offer_purchase')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // IDs der gekauften Angebote
        $purchasedOfferIds = array_column($bookings, 'reference_id');

        // Gekaufte Angebote mit Offer-Infos
        $purchasedOffers = [];
        foreach ($purchasedOfferIds as $offerId) {
            $offer = $offerModel->find($offerId);
            if ($offer) {
                $offer['price_paid'] = $bookingModel
                    ->where('user_id', $user->id)
                    ->where('reference_id', $offerId)
                    ->where('type', 'offer_purchase')
                    ->orderBy('created_at', 'DESC')
                    ->first()['amount'] ?? 0;
                $purchasedOffers[] = $offer;
            }
        }

        $totalPurchased = count($purchasedOffers);

        return view('account/reviews', [
            'title' => 'Bewertungen',
            'reviews' => $reviews,
            'pager' => $reviewModel->pager,
            'avgReview' => $avgReview,
            'totalReviews' => $totalReviews,
            'totalPurchased' => $totalPurchased,
        ]);
    }

}
