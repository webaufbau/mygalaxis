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
        $avgRating = $reviewModel->where('recipient_id', $user->id)->selectAvg('rating')->first()['rating'];

        $totalPurchased = $offerPurchaseModel->where('user_id', $user->id)->countAllResults();

        return view('account/reviews', [
            'title' => 'Bewertungen',
            'reviews' => $reviews,
            'pager' => $reviewModel->pager,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
            'totalPurchased' => $totalPurchased,
        ]);
    }

}
