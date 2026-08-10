@extends('layouts.app')
@section('title', __('checkout.title'))
@section('meta_description', __('checkout.meta_description'))

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col">
  <div class="bg-white border-b border-slate-100 py-4">
    <div class="container-site flex items-center gap-3">
      <a href="{{ route('home', ['locale' => $locale]) }}" aria-label="Dainely Home">
        <img src="{{ asset('images/Dainelycut.png') }}" alt="Dainely" class="h-10 w-auto">
      </a>
      <span class="text-slate-300">|</span>
      <span class="text-slate-500 text-sm">{{ __('checkout.secure_checkout') }}</span>
    </div>
  </div>

  <div class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center" id="checkout-preparing"
         data-shopify-url="{{ $shopifyCheckoutUrl }}"
         data-summary-url="{{ $cartSummaryUrl }}"
         data-square-url="{{ $squareFallbackUrl }}"
         data-products-url="{{ $productsUrl }}"
         data-square-enabled="{{ $squareFallbackEnabled ? '1' : '0' }}"
         data-csrf="{{ csrf_token() }}">
      <div class="mx-auto mb-6 w-12 h-12 rounded-full border-4 border-navy-200 border-t-navy-700 animate-spin" aria-hidden="true"></div>
      <h1 class="font-display font-bold text-navy-900 text-2xl mb-2">{{ __('checkout.preparing_title') }}</h1>
      <p class="text-slate-500 text-sm mb-8" id="checkout-preparing-msg">{{ __('checkout.preparing_copy') }}</p>

      {{-- Skeleton order summary preview --}}
      <div class="card p-5 text-left space-y-3 animate-pulse" id="checkout-skeleton">
        <div class="h-4 bg-slate-200 rounded w-1/3"></div>
        <div class="flex gap-3">
          <div class="w-14 h-14 bg-slate-200 rounded-xl flex-shrink-0"></div>
          <div class="flex-1 space-y-2 py-1">
            <div class="h-3 bg-slate-200 rounded w-3/4"></div>
            <div class="h-3 bg-slate-100 rounded w-1/2"></div>
          </div>
        </div>
        <div class="h-3 bg-slate-100 rounded w-full"></div>
        <div class="h-3 bg-slate-100 rounded w-2/3"></div>
      </div>

      <p class="text-red-600 text-sm mt-6 hidden" id="checkout-preparing-error"></p>
      <a href="{{ $productsUrl }}" class="inline-block mt-4 text-sm text-navy-600 underline hidden" id="checkout-preparing-back">{{ __('nav.continue_shopping') }}</a>
    </div>
  </div>
</div>

<script data-cfasync="false">
(function () {
  var root = document.getElementById('checkout-preparing');
  if (!root) return;

  var msg = document.getElementById('checkout-preparing-msg');
  var err = document.getElementById('checkout-preparing-error');
  var back = document.getElementById('checkout-preparing-back');
  var shopifyUrl = root.getAttribute('data-shopify-url');
  var summaryUrl = root.getAttribute('data-summary-url');
  var squareUrl = root.getAttribute('data-square-url');
  var productsUrl = root.getAttribute('data-products-url');
  var squareEnabled = root.getAttribute('data-square-enabled') === '1';
  var csrf = root.getAttribute('data-csrf');

  function fail(message, redirect) {
    if (msg) msg.textContent = '';
    if (err) {
      err.textContent = message || 'Checkout unavailable.';
      err.classList.remove('hidden');
    }
    if (back) back.classList.remove('hidden');
    if (redirect) {
      setTimeout(function () { window.location.href = redirect; }, 1200);
    }
  }

  // Optional: paint last cart from localStorage while Shopify URL is created
  try {
    var cached = localStorage.getItem('dainely_cart_summary');
    if (cached) {
      var parsed = JSON.parse(cached);
      if (parsed && Array.isArray(parsed.cartItems) && parsed.cartItems.length) {
        var sk = document.getElementById('checkout-skeleton');
        if (sk) {
          sk.classList.remove('animate-pulse');
          sk.innerHTML = '<p class="text-sm font-semibold text-navy-900 mb-2">{{ __('checkout.order_summary') }}</p>' +
            parsed.cartItems.slice(0, 3).map(function (item) {
              return '<div class="flex gap-3 mb-2"><img src="' + (item.image || '') + '" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100"/><div class="min-w-0"><p class="text-sm font-medium truncate">' +
                (item.title || '') + '</p><p class="text-xs text-slate-400">×' + (item.quantity || 1) + '</p></div></div>';
            }).join('');
        }
      }
    }
  } catch (e) {}

  fetch(summaryUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
    .then(function (r) { return r.json(); })
    .then(function (summary) {
      if (summary && summary.empty) {
        fail(summary.error || '{{ __('checkout.cart_empty') }}', summary.redirect || productsUrl);
        return null;
      }
      try {
        if (summary && summary.cartItems) {
          localStorage.setItem('dainely_cart_summary', JSON.stringify({
            cartItems: summary.cartItems,
            pricing: summary.pricing || {},
            savedAt: Date.now(),
          }));
        }
      } catch (e) {}
      return fetch(shopifyUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: '{}',
      });
    })
    .then(function (r) {
      if (!r) return null;
      return r.json().then(function (data) {
        return { ok: r.ok, data: data };
      });
    })
    .then(function (result) {
      if (!result) return;
      var data = result.data || {};
      if (result.ok && data.redirect) {
        try { localStorage.removeItem('dainely_cart_summary'); } catch (e) {}
        window.location.href = data.redirect;
        return;
      }
      if (data.redirect && squareEnabled) {
        window.location.href = data.redirect;
        return;
      }
      fail(data.error || '{{ __('checkout.unavailable') }}', data.redirect || (squareEnabled ? squareUrl : productsUrl));
    })
    .catch(function () {
      fail('{{ __('checkout.unavailable') }}', squareEnabled ? squareUrl : productsUrl);
    });
})();
</script>
@endsection
