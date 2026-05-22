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
        $squareAppId  = $this->square->getApplicationId();
        $squareEnv    = $this->square->getEnvironment();
        $locale       = App::getLocale();
        $currency     = $this->currency->getCurrencyForLocale($locale);

        return view('checkout.index', compact('squareAppId', 'squareEnv', 'locale', 'currency'));
    }

    /**
     * Process the checkout: validate → charge Square → create Shopify order.
     */
    public function process(Request $request)
    {
        // Database disabled — checkout cannot persist orders
        return response()->json([
            'success' => false,
            'message' => 'Checkout is unavailable while running without a database. Browse products and contact us to order.',
        ], 503);

        /*
        $request->validate([
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
            'country'           => 'required|string|size:2',
            'items'             => 'required|array|min:1',
            'discount_code'     => 'nullable|string|max:50',
            'gdpr_consent'      => 'nullable|boolean',
        ]);

        $locale    = App::getLocale();
        $currency  = $this->currency->getCurrencyForLocale($locale);

        $subtotalUsd    = 0;
        $orderItems     = [];

        foreach ($request->items as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            $lineTotal = $product->price_usd * $item['quantity'];
            $subtotalUsd += $lineTotal;
            $orderItems[] = [
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'sku'             => $product->sku,
                'quantity'        => $item['quantity'],
                'unit_price_usd'  => $product->price_usd,
                'total_price_usd' => $lineTotal,
            ];
        }

        $discountUsd  = 0;
        $discountCode = null;

        if ($request->filled('discount_code')) {
            $discountCode = DiscountCode::where('code', strtoupper($request->discount_code))
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit'))
                ->first();

            if ($discountCode) {
                $discountUsd = match($discountCode->type) {
                    'percentage'   => round($subtotalUsd * ($discountCode->value / 100), 2),
                    'fixed'        => min($discountCode->value, $subtotalUsd),
                    'free_shipping'=> 0,
                    default        => 0,
                };
            }
        }

        $shippingUsd = $subtotalUsd >= 75 ? 0 : 9.99;
        if ($discountCode?->type === 'free_shipping') $shippingUsd = 0;

        $taxUsd      = 0;
        $totalUsd    = max(0, $subtotalUsd - $discountUsd + $shippingUsd + $taxUsd);
        $totalCents  = (int) round($totalUsd * 100);

        DB::beginTransaction();
        try {
            $order = Order::create([...]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            $payment = $this->square->createPayment(...);

            if (!$payment['success']) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            $order->update(['status' => 'paid', 'square_payment_id' => $payment['payment_id']]);
            $discountCode?->increment('usage_count');
            DB::commit();

            \App\Jobs\CreateShopifyOrder::dispatch($order);
            $this->sendlane->subscribeContact([...], $locale);

            return response()->json([
                'success'  => true,
                'redirect' => route('checkout.confirmation', ['locale' => $locale, 'order' => $order->order_number]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout process error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
        */
    }

    /**
     * Order confirmation page.
     */
    public function confirmation(string $locale, string $order)
    {
        // Database disabled — demo confirmation only
        // $order = Order::where('order_number', $order)->with('items')->firstOrFail();

        $order = (object) [
            'order_number'        => $order,
            'customer_first_name' => 'Guest',
            'customer_email'      => 'guest@example.com',
            'total_usd'           => 89.00,
            'shipping_usd'        => 0,
            'discount_amount_usd' => 0,
            'items'               => collect([
                (object) [
                    'product_name'    => 'Dainely Belt',
                    'quantity'        => 1,
                    'total_price_usd' => 89.00,
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
