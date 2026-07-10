<?php

namespace App\Providers;

use App\Services\ProductTranslationService;
use App\Services\ShopifyService;
use App\Support\CheckoutCart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Hosting: subdomain docroot is dev.dainelylab.com/ (not public/).
        $customPublic = env('APP_PUBLIC_PATH');
        if (is_string($customPublic) && $customPublic !== '' && is_dir($customPublic)) {
            $this->app->usePublicPath($customPublic);

            return;
        }

        $alternatePublic = base_path('dev.dainelylab.com');
        $defaultManifest = base_path('public/build/manifest.json');
        $alternateManifest = $alternatePublic.'/build/manifest.json';

        if (is_file($alternateManifest) && ! is_file($defaultManifest) && is_dir($alternatePublic)) {
            $this->app->usePublicPath($alternatePublic);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (is_file(public_path('build/manifest.json')) && is_file(public_path('hot'))) {
            @unlink(public_path('hot'));
        }

        // Cloudflare Rocket Loader breaks Vite ES modules; skip it on app bundles.
        Vite::useScriptTagAttributes([
            'data-cfasync' => 'false',
        ]);

        View::composer(['partials.header', 'partials.footer', 'partials.cart-nav-link', 'checkout.index'], function ($view) {
            $view->with('cartItemCount', CheckoutCart::itemCount());
        });

        // Share Shopify products with the site header dropdown.
        View::composer(['partials.header', 'partials.footer', 'blog.show'], function ($view) {
            /** @var ShopifyService $shopify */
            $shopify = app(ShopifyService::class);

            $cacheKey = 'header_shopify_products_v2';
            $ttlSeconds = 15 * 60; // keep fast and avoid hammering Shopify

            $payload = Cache::remember($cacheKey, $ttlSeconds, function () use ($shopify) {
                $shopifyResult = $shopify->fetchProducts(50);
                if (($shopifyResult['success'] ?? false) === true) {
                    $raw = $shopifyResult['products'] ?? [];
                    $featured = null;
                    foreach ($raw as $product) {
                        if (($product['status'] ?? 'active') === 'active') {
                            $featured = $shopify->mapProductForCta($product);
                            break;
                        }
                    }

                    return [
                        'products' => $shopify->mapProductsForDisplay($raw),
                        'featured' => $featured,
                        'error' => null,
                    ];
                }

                return [
                    'products' => [],
                    'featured' => null,
                    'error' => $shopifyResult['error'] ?? 'Could not load products from Shopify.',
                ];
            });

            $locale = app()->getLocale();
            /** @var ProductTranslationService $productTranslations */
            $productTranslations = app(ProductTranslationService::class);
            $products = $productTranslations->applyMany($payload['products'] ?? [], $locale);
            $featured = $payload['featured'] ?? null;
            if (is_array($featured)) {
                $featured = $productTranslations->apply($featured, $locale);
            }

            $view->with([
                'headerShopifyProducts' => $products,
                'headerShopifyProductsError' => $payload['error'] ?? null,
                'featuredShopifyProduct' => $featured,
            ]);
        });
    }
}

