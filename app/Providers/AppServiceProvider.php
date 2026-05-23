<?php

namespace App\Providers;

use App\Services\ShopifyService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share Shopify products with the site header dropdown.
        View::composer('partials.header', function ($view) {
            /** @var ShopifyService $shopify */
            $shopify = app(ShopifyService::class);

            $cacheKey = 'header_shopify_products_v1';
            $ttlSeconds = 15 * 60; // keep fast and avoid hammering Shopify

            $payload = Cache::remember($cacheKey, $ttlSeconds, function () use ($shopify) {
                $shopifyResult = $shopify->fetchProducts(12);
                if (($shopifyResult['success'] ?? false) === true) {
                    return [
                        'products' => $shopify->mapProductsForDisplay($shopifyResult['products'] ?? []),
                        'error' => null,
                    ];
                }

                return [
                    'products' => [],
                    'error' => $shopifyResult['error'] ?? 'Could not load products from Shopify.',
                ];
            });

            $view->with([
                'headerShopifyProducts' => $payload['products'] ?? [],
                'headerShopifyProductsError' => $payload['error'] ?? null,
            ]);
        });
    }
}

