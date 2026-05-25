<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
// use App\Models\DiscountCode;
// use App\Models\Order;
// use App\Models\OrderItem;
use App\Services\SquareService;
use App\Services\ShopifyService;
use App\Services\SendlaneService;
use App\Services\CurrencyService;
use App\Support\CheckoutCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected SquareService $square,
        protected ShopifyService $shopify,
        protected SendlaneService $sendlane,
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

        if (($cart['source'] ?? 'static') === 'static') {
            $cart = $this->populateCartFromShopify($cart);
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
     * Replace default static cart data with first available Shopify product.
     */
    private function populateCartFromShopify(array $cart): array
    {
        try {
            $result = $this->shopify->fetchProducts(1);
            if (!$result['success'] || empty($result['products'])) {
                return $cart;
            }

            $product  = $result['products'][0];
            $firstVar = $product['variants'][0] ?? [];
            $image    = $product['images'][0]['src']
                        ?? ($product['image']['src'] ?? null);

            $cart['product_id']       = (string) ($product['id'] ?? $cart['product_id']);
            $cart['title']            = $product['title'] ?? $cart['title'];
            $cart['subtitle']         = \Illuminate\Support\Str::limit(strip_tags($product['body_html'] ?? ''), 80) ?: $cart['subtitle'];
            $cart['image']            = $image ?: $cart['image'];
            $cart['price']            = (float) ($firstVar['price'] ?? $cart['price']);
            $cart['compare_at_price'] = isset($firstVar['compare_at_price']) ? (float) $firstVar['compare_at_price'] : $cart['compare_at_price'];
            $cart['source']           = 'shopify';

            CheckoutCart::put($cart);
        } catch (\Throwable $e) {
            Log::warning('Could not populate cart from Shopify', ['error' => $e->getMessage()]);
        }

        return $cart;
    }

    /**
     * Process the checkout: tokenize → charge Square → confirmation.
     * Runs without a database — no order persistence.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'source_id'      => 'required|string',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address1'       => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'zip'            => 'required|string|max:20',
            'country'        => 'required|string|max:2',
            'qty'            => 'required|integer|min:1|max:20',
            'amount_cents'   => 'required|integer|min:100',
            'discount_code'  => 'nullable|string|max:50',
        ]);

        $locale      = App::getLocale();
        $orderRef    = 'DLY-' . strtoupper(\Illuminate\Support\Str::random(8));
        $amountCents = (int) $validated['amount_cents'];
        $currency    = $this->currency->getCurrencyForLocale($locale);

        // Charge via Square
        $payment = $this->square->createPayment(
            $validated['source_id'],
            $amountCents,
            $orderRef,
            $currency['code'] ?? 'USD'
        );

        if (!$payment['success']) {
            $errorMsg = $payment['errors'][0]['detail'] ?? 'Payment declined.';
            Log::warning('Checkout payment failed', ['ref' => $orderRef, 'error' => $errorMsg]);
            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }

        Log::info('Checkout payment success', [
            'order_ref'  => $orderRef,
            'payment_id' => $payment['payment_id'],
            'amount'     => $amountCents,
            'email'      => $validated['email'],
        ]);

        session([
            'checkout.last_order' => [
                'order_ref'   => $orderRef,
                'payment_id'  => $payment['payment_id'] ?? null,
                'email'       => $validated['email'],
                'first_name'  => $validated['first_name'],
                'cart'        => CheckoutCart::get(),
                'amount_cents'=> $amountCents,
            ],
        ]);

        return response()->json([
            'success'  => true,
            'redirect' => route('checkout.confirmation', [
                'locale' => $locale,
                'order'  => $orderRef,
            ]),
        ]);
    }

    /**
     * Order confirmation page.
     */
    public function confirmation(string $locale, string $order)
    {
        $lastOrder = session('checkout.last_order');
        $cart      = is_array($lastOrder['cart'] ?? null) ? $lastOrder['cart'] : CheckoutCart::get();
        $qty       = (int) ($cart['quantity'] ?? 1);
        $price     = (float) ($cart['price'] ?? 89);

        $order = (object) [
            'order_number'        => $order,
            'customer_first_name' => $lastOrder['first_name'] ?? 'Guest',
            'customer_email'      => $lastOrder['email'] ?? 'guest@example.com',
            'total_usd'           => ($lastOrder['amount_cents'] ?? ($price * $qty * 100)) / 100,
            'shipping_usd'        => 0,
            'discount_amount_usd' => 0,
            'items'               => collect([
                (object) [
                    'product_name'    => $cart['title'] ?? 'Dainely Belt',
                    'quantity'        => $qty,
                    'total_price_usd' => $price * $qty,
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
        // Database disabled
        return response()->json([
            'valid'   => false,
            'message' => __('checkout.invalid_discount'),
        ]);

        /*
        $request->validate([
            'code'         => 'required|string',
            'subtotal_usd' => 'required|numeric|min:0',
        ]);

        $code = DiscountCode::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit'))
            ->first();

        if (!$code) {
            return response()->json(['valid' => false, 'message' => __('checkout.invalid_discount')]);
        }

        $discount = match($code->type) {
            'percentage'   => round($request->subtotal_usd * ($code->value / 100), 2),
            'fixed'        => min($code->value, $request->subtotal_usd),
            'free_shipping'=> 0,
            default        => 0,
        };

        return response()->json([
            'valid'    => true,
            'type'     => $code->type,
            'value'    => $code->value,
            'discount' => $discount,
            'message'  => __('checkout.discount_applied', ['amount' => number_format($discount, 2)]),
        ]);
        */
    }
}
