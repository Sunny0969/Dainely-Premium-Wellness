<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SquareService;
use App\Services\ShopifyService;
use App\Services\CurrencyService;
use App\Support\CheckoutCart;
use App\Support\ProductSlugResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected SquareService $square,
        protected ShopifyService $shopify,
        protected CurrencyService $currency
    ) {}

    /**
     * Show the checkout page.
     */
    public function index()
    {
        if (request()->getHost() === '127.0.0.1' && app()->environment('local')) {
            return redirect()->to(str_replace('127.0.0.1', 'localhost', request()->fullUrl()));
        }

        $squareAppId      = $this->square->getApplicationId();
        $squareEnv        = $this->square->getEnvironment();
        $squareLocationId = $this->square->getLocationId();
        $squareConfigured = $this->square->isConfigured();
        $locale           = App::getLocale();
        $currencyCode     = $this->currency->getCurrencyForLocale($locale);
        $currencySymbol   = config("currency.supported.{$currencyCode}.symbol", '$');
        $currency         = [
            'code'   => $currencyCode,
            'symbol' => $currencySymbol,
        ];
        
        $cart = CheckoutCart::get();

        // Only use a fallback when nothing was added to cart — never overwrite session cart.
        if (! CheckoutCart::exists()) {
            $cart = $this->populateFeaturedBeltFallback();
        }

        // Convert cart item prices to active currency for checkout view and Alpine.js
        foreach ($cart as &$item) {
            $item['price'] = $this->currency->convert((float) $item['price'], $currencyCode);
            if (! empty($item['compare_at_price'])) {
                $item['compare_at_price'] = $this->currency->convert((float) $item['compare_at_price'], $currencyCode);
            }
        }
        unset($item);

        return view('checkout.index', compact(
            'squareAppId',
            'squareEnv',
            'squareLocationId',
            'squareConfigured',
            'locale',
            'currency',
            'cart'
        ));
    }

    /**
     * Fallback when checkout is opened without Add to Cart — featured belt only.
     */
    private function populateFeaturedBeltFallback(): array
    {
        try {
            $handle = ProductSlugResolver::resolveHandle('dainely-belt');
            $result = $this->shopify->fetchProductByHandle($handle);

            if (! $result['success'] || empty($result['product'])) {
                return CheckoutCart::get();
            }

            $product  = $result['product'];
            $firstVar = $product['variants'][0] ?? [];
            $image    = $product['images'][0]['src']
                        ?? ($product['image']['src'] ?? null);

            $fallbackItem = [
                'product_id'       => (string) ($product['id'] ?? $handle),
                'title'            => $product['title'] ?? 'Dainely Belt',
                'subtitle'         => \Illuminate\Support\Str::limit(strip_tags($product['body_html'] ?? ''), 80) ?: 'Premium Lumbar Support',
                'image'            => $image ?: asset('images/dainely-belt-product.png'),
                'price'            => (float) ($firstVar['price'] ?? 89.00),
                'compare_at_price' => isset($firstVar['compare_at_price']) ? (float) $firstVar['compare_at_price'] : 119.00,
                'option_label'     => $firstVar['title'] ?? null,
                'variant_id'       => isset($firstVar['id']) ? (string) $firstVar['id'] : null,
                'source'           => 'shopify',
                'quantity'         => 1,
            ];

            CheckoutCart::clear();
            CheckoutCart::add($fallbackItem);
        } catch (\Throwable $e) {
            Log::warning('Could not populate fallback cart from Shopify', ['error' => $e->getMessage()]);
        }

        return CheckoutCart::get();
    }

    /**
     * Process the checkout: tokenize → charge Square → Shopify order → confirmation.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'source_id'       => 'required|string',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'address1'        => 'required|string|max:255',
            'address2'        => 'nullable|string|max:255',
            'city'            => 'required|string|max:100',
            'state'           => 'nullable|string|max:100',
            'zip'             => 'required|string|max:20',
            'country'         => 'required|string|max:2',
            'amount_cents'    => 'required|integer|min:100',
            'discount_code'   => 'nullable|string|max:50',
            'shipping_method' => 'nullable|string|max:50',
            'items'           => 'required|array',
            'items.*.variant_id' => 'nullable|string',
            'items.*.product_id' => 'required|string',
            'items.*.quantity'   => 'required|integer|min:1|max:20',
        ]);

        $locale           = App::getLocale();
        $orderRef         = 'DLY-' . strtoupper(\Illuminate\Support\Str::random(8));
        $shippingMethod   = $validated['shipping_method'] ?? 'standard';
        
        // 1. Sync / update session cart items and quantities with what was submitted
        $cart = CheckoutCart::get();
        foreach ($validated['items'] as $submittedItem) {
            foreach ($cart as &$cartItem) {
                $match = (!empty($submittedItem['variant_id']) && $cartItem['variant_id'] === $submittedItem['variant_id'])
                    || (empty($submittedItem['variant_id']) && $cartItem['product_id'] === $submittedItem['product_id']);
                if ($match) {
                    $cartItem['quantity'] = (int) $submittedItem['quantity'];
                }
            }
        }
        unset($cartItem);
        CheckoutCart::put($cart);

        // 2. Calculate totals in base currency (USD)
        $totals           = $this->calculateCheckoutTotals($cart, $shippingMethod);
        
        // 3. Convert expected total (USD) to target currency (e.g. EUR) for matching amount_cents
        $currencyCode     = $this->currency->getCurrencyForLocale($locale);
        $expectedConverted = $this->currency->convert($totals['total'], $currencyCode);
        $expectedCents    = (int) round($expectedConverted * 100);
        $amountCents      = (int) $validated['amount_cents'];

        if (abs($expectedCents - $amountCents) > 1) {
            Log::warning('Checkout amount mismatch', [
                'expected' => $expectedCents,
                'received' => $amountCents,
                'locale'   => $locale,
                'currency' => $currencyCode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order total changed. Please refresh checkout and try again.',
            ], 422);
        }

        // Charge via Square in target currency (EUR or USD)
        $payment = $this->square->createPayment(
            $validated['source_id'],
            $amountCents,
            $orderRef,
            $currencyCode
        );

        if (! $payment['success']) {
            $errorMsg = $payment['errors'][0]['detail'] ?? 'Payment declined.';
            Log::warning('Checkout payment failed', ['ref' => $orderRef, 'error' => $errorMsg]);

            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }

        // 4. Create Shopify order
        $shopifyOrder = null;
        try {
            $shopifyOrder = $this->shopify->createOrderFromCheckout([
                'order_number'      => $orderRef,
                'email'             => $validated['email'],
                'first_name'        => $validated['first_name'],
                'last_name'         => $validated['last_name'],
                'phone'             => $validated['phone'] ?? null,
                'address1'          => $validated['address1'],
                'address2'          => $validated['address2'] ?? null,
                'city'              => $validated['city'],
                'state'             => $validated['state'] ?? null,
                'zip'               => $validated['zip'],
                'country'           => $validated['country'],
                'cart'              => $cart,
                'shipping_method'   => $shippingMethod,
                'shipping_usd'      => $totals['shipping'],
                'total_usd'         => $totals['total'],
                'discount_code'     => $validated['discount_code'] ?? null,
                'square_payment_id' => $payment['payment_id'] ?? null,
                'locale'            => $locale,
            ]);
        } catch (\Throwable $e) {
            Log::error('Shopify order sync failed after payment', [
                'ref'   => $orderRef,
                'error' => $e->getMessage(),
            ]);
        }

        if ($shopifyOrder) {
            Log::info('Shopify order synced', [
                'dainely_ref'    => $orderRef,
                'shopify_id'     => $shopifyOrder['id'] ?? null,
                'shopify_number' => $shopifyOrder['order_number'] ?? null,
            ]);
        } else {
            Log::warning('Shopify order not created — payment captured', ['ref' => $orderRef]);
        }

        Log::info('Checkout payment success', [
            'order_ref'  => $orderRef,
            'payment_id' => $payment['payment_id'],
            'amount'     => $amountCents,
            'email'      => $validated['email'],
        ]);

        session([
            'checkout.last_order' => [
                'order_ref'            => $orderRef,
                'payment_id'           => $payment['payment_id'] ?? null,
                'shopify_order_id'     => $shopifyOrder['id'] ?? null,
                'shopify_order_number' => $shopifyOrder['order_number'] ?? null,
                'shopify_order_name'   => $shopifyOrder['name'] ?? null,
                'shopify_sync_failed'  => $shopifyOrder === null && config('shopify.sync_orders', true),
                'email'                => $validated['email'],
                'first_name'           => $validated['first_name'],
                'last_name'            => $validated['last_name'],
                'phone'                => $validated['phone'] ?? null,
                'address1'             => $validated['address1'],
                'address2'             => $validated['address2'] ?? null,
                'city'                 => $validated['city'],
                'state'                => $validated['state'] ?? null,
                'zip'                  => $validated['zip'],
                'country'              => $validated['country'],
                'cart'                 => $cart,
                'amount_cents'         => $amountCents,
                'discount_code'        => $validated['discount_code'] ?? null,
                'shipping_method'      => $shippingMethod,
            ],
        ]);

        CheckoutCart::clear();

        return response()->json([
            'success'  => true,
            'redirect' => route('checkout.confirmation', [
                'locale' => $locale,
                'order'  => $orderRef,
            ]),
        ]);
    }

    /**
     * @return array{subtotal: float, shipping: float, total: float}
     */
    private function calculateCheckoutTotals(array $cart, string $shippingMethod): array
    {
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $unitPrice = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $subtotal += round($unitPrice * $qty, 2);
        }
        $subtotal  = round($subtotal, 2);
        $shipping  = $this->shopify->estimateShippingUsd($subtotal, $shippingMethod);
        $total     = round($subtotal + $shipping, 2);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
        ];
    }

    /**
     * Order confirmation page.
     */
    public function confirmation(string $locale, string $order)
    {
        $lastOrder = session('checkout.last_order');
        $cartItems = is_array($lastOrder['cart'] ?? null) 
            ? (isset($lastOrder['cart'][0]) ? $lastOrder['cart'] : [$lastOrder['cart']]) 
            : CheckoutCart::get();

        $subtotal = 0.0;
        $itemsCollection = collect();

        foreach ($cartItems as $item) {
            $itemQty = (int) ($item['quantity'] ?? 1);
            $itemPrice = (float) ($item['price'] ?? 0);
            $itemSubtotal = $itemPrice * $itemQty;
            $subtotal += $itemSubtotal;

            $itemsCollection->push((object) [
                'product_name'    => $item['title'] ?? 'Dainely Product',
                'quantity'        => $itemQty,
                'total_price_usd' => $itemSubtotal,
                'image_url'       => $item['image'] ?? asset('images/dainely-belt-product.png'),
            ]);
        }

        $totalCents = (int) ($lastOrder['amount_cents'] ?? ($subtotal * 100));
        $totalUsd   = $totalCents / 100;
        
        $shippingMethod = $lastOrder['shipping_method'] ?? 'standard';
        $shippingUsd    = $shippingMethod === 'express' ? 24.99 : ($subtotal >= 75 ? 0.00 : 9.99);
        $discountUsd    = max(0.00, round(($subtotal + $shippingUsd) - $totalUsd, 2));

        $order = (object) [
            'order_number'        => $order,
            'shopify_order_name'  => $lastOrder['shopify_order_name'] ?? null,
            'shopify_sync_failed' => (bool) ($lastOrder['shopify_sync_failed'] ?? false),
            'customer_first_name' => $lastOrder['first_name'] ?? 'Guest',
            'customer_email'      => $lastOrder['email'] ?? 'guest@example.com',
            'shipping_address1'   => $lastOrder['address1'] ?? '123 Main St',
            'shipping_city'       => $lastOrder['city'] ?? 'New York',
            'shipping_country'    => $lastOrder['country'] ?? 'US',
            'subtotal_usd'        => $subtotal,
            'shipping_usd'        => $shippingUsd,
            'discount_amount_usd' => $discountUsd,
            'discount_code'       => $lastOrder['discount_code'] ?? null,
            'shipping_method'     => $shippingMethod,
            'total_usd'           => $totalUsd,
            'items'               => $itemsCollection,
        ];

        return view('checkout.confirmation', compact('order'));
    }

    /**
     * Validate a discount code via AJAX.
     */
    public function validateDiscount(Request $request)
    {
        return response()->json([
            'valid'   => false,
            'message' => __('checkout.invalid_discount'),
        ]);
    }
}
