<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CheckoutCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CartController extends Controller
{
    public function store(Request $request): RedirectResponse
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
            'source'           => 'nullable|string|in:shopify,static',
        ]);

        CheckoutCart::put([
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
            'variant_id'       => $validated['variant_id'] ?? null,
            'source'           => $validated['source'] ?? (! empty($validated['variant_id']) ? 'shopify' : 'static'),
        ]);

        return redirect()->route('checkout.index', ['locale' => App::getLocale()]);
    }
}
