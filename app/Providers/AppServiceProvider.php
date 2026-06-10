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
        \Illuminate\Support\Facades\Blade::directive('currency', function ($expression) {
            return "<?php echo app(App\Services\CurrencyService::class)->format((float) ($expression), app()->getLocale()); ?>";
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

            $view->with([
                'headerShopifyProducts' => $payload['products'] ?? [],
                'headerShopifyProductsError' => $payload['error'] ?? null,
                'featuredShopifyProduct' => $payload['featured'] ?? null,
            ]);
        });
    }
}

