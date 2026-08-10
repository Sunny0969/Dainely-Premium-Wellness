@extends('layouts.admin')

@section('admin_title', 'Shipping & Free Shipping')

@section('admin_content')
<div class="max-w-2xl">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Free shipping threshold</h2>
        <p class="text-slate-500 mt-2 text-sm leading-relaxed">
            Set the cart total (in USD) needed for free shipping. This amount shows in the top banner,
            product pages, and checkout. When a customer’s order reaches this amount, shipping becomes
            <strong class="text-slate-700">FREE</strong>.
        </p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
        <form method="POST" action="/dainely-admin-panel/shipping" class="space-y-6">
            @csrf

            <div>
                <label for="free_shipping_threshold_usd" class="block text-sm font-semibold text-slate-800 mb-2">
                    Free shipping over (USD)
                </label>
                <div class="relative max-w-xs">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-medium">$</span>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="99999.99"
                        id="free_shipping_threshold_usd"
                        name="free_shipping_threshold_usd"
                        value="{{ old('free_shipping_threshold_usd', number_format($thresholdUsd, 2, '.', '')) }}"
                        class="form-input w-full pl-8 text-lg font-semibold @error('free_shipping_threshold_usd') border-rose-400 ring-1 ring-rose-400 @enderror"
                        placeholder="29.99"
                        required
                    >
                </div>
                @error('free_shipping_threshold_usd')
                    <p class="text-rose-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                <p class="text-slate-400 text-xs mt-2">
                    Example: enter <strong>40</strong> → customers see “Free shipping on orders over $40.00”.
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-600 space-y-2">
                <p class="font-semibold text-slate-800">What customers will see</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Top site banner: Free shipping on orders over <span class="font-semibold text-navy-700">${{ number_format($thresholdUsd, 2) }}</span></li>
                    <li>Checkout: free shipping when cart subtotal reaches that amount</li>
                </ul>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-navy-700 text-white text-sm font-semibold hover:bg-navy-800 transition">
                    Save free shipping amount
                </button>
                <a href="/dainely-admin-panel/dashboard" class="text-sm text-slate-500 hover:text-slate-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
