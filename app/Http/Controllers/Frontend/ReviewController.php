<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use App\Support\ProductSlugResolver;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviews) {}

    /**
     * Lazy-load reviews HTML for a product (AJAX).
     */
    public function productReviews(string $locale, string $handle): JsonResponse
    {
        $handle = ProductSlugResolver::resolveHandle($handle);
        $reviewData = $this->reviews->getProductReviews($handle, 100);

        $reviewStats = [
            'average_rating'   => $reviewData['average_rating'],
            'total_reviews'    => $reviewData['total_count'],
            'rating_breakdown' => $reviewData['rating_breakdown'],
        ];

        $html = view('partials.reviews', [
            'reviews'     => $reviewData['reviews'] ?? [],
            'reviewStats' => $reviewStats,
        ])->render();

        return response()->json([
            'html'  => $html,
            'stats' => $reviewStats,
        ]);
    }
}
