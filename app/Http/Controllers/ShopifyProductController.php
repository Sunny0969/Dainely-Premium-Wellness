<?php

namespace App\Http\Controllers;

use App\Services\ShopifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ShopifyProductController extends Controller
{
    public function __construct(protected ShopifyService $shopify) {}

    /**
     * Step 1: client_credentials → shpat_ token
     * Step 2: Admin API products.json → blade view
     */
    public function index(Request $request): View|JsonResponse
    {
        $shop = config('shopify.store_domain');
        $limit = max(1, min((int) $request->query('limit', 50), 250));
        $apiVersion = config('shopify.api_version', '2024-01');

        $meta = [
            'shop_domain'    => $shop,
            'api_version'    => $apiVersion,
            'limit'          => $limit,
            'product_count'  => 0,
            'source'         => null,
        ];

        // --- STEP 1: Get access token (client_credentials) ---
        $auth = $this->shopify->requestAccessTokenViaClientCredentials();

        if (! $auth['success'] || empty($auth['token'])) {
            Log::error('Shopify Auth Failed: ' . ($auth['error'] ?? 'unknown'));

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to authenticate with Shopify', 'detail' => $auth['error']], 401);
            }

            return view('products.index', [
                'products' => [],
                'error'    => $auth['error'] ?? 'Failed to authenticate with Shopify',
                'meta'     => $meta,
            ]);
        }

        $accessToken = $auth['token'];

        // --- STEP 2: Fetch products with shpat_ token ---
        $result = $this->shopify->fetchProductsFromAdminApi($limit, $accessToken);

        $meta['source'] = 'client_credentials';
        $meta['product_count'] = count($result['products']);

        if (! $result['success']) {
            Log::error('Shopify products fetch failed: ' . ($result['error'] ?? ''));

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch products', 'detail' => $result['error']], $result['status']);
            }

            return view('products.index', [
                'products' => [],
                'error'    => $result['error'] ?? 'Failed to fetch products',
                'meta'     => $meta,
            ]);
        }

        $products = $result['products'];

        if ($request->expectsJson()) {
            return response()->json(['products' => $products, 'meta' => $meta]);
        }

        return view('products.index', compact('products', 'meta'))->with('error', null);
    }

    /**
     * Show a single Shopify product detail page.
     */
    public function show(Request $request, int|string $id): View|JsonResponse
    {
        // Get access token
        $auth = $this->shopify->requestAccessTokenViaClientCredentials();
        $accessToken = ($auth['success'] && !empty($auth['token'])) ? $auth['token'] : null;

        $result = $this->shopify->fetchProductById($id, $accessToken);

        if (!$result['success'] || empty($result['product'])) {
            abort(404, 'Product not found.');
        }

        $product = $result['product'];

        return view('products.show', compact('product'));
    }
}
