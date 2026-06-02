@extends('layouts.app')

@php
  $locale = app()->getLocale();
  $cartAddUrl = route('cart.store', ['locale' => $locale]);
@endphp

@section('title', 'Products — ' . config('app.name'))
@section('meta_description', 'Shop the full Dainely range — premium lower back stabilization and daily wellness products.')

@section('content')

{{-- ─── PAGE HERO ─────────────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-navy-950 to-navy-900 text-white py-16">
  <div class="container-site text-center">
    <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-3">Shop All Products</p>
    <h1 class="font-display font-bold text-4xl lg:text-5xl mb-4">Premium Wellness Products</h1>
    <p class="text-navy-300 text-base max-w-xl mx-auto">
      Every Dainely product is designed for real routines — modern movement, long workdays, and everyday life.
    </p>
  </div>
</section>

{{-- ─── FILTER / SORT BAR ────────────────────────────────────────────────── --}}
<section class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm"
  x-data="{
    search: '',
    sort: 'default',
    get filteredProducts() {
      let list = window._allProducts || [];
      if (this.search.trim()) {
        const q = this.search.toLowerCase();
        list = list.filter(p => p.title.toLowerCase().includes(q));
      }
      if (this.sort === 'price-asc')  list = [...list].sort((a,b) => a.price - b.price);
      if (this.sort === 'price-desc') list = [...list].sort((a,b) => b.price - a.price);
      if (this.sort === 'az')         list = [...list].sort((a,b) => a.title.localeCompare(b.title));
      if (this.sort === 'za')         list = [...list].sort((a,b) => b.title.localeCompare(a.title));
      return list;
    }
  }"
>
  <div class="container-site py-3">
    <div class="flex flex-wrap gap-3 items-center">
      {{-- Search --}}
      <div class="flex-1 min-w-[180px] relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input
          type="search"
          x-model="search"
          placeholder="Search products…"
          id="product-search"
          class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-navy-400 focus:border-transparent"
        >
      </div>
      {{-- Sort --}}
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-slate-500 text-xs font-medium hidden sm:inline">Sort:</span>
        @foreach([
          ['default', 'Default'],
          ['price-asc', 'Price ↑'],
          ['price-desc', 'Price ↓'],
          ['az', 'A → Z'],
          ['za', 'Z → A'],
        ] as [$val, $label])
        <button
          type="button"
          @click="sort = '{{ $val }}'"
          :class="sort === '{{ $val }}' ? 'bg-navy-700 text-white border-navy-700' : 'bg-white text-slate-600 border-slate-200 hover:border-navy-400 hover:text-navy-700'"
          class="border text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
        >{{ $label }}</button>
        @endforeach
      </div>
      {{-- Result count --}}
      <p class="text-slate-500 text-xs ml-auto hidden sm:block" x-text="filteredProducts.length + ' product' + (filteredProducts.length === 1 ? '' : 's')"></p>
    </div>
  </div>
</section>

{{-- ─── PRODUCT GRID ─────────────────────────────────────────────────────── --}}
<section class="section bg-slate-50" aria-label="Product catalog">
  <div class="container-site">

    @if($error)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 mb-8 text-amber-800 text-sm">
      <p class="font-semibold mb-1">Could not load products from Shopify</p>
      <p>{{ $error }}</p>
    </div>
    @endif

    @if(count($products) > 0)

    {{-- Serialise products to JS for Alpine filter/sort --}}
    <script>
      window._allProducts = @json(collect($products)->map(function($p) use ($locale) {
        $img   = $p['image']['src'] ?? ($p['images'][0]['src'] ?? null);
        $price = (float) ($p['variants'][0]['price'] ?? 0);
        $compareAt = (float) ($p['variants'][0]['compare_at_price'] ?? 0);
        return [
          'id'         => (string) ($p['id'] ?? ''),
          'handle'     => $p['handle'] ?? '',
          'title'      => $p['title'] ?? 'Product',
          'price'      => $price,
          'compareAt'  => $compareAt,
          'image'      => $img,
          'status'     => $p['status'] ?? 'active',
          'variantCount'=> count($p['variants'] ?? []),
          'url'        => route('products.show', ['locale' => $locale, 'slug' => $p['handle'] ?? $p['id']]),
        ];
      })->values()->all());
    </script>

    {{-- Alpine grid — watches the filter component on the sticky bar --}}
    <div
      x-data="{
        get list() {
          const bar = document.querySelector('[x-data]').__x ? null : null;
          // Read from global filtered list driven by the sticky bar's x-data
          return window._allProducts || [];
        }
      }"
    >
      {{-- Static rendered grid (no JS required for basic browsing) --}}
      <div id="products-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($products as $product)
        @php
          $pImg    = $product['image']['src'] ?? ($product['images'][0]['src'] ?? null);
          $pPrice  = $product['variants'][0]['price'] ?? null;
          $pCompare= $product['variants'][0]['compare_at_price'] ?? null;
          $pHandle = $product['handle'] ?? $product['id'];
          $pUrl    = route('products.show', ['locale' => $locale, 'slug' => $pHandle]);
          $pStatus = $product['status'] ?? 'active';
          $pVars   = count($product['variants'] ?? []);
          $isDainBelt = in_array($pHandle, ['dainely-belt','dainely-comfort-belt']);
          $displayPrice = $isDainBelt ? 64.00 : (float) ($pPrice ?? 0);
          $displayCompare = $isDainBelt ? 119.00 : (float) ($pCompare ?? 0);
          $savePct = ($displayCompare > $displayPrice) ? round((($displayCompare - $displayPrice)/$displayCompare)*100) : 0;
        @endphp

        {{-- Each card is its own Alpine component so buttons work independently --}}
        <div
          class="product-card group bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-md hover:border-navy-100 transition-all flex flex-col"
          data-title="{{ strtolower($product['title'] ?? '') }}"
          data-price="{{ $displayPrice }}"
          x-data="productPurchase(false, @js([
            'id'               => (string)($product['id'] ?? $pHandle),
            'title'            => $product['title'] ?? 'Product',
            'subtitle'         => 'Premium Wellness Product',
            'image'            => $pImg ?: asset('images/dainely-belt-product.png'),
            'price'            => $displayPrice,
            'compare_at_price' => $displayCompare ?: null,
            'variants'         => [],
            'source'           => 'shopify',
          ]), @js($cartAddUrl))"
        >
          {{-- Product Image --}}
          <a href="{{ $pUrl }}" class="block relative overflow-hidden aspect-square bg-slate-50">
            @if($pImg)
            <img
              src="{{ $pImg }}"
              alt="{{ $product['title'] ?? 'Product' }}"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
              width="400" height="400"
            >
            @else
            <div class="w-full h-full flex items-center justify-center bg-slate-100">
              <svg class="w-16 h-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            @endif

            {{-- Badges --}}
            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
              @if($isDainBelt)
              <span class="bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">Best Seller</span>
              @endif
              @if($savePct > 0)
              <span class="bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">-{{ $savePct }}%</span>
              @endif
            </div>
            @if($pStatus !== 'active')
            <span class="absolute top-3 right-3 bg-slate-500/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">{{ ucfirst($pStatus) }}</span>
            @endif
          </a>

          {{-- Card Body --}}
          <div class="p-4 flex flex-col flex-1">
            <a href="{{ $pUrl }}" class="block mb-auto">
              <h2 class="font-display font-bold text-navy-900 text-sm leading-snug mb-1 line-clamp-2 group-hover:text-navy-600 transition-colors">{{ $product['title'] ?? 'Product' }}</h2>
            </a>

            {{-- Rating --}}
            <div class="flex gap-0.5 my-2">
              @for($s=0;$s<5;$s++)
              <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              @endfor
              <span class="text-slate-500 text-[10px] ml-1">4.8</span>
            </div>

            {{-- Price --}}
            <div class="flex items-center gap-2 mb-3">
              @if($displayPrice > 0)
              <span class="font-bold text-navy-900 text-base">${{ number_format($displayPrice, 2) }}</span>
              @if($displayCompare > $displayPrice)
              <span class="text-slate-400 line-through text-xs">${{ number_format($displayCompare, 2) }}</span>
              @endif
              @else
              <span class="text-slate-400 text-sm italic">Contact for price</span>
              @endif
            </div>

            {{-- Variants info --}}
            @if($pVars > 1 && !$isDainBelt)
            <p class="text-slate-400 text-[10px] mb-2">{{ $pVars }} variants available</p>
            @elseif($isDainBelt)
            <p class="text-slate-400 text-[10px] mb-2">Sizes: S/M · L/XL · 2XL · 3XL</p>
            @endif

            {{-- Action buttons --}}
            <div class="flex gap-2 mt-auto pt-3 border-t border-slate-100">
              {{-- "Add to Cart" — goes directly to checkout --}}
              <button
                type="button"
                @click="goToCheckout($event)"
                id="add-to-cart-{{ $product['id'] ?? $loop->index }}"
                class="flex-1 inline-flex items-center justify-center gap-1.5 bg-navy-700 hover:bg-navy-800 text-white text-xs font-semibold px-3 py-2.5 rounded-xl transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Add to Cart
              </button>
              {{-- "View" — goes to detail page --}}
              <a
                href="{{ $pUrl }}"
                id="view-product-{{ $product['id'] ?? $loop->index }}"
                class="inline-flex items-center justify-center gap-1 text-navy-600 hover:text-navy-800 border border-navy-200 hover:border-navy-400 text-xs font-semibold px-3 py-2.5 rounded-xl transition-colors"
              >
                View
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </a>
            </div>

            {{-- Hidden cart form for this product --}}
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

      {{-- Empty search state (JS-driven) --}}
      <div id="empty-search-msg" class="hidden text-center py-20">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-slate-500 font-semibold">No products match your search</p>
        <p class="text-slate-400 text-sm mt-1">Try a different keyword or clear the filter</p>
      </div>

    </div>

    @elseif(!$error)
    <div class="text-center py-20">
      <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      <p class="text-slate-500 font-semibold">No products found</p>
      <p class="text-slate-400 text-sm mt-1">Check your Shopify connection or add products to your store.</p>
    </div>
    @endif

  </div>
</section>

@push('scripts')
<script>
(function() {
  const searchInput = document.getElementById('product-search');
  const grid  = document.getElementById('products-grid');
  const empty = document.getElementById('empty-search-msg');
  if (!searchInput || !grid) return;

  function applyFilters() {
    const q    = (searchInput.value || '').toLowerCase().trim();
    const sort = document.querySelector('[class*="bg-navy-700"][class*="text-white"]')?.dataset?.sort || 'default';
    const cards = Array.from(grid.querySelectorAll('.product-card'));

    let visible = cards.filter(card => {
      if (!q) return true;
      return card.dataset.title.includes(q);
    });

    // Sort
    const sortBtns = document.querySelectorAll('[data-sort]');
    let activeSortVal = 'default';
    sortBtns.forEach(btn => { if (btn.classList.contains('bg-navy-700')) activeSortVal = btn.dataset.sort; });

    if (activeSortVal === 'price-asc')  visible.sort((a,b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
    if (activeSortVal === 'price-desc') visible.sort((a,b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
    if (activeSortVal === 'az') visible.sort((a,b) => a.dataset.title.localeCompare(b.dataset.title));
    if (activeSortVal === 'za') visible.sort((a,b) => b.dataset.title.localeCompare(a.dataset.title));

    // Hide all, then show matching (preserve order for sort)
    cards.forEach(c => c.style.display = 'none');
    if (visible.length === 0) {
      empty.classList.remove('hidden');
    } else {
      empty.classList.add('hidden');
      visible.forEach(c => { c.style.display = ''; grid.appendChild(c); });
    }
  }

  // Wire search input
  searchInput.addEventListener('input', applyFilters);

  // Wire sort buttons
  document.querySelectorAll('[data-sort]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-sort]').forEach(b => {
        b.classList.remove('bg-navy-700','text-white','border-navy-700');
        b.classList.add('bg-white','text-slate-600','border-slate-200');
      });
      this.classList.add('bg-navy-700','text-white','border-navy-700');
      this.classList.remove('bg-white','text-slate-600','border-slate-200');
      applyFilters();
    });
  });
})();
</script>
@endpush

@endsection
