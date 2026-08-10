<?php

namespace App\Services;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductBundle;
use App\Support\CheckoutCart;
use App\Support\SupabaseDb;
use Illuminate\Http\RedirectResponse;

/**
 * Phase 2 §8.4 — resolve landing page offer (product or bundle) into checkout cart.
 */
class LandingOfferService
{
    /**
     * @return array{
     *   type: 'product'|'bundle'|'catalog'|null,
     *   label: string,
     *   checkout_url: string|null,
     *   product: ?Product,
     *   bundle: ?ProductBundle,
     *   discount_code: ?string
     * }
     */
    public function resolve(LandingPage $page, string $locale): array
    {
        $label = trim((string) ($page->cta_label ?: '')) ?: __('Shop Now');
        $discount = trim((string) ($page->discount_code ?: '')) ?: null;

        if ($page->bundle_id) {
            $bundle = SupabaseDb::run(
                fn () => ProductBundle::with('items.product')
                    ->where('id', $page->bundle_id)
                    ->first(),
                null
            );

            if ($bundle) {
                return [
                    'type' => 'bundle',
                    'label' => $label,
                    'checkout_url' => route('landing.checkout', ['locale' => $locale, 'id' => $page->id]),
                    'product' => null,
                    'bundle' => $bundle,
                    'discount_code' => $discount,
                ];
            }
        }

        if ($page->shopify_product_id) {
            $product = $this->findProduct($page->shopify_product_id);
            if ($product) {
                return [
                    'type' => 'product',
                    'label' => $label,
                    'checkout_url' => route('landing.checkout', ['locale' => $locale, 'id' => $page->id]),
                    'product' => $product,
                    'bundle' => null,
                    'discount_code' => $discount,
                ];
            }
        }

        return [
            'type' => 'catalog',
            'label' => $label,
            'checkout_url' => route('products.index', ['locale' => $locale]),
            'product' => null,
            'bundle' => null,
            'discount_code' => $discount,
        ];
    }

    public function addOfferToCartAndRedirect(LandingPage $page, string $locale): RedirectResponse
    {
        if ($code = trim((string) ($page->discount_code ?: ''))) {
            session(['pending_discount_code' => strtoupper($code)]);
        }

        $offer = $this->resolve($page, $locale);

        if ($offer['type'] === 'bundle' && $offer['bundle']) {
            return $this->addBundle($offer['bundle'], $locale);
        }

        if ($offer['type'] === 'product' && $offer['product']) {
            return $this->addProduct($offer['product'], $locale);
        }

        return redirect()
            ->route('products.index', ['locale' => $locale])
            ->with('error', 'This landing page has no product or bundle linked.');
    }

    protected function findProduct(string $shopifyProductId): ?Product
    {
        $raw = (string) $shopifyProductId;
        $gid = str_starts_with($raw, 'gid://')
            ? $raw
            : 'gid://shopify/Product/' . preg_replace('/\D+/', '', $raw);

        return SupabaseDb::run(
            fn () => Product::query()
                ->where(function ($q) use ($gid, $raw) {
                    $q->where('shopify_product_id', $gid)
                        ->orWhere('shopify_product_id', $raw)
                        ->orWhere('handle', $raw);
                })
                ->first(),
            null
        );
    }

    protected function addProduct(Product $product, string $locale): RedirectResponse
    {
        CheckoutCart::addItem([
            'product_id' => (string) $product->id,
            'title' => $product->title,
            'subtitle' => null,
            'image' => $product->featured_image ?: asset('images/dainely-belt-product.png'),
            'price' => (float) $product->price,
            'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
            'quantity' => 1,
            'variant_id' => $product->variant_id,
            'sku' => $product->sku,
            'source' => 'shopify',
        ]);

        return redirect()->route('checkout.index', ['locale' => $locale]);
    }

    protected function addBundle(ProductBundle $bundle, string $locale): RedirectResponse
    {
        $bundle->loadMissing('items.product');

        if ($bundle->items->isEmpty()) {
            return back()->withErrors(['bundle' => 'Bundle has no items.']);
        }

        foreach ($bundle->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            CheckoutCart::addItem([
                'product_id' => (string) $product->id,
                'title' => method_exists($product, 'getTranslatedTitle')
                    ? $product->getTranslatedTitle($locale)
                    : (string) $product->title,
                'subtitle' => $bundle->title,
                'image' => $product->featured_image ?: asset('images/dainely-belt-product.png'),
                'price' => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'quantity' => (int) ($item->quantity ?: 1),
                'variant_id' => $product->variant_id,
                'sku' => $product->sku,
                'source' => 'shopify',
                'bundle_id' => (string) $bundle->id,
            ]);
        }

        return redirect()->route('checkout.index', ['locale' => $locale]);
    }
}
