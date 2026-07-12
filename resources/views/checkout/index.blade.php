@extends('layouts.app')
@section('title', __('checkout.title'))
@section('meta_description', __('checkout.meta_description'))

@php
  $checkoutCountries = [
    'US', 'GB', 'CA', 'AU', 'FR', 'DE', 'NL', 'BE', 'ES', 'IT', 'SE', 'NO', 'DK',
    'CH', 'AT', 'PL', 'PT', 'IE', 'NZ', 'ZA',
  ];
  $checkoutCountryLabels = [
    'US' => 'United States',
    'GB' => 'United Kingdom',
    'CA' => 'Canada',
    'AU' => 'Australia',
    'FR' => 'France',
    'DE' => 'Germany',
    'NL' => 'Netherlands',
    'BE' => 'Belgium',
    'ES' => 'Spain',
    'IT' => 'Italy',
    'SE' => 'Sweden',
    'NO' => 'Norway',
    'DK' => 'Denmark',
    'CH' => 'Switzerland',
    'AT' => 'Austria',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'IE' => 'Ireland',
    'NZ' => 'New Zealand',
    'ZA' => 'South Africa',
  ];
  $geoSvc = app(\App\Services\GeoLocaleService::class);
  $defaultCountry = $geoSvc->defaultCheckoutCountry($locale, $checkoutCountries);
  $squareCountryLocales = [
    'US' => 'en-US', 'GB' => 'en-GB', 'UK' => 'en-GB', 'CA' => 'en-CA', 'AU' => 'en-AU',
    'FR' => 'fr-FR', 'DE' => 'de-DE', 'NL' => 'nl-NL', 'IE' => 'en-IE', 'NZ' => 'en-NZ',
    'BE' => 'fr-BE', 'ES' => 'es-ES', 'IT' => 'it-IT', 'SE' => 'sv-SE', 'NO' => 'nb-NO',
    'DK' => 'da-DK', 'CH' => 'de-CH', 'AT' => 'de-AT', 'PL' => 'pl-PL', 'PT' => 'pt-PT', 'ZA' => 'en-ZA',
  ];
  $squareLocale = match ($locale) {
    'fr' => 'fr-FR',
    'de' => 'de-DE',
    default => $squareCountryLocales[$defaultCountry] ?? 'en-US',
  };
  $chargeCurrency = strtoupper((string) config('square.charge_currency', 'USD'));
  $usdSymbol = app(\App\Services\CurrencyService::class)->getCurrencyMeta($chargeCurrency)['symbol'] ?? '$';
  $freeShipAmount = ($currencyMeta['symbol'] ?? $pricing['currency_symbol']) . number_format($pricing['free_shipping_threshold'], 2);
  $summarySubtotal = array_sum(array_map(fn ($item) => (float) ($item['line_total'] ?? 0), $cartItems));
  $summaryItemCount = array_sum(array_map(fn ($item) => max(1, (int) ($item['quantity'] ?? 1)), $cartItems));
  $vatNote = in_array($locale, ['fr', 'de'], true) ? __('checkout.vat_note') : null;
  $currencySvc = app(\App\Services\CurrencyService::class);
  $rates = $currencySvc->getRates();
  $currencyMetaForJs = collect(config('currency.supported', []))->mapWithKeys(function ($meta, $code) use ($rates) {
      return [$code => [
          'symbol' => $meta['symbol'] ?? '$',
          'rate'   => (float) ($rates[$code] ?? 1.0),
      ]];
  })->all();
  $checkoutClientConfig = [
    'cartItems'            => $cartItems,
    'pricing'              => $pricing,
    'summarySubtotal'      => $summarySubtotal,
    'summaryTax'           => (float) ($pricing['tax'] ?? 0),
    'summarySubtotalUsd'   => (float) ($pricing['subtotal_usd'] ?? 0),
    'currencySymbol'       => $pricing['currency_symbol'],
    'currencyCode'         => $pricing['currency_code'],
    'lockDisplayCurrency'  => true,
    'paymentCurrency'      => $pricing['currency_code'],
    'paymentCountry'       => $defaultCountry,
    'chargeCurrency'       => $chargeCurrency,
    'usdSymbol'            => $usdSymbol,
    'exchangeRate'         => $pricing['exchange_rate'] ?? 1,
    'squareLocale'         => $squareLocale,
    'sizeLabel'            => __('checkout.size_label'),
    'defaultCountry'       => $defaultCountry,
    'shippingCost'         => $pricing['shipping'] ?? 0,
    'taxAmount'            => $pricing['tax'] ?? 0,
    'labelFree'            => __('checkout.free'),
    'labelTaxCalculating'  => __('checkout.tax_calculating'),
    'freeShipQualifies'    => __('checkout.free_ship_qualifies'),
    'freeShipRemaining'    => __('checkout.free_ship_remaining', ['amount' => $freeShipAmount]),
    'paymentSuccessMessage'=> __('checkout.payment_success'),
    'squareAppId'          => $squareAppId,
    'squareLocationId'     => $squareLocationId,
    'urls'                 => [
      'taxEstimate'      => route('checkout.tax-estimate', ['locale' => $locale]),
      'discountValidate' => route('checkout.validate-discount', ['locale' => $locale]),
      'process'            => route('checkout.process', ['locale' => $locale]),
      'cartUpdate'         => route('cart.update', ['locale' => $locale]),
      'shopUrl'            => route('products.index', ['locale' => $locale]),
    ],
    'taxFallback'          => config('shopify_tax_fallback'),
    'countryCurrency'      => $geoSvc->countryCurrencyMap(),
    'currencyMeta'         => $currencyMetaForJs,
    'postalPlaceholders'   => config('postal.placeholders', []),
    'postalPatterns'       => \App\Support\PostalCode::patternsForClient(),
    'postalUppercase'      => config('postal.uppercase_on_validate', []),
    'squareSkipPrefillCountries'   => config('postal.square_skip_prefill_countries', ['AU', 'NZ']),
    'squareCountryLocales' => $squareCountryLocales,
    'i18n' => [
      'err_first_name'        => __('checkout.err_first_name'),
      'err_last_name'         => __('checkout.err_last_name'),
      'err_email'             => __('checkout.err_email'),
      'err_address'           => __('checkout.err_address'),
      'err_city'              => __('checkout.err_city'),
      'err_zip'               => __('checkout.err_zip'),
      'err_zip_invalid'       => __('checkout.err_zip_invalid'),
      'err_billing_zip'       => __('checkout.err_billing_zip'),
      'err_billing_zip_mismatch' => __('checkout.err_billing_zip_mismatch'),
      'err_country'           => __('checkout.err_country'),
      'discount_applied'      => __('checkout.discount_applied'),
      'invalid_discount'      => __('checkout.invalid_discount'),
      'err_discount_validate' => __('checkout.err_discount_validate'),
      'err_payment_not_ready' => __('checkout.err_payment_not_ready'),
      'err_payment_failed'    => __('checkout.err_payment_failed'),
      'err_card_tokenize'     => __('checkout.err_card_tokenize'),
      'err_unexpected'        => __('checkout.err_unexpected'),
      'err_server_response'   => __('checkout.err_server_response'),
      'err_tax_failed'        => __('checkout.tax_estimate_failed'),
      'err_secure_context'    => 'Square requires a secure page. Locally, open http://localhost:8000 (not 127.0.0.1). On live, use HTTPS.',
      'err_sdk_load'          => 'Payment SDK failed to load. Please refresh the page.',
      'err_square_app'        => 'Square Application ID is missing. Set SQUARE_APPLICATION_ID in .env.',
      'err_square_location'   => 'Square Location ID is missing. In Square Developer Dashboard → Sandbox → Locations, copy your Location ID into SQUARE_LOCATION_ID in .env.',
      'err_square_init'       => 'Could not load payment form',
      'remove_item'           => __('checkout.remove_item'),
      'remove_item_aria'      => __('checkout.remove_item_aria'),
    ],
  ];
@endphp

{{--
  TEMPORARY Square fallback UI only.
  Primary checkout redirects to Shopify (payment handled by Shopify).
  This page loads when ?square=1 or Shopify checkout URL creation fails.
--}}
{{-- Checkout config + Square SDK in <head> (before Vite bundle runs) --}}
@push('head_scripts')
<script data-cfasync="false">window.__CHECKOUT__ = @json($checkoutClientConfig);</script>
@if($squareEnv === 'sandbox')
<script data-cfasync="false" src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
@else
<script data-cfasync="false" src="https://web.squarecdn.com/v1/square.js"></script>
@endif
@endpush

@section('content')
{{-- Backup config for hosts that defer head scripts (e.g. Cloudflare Rocket Loader) --}}
<script data-cfasync="false">window.__CHECKOUT__ = @json($checkoutClientConfig);</script>
<div class="min-h-screen bg-slate-50">

  {{-- Checkout header --}}
  <div class="bg-white border-b border-slate-100 py-4">
    <div class="container-site flex items-center justify-between gap-4">
      <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('home', ['locale' => $locale]) }}" class="flex-shrink-0" aria-label="Dainely Home">
          <img src="{{ asset('images/Dainelycut.png') }}" alt="Dainely" class="h-10 w-auto">
        </a>
        <span class="text-slate-300 hidden sm:inline">|</span>
        <span class="text-slate-500 text-sm hidden sm:inline truncate">{{ __('checkout.secure_checkout') }}</span>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        <a
          href="{{ route('products.index', ['locale' => $locale]) }}"
          class="inline-flex text-sm font-medium text-navy-600 hover:text-navy-800 hover:underline"
        >
          {{ __('nav.continue_shopping') }}
        </a>
        <div class="hidden md:flex items-center gap-2 text-slate-500 text-sm">
          <svg class="w-4 h-4 text-sage-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <span>{{ __('checkout.ssl_secured') }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="container-site py-10">
    <div class="grid lg:grid-cols-[1fr_400px] gap-10 items-start" x-data="checkoutForm"
         data-display-currency="{{ $pricing['currency_code'] }}"
         data-display-symbol="{{ $pricing['currency_symbol'] }}">

      {{-- LEFT: Multi-step form --}}
      <div class="space-y-6">

        {{-- Step indicator --}}
        <div class="flex items-center mb-2">
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 1 ? 'active' : ''">1</div>
            <span class="text-sm font-medium" :class="step >= 1 ? 'text-navy-700' : 'text-slate-400'">{{ __('checkout.step_contact') }}</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 2 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 2 ? 'active' : ''">2</div>
            <span class="text-sm font-medium" :class="step >= 2 ? 'text-navy-700' : 'text-slate-400'">{{ __('checkout.step_shipping') }}</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 3 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 3 ? 'active' : ''">3</div>
            <span class="text-sm font-medium" :class="step >= 3 ? 'text-navy-700' : 'text-slate-400'">{{ __('checkout.step_payment') }}</span>
          </div>
        </div>

        {{-- STEP 1: Contact --}}
        <div class="card p-6" x-show="step === 1" x-transition>
          <h2 class="font-display font-bold text-navy-900 text-xl mb-6">{{ __('checkout.contact_title') }}</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label" for="first_name">{{ __('checkout.first_name') }} {{ __('checkout.required') }}</label>
              <input type="text" id="first_name" x-model="form.first_name" class="form-input" :class="errors.first_name ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="Sarah" required autocomplete="given-name">
              <p x-show="errors.first_name" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.first_name"></p>
            </div>
            <div>
              <label class="form-label" for="last_name">{{ __('checkout.last_name') }} {{ __('checkout.required') }}</label>
              <input type="text" id="last_name" x-model="form.last_name" class="form-input" :class="errors.last_name ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="Mitchell" required autocomplete="family-name">
              <p x-show="errors.last_name" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.last_name"></p>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="email">{{ __('checkout.email') }} {{ __('checkout.required') }}</label>
              <input type="email" id="email" x-model="form.email" class="form-input" :class="errors.email ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="sarah@example.com" required autocomplete="email">
              <p class="text-slate-400 text-xs mt-1">{{ __('checkout.email_hint') }}</p>
              <p x-show="errors.email" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.email"></p>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="phone">{{ __('checkout.phone') }}</label>
              <input type="tel" id="phone" x-model="form.phone" class="form-input" placeholder="+1 555 000 0000" autocomplete="tel">
            </div>
          </div>
          <div class="mt-6">
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">
              {{ __('checkout.continue_shipping') }}
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
          </div>
        </div>

        {{-- STEP 2: Shipping --}}
        <div class="card p-6" x-show="step === 2" x-transition>
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-navy-900 text-xl">{{ __('checkout.shipping_title') }}</h2>
            <button @click="step = 1" class="text-navy-600 text-sm hover:underline">{{ __('checkout.edit_contact') }}</button>
          </div>
          <div class="space-y-4">
            <div>
              <label class="form-label" for="address1">{{ __('checkout.address1') }} {{ __('checkout.required') }}</label>
              <input type="text" id="address1" x-model="form.address1" class="form-input" :class="errors.address1 ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="123 Main Street" required autocomplete="address-line1">
              <p x-show="errors.address1" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.address1"></p>
            </div>
            <div>
              <label class="form-label" for="address2">{{ __('checkout.address2') }}</label>
              <input type="text" id="address2" x-model="form.address2" class="form-input" placeholder="Apt 4B" autocomplete="address-line2">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label" for="city">{{ __('checkout.city') }} {{ __('checkout.required') }}</label>
                <input type="text" id="city" x-model="form.city" class="form-input" :class="errors.city ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="New York" required autocomplete="address-level2">
                <p x-show="errors.city" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.city"></p>
              </div>
              <div>
                <label class="form-label" for="state">{{ __('checkout.state') }}</label>
                <input type="text" id="state" x-model="form.state" class="form-input" placeholder="NY" autocomplete="address-level1">
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label" for="zip">{{ __('checkout.zip') }} {{ __('checkout.required') }}</label>
                <input
                  type="text"
                  id="zip"
                  x-model="form.zip"
                  class="form-input"
                  :class="errors.zip ? 'border-red-500 ring-1 ring-red-500' : ''"
                  :placeholder="zipPlaceholder()"
                  inputmode="text"
                  autocapitalize="characters"
                  autocomplete="postal-code"
                  required
                >
                <p x-show="errors.zip" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.zip"></p>
              </div>
              <div>
                <label class="form-label" for="country">{{ __('checkout.country') }} {{ __('checkout.required') }}</label>
                <select id="country" x-model="form.country" @change="onCountryChange()" class="form-input" :class="errors.country ? 'border-red-500 ring-1 ring-red-500' : ''" required autocomplete="country">
                  <option value="">{{ __('checkout.select_country') }}</option>
                  @foreach($checkoutCountryLabels as $code => $label)
                  <option value="{{ $code }}" @selected($defaultCountry === $code)>{{ $label }}</option>
                  @endforeach
                </select>
                <p x-show="errors.country" x-cloak class="text-red-600 text-xs mt-1" x-text="errors.country"></p>
              </div>
            </div>
          </div>

          {{-- Shipping method --}}
          <div class="mt-6">
            <p class="form-label mb-3">{{ __('checkout.shipping_method') }}</p>
            <div class="space-y-2">
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors"
                :class="form.shipping_method === 'standard' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
                <div class="flex items-center gap-3">
                  <input type="radio" x-model="form.shipping_method" value="standard" class="text-navy-600">
                  <div>
                    <p class="font-semibold text-slate-800 text-sm">{{ __('checkout.standard_shipping') }}</p>
                    <p class="text-slate-400 text-xs">{{ __('checkout.standard_days') }}</p>
                  </div>
                </div>
                <span class="font-semibold text-slate-700" x-text="subtotal() >= pricing.free_shipping_threshold ? labelFree : formatMoney(shippingRate('standard'))"></span>
              </label>
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors"
                :class="form.shipping_method === 'express' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
                <div class="flex items-center gap-3">
                  <input type="radio" x-model="form.shipping_method" value="express" class="text-navy-600">
                  <div>
                    <p class="font-semibold text-slate-800 text-sm">{{ __('checkout.express_shipping') }}</p>
                    <p class="text-slate-400 text-xs">{{ __('checkout.express_days') }}</p>
                  </div>
                </div>
                <span class="font-semibold text-slate-700" x-text="subtotal() >= pricing.free_shipping_threshold ? labelFree : formatMoney(shippingRate('express'))"></span>
              </label>
            </div>
          </div>

          <div class="mt-6">
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">
              {{ __('checkout.continue_payment') }}
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
          </div>
        </div>

        {{-- STEP 3: Payment --}}
        <div class="card p-6" x-show="step === 3" x-transition>
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-navy-900 text-xl">{{ __('checkout.payment_title') }}</h2>
            <button @click="step = 2" class="text-navy-600 text-sm hover:underline">{{ __('checkout.edit_shipping') }}</button>
          </div>

          {{-- Localized amount due (display currency) --}}
          <div class="mb-5 rounded-xl border-2 border-navy-100 bg-navy-50 px-5 py-4">
            <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold mb-1">{{ __('checkout.payment_amount_due') }}</p>
            <p class="font-display font-bold text-3xl text-navy-900">
              <span x-text="formatMoney(total())"></span>
              <span class="text-lg font-semibold text-slate-500 ml-1" x-text="currencyCode"></span>
            </p>
            @if(in_array($locale, ['fr', 'de'], true))
            <p class="text-slate-500 text-xs mt-2" x-show="currencyCode !== chargeCurrency">
              {{ __('checkout.charge_usd_note') }}
              <strong x-text="formatUsd(totalUsd()) + ' ' + chargeCurrency"></strong>{{ __('checkout.charge_usd_note_suffix') }}
            </p>
            @endif
          </div>

          {{-- Card brands --}}
          <div class="flex items-center gap-2 mb-5">
            <span class="text-slate-500 text-xs">{{ __('checkout.we_accept') }}</span>
            @foreach(['VISA', 'MC', 'AMEX', 'DISCOVER'] as $card)
            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded">{{ $card }}</span>
            @endforeach
            <div class="ml-auto flex items-center gap-1 text-sage-600">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              <span class="text-xs font-semibold">{{ __('checkout.ssl_secured') }}</span>
            </div>
          </div>

          {{-- Square card container --}}
          <div class="border-2 border-slate-200 rounded-xl p-5 mb-5 bg-slate-50">
            <div class="flex items-center gap-2 mb-4">
              <svg class="w-8 h-8 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <div>
                <p class="text-slate-800 font-semibold text-sm">{{ __('checkout.secure_card') }}</p>
                <p class="text-slate-400 text-xs">{{ __('checkout.card_never_stored') }}</p>
              </div>
            </div>

            <div
              id="card-container"
              class="min-h-[56px]"
              data-square-app-id="{{ $squareAppId }}"
              data-square-location-id="{{ $squareLocationId }}"
              data-payment-currency="{{ $pricing['currency_code'] }}"
              data-payment-country="{{ $defaultCountry }}"
              data-square-locale="{{ $squareLocale }}"
            ></div>
            <div id="payment-status-container" class="text-sm text-slate-500 mt-2 hidden"></div>
          </div>

          {{-- Sandbox test card notice --}}
          @if($squareEnv === 'sandbox')
          <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm">
            <p class="font-semibold text-amber-800 mb-1">{{ __('checkout.sandbox_title') }}</p>
            <p class="text-amber-700 text-xs mb-2">{{ __('checkout.sandbox_card') }}</p>
            @if(!$squareConfigured)
            <p class="text-amber-800 text-xs">Add <strong>SQUARE_LOCATION_ID</strong> to <code>.env</code> from Square Developer Dashboard → Sandbox → Locations.</p>
            @endif
            @if(request()->getHost() === '127.0.0.1')
            <p class="text-amber-800 text-xs mt-1">Use <strong>http://localhost:8000</strong> for local Square payments (not 127.0.0.1).</p>
            @endif
          </div>
          @endif

          {{-- Error / success messages --}}
          <div x-show="paymentError" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-red-700 text-sm" x-text="paymentError"></div>
          <div x-show="paymentSuccess" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 text-emerald-700 text-sm font-semibold" x-text="paymentSuccess"></div>

          {{-- Place Order button --}}
          <button
            type="button"
            id="place-order-btn"
            @click="submitOrder()"
            :disabled="loading || !cardReady"
            class="btn-gold-lg w-full justify-center disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span x-show="!loading" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              {{ __('checkout.place_order') }} — <span x-text="formatMoney(total())"></span>
            </span>
            <span x-show="loading" class="flex items-center gap-2">
              <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              {{ __('checkout.processing') }}
            </span>
          </button>
          <p class="text-slate-400 text-xs text-center mt-3">
            {{ __('checkout.terms_prefix') }}
            <a href="{{ route('terms', ['locale' => $locale]) }}" class="underline hover:text-navy-700">{{ __('checkout.terms') }}</a>
            &amp;
            <a href="{{ route('privacy', ['locale' => $locale]) }}" class="underline hover:text-navy-700">{{ __('checkout.privacy') }}</a>.
          </p>
        </div>
      </div>

      {{-- RIGHT: Order Summary (server-rendered + Alpine-enhanced) --}}
      <div class="space-y-4 lg:sticky lg:top-24" id="checkout-order-summary">
        <div class="card p-6">
          <h3 class="font-display font-bold text-navy-900 text-lg mb-5">
            {{ __('checkout.order_summary') }}
            (<span x-text="cartItemCount()">{{ $summaryItemCount }}</span>)
          </h3>

          <div class="space-y-4 mb-5 max-h-80 overflow-y-auto overflow-x-visible pr-1 pt-1">
            <template x-for="item in cartItems" :key="item.key">
              <div class="flex items-start gap-3">
                <div class="relative flex-shrink-0 pt-1 pb-1">
                  <img :src="item.image" :alt="item.title" class="w-16 h-16 rounded-xl object-cover bg-slate-100 ring-1 ring-slate-100">
                  <span class="absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 bg-navy-600 text-white text-xs rounded-full flex items-center justify-center font-bold shadow-sm ring-2 ring-white" x-text="item.quantity"></span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-slate-800 text-sm truncate" x-text="item.title"></p>
                  <p
                    class="text-slate-400 text-xs truncate"
                    x-text="item.option_label ? (sizeLabel + ': ' + item.option_label) : (item.subtitle || '')"
                  ></p>
                  <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
                    <div class="flex items-center gap-2">
                      <button type="button" @click="updateQty(item.key, -1)" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center" aria-label="Decrease quantity">−</button>
                      <span class="font-semibold text-navy-900 text-sm min-w-[1rem] text-center" x-text="item.quantity"></span>
                      <button type="button" @click="updateQty(item.key, 1)" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center" aria-label="Increase quantity">+</button>
                    </div>
                    <button
                      type="button"
                      @click="removeItem(item.key)"
                      class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-red-600 transition-colors"
                      :aria-label="checkoutI18n.remove_item_aria + ' ' + item.title"
                    >
                      <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      <span x-text="checkoutI18n.remove_item">{{ __('checkout.remove_item') }}</span>
                    </button>
                  </div>
                </div>
                <p class="font-semibold text-navy-900 whitespace-nowrap pt-1" x-text="formatMoney(lineTotal(item))"></p>
              </div>
            </template>
            <p x-show="cartItems.length === 0" x-cloak class="text-slate-500 text-sm">{{ __('checkout.cart_empty') }}</p>
          </div>

          {{-- Discount code --}}
          <div class="border-t border-slate-100 pt-4 mb-4">
            <label class="form-label mb-2" for="discount-code">{{ __('checkout.discount_code') }}</label>
            <div class="flex gap-2">
              <input type="text" id="discount-code" x-model="discountCode" class="form-input flex-1" placeholder="{{ __('checkout.discount_placeholder') }}" @keydown.enter="applyDiscount()">
              <button @click="applyDiscount()" class="btn-outline px-4 text-sm whitespace-nowrap">{{ __('checkout.apply') }}</button>
            </div>
            <p x-show="discountMessage" class="text-sm mt-2" :class="discountValid ? 'text-sage-600' : 'text-red-600'" x-text="discountMessage"></p>
          </div>

          {{-- Totals --}}
          <div class="space-y-2 border-t border-slate-100 pt-4">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">{{ __('checkout.subtotal') }}</span>
              <span class="text-slate-700" x-text="displaySubtotal()">{{ $pricing['currency_symbol'] }}{{ number_format($summarySubtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm" x-show="discount > 0">
              <span class="text-sage-600">{{ __('checkout.discount') }}</span>
              <span class="text-sage-600" x-text="'-' + formatMoney(discount)"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">{{ __('checkout.shipping') }}</span>
              <span x-text="shippingCost === 0 ? labelFree : formatMoney(shippingCost)"
                    :class="shippingCost === 0 ? 'text-sage-600 font-semibold' : 'text-slate-700'">{{ ($pricing['shipping'] ?? 0) == 0 ? __('checkout.free') : $pricing['currency_symbol'] . number_format((float) ($pricing['shipping'] ?? 0), 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">{{ __('checkout.tax') }}<span x-show="taxLoading" class="text-xs text-slate-400 ml-1">…</span></span>
              <span class="text-slate-700" x-text="displayTax()">{{ $pricing['currency_symbol'] }}{{ number_format((float) ($pricing['tax'] ?? 0), 2) }}</span>
            </div>
            <p x-show="taxError" class="text-amber-600 text-xs" x-text="taxError"></p>
            @if($vatNote)
            <p class="text-slate-400 text-xs">{{ $vatNote }}</p>
            @endif
            <div class="flex justify-between font-bold text-lg border-t border-slate-200 pt-3 mt-2">
              <span class="text-navy-900">{{ __('checkout.total') }} (<span x-text="currencyCode">{{ $pricing['currency_code'] }}</span>)</span>
              <span class="text-navy-900" x-text="displayTotal()">{{ $pricing['currency_symbol'] }}{{ number_format((float) ($pricing['total'] ?? 0), 2) }}</span>
            </div>
          </div>
        </div>

        {{-- Trust badges --}}
        <div class="card p-5 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-sage-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">{{ __('checkout.guarantee_title') }}</p>
              <p class="text-slate-400 text-xs">{{ __('checkout.guarantee_copy') }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-navy-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">{{ __('checkout.free_ship_title', ['amount' => $freeShipAmount]) }}</p>
              <p class="text-slate-400 text-xs" x-text="subtotal() >= pricing.free_shipping_threshold ? freeShipQualifies : freeShipRemaining"></p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">{{ __('checkout.secure_pay_title') }}</p>
              <p class="text-slate-400 text-xs">{{ __('checkout.secure_pay_copy') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
