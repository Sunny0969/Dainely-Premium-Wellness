<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CheckoutCart;
use App\Support\CheckoutTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CartController extends Controller
{
    public function __construct(
        protected CheckoutTotals $totals,
    ) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id'       => 'required|string|max:100',
            'title'            => 'required|string|max:255',
            'subtitle'         => 'nullable|string|max:500',
            'image'            => 'required|string|max:2048',
            'price'            => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'quantity'         => 'required|integer|min:1|max:20',
            'option_label'     => 'nullable|string|max:255',
            'option_value'     => 'nullable|string|max:255',
            'variant_id'       => 'nullable|string|max:100',
            'handle'           => 'nullable|string|max:255',
            'source'           => 'nullable|string|in:shopify,static',
            'intent'           => 'nullable|string|in:add,checkout',
        ]);

        if (\App\Support\ProductRequiresSize::missingSelection(
            $validated['product_id'],
            $validated['title'],
            $validated['option_label'] ?? null,
            $validated['option_value'] ?? null,
            $validated['handle'] ?? null,
        )) {
            $message = __('products.select_option');
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->withErrors(['option' => $message]);
        }

        CheckoutCart::addItem([
            'product_id'       => $validated['product_id'],
            'title'            => $validated['title'],
            'subtitle'         => $validated['subtitle'] ?? null,
            'image'            => $validated['image'],
            'price'            => round((float) $validated['price'], 2),
            'compare_at_price' => isset($validated['compare_at_price'])
                ? round((float) $validated['compare_at_price'], 2)
                : null,
            'quantity'         => (int) $validated['quantity'],
            'option_label'     => $validated['option_label'] ?? null,
            'option_value'     => $validated['option_value'] ?? null,
            'variant_id'       => $this->normalizeStoredVariantId($validated['variant_id'] ?? null),
            'source'           => $validated['source'] ?? (! empty($validated['variant_id']) ? 'shopify' : 'static'),
        ]);

        $locale      = App::getLocale();
        $itemCount   = CheckoutCart::itemCount();
        $checkoutUrl = route('checkout.index', ['locale' => $locale]);
        $message     = __('products.added_to_cart', ['title' => $validated['title']]);
        $intent      = $validated['intent'] ?? 'add';

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => $message,
                'item_count'   => $itemCount,
                'checkout_url' => $checkoutUrl,
                'redirect'     => $intent === 'checkout' ? $checkoutUrl : null,
            ]);
        }

        if ($intent === 'checkout') {
            return redirect()->to($checkoutUrl)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line_quantities'   => 'nullable|array',
            'line_quantities.*' => 'integer|min:0|max:20',
            'remove_key'        => 'nullable|string|max:255',
        ]);

        if (! empty($validated['remove_key'])) {
            CheckoutCart::removeItem($validated['remove_key']);
        }

        if (! empty($validated['line_quantities']) && is_array($validated['line_quantities'])) {
            CheckoutCart::updateQuantities($validated['line_quantities']);
        }

        $locale = App::getLocale();
        $items  = CheckoutCart::getItems();

        return response()->json([
            'success'    => true,
            'empty'      => $items === [],
            'item_count' => CheckoutCart::itemCount(),
            'cartItems'  => $this->totals->itemsForDisplay($items, $locale),
            'redirect'   => $items === []
                ? route('products.index', ['locale' => $locale])
                : null,
        ]);
    }

    private function normalizeStoredVariantId(?string $variantId): ?string
    {
        if ($variantId === null || $variantId === '') {
            return null;
        }

        if (! is_numeric($variantId) || (int) $variantId <= 0) {
            return null;
        }

        return (string) (int) $variantId;
    }
}
