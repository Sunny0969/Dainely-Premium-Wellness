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
        // Fail-fast Postgres connects (Supabase) — avoid 30s PHP fatal on hung TCP.
        $this->app->bind('db.connector.pgsql', \App\Database\PostgresConnector::class);

        // PG boolean bindings: keep true/false (not 0/1) for emulate-prepares / pooler.
        \Illuminate\Database\Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new \App\Database\SupabasePostgresConnection($connection, $database, $prefix, $config);
        });

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
        // Web only — never cap CLI (php artisan serve / queue / migrate are long-running).
        if (! $this->app->runningInConsole()) {
            @ini_set('max_execution_time', '60');
            @set_time_limit(60);
        }

        if (is_file(public_path('build/manifest.json')) && is_file(public_path('hot'))) {
            @unlink(public_path('hot'));
        }

        // Cloudflare Rocket Loader breaks Vite ES modules; skip it on app bundles.
        Vite::useScriptTagAttributes([
            'data-cfasync' => 'false',
        ]);

        View::share('adminBase', 'dainely-admin-panel');

        View::composer(['partials.header', 'partials.footer', 'partials.cart-nav-link', 'checkout.index'], function ($view) {
            $view->with('cartItemCount', CheckoutCart::itemCount());
        });

        // Share Shopify products with the site header dropdown (once per request).
        View::composer(['partials.header', 'partials.footer', 'blog.show'], function ($view) {
            static $shared = null;

            if ($shared === null) {
                /** @var ShopifyService $shopify */
                $shopify = app(ShopifyService::class);

                $cacheKey = 'header_shopify_products_v2';
                $ttlSeconds = 15 * 60;

                $payload = Cache::remember($cacheKey, $ttlSeconds, function () use ($shopify) {
                    $shopifyResult = $shopify->fetchProducts(50);
                    if (($shopifyResult['success'] ?? false) === true) {
                        $raw = $shopifyResult['products'] ?? [];
                        $mapped = \App\Support\ProductVisibility::filterShopifyProducts(
                            $shopify->mapProductsForDisplay($raw)
                        );
                        $featured = null;
                        foreach ($raw as $product) {
                            $handle = (string) ($product['handle'] ?? '');
                            if (($product['status'] ?? 'active') === 'active'
                                && ! \App\Support\ProductVisibility::isHandleHidden($handle)) {
                                $featured = $shopify->mapProductForCta($product);
                                break;
                            }
                        }

                        return [
                            'products' => $mapped,
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

                $shared = [
                    'headerShopifyProducts' => $products,
                    'headerShopifyProductsError' => $payload['error'] ?? null,
                    'featuredShopifyProduct' => $featured,
                ];
            }

            $view->with($shared);
        });
    }
}

