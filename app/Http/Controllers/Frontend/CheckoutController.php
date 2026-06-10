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
        $currency         = $this->currency->getCurrencyForLocale($locale);
        $cart             = CheckoutCart::get();

        // Only use a fallback when nothing was added to cart — never overwrite session cart.
        if (! CheckoutCart::exists()) {
            $cart = $this->populateFeaturedBeltFallback($cart);
        }

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
    private function populateFeaturedBeltFallback(array $cart): array
    {
        try {
            $handle = ProductSlugResolver::resolveHandle('dainely-belt');
            $result = $this->shopify->fetchProductByHandle($handle);

            if (! $result['success'] || empty($result['product'])) {
                return $cart;
            }

            $product  = $result['product'];
            $firstVar = $product['variants'][0] ?? [];
            $image    = $product['images'][0]['src']
                        ?? ($product['image']['src'] ?? null);

            $cart['product_id']       = (string) ($product['id'] ?? $handle);
            $cart['title']            = $product['title'] ?? $cart['title'];
            $cart['subtitle']         = \Illuminate\Support\Str::limit(strip_tags($product['body_html'] ?? ''), 80) ?: $cart['subtitle'];
            $cart['image']            = $image ?: $cart['image'];
            $cart['price']            = (float) ($firstVar['price'] ?? $cart['price']);
            $cart['compare_at_price'] = isset($firstVar['compare_at_price']) ? (float) $firstVar['compare_at_price'] : $cart['compare_at_price'];
            $cart['option_label']     = $firstVar['title'] ?? null;
            $cart['variant_id']       = isset($firstVar['id']) ? (string) $firstVar['id'] : null;
            $cart['source']           = 'shopify';

            CheckoutCart::put($cart);
        } catch (\Throwable $e) {
            Log::warning('Could not populate fallback cart from Shopify', ['error' => $e->getMessage()]);
        }

        return $cart;
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
            'qty'             => 'required|integer|min:1|max:20',
            'amount_cents'    => 'required|integer|min:100',
            'discount_code'   => 'nullable|string|max:50',
            'shipping_method' => 'nullable|string|max:50',
        ]);

        $locale           = App::getLocale();
        $orderRef         = 'DLY-' . strtoupper(\Illuminate\Support\Str::random(8));
        $cart             = CheckoutCart::get();
        $qty              = (int) $validated['qty'];
        $shippingMethod   = $validated['shipping_method'] ?? 'standard';
        $totals           = $this->calculateCheckoutTotals($cart, $qty, $shippingMethod);
        $expectedCents    = (int) round($totals['total'] * 100);
        $amountCents      = (int) $validated['amount_cents'];

        if (abs($expectedCents - $amountCents) > 1) {
            Log::warning('Checkout amount mismatch', [
                'expected' => $expectedCents,
                'received' => $amountCents,
                'cart'     => $cart['title'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order total changed. Please refresh checkout and try again.',
            ], 422);
        }

        $currency = $this->currency->getCurrencyForLocale($locale);

        // Charge via Square
        $payment = $this->square->createPayment(
            $validated['source_id'],
            $amountCents,
            $orderRef,
            $currency['code'] ?? 'USD'
        );

        if (! $payment['success']) {
            $errorMsg = $payment['errors'][0]['detail'] ?? 'Payment declined.';
            Log::warning('Checkout payment failed', ['ref' => $orderRef, 'error' => $errorMsg]);

            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }

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
                'qty'               => $qty,
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
            'order_ref'   => $orderRef,
            'payment_id'  => $payment['payment_id'],
            'amount'      => $amountCents,
            'email'       => $validated['email'],
            'square_env'  => $this->square->getEnvironment(),
            'square_mock' => $payment['mock'] ?? false,
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
                'qty'                  => $qty,
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
    private function calculateCheckoutTotals(array $cart, int $qty, string $shippingMethod): array
    {
        $unitPrice = (float) ($cart['price'] ?? 0);
        $subtotal  = round($unitPrice * $qty, 2);
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
        $cart      = is_array($lastOrder['cart'] ?? null) ? $lastOrder['cart'] : CheckoutCart::get();
        $qty       = (int) ($cart['quantity'] ?? ($lastOrder['qty'] ?? 1));
        $price     = (float) ($cart['price'] ?? 89);
        $subtotal  = $price * $qty;

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
            'items'               => collect([
                (object) [
                    'product_name'    => $cart['title'] ?? 'Dainely Belt',
                    'quantity'        => $qty,
                    'total_price_usd' => $subtotal,
                    'image_url'       => $cart['image'] ?? asset('images/dainely-belt-product.png'),
                ],
            ]),
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
