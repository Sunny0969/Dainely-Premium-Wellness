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
  $locale    = app()->getLocale();
  $checkoutUrl = route('checkout.index', ['locale' => $locale]);
  $cartAddUrl  = route('cart.store',    ['locale' => $locale]);
  $requiresOption = count($variants) > 1;

  // Detect Dainely Belt variants
  $isDainelyBelt = in_array($handle, ['dainely-belt', 'dainely-comfort-belt']);

  // For Dainely Belt: use fixed price / sizes; for others use Shopify variants
  if ($isDainelyBelt) {
    $beltPrice   = 64.00;
    $beltCompare = 119.00;
    $beltSaving  = round((($beltCompare - $beltPrice) / $beltCompare) * 100);
    $staticSizes = ['S/M', 'L/XL', '2XL', '3XL'];
    $cartProduct = [
      'id'              => (string) ($product['id'] ?? $handle),
      'title'           => 'Dainely Belt',
      'subtitle'        => 'Premium everyday lower back stabilization',
      'image'           => $mainImg ?: asset('images/dainely-belt-product.png'),
      'price'           => $beltPrice,
      'compare_at_price'=> $beltCompare,
      'variants'        => collect($staticSizes)->values()->map(fn($s, $i) => [
        'index'            => $i,
        'id'               => $s,
        'title'            => $s,
        'price'            => $beltPrice,
        'compare_at_price' => $beltCompare,
      ])->all(),
      'source' => 'shopify',
    ];
    $requiresOption = true;
  } else {
    $cartProduct = [
      'id'              => (string) ($product['id'] ?? $handle),
      'title'           => $title,
      'subtitle'        => \Illuminate\Support\Str::limit($plainDesc, 100) ?: 'Premium Wellness Product',
      'image'           => $mainImg ?: asset('images/dainely-belt-product.png'),
      'price'           => (float) ($price ?? 0),
      'compare_at_price'=> $compareAt ? (float) $compareAt : null,
      'variants'        => collect($variants)->values()->map(function ($v, $i) {
        return [
          'index'            => $i,
          'id'               => (string) ($v['id'] ?? $i),
          'title'            => $v['title'] ?? 'Option',
          'price'            => (float) ($v['price'] ?? 0),
          'compare_at_price' => isset($v['compare_at_price']) ? (float) $v['compare_at_price'] : null,
        ];
      })->all(),
      'source' => 'shopify',
    ];
  }
@endphp

@section('title', ($isDainelyBelt ? 'Dainely Belt — Premium Lower Back Stabilization' : $title . ' — ' . config('app.name')))
@section('meta_description', ($isDainelyBelt ? 'Premium everyday lower back stabilization designed for modern movement and long daily routines. Free shipping on orders over $75.' : (\Illuminate\Support\Str::limit($plainDesc, 160) ?: 'View product details.')))
@section('og_image', $mainImg ?? asset('images/dainely-belt-product.png'))

@if($isDainelyBelt)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely Belt",
  "image": "{{ asset('images/dainely-belt-product.png') }}",
  "description": "Premium everyday lower back stabilization designed for modern movement and long daily routines.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "64.00",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "1247"
  }
}
</script>
@endsection
@endif

@section('content')

{{-- ============================================================
     DAINELY BELT — PREMIUM CUSTOM PAGE
     ============================================================ --}}
@if($isDainelyBelt)

<div x-data="productPurchase(true, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely Belt</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="{
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/dainely-belt-product.png') }}',
          '{{ asset('images/hero-lifestyle.png') }}',
          '{{ asset('images/lifestyle-desk-professional.png') }}',
          '{{ asset('images/lifestyle-everyday-movement.png') }}'
        ],
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="Dainely Belt" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Developed
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        <div class="grid grid-cols-4 gap-2">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-400'" class="rounded-xl overflow-hidden aspect-square focus:outline-none transition-all">
              <img :src="img" alt="" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        {{-- Trust strip --}}
        <div class="grid grid-cols-3 gap-3 mt-5 p-4 bg-slate-50 rounded-2xl">
          @foreach([['30-Day', 'Guarantee', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'sage'], ['Free Ship', 'Over $75', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'navy'], ['Secure', 'Payment', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'gold']] as [$label, $sub, $path, $c])
          <div class="text-center">
            <div class="w-8 h-8 bg-{{ $c }}-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-4 h-4 text-{{ $c }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
            </div>
            <p class="text-slate-700 text-xs font-semibold">{{ $label }}</p>
            <p class="text-slate-500 text-[10px]">{{ $sub }}</p>
          </div>
          @endforeach
        </div>
      </div>

      {{-- RIGHT: Product Info --}}
      <div>
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Premium Everyday Lower Back Stabilization</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.1;">
          The support you need.<br>The freedom to keep moving.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">4.8</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">1,247 verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($beltPrice, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($beltCompare, 2) }}</span>
          </div>
          <div class="ml-auto">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $beltSaving }}%</span>
          </div>
        </div>
        <p class="text-slate-500 text-xs mb-5">Or 4 interest-free payments of $16.00 with Square.</p>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Long hours at a desk or on your feet shouldn't dictate your comfort.
          Dainely Belt provides targeted, adjustable lower back stabilization designed to fit naturally into your day — under your clothes, during movement, and throughout modern routines.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Targeted SI Joint stabilization for balanced everyday movement', 'sage'],
            ['Proprietary breathable mesh — designed for extended daily wear', 'sage'],
            ['Dual-tension adjustment: seated support or firmer stabilization', 'sage'],
            ['Low-profile silhouette — discreet under most everyday clothing', 'sage'],
            ['Flexible enough to move with you through work, travel & daily activity', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Size selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> true,
          'options'       => $staticSizes,
          'optionType'    => 'static',
          'optionLabel'   => 'Select Size',
          'showSizeGuide' => true,
          'sizeGuideHref' => '#size-guide',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Dainely Belt',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">60-Day Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Try it as part of your daily routine with full confidence. Not right? Full refund.</p>
          </div>
        </div>

        {{-- Micro-trust row --}}
        <div class="flex flex-wrap gap-4 mt-5 text-xs text-slate-500">
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> Secure checkout</span>
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Fast shipping</span>
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> Trusted by thousands</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 2. AUTHORITY STRIP ────────────────────────────────────── --}}
<section class="bg-white border-y border-slate-100 py-10" aria-label="Trust signals">
  <div class="container-site">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
      @foreach([
        ['Anatomically Designed', 'Targeted stabilization around the lower back and SI joint region.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Breathable Performance Fabric', 'Lightweight materials designed for extended daily wear.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['60-Day Comfort Guarantee', 'Try it as part of your routine with full confidence.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Discrete Under-Clothing Fit', 'Low-profile design intended for everyday wear under regular clothing.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
      ] as [$title, $copy, $path])
      <div class="group">
        <div class="w-12 h-12 bg-slate-50 group-hover:bg-navy-50 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
          <svg class="w-6 h-6 text-slate-500 group-hover:text-navy-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
        </div>
        <p class="font-semibold text-slate-800 text-sm mb-1">{{ $title }}</p>
        <p class="text-slate-500 text-xs leading-relaxed">{{ $copy }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 3. LIFESTYLE POSITIONING ──────────────────────────────── --}}
<section class="section bg-stone-50" aria-label="Lifestyle">
  <div class="container-site">
    <div class="max-w-2xl mb-12">
      <p class="eyebrow mb-3">The Invisible Partner</p>
      <h2 class="heading-section text-stone-900 mb-4">Support that fits into real life</h2>
      <p class="text-body text-stone-600">
        Most back supports are bulky, restrictive, and designed for moments when you stop moving.
        Dainely is different — designed for the in-between moments: the morning commute, the long meeting,
        the afternoon walk, the standing desk, and the routines that make up everyday life.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['lifestyle-desk-professional.png', 'At the Standing Desk', 'Lightweight support through long work sessions — discreet under professional clothing.'],
        ['lifestyle-everyday-movement.png', 'During Daily Movement', 'Engineered to move with you through errands, walks, and everyday activity.'],
        ['lifestyle-travel-commute.png', 'Commute & Travel', 'Comfortable through long seated routines — from morning drives to air travel.'],
      ] as [$img, $cap, $sub])
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ asset('images/' . $img) }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
        </div>
        <figcaption>
          <p class="font-semibold text-stone-800 text-sm mb-0.5">{{ $cap }}</p>
          <p class="text-stone-500 text-xs">{{ $sub }}</p>
        </figcaption>
      </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4. PRODUCT BENEFITS ───────────────────────────────────── --}}
<section class="section bg-white" aria-label="Product benefits">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Why It's Worth It</p>
      <h2 class="heading-section mb-4">Designed for targeted everyday stabilization</h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach([
        ['Targeted SI Joint Stabilization', 'Unlike bulky wraparound braces, Dainely focuses support around the lower back and pelvic region to encourage balanced everyday movement.', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'navy'],
        ['Proprietary Breathable Mesh', 'Engineered for extended wear with airflow-focused materials designed to reduce heat buildup during long daily routines.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z', 'sage'],
        ['Dual-Tension Adjustment', 'Two-layer strap system allows quick adjustment between lighter seated support and firmer everyday stabilization.', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'gold'],
        ['Low-Profile Silhouette', 'Designed to remain discreet under most everyday clothing without adding unnecessary bulk or limiting movement.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'navy'],
        ['Flexible Everyday Wear', 'Moves naturally with your body throughout work, travel, and daily activity without restricting natural movement.', 'M13 10V3L4 14h7v7l9-11h-7z', 'sage'],
        ['Fast Everyday Setup', 'Easy to put on, remove, and adjust throughout the day — no complex fastening systems required.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'gold'],
      ] as [$title, $copy, $path, $color])
      <div class="card p-7 group hover:shadow-lg transition-shadow">
        <div class="w-11 h-11 bg-{{ $color }}-50 rounded-xl flex items-center justify-center mb-4 group-hover:bg-{{ $color }}-100 transition-colors">
          <svg class="w-5 h-5 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
        </div>
        <h3 class="heading-card mb-2">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $copy }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. HOW IT WORKS ───────────────────────────────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-3">Step by Step</p>
      <h2 class="heading-section text-white mb-4">How Dainely works with your movement</h2>
      <p class="text-navy-300 text-base max-w-2xl mx-auto">
        Rather than immobilizing your body with rigid structure, Dainely provides adjustable stabilization
        while allowing natural movement throughout your routine.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Stabilize', 'The lightweight compression system wraps around your lower back and SI joint region, providing targeted support that adjusts to your body.', 'navy'],
        ['02', 'Support', 'Dual-layer panels maintain posture awareness throughout daily activities — sitting, standing, walking, and commuting.', 'gold'],
        ['03', 'Move Freely', 'Unlike rigid braces, Dainely moves with you, helping you feel more supported during long periods of sitting, standing, and movement.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="bg-white/10 rounded-3xl p-8 text-center hover:bg-white/15 transition-colors">
        <div class="w-16 h-16 bg-{{ $color }}-500/20 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-300">{{ $num }}</span>
        </div>
        <h3 class="font-display font-bold text-white text-xl mb-3">{{ $title }}</h3>
        <p class="text-navy-300 text-sm leading-relaxed">{{ $desc }}</p>
      </div>
      @endforeach
    </div>

    {{-- Clinical stats --}}
    <div class="grid sm:grid-cols-4 gap-4 mt-14">
      @foreach([['87%','Report improved comfort within 4 weeks'],['94%','Would recommend to a friend'],['3 yrs','Clinical development timeline'],['50K+','Customers served worldwide']] as [$stat,$label])
      <div class="bg-white/10 rounded-2xl p-5 text-center">
        <p class="font-display font-bold text-3xl text-gold-300 mb-1">{{ $stat }}</p>
        <p class="text-navy-300 text-xs">{{ $label }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 6. TESTIMONIALS & REVIEWS ─────────────────────────────── --}}
<section id="reviews" class="section bg-section-alt" aria-label="Customer reviews">
  <div class="container-site">
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">Real Customer Experiences</p>
      <h2 class="heading-section mb-4">What Our Customers Say</h2>
      <div class="flex items-center justify-center gap-3 mb-6">
        <div class="flex gap-0.5">
          @for ($i = 0; $i < 5; $i++)
          <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          @endfor
        </div>
        <span class="text-slate-700 font-bold">4.8 / 5</span>
        <span class="text-slate-400">·</span>
        <span class="text-slate-500 text-sm">1,247 verified reviews</span>
      </div>
      {{-- Review filter tags --}}
      <div class="flex flex-wrap gap-2 justify-center">
        @foreach(['Back Pain','Sciatica','Sitting at Work','Driving','Standing Desk','Everyday Wear'] as $tag)
        <span class="px-4 py-1.5 rounded-full text-xs font-semibold bg-white border border-slate-200 text-slate-600 cursor-default hover:border-navy-300 hover:text-navy-700 transition-colors">{{ $tag }}</span>
        @endforeach
      </div>
    </div>

    {{-- Featured review --}}
    <div class="bg-navy-50 border border-navy-100 rounded-3xl p-8 mb-8 max-w-3xl mx-auto">
      <div class="flex gap-1 mb-4">
        @for ($i = 0; $i < 5; $i++)<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
      </div>
      <blockquote class="text-lg text-navy-800 font-display italic leading-relaxed mb-4">
        "I struggled with shooting sciatica discomfort during my morning commute for years. I started wearing the Dainely Belt during my drive, and the added stabilization changed how I approached my workday."
      </blockquote>
      <p class="text-slate-500 text-xs">— Sarah K., Verified Buyer · Dainely Belt Size L/XL</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['Sarah M.', 'Texas, USA', 'testimonial-sarah.jpg', '"I have had lower back discomfort for 3 years. After wearing the Dainely Belt consistently for 2 weeks, I noticed a real difference in how I felt sitting at my desk. The fit is comfortable and it stays in place all day."', 5, 'Dainely Belt · Size L/XL', 'Sitting at Work'],
        ['Jean-Pierre D.', 'Paris, France', 'testimonial-jean.jpg', '"Quality construction and thoughtful design. I wear it during my commute and throughout my workday. The adjustment system is simple and effective — this has become part of my daily routine."', 5, 'Dainely Belt · Size M', 'Driving'],
        ['Klaus H.', 'Munich, Germany', 'testimonial-klaus.jpg', '"I use it for desk work and occasional travel. Simple to put on, discreet under clothing, and the support feels consistent throughout the day. I recommend it to anyone spending long hours seated."', 5, 'Dainely Belt · Size XL', 'Everyday Wear'],
      ] as [$name, $location, $avatar, $review, $stars, $product, $tag])
      <div class="testimonial-card">
        <div class="flex items-start justify-between mb-3">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < $stars; $i++)
            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="trust-badge text-sage-700 bg-sage-50 border-sage-200 text-[10px]">✓ Verified</span>
        </div>
        <span class="inline-block px-2 py-0.5 rounded-full bg-navy-50 text-navy-600 text-[10px] font-semibold mb-3">{{ $tag }}</span>
        <p class="text-slate-700 text-sm leading-relaxed mb-4">{{ $review }}</p>
        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
          <img src="{{ asset('images/' . $avatar) }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-slate-100" loading="lazy" width="40" height="40">
          <div>
            <p class="font-semibold text-slate-800 text-sm">{{ $name }}</p>
            <p class="text-slate-400 text-xs">{{ $location }} · {{ $product }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 7. VIDEO SECTION ─────────────────────────────────────── --}}
<section id="home-video" class="section bg-stone-900 text-white" aria-label="Dainely in motion">
  <div class="container-site">
    <div class="max-w-2xl mx-auto text-center mb-10">
      <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-3">A Day in Motion</p>
      <h2 class="heading-section text-white mb-3">Support that follows your lead</h2>
      <p class="text-stone-400">Watch how Dainely integrates naturally into a full day — from morning routine to evening wind-down.</p>
    </div>
    <div class="rounded-3xl overflow-hidden bg-stone-800 max-w-4xl mx-auto aspect-video ring-1 ring-white/10 relative">
      <img src="{{ asset('images/hero-lifestyle.png') }}" alt="Dainely Belt daily routine" class="w-full h-full object-cover opacity-80" loading="lazy" width="1280" height="720">
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center">
          <svg class="w-7 h-7 text-white ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
        </div>
      </div>
      <p class="absolute bottom-4 left-0 right-0 text-center text-white/60 text-xs">Video — self-hosted MP4 / WebM</p>
    </div>
    <p class="text-center text-stone-500 text-xs mt-4">For best results, upload a self-hosted MP4 to <code class="bg-stone-800 px-1.5 py-0.5 rounded text-stone-300">public/videos/dainely-daily-routine.mp4</code></p>
  </div>
</section>

{{-- ── 8. DAILY RELIEF SYSTEM ────────────────────────────────── --}}
<section class="section bg-white" aria-label="Daily Relief System">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 items-center rounded-3xl overflow-hidden bg-stone-50 ring-1 ring-stone-200">
      <div class="p-10 lg:p-14">
        <p class="eyebrow mb-3">Evolution of Care</p>
        <h2 class="heading-section text-stone-900 mb-4">The Daily Relief System</h2>
        <p class="text-body text-stone-600 mb-6">
          Stabilization is only one part of the equation. The Daily Relief System combines Dainely Belt
          for daytime support with an evening-focused recovery routine designed around movement,
          consistency, and everyday function.
        </p>
        <p class="text-stone-600 text-sm mb-8">A more complete approach to daily back wellness.</p>
        <a href="{{ route('products.index', ['locale' => $locale]) }}" class="btn-outline border-stone-300 text-stone-800 hover:bg-stone-900 hover:text-white hover:border-stone-900">
          View the Full Protocol →
        </a>
      </div>
      <div class="relative min-h-[300px] lg:min-h-full bg-stone-100">
        <img src="{{ asset('images/daily-relief-system.png') }}" alt="Daily Relief System" class="absolute inset-0 w-full h-full object-cover" loading="lazy" width="640" height="480">
      </div>
    </div>
  </div>
</section>

{{-- ── 9. EDUCATIONAL AUTHORITY ──────────────────────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Why rigid support often falls short</p>
        <h2 class="heading-section text-white mb-6">Support that works alongside natural movement</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Many traditional braces rely on rigid restriction that can feel bulky and difficult to integrate into everyday life.
          Dainely is designed around a different philosophy: support that works alongside natural movement.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          By providing targeted stabilization around the lower back and SI joint region, the belt is intended to help users
          feel supported throughout work, movement, and daily routines.
        </p>
        <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-outline border-white/30 text-white hover:bg-white/10">
          Read Our Physician-Reviewed Guide on SI Joint Health
        </a>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/spine-anatomy.png') }}" alt="SI joint stabilization anatomy" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"A thoughtful approach to lumbar stabilization."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 10. SIZE GUIDE ────────────────────────────────────────── --}}
<section id="size-guide" class="section bg-white" aria-label="Size guide">
  <div class="container-site">
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">Perfect Fit</p>
      <h2 class="heading-section mb-4">Choose Your Size</h2>
    </div>
    <div class="max-w-2xl mx-auto overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
      <table class="w-full text-sm text-left">
        <thead class="bg-navy-50 text-navy-700 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3 font-semibold">Size</th>
            <th class="px-5 py-3 font-semibold">Waist Circumference</th>
            <th class="px-5 py-3 font-semibold">Recommended For</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach([
            ['S/M', '26" – 34"', 'Small to Medium frame'],
            ['L/XL', '34" – 42"', 'Large to Extra Large frame'],
            ['2XL', '42" – 50"', 'Double Extra Large frame'],
            ['3XL', '50" – 58"', 'Triple Extra Large frame'],
          ] as [$size, $waist, $rec])
          <tr class="hover:bg-slate-50/80">
            <td class="px-5 py-3 font-semibold text-navy-900">{{ $size }}</td>
            <td class="px-5 py-3 text-slate-600">{{ $waist }}</td>
            <td class="px-5 py-3 text-slate-500 text-xs">{{ $rec }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

{{-- ── 11. FAQ ───────────────────────────────────────────────── --}}
<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Common Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['faq1', 'Will this make my muscles weak?', 'No. Unlike rigid braces, Dainely is designed for flexible everyday support while allowing natural movement throughout your routine.'],
        ['faq2', 'How do I choose my size?', 'Use our size chart above. Measure your natural waistline and match to the circumference ranges listed. If between sizes, size up for comfort.'],
        ['faq3', 'Can I wash it?', 'Yes. Hand wash with mild soap and air dry to help preserve material integrity and shape.'],
        ['faq4', 'Can I wear it while sitting?', 'Many customers wear Dainely during desk work, commuting, and extended seated routines. Adjust tension for seated comfort.'],
        ['faq5', 'Is it visible under clothing?', 'The low-profile design is intended to remain discreet under most everyday clothing, including work shirts and casual wear.'],
        ['faq6', 'How long can I wear it daily?', 'Many customers wear Dainely throughout workdays and daily routines depending on personal comfort preference. Start with 2–4 hours and adjust as needed.'],
        ['faq7', 'What is your return policy?', 'We offer a 60-Day Comfort Guarantee. If you\'re not satisfied, contact support for a full refund — no questions asked.'],
        ['faq8', 'When will my order ship?', 'Orders ship within 1–2 business days. Standard shipping is 5–8 business days. Free shipping on all orders over $75.'],
      ] as [$id, $q, $a])
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <button @click="toggle('{{ $id }}')" class="w-full flex items-center justify-between px-6 py-4 text-left focus:outline-none group">
          <span class="font-semibold text-slate-800 text-sm group-hover:text-navy-700 transition-colors">{{ $q }}</span>
          <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0 ml-4" :class="isOpen('{{ $id }}') ? 'rotate-180 text-navy-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="isOpen('{{ $id }}')" x-collapse class="px-6 pb-5">
          <p class="text-slate-600 text-sm leading-relaxed">{{ $a }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 12. SHIPPING / GUARANTEE ──────────────────────────────── --}}
<section class="section bg-white" aria-label="Shipping and guarantee">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Peace of Mind</p>
      <h2 class="heading-section mb-4">Designed with confidence in mind</h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach([
        ['Secure Checkout', '256-bit SSL encrypted payment processing', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'navy'],
        ['Fast Shipping', 'Orders ship within 1–2 business days. Free shipping over $75', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'sage'],
        ['60-Day Guarantee', 'Not satisfied? Full refund — no questions, no hassle', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'gold'],
        ['Responsive Support', 'Mon–Fri 9am–5pm. Email: contact@dainelylab.com', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'navy'],
      ] as [$title, $copy, $path, $color])
      <div class="text-center p-6 rounded-2xl bg-{{ $color }}-50 border border-{{ $color }}-100">
        <div class="w-12 h-12 bg-{{ $color }}-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
        </div>
        <p class="font-bold text-{{ $color }}-900 text-sm mb-1">{{ $title }}</p>
        <p class="text-{{ $color }}-700 text-xs leading-relaxed">{{ $copy }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 13. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Ready to Start</p>
    <h2 class="heading-section mb-4">Support your movement. Support your routine.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for long days, movement, work, travel, and modern life.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">$64.00</span>
    </div>
    <p class="text-slate-500 text-sm mb-8">Or 4 interest-free payments of $16.00 with Square.</p>

    <div class="max-w-sm mx-auto space-y-3">
      <div class="mb-4">
        <p class="text-sm font-semibold text-slate-700 mb-3">Select your size:</p>
        <div class="flex flex-wrap gap-2 justify-center">
          @foreach($staticSizes as $size)
          <button type="button" @click="selectOption(@js($size))" :class="optionClasses(@js($size))" class="border-2 font-semibold py-2 px-5 rounded-xl text-sm transition-all duration-200 focus:outline-none">
            {{ $size }}
          </button>
          @endforeach
        </div>
        <p x-show="!canPurchase" x-cloak class="mt-2 text-sm text-slate-500">Please select a size above to continue.</p>
      </div>
      <button type="button" @click="goToCheckout($event)" :class="purchaseLinkClasses()" :aria-disabled="!canPurchase" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping Included
      </button>
      <button type="button" @click="goToCheckout($event)" :class="purchaseLinkClasses()" :aria-disabled="!canPurchase" class="btn-gold-lg w-full justify-center">
        Get Your Dainely Belt
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 60-Day Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Trusted by 50,000+ customers</span>
    </div>
  </div>
</section>

{{-- ── 14. RELATED PRODUCTS ──────────────────────────────────── --}}
<section class="section bg-stone-50 border-t border-stone-100" aria-label="Related products">
  <div class="container-site">
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">Complete Your Routine</p>
      <h2 class="heading-section mb-2">Complete your daily support routine</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
      @foreach([
        ['Daily Relief System', 'Belt + foam roller + resistance bands + recovery guide. A more complete daily wellness protocol.', 'daily-relief-system.png', 'View Protocol →'],
        ['Recovery & Mobility', 'Targeted stretching and movement routines designed to complement your daily support.', 'recovery-edu.png', 'Explore →'],
        ['Educational Resources', 'Physician-reviewed guides on SI joint health, posture awareness, and daily movement.', 'spine-anatomy.png', 'Read Guides →'],
      ] as [$title, $copy, $img, $cta])
      <div class="card overflow-hidden group">
        <div class="overflow-hidden h-48 bg-slate-100">
          <img src="{{ asset('images/' . $img) }}" alt="{{ $title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
        </div>
        <div class="p-6">
          <h3 class="heading-card mb-2">{{ $title }}</h3>
          <p class="text-body text-sm mb-4">{{ $copy }}</p>
          <a href="{{ route('products.index', ['locale' => $locale]) }}" class="text-navy-600 hover:text-navy-800 font-semibold text-sm transition-colors">{{ $cta }}</a>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Back to shop --}}
<div class="py-8 bg-white border-t border-slate-100">
  <div class="container-site text-center">
    <a href="{{ route('products.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-navy-600 hover:text-navy-800 font-semibold transition-colors text-sm">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      Back to All Products
    </a>
  </div>
</div>

{{-- NOTE: The hidden checkout form is rendered by the product-purchase partial above (x-ref="checkoutForm") --}}

{{-- ── STICKY BOTTOM ORDER BAR ───────────────────────────────── --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-3 sm:gap-4">
      <img src="{{ $mainImg ?: asset('images/dainely-belt-product.png') }}" alt="Dainely Belt" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">Dainely Belt</p>
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold text-sm">${{ number_format($beltPrice, 2) }}</span>
          <span class="text-slate-400 line-through text-xs">${{ number_format($beltCompare, 2) }}</span>
          <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">-{{ $beltSaving }}%</span>
        </div>
      </div>
      <button type="button" @click="goToCheckout($event)" :class="canPurchase ? 'bg-navy-700 hover:bg-navy-800' : 'bg-slate-400 cursor-not-allowed pointer-events-none opacity-70'" :aria-disabled="!canPurchase" class="flex-shrink-0 inline-flex items-center gap-1.5 text-white font-bold px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-colors text-xs sm:text-sm shadow-md">
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Order Now
      </button>
      <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-slate-100 hover:bg-navy-100 text-slate-600 hover:text-navy-700 flex items-center justify-center transition-colors" title="Back to top">
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
      </button>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  const stickyBar = document.getElementById('sticky-order-bar');
  const heroSection = document.getElementById('product-hero');
  function updateStickyBar() {
    if (!heroSection || !stickyBar) return;
    stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
  }
  window.addEventListener('scroll', updateStickyBar, { passive: true });
  updateStickyBar();
</script>
@endpush

{{-- ============================================================
     STANDARD SHOPIFY PRODUCT PAGE (non-Dainely Belt products)
     ============================================================ --}}
@else

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- Breadcrumb --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">{{ __('nav.products') }}</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">{{ \Illuminate\Support\Str::limit($title, 40) }}</span>
    </nav>
  </div>
</div>

{{-- Standard product hero --}}
<section class="section bg-white" aria-label="Product detail">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- Left: Image --}}
      <div x-data="shopifyGallery()" class="lg:sticky lg:top-24">
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-md mb-4">
          <template x-if="images.length > 0">
            <img :src="images[active]" :alt="'{{ $title }} view ' + (active + 1)" class="w-full aspect-square object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $status === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white' }}">{{ ucfirst($status) }}</span>
          </div>
          @if($vendor)
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-navy-700 text-xs font-semibold">{{ $vendor }}</span>
          </div>
          @endif
        </div>
        @if(count($images) > 1)
        <div class="flex gap-2 overflow-x-auto pb-2 lg:grid lg:grid-cols-5">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'" class="rounded-xl overflow-hidden aspect-square w-14 h-14 flex-shrink-0 lg:w-auto lg:h-auto">
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif
        <div class="grid grid-cols-3 gap-3 mt-6 p-4 bg-slate-50 rounded-2xl">
          @foreach([['30-Day', 'Guarantee', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'sage'], ['Free Ship', 'Over $75', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'navy'], ['Secure', 'Payment', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'gold']] as [$label, $sub, $path, $c])
          <div class="text-center">
            <div class="w-8 h-8 bg-{{ $c }}-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-4 h-4 text-{{ $c }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
            </div>
            <p class="text-slate-700 text-xs font-semibold">{{ $label }}</p>
            <p class="text-slate-500 text-[10px]">{{ $sub }}</p>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Right: Info --}}
      <div>
        @if($vendor)<p class="eyebrow mb-3">{{ $vendor }}</p>@endif
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(1.75rem,4vw,2.5rem); line-height: 1.15;">{{ $title }}</h1>
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for($i=0;$i<5;$i++)<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
          </div>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>
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
        @if($plainDesc)
        <div class="text-slate-600 text-base leading-relaxed mb-6 prose prose-slate max-w-none">{!! $desc !!}</div>
        @endif
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'addToCartText' => 'Add to Cart',
          'orderNowText'  => 'Order Now — Free Shipping',
        ])
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Money-Back Guarantee</p>
            <p class="text-sage-600 text-xs">Not satisfied? Full refund, no questions asked.</p>
          </div>
        </div>
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

{{-- Variants table --}}
@if(count($variants) > 1)
<section class="section bg-section-alt">
  <div class="container-site">
    <div class="text-center mb-10">
      <h2 class="heading-section mb-4">All Variants</h2>
    </div>
    <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
          <tr><th class="px-4 py-3 font-medium">Option</th><th class="px-4 py-3 font-medium">SKU</th><th class="px-4 py-3 font-medium">Price</th><th class="px-4 py-3 font-medium">Compare At</th><th class="px-4 py-3 font-medium">Available</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($variants as $variant)
          <tr class="hover:bg-slate-50/80">
            <td class="px-4 py-3 font-medium text-navy-900">{{ $variant['title'] ?? '—' }}</td>
            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant['sku'] ?? '—' }}</td>
            <td class="px-4 py-3 font-semibold text-navy-800">@if(!empty($variant['price'])) ${{ number_format((float)$variant['price'], 2) }} @else — @endif</td>
            <td class="px-4 py-3 text-slate-400 line-through text-sm">@if(!empty($variant['compare_at_price'])) ${{ number_format((float)$variant['compare_at_price'], 2) }} @else — @endif</td>
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

<section class="py-8 bg-white border-t border-slate-100">
  <div class="container-site text-center">
    <a href="{{ route('products.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-navy-600 hover:text-navy-800 font-semibold transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      Back to All Products
    </a>
  </div>
</section>

{{-- Sticky bar for standard products --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-2 sm:gap-4">
      @if($mainImg)
        <img src="{{ $mainImg }}" alt="{{ $title }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      @endif
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">{{ $title }}</p>
        @if($price)
          <div class="flex items-center gap-1 sm:gap-2">
            <span class="text-navy-700 font-bold text-sm">${{ number_format((float)$price, 2) }}</span>
            @if($compareAt && (float)$compareAt > (float)$price)
              <span class="text-slate-400 line-through text-[10px]">${{ number_format((float)$compareAt, 2) }}</span>
            @endif
          </div>
        @endif
      </div>
      <button type="button" @click="goToCheckout($event)" :class="canPurchase ? 'bg-navy-700 hover:bg-navy-800' : 'bg-slate-400 cursor-not-allowed pointer-events-none opacity-70'" :aria-disabled="!canPurchase" class="flex-shrink-0 inline-flex items-center gap-1.5 text-white font-bold px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-colors text-xs sm:text-sm shadow-md">Order Now</button>
      <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-slate-100 hover:bg-navy-100 text-slate-600 hover:text-navy-700 flex items-center justify-center transition-colors" title="Back to top">
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
      </button>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  const stickyBar = document.getElementById('sticky-order-bar');
  const heroSection = document.querySelector('section[aria-label="Product detail"]');
  function updateStickyBar() {
    if (!heroSection || !stickyBar) return;
    stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
  }
  window.addEventListener('scroll', updateStickyBar, { passive: true });
  updateStickyBar();
  document.addEventListener('alpine:init', () => {
    Alpine.data('shopifyGallery', () => ({
      active: 0,
      images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
      setActive(i) { this.active = i; },
    }));
  });
</script>
@endpush

@endif

@endsection
