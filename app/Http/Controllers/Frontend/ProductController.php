<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ShopifyService;
use App\Support\ProductSlugResolver;

class ProductController extends Controller
{
    public function __construct(protected ShopifyService $shopify) {}

    public function index(string $locale)
    {
        $result = $this->shopify->fetchProducts(50);
        $products = $result['success']
            ? $this->shopify->mapProductsForDisplay($result['products'])
            : [];
        $error = $result['success'] ? null : ($result['error'] ?? 'Could not load products from Shopify.');

        return view('pages.products.index', compact('products', 'locale', 'error'));
    }

    public function show(string $locale, string $slug)
    {
        $handle = ProductSlugResolver::resolveHandle($slug);
        $shopifyResult = $this->shopify->fetchProductByHandle($handle);

        if (! $shopifyResult['success'] || empty($shopifyResult['product'])) {
            abort(404);
        }

        $product = $shopifyResult['product'];

        return view('products.show', ['product' => $product]);
    }
}
