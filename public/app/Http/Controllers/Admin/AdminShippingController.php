<?php

namespace App\Http\Controllers\Admin;

use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminShippingController extends AdminController
{
    public function edit()
    {
        $thresholdUsd = SiteSettings::freeShippingThresholdUsd();

        return view('admin.shipping.edit', compact('thresholdUsd'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'free_shipping_threshold_usd' => 'required|numeric|min:0|max:99999.99',
        ], [
            'free_shipping_threshold_usd.required' => 'Please enter a free shipping amount.',
            'free_shipping_threshold_usd.numeric'  => 'Amount must be a number (e.g. 29.99).',
            'free_shipping_threshold_usd.min'      => 'Amount cannot be negative.',
        ]);

        $amount = round((float) $validated['free_shipping_threshold_usd'], 2);
        SiteSettings::setFreeShippingThresholdUsd($amount);

        // Drop cached shipping quotes so checkout uses the new threshold immediately.
        Cache::forget(\App\Services\ShopifyService::CATALOG_CACHE_KEY);
        try {
            // File/redis: best-effort clear of rate keys is not required — keys include threshold.
        } catch (\Throwable) {
            // ignore
        }

        return redirect('/dainely-admin-panel/shipping')
            ->with('success', 'Free shipping threshold updated to $'.number_format($amount, 2).'. Customers will see this on the site and at checkout.');
    }
}
