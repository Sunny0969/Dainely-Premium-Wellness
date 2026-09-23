<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\ProductBundle;
use App\Support\CheckoutCart;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $query = ProductBundle::query()
            ->with(['items.product'])
            ->where('locale', $locale);
            
        if (str_starts_with($slug, 'bundle_')) {
            $query->where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('id', str_replace('bundle_', '', $slug));
            });
        } else {
            $query->where('slug', $slug);
        }
        
        $bundle = $query->first();

        if (!$bundle) {
            $fallbackQuery = ProductBundle::query()
                ->with(['items.product'])
                ->where('locale', 'en');
                
            if (str_starts_with($slug, 'bundle_')) {
                $fallbackQuery->where(function($q) use ($slug) {
                    $q->where('slug', $slug)
                      ->orWhere('id', str_replace('bundle_', '', $slug));
                });
            } else {
                $fallbackQuery->where('slug', $slug);
            }
            
            $bundle = $fallbackQuery->firstOrFail();

            if ($locale !== 'en') {
                try {
                    $translator = app(\App\Services\ContentTranslationService::class);
                    $bundle->title = $translator->translateContent($bundle->title, 'en', $locale);
                    if ($bundle->description) {
                        $bundle->description = $translator->translateContent($bundle->description, 'en', $locale);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to translate bundle: " . $e->getMessage());
                }
            }
        }

        $shopifyService = app(\App\Services\ShopifyService::class);
        $catalog = $shopifyService->fetchProducts(250)['products'] ?? [];
        $catalogMapped = $shopifyService->mapProductsForDisplay($catalog);
        
        $componentProducts = [];
        foreach($bundle->items as $item) {
            if($item->product) {
                $matched = collect($catalogMapped)->firstWhere('handle', $item->product->handle);
                if($matched) {
                    $matched['bundle_quantity'] = $item->quantity;
                    $matched['db_product_id'] = $item->product->id;
                    
                    // Fetch live variants if the product requires it and shopify cache only gave 1 variant
                    if (\App\Support\ProductRequiresSize::check((string) $matched['id'], $matched['title'], $matched['handle'])) {
                        if (count($matched['variants']) <= 1) {
                            $liveProduct = $shopifyService->fetchProductByHandle($matched['handle'], null, true);
                            if (!empty($liveProduct['success']) && !empty($liveProduct['product']['variants'])) {
                                $options = $liveProduct['product']['variants'];
                                if (count($options) > 1) {
                                    $matched['variants'] = $options;
                                    $matched['variant_count'] = count($options);
                                }
                            }
                        }
                    }

                    // Fetch localized content from DB
                    $content = $item->product->productContents()->where('locale', $locale)->first();
                    
                    // Apply static translation from catalog for title if available
                    $productTranslator = app(\App\Services\ProductTranslationService::class);
                    $originalTitle = $matched['title'] ?? '';
                    $matched = $productTranslator->apply($matched, $locale);
                    
                    if (($matched['title'] ?? '') === $originalTitle && config('services.auto_translate.enabled', true)) {
                        try {
                            $matched['title'] = app(\App\Services\ContentTranslationService::class)->translateContent($originalTitle, 'en', $locale);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning("Failed to translate component title: " . $e->getMessage());
                        }
                    }
                    
                    // The user requested to ONLY show the single product written description (overview + benefits)
                    $matched['overview'] = $content ? $content->overview : '';
                    $matched['benefits_html'] = $content ? $content->benefits : '';
                    
                    $componentProducts[] = $matched;
                }
            }
        }
        
        return view('bundles.show', compact('bundle', 'locale', 'componentProducts'));
    }

    public function addToCart(string $locale, int $bundleId, Request $request)
    {
        $bundle = ProductBundle::query()
            ->with(['items.product'])
            ->where('id', $bundleId)
            ->firstOrFail();

        if ($bundle->items->isEmpty()) {
            return back()->withErrors(['bundle' => __('bundles.empty_bundle')]);
        }

        $added = 0;
        
        // Check if user posted specific variants for each component
        $variants = $request->input('variants', []); // e.g. [db_product_id => variant_id]

        foreach ($bundle->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }
            
            $variantId = $variants[$product->id] ?? $product->variant_id;
            
            $optionLabel = null;
            $optionValue = null;
            
            // If the selected variant ID looks like a small integer (mock size index), map it back to its title
            if (\App\Support\ProductRequiresSize::check((string) $product->id, $product->title, $product->handle)) {
                $assets = \App\Support\ProductLandingAssets::forProduct(
                    null, $product->handle, $product->featured_image, [], true,
                    (float) $product->price, null, '', '', 'products'
                );
                $options = $assets['purchaseOptions']['options'] ?? [];
                
                $matchedOption = collect($options)->firstWhere('id', (string) $variantId);
                if ($matchedOption) {
                    // Only map to custom properties if this is a mock variant ID (e.g. "0", "1")
                    // Real Shopify variant IDs are long integers (e.g. 45318213468214)
                    if (strlen((string) $variantId) < 5) {
                        $optionLabel = 'Size'; // Standard option label
                        $optionValue = $matchedOption['title'];
                        $variantId = null; // Don't pass a mock variant ID to Shopify checkout
                    }
                }
            }

            CheckoutCart::addItem([
                'product_id' => (string) $product->id,
                'title' => $product->getTranslatedTitle($locale),
                'subtitle' => __('bundles.component_of', ['title' => $bundle->title]),
                'image' => $product->featured_image ?: asset('images/dainely-belt-product.png'),
                'price' => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'quantity' => (int) ($item->quantity ?: 1),
                'variant_id' => $variantId,
                'option_label' => $optionLabel,
                'option_value' => $optionValue,
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
