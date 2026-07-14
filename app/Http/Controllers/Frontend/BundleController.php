<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\ProductBundle;
use App\Support\CheckoutCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BundleController extends Controller
{
    /**
     * Expand a bundle into its component products and add them all to the cart.
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

        foreach ($bundle->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            CheckoutCart::addItem([
                'product_id'       => (string) $product->id,
                'title'            => $product->title,
                'subtitle'         => __('bundles.component_of', ['title' => $bundle->title]),
                'image'            => $product->featured_image ?: asset('images/dainely-belt-product.png'),
                'price'            => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'quantity'         => (int) ($item->quantity ?: 1),
                'variant_id'       => $product->variant_id,
                'sku'              => $product->sku,
                'source'           => 'shopify',
            ]);
        }

        // Redirect directly to checkout
        return redirect()->route('checkout.index', ['locale' => $locale]);
    }
}
