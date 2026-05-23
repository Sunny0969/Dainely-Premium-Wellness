@extends('layouts.app')

@php
  $title     = $product['title'] ?? 'Product';
  $desc      = $product['body_html'] ?? '';
  $plainDesc = strip_tags($desc);
  $images    = $product['images'] ?? [];
  $mainImg   = $images[0]['src'] ?? ($product['image']['src'] ?? null);
  $variants  = $product['variants'] ?? [];
  $firstVar  = $variants[0] ?? [];
  $price     = $firstVar['price'] ?? null;
  $compareAt = $firstVar['compare_at_price'] ?? null;
  $status    = $product['status'] ?? 'active';
  $vendor    = $product['vendor'] ?? '';
  $tags      = $product['tags'] ?? '';
  $handle    = $product['handle'] ?? '';
  $shopDomain = config('shopify.store_domain', '');
@endphp

@section('title', $title . ' — ' . config('app.name'))
@section('meta_description', Str::limit($plainDesc, 160) ?: 'View product details.')
@section('og_image', $mainImg ?? '')

@section('content')

{{-- Breadcrumb --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('shop.index') }}" class="hover:text-navy-700 transition-colors">Shop</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">{{ Str::limit($title, 40) }}</span>
    </nav>
  </div>
</div>

{{-- PRODUCT HERO --}}
<section class="section bg-white" aria-label="Product detail">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Image Gallery --}}
      <div x-data="shopifyGallery()" class="sticky top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-medium mb-4 group">
          <template x-if="images.length > 0">
            <img
              :src="images[active]"
              :alt="'{{ $title }} — image ' + (active + 1)"
              class="w-full aspect-square object-cover object-center transition-all duration-500"
              width="640" height="640"
            >
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          {{-- Status badge --}}
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $status === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white' }}">
              {{ ucfirst($status) }}
            </span>
          </div>
          @if($vendor)
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow-soft">
            <span class="text-navy-700 text-xs font-semibold">{{ $vendor }}</span>
          </div>
          @endif
        </div>

        {{-- Thumbnails --}}
        @if(count($images) > 1)
        <div class="grid grid-cols-5 gap-2">
          <template x-for="(img, i) in images" :key="i">
            <button
              @click="setActive(i)"
              :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'"
              class="rounded-xl overflow-hidden aspect-square focus:outline-none transition-all duration-200"
            >
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif

        {{-- Trust strip --}}
        <div class="grid grid-cols-3 gap-3 mt-6 p-4 bg-slate-50 rounded-2xl">
          <div class="text-center">
            <div class="w-8 h-8 bg-sage-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-4 h-4 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <p class="text-slate-700 text-xs font-semibold">30-Day</p>
            <p class="text-slate-500 text-[10px]">Guarantee</p>
          </div>
          <div class="text-center">
            <div class="w-8 h-8 bg-navy-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-4 h-4 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="text-slate-700 text-xs font-semibold">Free Ship</p>
            <p class="text-slate-500 text-[10px]">Over $75</p>
          </div>
          <div class="text-center">
            <div class="w-8 h-8 bg-gold-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-4 h-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <p class="text-slate-700 text-xs font-semibold">Secure</p>
            <p class="text-slate-500 text-[10px]">Payment</p>
          </div>
        </div>
      </div>

      {{-- RIGHT: Product Info --}}
      <div>
        @if($vendor)
        <p class="eyebrow mb-3">{{ $vendor }}</p>
        @endif

        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(1.75rem, 4vw, 2.5rem); line-height: 1.15;">
          {{ $title }}
        </h1>

        {{-- Rating row (static decorative) --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="stars flex items-center gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-sage-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        @if($price)
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format((float)$price, 2) }}</span>
            @if($compareAt && (float)$compareAt > (float)$price)
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format((float)$compareAt, 2) }}</span>
            @endif
          </div>
          @if($compareAt && (float)$compareAt > (float)$price)
          <div class="ml-auto">
            @php $saving = round((((float)$compareAt - (float)$price) / (float)$compareAt) * 100); @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $saving }}%</span>
          </div>
          @endif
        </div>
        @endif

        {{-- Description --}}
        @if($plainDesc)
        <div class="text-slate-600 text-base leading-relaxed mb-6 prose prose-slate max-w-none">
          {!! $desc !!}
        </div>
        @endif

        {{-- Variants selector --}}
        @if(count($variants) > 1)
        <div class="mb-6" x-data="{ selected: null }">
          <label class="form-label mb-3 block">Select Option</label>
          <div class="flex flex-wrap gap-2">
            @foreach($variants as $variant)
            <button
              @click="selected = {{ $loop->index }}"
              :class="selected === {{ $loop->index }} ? 'border-navy-600 bg-navy-50 text-navy-700' : 'border-slate-200 text-slate-600 hover:border-navy-400'"
              class="border-2 font-semibold py-2 px-4 rounded-xl text-sm transition-all duration-200 focus:outline-none"
            >
              {{ $variant['title'] ?? 'Option' }}
              @if(!empty($variant['price']))
              — ${{ number_format((float)$variant['price'], 2) }}
              @endif
            </button>
            @endforeach
          </div>
        </div>
        @endif

        {{-- Quantity + Add to Cart --}}
        <div class="flex items-center gap-4 mb-4">
          <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden">
            <button class="px-4 py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-lg"
              x-data x-on:click="$dispatch('decrement-qty')">−</button>
            <span class="px-5 py-3 font-semibold text-navy-900 text-lg border-x-2 border-slate-200" x-data="{qty:1}" x-on:increment-qty.window="qty++" x-on:decrement-qty.window="qty = Math.max(1, qty-1)" x-text="qty" id="qty-display">1</span>
            <button class="px-4 py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-lg"
              x-data x-on:click="$dispatch('increment-qty')">+</button>
          </div>
          <a
            href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
            class="btn-primary-lg flex-1 justify-center"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Add to Cart
          </a>
        </div>

        {{-- Order Now --}}
        <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="btn-gold-lg w-full justify-center mb-6">
          Order Now — Free Shipping
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Money-Back Guarantee</p>
            <p class="text-sage-600 text-xs">Not satisfied? Full refund, no questions asked. Zero risk.</p>
          </div>
        </div>

        {{-- Tags --}}
        @if($tags)
        <div class="mt-6 flex flex-wrap gap-2">
          @foreach(explode(',', $tags) as $tag)
          @if(trim($tag))
          <span class="bg-slate-100 text-slate-600 text-xs font-medium px-3 py-1 rounded-full">{{ trim($tag) }}</span>
          @endif
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- All Variants Table --}}
@if(count($variants) > 0)
<section class="section bg-section-alt">
  <div class="container-site">
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">Available Options</p>
      <h2 class="heading-section mb-4">All Variants</h2>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-medium">Option</th>
            <th class="px-4 py-3 font-medium">SKU</th>
            <th class="px-4 py-3 font-medium">Price</th>
            <th class="px-4 py-3 font-medium">Compare At</th>
            <th class="px-4 py-3 font-medium">Available</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($variants as $variant)
          <tr class="hover:bg-slate-50/80">
            <td class="px-4 py-3 font-medium text-navy-900">{{ $variant['title'] ?? '—' }}</td>
            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant['sku'] ?? '—' }}</td>
            <td class="px-4 py-3 font-semibold text-navy-800">
              @if(!empty($variant['price'])) ${{ number_format((float)$variant['price'], 2) }} @else — @endif
            </td>
            <td class="px-4 py-3 text-slate-400 line-through text-sm">
              @if(!empty($variant['compare_at_price'])) ${{ number_format((float)$variant['compare_at_price'], 2) }} @else — @endif
            </td>
            <td class="px-4 py-3">
              @if(($variant['inventory_quantity'] ?? 1) > 0)
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800">In Stock</span>
              @else
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700">Out of Stock</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
@endif

{{-- Back to shop --}}
<section class="py-8 bg-white border-t border-slate-100">
  <div class="container-site text-center">
    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-navy-600 hover:text-navy-800 font-semibold transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      Back to All Products
    </a>
  </div>
</section>

{{-- ============================================================
     STICKY BOTTOM ORDER BAR — appears when user scrolls past hero
     ============================================================ --}}
<div
  id="sticky-order-bar"
  class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out"
  aria-label="Quick order bar"
>
  <div class="container-site py-3">
    <div class="flex items-center gap-4">

      {{-- Product thumbnail --}}
      @if($mainImg)
      <img src="{{ $mainImg }}" alt="{{ $title }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      @endif

      {{-- Product name + price --}}
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-sm truncate">{{ $title }}</p>
        @if($price)
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold">${{ number_format((float)$price, 2) }}</span>
          @if($compareAt && (float)$compareAt > (float)$price)
          <span class="text-slate-400 line-through text-xs">${{ number_format((float)$compareAt, 2) }}</span>
          <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
            -{{ round((((float)$compareAt - (float)$price) / (float)$compareAt) * 100) }}%
          </span>
          @endif
        </div>
        @endif
      </div>

      {{-- Qty counter --}}
      <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden flex-shrink-0">
        <button
          onclick="stickyQty = Math.max(1, stickyQty - 1); document.getElementById('sticky-qty-display').textContent = stickyQty"
          class="px-3 py-2 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold">−</button>
        <span id="sticky-qty-display" class="px-4 py-2 font-semibold text-navy-900 border-x-2 border-slate-200 min-w-[2.5rem] text-center">1</span>
        <button
          onclick="stickyQty = stickyQty + 1; document.getElementById('sticky-qty-display').textContent = stickyQty"
          class="px-3 py-2 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold">+</button>
      </div>

      {{-- Order Now button --}}
      @if($shopDomain && $handle)
      <a
        href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
        class="flex-shrink-0 inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-sm shadow-md"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Order Now
      </a>
      @endif

      {{-- Scroll to top --}}
      <button
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 hover:bg-navy-100 text-slate-600 hover:text-navy-700 flex items-center justify-center transition-colors"
        title="Back to top"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
      </button>

    </div>
  </div>
</div>

@push('scripts')
<script>
  // Sticky bar qty counter
  let stickyQty = 1;

  // Show sticky bar when user scrolls past the product hero section
  const stickyBar = document.getElementById('sticky-order-bar');
  const heroSection = document.querySelector('section[aria-label="Product detail"]');

  function updateStickyBar() {
    if (!heroSection || !stickyBar) return;
    const heroBottom = heroSection.getBoundingClientRect().bottom;
    if (heroBottom < 0) {
      stickyBar.classList.remove('translate-y-full');
    } else {
      stickyBar.classList.add('translate-y-full');
    }
  }

  window.addEventListener('scroll', updateStickyBar, { passive: true });
  updateStickyBar();

  // Alpine gallery
  document.addEventListener('alpine:init', () => {
    Alpine.data('shopifyGallery', () => ({
      active: 0,
      images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
      setActive(i) { this.active = i; },
    }));
  });
</script>
@endpush

@endsection
