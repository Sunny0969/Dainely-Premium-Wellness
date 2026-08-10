@extends('layouts.app')

@php
  use App\Support\ProductSlugResolver;

  $locale = app()->getLocale();
  $cartAddUrl = route('cart.store', ['locale' => $locale]);
  $checkoutUrl = route('checkout.index', ['locale' => $locale]);
  $filters = $filters ?? ['q' => '', 'min_price' => null, 'max_price' => null, 'sort' => ''];
  $reviewStatsByHandle = $reviewStatsByHandle ?? [];
@endphp

@section('title', __('products.meta_title'))
@section('meta_description', __('products.meta_description'))

@section('content')

{{-- ─── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-navy-950 to-navy-900 text-white py-14 lg:py-20">
  <div class="container-site text-center">
    <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-3">{{ __('products.eyebrow') }}</p>
    <h1 class="font-display font-bold text-4xl lg:text-5xl mb-4 leading-tight text-white">{{ __('products.title') }}</h1>
    <p class="text-navy-300 text-base max-w-xl mx-auto leading-relaxed">
      {{ __('products.subtitle') }}
    </p>
  </div>
</section>

{{-- ─── STICKY FILTER / SORT BAR ──────────────────────────────────────────── --}}
<div class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
  <div class="container-site py-3">
    <div class="flex flex-wrap gap-3 items-center">

      {{-- Live Search (client-side) --}}
      <div class="flex-1 min-w-[180px] relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input
          id="product-search"
          type="search"
          placeholder="{{ __('products.search') }}"
          value="{{ $filters['q'] ?? '' }}"
          class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-navy-400 focus:border-transparent"
        >
      </div>

      {{-- Sort buttons --}}
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-slate-500 text-xs font-medium hidden sm:inline mr-1">{{ __('products.sort') }}:</span>
        @php $activeSort = $filters['sort'] ?? ''; @endphp
        @foreach([
          ['default',    __('products.sort_default')],
          ['price-asc',  __('products.sort_price_asc')],
          ['price-desc', __('products.sort_price_desc')],
          ['az',         __('products.sort_az')],
          ['za',         __('products.sort_za')],
        ] as [$val, $label])
        <button
          type="button"
          data-sort="{{ $val }}"
          class="sort-btn border text-xs font-semibold px-3 py-2 rounded-lg transition-colors
            {{ ($activeSort === $val || ($val === 'default' && !$activeSort)) ? 'bg-navy-700 text-white border-navy-700' : 'bg-white text-slate-600 border-slate-200 hover:border-navy-400 hover:text-navy-700' }}"
        >{{ $label }}</button>
        @endforeach
      </div>

      {{-- Count --}}
      <p id="product-count" class="text-slate-500 text-xs ml-auto hidden sm:block">{{ __('products.count', ['count' => count($products)]) }}</p>
    </div>
  </div>
</div>

{{-- ─── PRODUCT GRID ─────────────────────────────────────────────────────── --}}
<section class="section bg-slate-50" aria-label="Product catalog">
  <div class="container-site">

    @if($error)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 mb-8 text-amber-800 text-sm">
      <p class="font-semibold mb-1">⚠ {{ __('products.shopify_error') }}</p>
      <p>{{ $error }}</p>
    </div>
    @endif

    @if(count($products) > 0)

    <div id="products-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

      @foreach($products as $product)
      @php
        $pImg = $product['image'] ?? null;
        if (is_array($pImg)) {
            $pImg = $pImg['src'] ?? null;
        }
        $pImg = $pImg ?: ($product['images'][0]['src'] ?? null);
        $pHandle     = $product['handle'] ?? $product['id'];
        $pStatus     = $product['status'] ?? 'active';
        $pVars       = $product['variant_count'] ?? count($product['variants'] ?? []);
        $pPrice      = (float) ($product['price'] ?? ($product['variants'][0]['price'] ?? 0));
        $pCompare    = (float) ($product['compare_at'] ?? ($product['variants'][0]['compare_at_price'] ?? 0));
        $pUrl        = route('products.show', ['locale' => $locale, 'slug' => $pHandle]);
        $pReviewHandle = ProductSlugResolver::resolveHandle((string) $pHandle);
        $pReviewStats  = $reviewStatsByHandle[$pReviewHandle] ?? ['average_rating' => 0, 'total_reviews' => 0];
        $displayPrice   = $pPrice;
        $displayCompare = $pCompare;
        $savePct        = ($displayCompare > $displayPrice && $displayPrice > 0)
                            ? round((($displayCompare - $displayPrice) / $displayCompare) * 100)
                            : 0;
        $isDainBelt     = str_contains(strtolower((string) $pHandle), 'dainely-belt') || str_contains(strtolower((string) ($product['title'] ?? '')), 'dainely belt');
        $cartVariants   = \App\Support\ProductLandingAssets::mapVariantsForCart($product['variants'] ?? []);
        $cartData = [
          'id'              => (string) ($product['id'] ?? $pHandle),
          'title'           => $product['title'] ?? 'Product',
          'subtitle'        => 'Premium Wellness Product',
          'image'           => $pImg ?: '',
          'price'           => $displayPrice,
          'compare_at_price'=> $displayCompare ?: null,
          'variants'        => $cartVariants,
          'source'          => 'shopify',
        ];
      @endphp

      {{-- Card: each has its own Alpine productPurchase scope so buttons work independently --}}
      <div
        class="product-card group bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:border-navy-100 transition-all flex flex-col"
        data-title="{{ strtolower($product['title'] ?? '') }}"
        data-price="{{ $displayPrice }}"
        x-data="productPurchase(false, @js($cartData), @js($cartAddUrl), @js($checkoutUrl))"
      >

        {{-- Image --}}
        <a href="{{ $pUrl }}" class="block relative overflow-hidden aspect-square bg-slate-50" tabindex="-1">
          @if($pImg)
          <img
            src="{{ \App\Support\ProductLandingAssets::cdnSized($pImg, 500) }}"
            alt="{{ $product['title'] ?? 'Product' }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
            decoding="async"
            width="400" height="400"
          >
          @else
          <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-50">
            <svg class="w-16 h-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif

          {{-- Badges --}}
          <div class="absolute top-3 left-3 flex flex-col gap-1.5">
            @if($isDainBelt)
            <span class="bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">Best Seller</span>
            @endif
            @if($savePct > 0)
            <span class="bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">-{{ $savePct }}%</span>
            @endif
          </div>
        </a>

        {{-- Body --}}
        <div class="p-4 flex flex-col flex-1">

          <a href="{{ $pUrl }}" class="block mb-2">
            <h2 class="font-display font-bold text-navy-900 text-sm leading-snug line-clamp-2 group-hover:text-navy-600 transition-colors">
              {{ $product['title'] ?? 'Product' }}
            </h2>
          </a>

@include('partials.product-rating-compact', ['stats' => $pReviewStats, 'ratingId' => $product['id'] ?? $loop->index])

          {{-- Price --}}
          <div class="flex items-center gap-2 mb-2">
            @if($displayPrice > 0)
            <span class="font-bold text-navy-900 text-base">{{ app(\App\Services\CurrencyService::class)->formatForLocale($displayPrice, $locale) }}</span>
            @if($displayCompare > $displayPrice)
            <span class="text-slate-400 line-through text-xs">{{ app(\App\Services\CurrencyService::class)->formatForLocale($displayCompare, $locale) }}</span>
            @endif
            @else
            <span class="text-slate-400 text-sm italic">{{ __('products.price_on_request') }}</span>
            @endif
          </div>

          {{-- Size/variant note --}}
          @if($pVars > 1)
          <p class="text-slate-400 text-[10px] mb-3">{{ __('products.options_available', ['count' => $pVars]) }}</p>
          @endif

          {{-- CTA buttons --}}
          <div class="flex gap-2 mt-auto pt-3 border-t border-slate-100">

            {{-- ADD TO CART ─ submits hidden form → CartController → CheckoutController --}}
            <button
              type="button"
              id="add-to-cart-{{ $product['id'] ?? $loop->index }}"
              @click="addToCart($event)"
              class="flex-1 inline-flex items-center justify-center gap-1.5 bg-navy-700 hover:bg-navy-800 active:scale-95 text-white text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-sm"
            >
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              {{ __('products.add_to_cart') }}
            </button>

            {{-- VIEW PRODUCT --}}
            <a
              href="{{ $pUrl }}"
              id="view-product-{{ $product['id'] ?? $loop->index }}"
              class="inline-flex items-center justify-center gap-1 border border-slate-200 hover:border-navy-400 hover:text-navy-700 text-slate-600 text-xs font-semibold px-3 py-2.5 rounded-xl transition-colors"
            >
              {{ __('products.view_product_short') }}
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          </div>

          {{-- Hidden form — REQUIRED by productPurchase Alpine component ($refs.checkoutForm) --}}
          <form x-ref="checkoutForm" method="POST" action="{{ $cartAddUrl }}" class="hidden">
            @csrf
            <input type="hidden" name="product_id">
            <input type="hidden" name="title">
            <input type="hidden" name="subtitle">
            <input type="hidden" name="image">
            <input type="hidden" name="price">
            <input type="hidden" name="compare_at_price">
            <input type="hidden" name="quantity">
            <input type="hidden" name="option_label">
            <input type="hidden" name="option_value">
            <input type="hidden" name="variant_id">
            <input type="hidden" name="source">
          </form>

        </div>
      </div>
      @endforeach

    </div>

    {{-- Empty search state --}}
    <div id="empty-search-msg" class="hidden text-center py-20">
      <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <p class="text-slate-600 font-semibold">No products match your search</p>
      <p class="text-slate-400 text-sm mt-1">Try a different keyword or clear the filter</p>
    </div>

    @elseif(!$error)
    <div class="text-center py-20">
      <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      <p class="text-slate-600 font-semibold">No products found</p>
      <p class="text-slate-400 text-sm mt-1">Check your Shopify connection or add products to your store.</p>
    </div>
    @endif

  </div>
</section>

@push('scripts')
<script>
(function () {
  'use strict';

  const searchInput = document.getElementById('product-search');
  const grid        = document.getElementById('products-grid');
  const emptyMsg    = document.getElementById('empty-search-msg');
  const countEl     = document.getElementById('product-count');
  const sortBtns    = document.querySelectorAll('.sort-btn');

  if (!grid) return;

  const allCards = Array.from(grid.querySelectorAll('.product-card'));

  function getActiveSort() {
    for (const btn of sortBtns) {
      if (btn.classList.contains('bg-navy-700')) return btn.dataset.sort;
    }
    return 'default';
  }

  function applyFilters() {
    const q    = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const sort = getActiveSort();

    // Filter
    let visible = allCards.filter(card => !q || card.dataset.title.includes(q));

    // Sort
    if (sort === 'price-asc')  visible.sort((a,b) => parseFloat(a.dataset.price||0) - parseFloat(b.dataset.price||0));
    if (sort === 'price-desc') visible.sort((a,b) => parseFloat(b.dataset.price||0) - parseFloat(a.dataset.price||0));
    if (sort === 'az')  visible.sort((a,b) => (a.dataset.title||'').localeCompare(b.dataset.title||''));
    if (sort === 'za')  visible.sort((a,b) => (b.dataset.title||'').localeCompare(a.dataset.title||''));

    // Apply visibility
    allCards.forEach(c => { c.style.display = 'none'; });

    if (visible.length === 0) {
      if (emptyMsg) emptyMsg.classList.remove('hidden');
    } else {
      if (emptyMsg) emptyMsg.classList.add('hidden');
      visible.forEach(c => { c.style.display = ''; grid.appendChild(c); });
    }

    if (countEl) countEl.textContent = visible.length + ' product' + (visible.length === 1 ? '' : 's');
  }

  // Wire search
  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  // Wire sort buttons
  sortBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      sortBtns.forEach(b => {
        b.classList.remove('bg-navy-700', 'text-white', 'border-navy-700');
        b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
      });
      this.classList.add('bg-navy-700', 'text-white', 'border-navy-700');
      this.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
      applyFilters();
    });
  });
})();
</script>
@endpush

@endsection
