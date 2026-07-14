<?php

namespace App\Services;

use App\Models\Supabase\RecommendationRule;
use App\Models\Supabase\Product;

class RecommendationService
{
    /**
     * Get product recommendations based on cart contents.
     */
    public function getRecommendationsForCart(array $cartProductIds, int $limit = 4): \Illuminate\Support\Collection
    {
        // Fetch rules triggered by products currently in the cart
        $rules = RecommendationRule::where('rule_type', 'cart_has_product')
            ->where('source_item_type', Product::class)
            ->whereIn('source_item_id', $cartProductIds)
            ->orderByDesc('score')
            ->get();

        // Get the recommended product IDs that are NOT already in the cart
        $recommendedIds = $rules->where('recommended_item_type', Product::class)
            ->pluck('recommended_item_id')
            ->unique()
            ->diff($cartProductIds)
            ->take($limit);

        if ($recommendedIds->isEmpty()) {
            // Fallback: recommend top active products that are not in the cart
            return Product::where('status', 'active')
                ->whereNotIn('id', $cartProductIds)
                ->limit($limit)
                ->get();
        }

        return Product::where('status', 'active')
            ->whereIn('id', $recommendedIds)
            ->get();
    }
}
