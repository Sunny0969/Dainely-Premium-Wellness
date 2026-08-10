<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\ProductBundle;
use App\Support\CheckoutCart;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    /**
     * Phase 2 §9.1 — expand bundle into component line items, then checkout.
     */
    public function addToCart(string $locale, int $bundleId, Request $request)
    {
        $bundle = ProductBundle::query()
            ->with(['items.product'])
            ->where('id', $bundleId)
            ->where('locale', $locale)
            ->firstOrFail();

        if ($bundle->items->isEmpty()) {
            return back()->withErrors(['bundle' => __('bundles.empty_bundle')]);
        }

        $added = 0;

        foreach ($bundle->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            CheckoutCart::addItem([
                'product_id' => (string) $product->id,
                'title' => $product->getTranslatedTitle($locale),
                'subtitle' => __('bundles.component_of', ['title' => $bundle->title]),
                'image' => $product->featured_image ?: asset('images/dainely-belt-product.png'),
                'price' => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'quantity' => (int) ($item->quantity ?: 1),
                'variant_id' => $product->variant_id,
                'sku' => $product->sku,
                'source' => 'shopify',
                'bundle_id' => (string) $bundle->id,
            ]);
            $added++;
        }

        if ($added < 1) {
            return back()->withErrors(['bundle' => __('bundles.empty_bundle')]);
        }

        app(\App\Services\AnalyticsEventService::class)->track('add_to_cart', [
            'bundle_id' => $bundle->id,
            'title' => $bundle->title,
            'item_count' => $added,
            'content_type' => 'bundle',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('checkout.index', ['locale' => $locale]);
    }
}
