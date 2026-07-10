<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOrderToShopifyJob;
use App\Services\CurrencyService;
use App\Services\OrderPersistenceService;
use App\Services\ShopifyService;
use App\Services\ShopifyTaxService;
use App\Services\SquareService;
use App\Support\CheckoutCart;
use App\Support\CheckoutTotals;
use App\Support\PostalCode;
use App\Support\ProductSlugResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected SquareService $square,
        protected ShopifyService $shopify,
        protected ShopifyTaxService $shopifyTax,
        protected CurrencyService $currency,
        protected CheckoutTotals $totals,
        protected OrderPersistenceService $orderPersistence,
    ) {}

    public function index()
    {
        if (request()->getHost() === '127.0.0.1' && app()->environment('local')) {
            return redirect()->to(str_replace('127.0.0.1', 'localhost', request()->fullUrl()));
        }

        $locale = App::getLocale();

        if (! CheckoutCart::exists()) {
            $this->populateFeaturedBeltFallback();
        }

        $rawItems     = CheckoutCart::getItems();
        $items        = $this->totals->itemsForDisplay($rawItems, $locale);
        $initialTaxUsd = $this->shopifyTax->estimateInitialUsd($rawItems, $locale, 'standard');
        $pricing      = $this->totals->calculate($rawItems, 'standard', $locale, 0, $initialTaxUsd);
        $currencyMeta = $this->currency->getCurrencyMeta($pricing['currency_code']);
        $pricing['standard_shipping_rate'] = $this->currency->convert(9.99, $pricing['currency_code']);
        $pricing['express_shipping_rate']  = $this->currency->convert(24.99, $pricing['currency_code']);

        $this->storeCurrencySnapshot($pricing);

        return view('checkout.index', [
            'squareAppId'      => $this->square->getApplicationId(),
            'squareEnv'        => $this->square->getEnvironment(),
            'squareLocationId' => $this->square->getLocationId(),
            'squareConfigured' => $this->square->isConfigured(),
            'locale'           => $locale,
            'cartItems'        => $items,
            'pricing'          => $pricing,
            'currencyMeta'     => $currencyMeta,
        ]);
    }

    public function estimateTax(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'nullable|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:255',
            'address1'          => 'required|string|max:255',
            'city'              => 'required|string|max:100',
            'state'             => 'nullable|string|max:100',
            'zip'               => 'required|string|max:20',
            'country'           => 'required|string|size:2',
            'shipping_method'   => 'nullable|string|max:50',
            'line_quantities'   => 'nullable|array',
            'line_quantities.*' => 'integer|min:0|max:20',
            'discount_code'     => 'nullable|string|max:50',
        ]);

        $postalError = $this->postalValidationError($validated['country'], $validated['zip']);
        if ($postalError !== null) {
            return response()->json(['success' => false, 'message' => $postalError, 'errors' => ['zip' => $postalError]], 422);
        }

        $locale         = App::getLocale();
        $shippingMethod = $validated['shipping_method'] ?? 'standard';

        if (! empty($validated['line_quantities']) && is_array($validated['line_quantities'])) {
            CheckoutCart::updateQuantities($validated['line_quantities']);
        }

        $items = CheckoutCart::getItems();
        if ($items === []) {
            return response()->json(['success' => false, 'message' => __('checkout.cart_empty')], 422);
        }

        $address = [
            'first_name' => $validated['first_name'] ?? '',
            'last_name'  => $validated['last_name'] ?? '',
            'email'      => $validated['email'] ?? '',
            'address1'   => $validated['address1'],
            'address2'   => $validated['address2'] ?? '',
            'city'       => $validated['city'],
            'state'      => $validated['state'] ?? '',
            'zip'        => $validated['zip'],
            'country'    => strtoupper($validated['country']),
        ];

        $subtotalUsd = 0.0;
        foreach ($items as $item) {
            $subtotalUsd += (float) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1));
        }

        $discountUsd = 0.0;
        $discountCode = trim((string) ($validated['discount_code'] ?? ''));
        $forceFreeShipping = false;
        if ($discountCode !== '') {
            $discountResult = $this->shopify->validateDiscountCode($discountCode, $subtotalUsd);
            if ($discountResult['valid'] ?? false) {
                $discountUsd = (float) ($discountResult['discount_usd'] ?? 0);
                $forceFreeShipping = (bool) ($discountResult['free_shipping'] ?? false);
            }
        }

        $taxResult = $this->shopifyTax->estimate($items, $address, $shippingMethod, $discountUsd);

        if (! ($taxResult['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $taxResult['error'] ?? __('checkout.tax_estimate_failed'),
            ], 422);
        }

        $totals = $this->totals->calculate($items, $shippingMethod, $locale, $discountUsd, $taxResult['tax_usd'], $forceFreeShipping);

        $this->storeCurrencySnapshot($totals);

        return response()->json([
            'success'   => true,
            'tax_usd'   => $totals['tax_usd'],
            'tax'       => $totals['tax'],
            'tax_lines' => $taxResult['tax_lines'] ?? [],
            'cached'    => $taxResult['cached'] ?? false,
            'totals'    => $totals,
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'source_id'         => 'required|string',
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'email'             => 'required|email|max:255',
            'phone'             => 'nullable|string|max:20',
            'address1'          => 'required|string|max:255',
            'address2'          => 'nullable|string|max:255',
            'city'              => 'required|string|max:100',
            'state'             => 'nullable|string|max:100',
            'zip'               => 'required|string|max:20',
            'country'           => 'required|string|max:2',
            'line_quantities'   => 'nullable|array',
            'line_quantities.*' => 'integer|min:0|max:20',
            'amount_cents'      => 'required|integer|min:100',
            'discount_code'     => 'nullable|string|max:50',
            'shipping_method'   => 'nullable|string|max:50',
            'currency_code'     => 'nullable|string|size:3',
        ]);

        $postalError = $this->postalValidationError($validated['country'], $validated['zip']);
        if ($postalError !== null) {
            return response()->json(['success' => false, 'message' => $postalError], 422);
        }

        $locale         = App::getLocale();
        $orderRef       = 'DLY-' . strtoupper(\Illuminate\Support\Str::random(8));
        $shippingMethod = $validated['shipping_method'] ?? 'standard';

        if (! empty($validated['line_quantities']) && is_array($validated['line_quantities'])) {
            CheckoutCart::updateQuantities($validated['line_quantities']);
        }

        $items = CheckoutCart::getItems();
        if ($items === []) {
            return response()->json([
                'success' => false,
                'message' => __('checkout.amount_mismatch'),
            ], 422);
        }

        $address = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'address1'   => $validated['address1'],
            'address2'   => $validated['address2'] ?? '',
            'city'       => $validated['city'],
            'state'      => $validated['state'] ?? '',
            'zip'        => $validated['zip'],
            'country'    => strtoupper($validated['country']),
        ];

        $subtotalUsd = 0.0;
        foreach ($items as $item) {
            $subtotalUsd += (float) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1));
        }

        $discountUsd  = 0.0;
        $discountCode = trim((string) ($validated['discount_code'] ?? ''));
        $resolvedCode = null;
        $forceFreeShipping = false;
        if ($discountCode !== '') {
            $discountResult = $this->shopify->validateDiscountCode($discountCode, $subtotalUsd);
            if (! ($discountResult['valid'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $discountResult['message'] ?? __('checkout.invalid_discount'),
                ], 422);
            }
            $discountUsd  = (float) ($discountResult['discount_usd'] ?? 0);
            $resolvedCode = $discountResult['code'] ?? $discountCode;
            $forceFreeShipping = (bool) ($discountResult['free_shipping'] ?? false);
        }

        $taxResult = $this->shopifyTax->estimate($items, $address, $shippingMethod, $discountUsd);
        $taxUsd    = ($taxResult['success'] ?? false) ? (float) ($taxResult['tax_usd'] ?? 0) : 0.0;
        $taxLines  = $taxResult['tax_lines'] ?? [];

        if (! ($taxResult['success'] ?? false) && config('shopify.tax_enabled', true)) {
            Log::warning('Checkout proceeding with zero tax — estimate failed', [
                'ref'   => $orderRef,
                'error' => $taxResult['error'] ?? 'unknown',
            ]);
        }

        $totals             = $this->totals->calculate($items, $shippingMethod, $locale, $discountUsd, $taxUsd, $forceFreeShipping);
        $expectedCents      = $totals['amount_cents'];
        $amountCents        = (int) $validated['amount_cents'];
        $displayCurrency    = $totals['currency_code'];
        $squareCurrency     = $this->square->getChargeCurrency();
        $squareCents        = (int) round($totals['total_usd'] * 100);

        $this->storeCurrencySnapshot($totals);

        if (! empty($validated['currency_code']) && strtoupper($validated['currency_code']) !== $displayCurrency) {
            return response()->json([
                'success' => false,
                'message' => __('checkout.amount_mismatch'),
            ], 422);
        }

        if (abs($expectedCents - $amountCents) > 1) {
            Log::warning('Checkout amount mismatch', [
                'expected'          => $expectedCents,
                'received'          => $amountCents,
                'display_currency'  => $displayCurrency,
                'square_cents'      => $squareCents,
                'square_currency'   => $squareCurrency,
                'tax_usd'           => $taxUsd,
                'exchange_rate'     => $totals['exchange_rate'] ?? null,
                'items'             => count($items),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('checkout.amount_mismatch'),
            ], 422);
        }

        $payment = $this->square->createPayment(
            $validated['source_id'],
            $squareCents,
            $orderRef,
            $squareCurrency
        );

        if (! $payment['success']) {
            $errorMsg = $payment['errors'][0]['detail'] ?? 'Payment declined.';
            Log::warning('Checkout payment failed', ['ref' => $orderRef, 'error' => $errorMsg]);

            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }

        $shopifyPayload = [
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
            'country'           => strtoupper($validated['country']),
            'items'             => $items,
            'shipping_method'   => $shippingMethod,
            'shipping_usd'      => $totals['shipping_usd'],
            'tax_usd'           => $totals['tax_usd'],
            'tax_lines'         => $taxLines,
            'total_usd'         => $totals['total_usd'],
            'discount_usd'      => $totals['discount_usd'],
            'charge_currency'   => $squareCurrency,
            'display_currency'  => $displayCurrency,
            'display_subtotal'  => $totals['subtotal'],
            'display_shipping'  => $totals['shipping'],
            'display_tax'       => $totals['tax'],
            'display_total'     => $totals['total'],
            'display_discount'  => $this->currency->convert($totals['discount_usd'] ?? 0, $displayCurrency),
            'exchange_rate'     => $totals['exchange_rate'],
            'discount_code'     => $resolvedCode,
            'square_payment_id' => $payment['payment_id'] ?? null,
            'locale'            => $locale,
        ];

        $shopifyResult = ['success' => false, 'order' => null, 'error' => null];
        try {
            $shopifyResult = $this->shopify->createOrderFromCheckout($shopifyPayload);
        } catch (\Throwable $e) {
            Log::error('Shopify order sync failed after payment', [
                'ref'   => $orderRef,
                'error' => $e->getMessage(),
            ]);
            $shopifyResult = ['success' => false, 'order' => null, 'error' => $e->getMessage()];
        }

        $shopifyOrder = $shopifyResult['order'] ?? null;

        if ($shopifyOrder) {
            Log::info('Shopify order synced', [
                'dainely_ref'    => $orderRef,
                'shopify_id'     => $shopifyOrder['id'] ?? null,
                'shopify_number' => $shopifyOrder['order_number'] ?? null,
                'shopify_name'   => $shopifyOrder['name'] ?? null,
                'tax_usd'        => $totals['tax_usd'],
            ]);
        } else {
            Log::warning('Shopify order not created — payment captured, queueing retry', [
                'ref'   => $orderRef,
                'error' => $shopifyResult['error'] ?? 'unknown',
            ]);
            try {
                SyncOrderToShopifyJob::dispatch($orderRef);
            } catch (\Throwable $e) {
                Log::warning('Shopify sync job dispatch failed', [
                    'ref'   => $orderRef,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            $this->orderPersistence->savePaidOrder($orderRef, [
                'locale'              => $locale,
                'square_payment_id'   => $payment['payment_id'] ?? null,
                'shopify_synced'      => $shopifyOrder !== null,
                'shopify_order_id'    => $shopifyOrder['id'] ?? null,
                'shopify_order_name'  => $shopifyOrder['name'] ?? null,
                'discount_code'       => $resolvedCode,
                'items'               => $items,
                'totals'              => $totals,
                'customer'            => array_merge($address, ['phone' => $validated['phone'] ?? null]),
                'shopify_payload'     => $shopifyPayload,
            ]);
        } catch (\Throwable $e) {
            Log::error('Local order persistence failed after payment', [
                'ref'   => $orderRef,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Checkout payment success', [
            'order_ref'        => $orderRef,
            'payment_id'       => $payment['payment_id'],
            'square_cents'     => $squareCents,
            'square_currency'  => $squareCurrency,
            'display_cents'    => $amountCents,
            'display_currency' => $displayCurrency,
            'exchange_rate'    => $totals['exchange_rate'],
            'tax_usd'          => $totals['tax_usd'],
            'email'            => $validated['email'],
            'item_count'       => count($items),
            'square_env'       => $this->square->getEnvironment(),
        ]);

        session([
            'checkout.last_order' => [
                'order_ref'            => $orderRef,
                'payment_id'           => $payment['payment_id'] ?? null,
                'shopify_order_id'     => $shopifyOrder['id'] ?? null,
                'shopify_order_number' => $shopifyOrder['order_number'] ?? null,
                'shopify_order_name'   => $shopifyOrder['name'] ?? null,
                'shopify_sync_failed'  => $shopifyOrder === null && config('shopify.sync_orders', true),
                'shopify_sync_error'   => $shopifyResult['error'] ?? null,
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
                'items'                => $items,
                'amount_cents'         => $amountCents,
                'currency_code'        => $displayCurrency,
                'square_amount_cents'  => $squareCents,
                'square_currency_code' => $squareCurrency,
                'exchange_rate'        => $totals['exchange_rate'],
                'currency_symbol'      => $totals['currency_symbol'],
                'totals'               => $totals,
                'tax_lines'            => $taxLines,
                'discount_code'        => $resolvedCode,
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

    public function confirmation(string $locale, string $order)
    {
        $lastOrder = session('checkout.last_order');
        $items     = is_array($lastOrder['items'] ?? null) ? $lastOrder['items'] : CheckoutCart::getItems();
        $totals    = is_array($lastOrder['totals'] ?? null)
            ? $lastOrder['totals']
            : $this->totals->calculate($items, $lastOrder['shipping_method'] ?? 'standard', $locale);

        $currencySymbol = $lastOrder['currency_symbol'] ?? $totals['currency_symbol'];
        $currencyCode   = $lastOrder['currency_code'] ?? $totals['currency_code'];
        $displayDiscount = (float) ($totals['discount'] ?? $this->currency->convert(
            (float) ($totals['discount_usd'] ?? 0),
            $currencyCode
        ));

        $displayItems = collect($items)->map(function (array $item) use ($currencyCode) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $lineUsd = round((float) ($item['price'] ?? 0) * $qty, 2);

            return (object) [
                'product_name'    => $item['title'] ?? 'Product',
                'quantity'        => $qty,
                'total_price_usd' => $lineUsd,
                'total_price'     => $this->currency->convert($lineUsd, $currencyCode),
                'image_url'       => $item['image'] ?? asset('images/dainely-belt-product.png'),
            ];
        });

        $order = (object) [
            'order_number'        => $order,
            'shopify_order_name'  => $lastOrder['shopify_order_name'] ?? null,
            'shopify_sync_failed' => (bool) ($lastOrder['shopify_sync_failed'] ?? false),
            'shopify_sync_error'  => $lastOrder['shopify_sync_error'] ?? null,
            'customer_first_name' => $lastOrder['first_name'] ?? 'Guest',
            'customer_email'      => $lastOrder['email'] ?? 'guest@example.com',
            'shipping_address1'   => $lastOrder['address1'] ?? '',
            'shipping_city'       => $lastOrder['city'] ?? '',
            'shipping_country'    => $lastOrder['country'] ?? 'US',
            'subtotal_usd'        => $totals['subtotal_usd'],
            'subtotal'            => $totals['subtotal'],
            'shipping_usd'        => $totals['shipping_usd'],
            'shipping'            => $totals['shipping'],
            'tax_usd'             => $totals['tax_usd'] ?? 0,
            'tax'                 => $totals['tax'] ?? 0,
            'discount_amount_usd' => $totals['discount_usd'] ?? 0,
            'discount_amount'     => $displayDiscount,
            'discount_code'       => $lastOrder['discount_code'] ?? null,
            'shipping_method'     => $lastOrder['shipping_method'] ?? 'standard',
            'total_usd'           => $totals['total_usd'],
            'total'               => $totals['total'],
            'currency_symbol'     => $currencySymbol,
            'currency_code'       => $currencyCode,
            'items'               => $displayItems,
        ];

        return view('checkout.confirmation', compact('order', 'locale'));
    }

    public function validateDiscount(Request $request)
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50',
            'subtotal_usd' => 'nullable|numeric|min:0',
        ]);

        $items = CheckoutCart::getItems();
        $subtotalUsd = 0.0;
        foreach ($items as $item) {
            $subtotalUsd += (float) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1));
        }

        if ($subtotalUsd <= 0 && isset($validated['subtotal_usd'])) {
            $subtotalUsd = (float) $validated['subtotal_usd'];
        }

        $result = $this->shopify->validateDiscountCode($validated['code'], $subtotalUsd);

        if (! ($result['valid'] ?? false)) {
            return response()->json([
                'valid'    => false,
                'discount' => 0,
                'message'  => $result['message'] ?? __('checkout.invalid_discount'),
            ]);
        }

        $locale = App::getLocale();
        $currencyCode = $this->totals->resolveDisplayCurrency($locale);
        $discountLocal = $this->currency->convert((float) $result['discount_usd'], $currencyCode);

        return response()->json([
            'valid'          => true,
            'discount'       => $discountLocal,
            'discount_usd'   => (float) $result['discount_usd'],
            'code'           => $result['code'],
            'type'           => $result['type'],
            'value'          => $result['value'],
            'free_shipping'  => (bool) ($result['free_shipping'] ?? false),
            'message'        => $result['message'] ?? __('checkout.discount_applied'),
            'currency_code'  => $currencyCode,
        ]);
    }

    private function populateFeaturedBeltFallback(): void
    {
        try {
            $handle = ProductSlugResolver::resolveHandle('dainely-belt');
            $result = $this->shopify->fetchProductByHandle($handle);

            if (! $result['success'] || empty($result['product'])) {
                return;
            }

            $product  = $result['product'];
            $firstVar = $product['variants'][0] ?? [];
            $image    = $product['images'][0]['src'] ?? ($product['image']['src'] ?? null);

            CheckoutCart::addItem([
                'product_id'       => (string) ($product['id'] ?? $handle),
                'title'            => $product['title'] ?? 'Dainely Belt',
                'subtitle'         => \Illuminate\Support\Str::limit(strip_tags($product['body_html'] ?? ''), 80),
                'image'            => $image ?: asset('images/dainely-belt-product.png'),
                'price'            => (float) ($firstVar['price'] ?? 89),
                'compare_at_price' => isset($firstVar['compare_at_price']) ? (float) $firstVar['compare_at_price'] : null,
                'option_label'     => $firstVar['title'] ?? null,
                'variant_id'       => isset($firstVar['id']) ? (string) $firstVar['id'] : null,
                'source'           => 'shopify',
                'quantity'         => 1,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not populate fallback cart from Shopify', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  array<string, mixed>  $totals
     */
    private function storeCurrencySnapshot(array $totals): void
    {
        session([
            'checkout.currency_snapshot' => [
                'display_currency'     => $totals['currency_code'] ?? 'USD',
                'display_total'        => $totals['total'] ?? 0,
                'display_amount_cents' => $totals['amount_cents'] ?? 0,
                'square_currency'      => $totals['square_currency_code'] ?? 'USD',
                'square_amount_cents'  => $totals['square_amount_cents'] ?? 0,
                'total_usd'            => $totals['total_usd'] ?? 0,
                'exchange_rate'        => $totals['exchange_rate'] ?? 1.0,
                'captured_at'          => now()->toIso8601String(),
            ],
        ]);
    }

    private function postalValidationError(string $country, string $zip): ?string
    {
        if (PostalCode::isValid($country, $zip)) {
            return null;
        }

        return PostalCode::invalidMessage($country);
    }
}
