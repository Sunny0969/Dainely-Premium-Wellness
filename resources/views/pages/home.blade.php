@extends('layouts.app')

@section('title', __('home.meta_title'))
@section('meta_description', __('home.meta_description'))
@section('og_image', asset('images/og-default.jpg'))
@section('og_title', __('home.hero_headline'))
@section('og_description', __('home.meta_description'))

@section('meta_canonical')
<link rel="canonical" href="{{ url()->current() }}">
@endsection

@section('meta_hreflang')
<link rel="alternate" hreflang="en" href="{{ url('/en') }}">
<link rel="alternate" hreflang="fr" href="{{ url('/fr') }}">
<link rel="alternate" hreflang="de" href="{{ url('/de') }}">
<link rel="alternate" hreflang="x-default" href="{{ url('/en') }}">
@endsection

@section('meta_schema')
@php
  $schemaProduct = $featuredBelt ?? null;
@endphp
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "name": "Dainely",
      "url": "{{ config('app.url') }}",
      "logo": "{{ asset('images/Dainelycut.png') }}"
    }
    @if($schemaProduct && !empty($schemaProduct['title']))
    ,{
      "@type": "Product",
      "name": {{ json_encode($schemaProduct['title']) }},
      "image": {{ json_encode($schemaProduct['image'] ?? '') }},
      "offers": {
        "@type": "Offer",
        "priceCurrency": "USD",
        "price": "{{ $schemaProduct['price'] ?? '0' }}",
        "availability": "https://schema.org/InStock"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.0",
        "reviewCount": "3200"
      }
    }
    @endif
  ]
}
</script>
@endsection

@section('content')
@php
  $beltUrl = !empty($featuredBelt['handle'])
    ? route('products.show', ['locale' => $locale, 'slug' => $featuredBelt['handle']])
    : route('products.index', ['locale' => $locale]);
  $drsUrl = !empty($dailyRelief['handle'])
    ? route('products.show', ['locale' => $locale, 'slug' => $dailyRelief['handle']])
    : route('products.index', ['locale' => $locale]);
  $beltImage = $featuredBelt['image'] ?? asset('images/dainely-belt-product.png');
  $beltTitle = $featuredBelt['title'] ?? 'Dainely Belt';
  $beltPrice = isset($featuredBelt['price']) ? (float) $featuredBelt['price'] : null;
@endphp

{{-- 1. HERO — split layout, mobile image first --}}
<section class="home-hero bg-stone-50 border-b border-stone-200/80" aria-label="Hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center py-10 md:py-16 lg:py-20">

      {{-- Mobile: image first --}}
      <div class="order-1 lg:order-2 relative animate-on-scroll">
        <div class="rounded-2xl md:rounded-3xl overflow-hidden bg-stone-200/60 shadow-soft aspect-[4/5] sm:aspect-[5/4] lg:aspect-square max-h-[min(72vh,520px)] mx-auto lg:max-h-none">
          <img
            src="{{ asset('images/hero-lifestyle.png') }}"
            alt="{{ $beltTitle }} worn during everyday activity"
            class="w-full h-full object-cover object-center"
            loading="eager"
            fetchpriority="high"
            width="640"
            height="640"
          >
        </div>
      </div>

      {{-- Copy --}}
      <div class="order-2 lg:order-1 flex flex-col justify-center animate-on-scroll">
        <h1 class="home-display text-stone-900 mb-5">
          {{ __('home.hero_headline') }}
        </h1>
        <p class="text-stone-600 text-base md:text-lg leading-relaxed mb-8 max-w-xl">
          {{ __('home.hero_subheadline') }}
        </p>

        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <a href="{{ $beltUrl }}" id="hero-cta-primary" class="btn-primary-lg w-full sm:w-auto justify-center">
            {{ __('home.hero_cta_primary') }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </a>
          <a href="#home-video" class="btn-outline w-full sm:w-auto justify-center border-stone-300 text-stone-800 hover:border-navy-600">
            {{ __('home.hero_cta_secondary') }}
          </a>
        </div>

        <p class="text-stone-500 text-xs sm:text-sm mb-4">{{ __('home.hero_micro_trust') }}</p>
        <div class="flex items-center gap-2 text-sm text-stone-600">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-3.5 h-3.5 text-[#00b67a]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span><strong class="text-stone-800">{{ __('home.hero_trustpilot') }}</strong></span>
        </div>
        <p class="text-stone-400 text-[11px] mt-1 max-w-md">{{ __('home.hero_trustpilot_note') }}</p>
      </div>
    </div>
  </div>
</section>

{{-- 2. TRUST STRIP --}}
<section class="bg-white border-b border-stone-100" aria-label="Trust signals">
  <div class="container-site py-8 md:py-10">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
      <div class="home-trust-item group">
        <div class="home-trust-icon">
          <svg class="w-5 h-5 text-stone-600 group-hover:text-gold-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="font-medium text-stone-800 text-sm leading-snug">{{ __('home.trust1_title') }}</p>
      </div>
      <div class="home-trust-item group">
        <div class="home-trust-icon">
          <svg class="w-5 h-5 text-stone-600 group-hover:text-gold-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h6"/></svg>
        </div>
        <p class="font-medium text-stone-800 text-sm leading-snug">{{ __('home.trust2_title') }}</p>
      </div>
      <div class="home-trust-item group">
        <div class="home-trust-icon">
          <svg class="w-5 h-5 text-stone-600 group-hover:text-gold-600 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </div>
        <p class="font-medium text-stone-800 text-sm leading-snug">{{ __('home.trust3_title') }}</p>
      </div>
      <div class="home-trust-item group">
        <div class="home-trust-icon">
          <svg class="w-5 h-5 text-stone-600 group-hover:text-gold-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <p class="font-medium text-stone-800 text-sm leading-snug">{{ __('home.trust4_title') }}</p>
      </div>
    </div>
  </div>
</section>

{{-- 3. FEATURED PRODUCT — Dainely Belt --}}
<section class="section bg-stone-50/80" aria-label="Featured product">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
      <div class="order-2 lg:order-1">
        <p class="eyebrow text-stone-500 mb-3">{{ __('home.featured_eyebrow') }}</p>
        <h2 class="heading-section text-stone-900 mb-5">{{ __('home.featured_title') }}</h2>
        <p class="text-body text-stone-600 mb-8">{{ __('home.featured_copy') }}</p>
        <a href="{{ $beltUrl }}" class="btn-primary-lg inline-flex">
          {{ __('home.featured_cta') }}
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        @if($beltPrice)
        <p class="mt-4 text-stone-500 text-sm">From <span class="font-semibold text-stone-800">${{ number_format($beltPrice, 2) }}</span></p>
        @endif
      </div>
      <div class="order-1 lg:order-2">
        <a href="{{ $beltUrl }}" class="block rounded-3xl overflow-hidden bg-white shadow-soft ring-1 ring-stone-200/80 group">
          <img
            src="{{ $beltImage }}"
            alt="{{ $beltTitle }}"
            class="w-full aspect-[4/5] object-cover group-hover:scale-[1.02] transition-transform duration-700"
            loading="lazy"
            width="560"
            height="700"
          >
        </a>
      </div>
    </div>
  </div>
</section>

{{-- 4. LIFESTYLE POSITIONING --}}
<section class="section bg-white" aria-label="Lifestyle">
  <div class="container-site">
    <div class="max-w-2xl mb-12 md:mb-16">
      <h2 class="heading-section text-stone-900 mb-4">{{ __('home.lifestyle_title') }}</h2>
      <p class="text-lead text-stone-600">{{ __('home.lifestyle_copy') }}</p>
    </div>
    <div class="grid md:grid-cols-3 gap-4 md:gap-6">
      @foreach([
        ['lifestyle-desk-professional.png', 'home.lifestyle_1'],
        ['lifestyle-everyday-movement.png', 'home.lifestyle_2'],
        ['lifestyle-travel-commute.png', 'home.lifestyle_3'],
      ] as [$img, $captionKey])
      <figure class="home-lifestyle-card group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100">
          <img
            src="{{ asset('images/' . $img) }}"
            alt="{{ __($captionKey) }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            loading="lazy"
            width="400"
            height="500"
          >
        </div>
        <figcaption class="mt-3 text-sm font-medium text-stone-700">{{ __($captionKey) }}</figcaption>
      </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- 5. WHY PEOPLE CHOOSE DAINELY --}}
<section class="section bg-stone-50 border-y border-stone-100" aria-label="Benefits">
  <div class="container-site">
    <h2 class="heading-section text-stone-900 mb-10 md:mb-14 text-center md:text-left">{{ __('home.why_title') }}</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
      @foreach([
        ['home.why_1_title', 'home.why_1_desc'],
        ['home.why_2_title', 'home.why_2_desc'],
        ['home.why_3_title', 'home.why_3_desc'],
        ['home.why_4_title', 'home.why_4_desc'],
        ['home.why_5_title', 'home.why_5_desc'],
        ['home.why_6_title', 'home.why_6_desc'],
      ] as [$tKey, $dKey])
      <div class="home-benefit-card">
        <h3 class="font-semibold text-stone-900 mb-2">{{ __($tKey) }}</h3>
        <p class="text-sm text-stone-600 leading-relaxed">{{ __($dKey) }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- 6. REAL CUSTOMER REVIEWS --}}
<section class="section bg-white" aria-label="Customer reviews">
  <div class="container-site">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
      <div>
        <h2 class="heading-section text-stone-900 mb-2">{{ __('home.reviews_title') }}</h2>
        <p class="text-stone-600 text-sm">{{ __('home.reviews_subtitle') }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        @foreach(['reviews_tag_support', 'reviews_tag_work', 'reviews_tag_driving', 'reviews_tag_wear', 'reviews_tag_travel'] as $tagKey)
        <span class="home-review-tag">{{ __('home.' . $tagKey) }}</span>
        @endforeach
      </div>
    </div>

    <div class="home-featured-review mb-8 md:mb-10">
      <div class="flex gap-1 mb-4">
        @for ($i = 0; $i < 5; $i++)
        <svg class="w-4 h-4 text-gold-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        @endfor
      </div>
      <blockquote class="text-lg md:text-xl text-stone-800 leading-relaxed font-display italic mb-3">
        {{ __('home.reviews_featured') }}
      </blockquote>
      <p class="text-stone-500 text-xs">{{ __('home.reviews_featured_attribution') }}</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['Sarah M.', 'Texas, USA', 'Comfortable enough to wear through long workdays. Fits under my shirt and stays in place.', 'testimonial-sarah.jpg'],
        ['Jean-Pierre D.', 'Paris, France', 'Well-made support that fits into my routine. Quality materials and thoughtful design.', 'testimonial-jean.jpg'],
        ['Klaus H.', 'Munich, Germany', 'I use it for commuting and desk work. Simple to adjust and easy to wear daily.', 'testimonial-klaus.jpg'],
      ] as [$name, $location, $review, $avatar])
      <article class="testimonial-card border-stone-200/80 shadow-none hover:shadow-soft">
        <div class="stars mb-3">
          @for ($i = 0; $i < 5; $i++)<svg class="w-4 h-4 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
        </div>
        <p class="text-stone-700 text-sm leading-relaxed flex-1">{{ $review }}</p>
        <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
          <img src="{{ asset('images/' . $avatar) }}" alt="{{ $name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-stone-100" loading="lazy" width="40" height="40">
          <div>
            <p class="font-semibold text-stone-800 text-sm">{{ $name }}</p>
            <p class="text-stone-400 text-xs">{{ $location }} · {{ __('home.reviews_verified') }}</p>
          </div>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>

{{-- 7. VIDEO (only if self-hosted file exists) --}}
<section id="home-video" class="section bg-stone-900 text-white" aria-label="Video">
  <div class="container-site">
    <div class="max-w-3xl mx-auto text-center mb-8 md:mb-10">
      <h2 class="heading-section text-white mb-3">{{ __('home.video_title') }}</h2>
      <p class="text-stone-400">{{ __('home.video_desc') }}</p>
    </div>
    <div class="rounded-2xl md:rounded-3xl overflow-hidden bg-stone-800 max-w-4xl mx-auto aspect-video ring-1 ring-white/10">
      @if($heroVideo)
      <video
        class="w-full h-full object-cover"
        playsinline
        muted
        loop
        autoplay
        preload="metadata"
        poster="{{ asset('images/hero-lifestyle.png') }}"
      >
        <source src="{{ $heroVideo }}" type="video/mp4">
      </video>
      @else
      <img
        src="{{ asset('images/hero-lifestyle.png') }}"
        alt="{{ __('home.video_title') }}"
        class="w-full h-full object-cover opacity-90"
        loading="lazy"
        width="1280"
        height="720"
      >
      @endif
    </div>
  </div>
</section>

{{-- 8. DAILY RELIEF SYSTEM --}}
<section class="section bg-white" aria-label="Daily Relief System">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 items-center home-drs-panel rounded-3xl overflow-hidden">
      <div class="p-8 md:p-12 lg:p-14">
        <h2 class="heading-section text-stone-900 mb-4">{{ __('home.drs_title') }}</h2>
        <p class="text-body text-stone-600 mb-8">{{ __('home.drs_copy') }}</p>
        <a href="{{ $drsUrl }}" class="btn-outline border-stone-300 text-stone-800 hover:bg-stone-900 hover:text-white hover:border-stone-900">
          {{ __('home.drs_cta') }}
        </a>
      </div>
      <div class="relative min-h-[280px] lg:min-h-full bg-stone-100">
        <img
          src="{{ $dailyRelief['image'] ?? asset('images/daily-relief-system.png') }}"
          alt="{{ __('home.drs_title') }}"
          class="absolute inset-0 w-full h-full object-cover"
          loading="lazy"
          width="640"
          height="480"
        >
      </div>
    </div>
  </div>
</section>

{{-- 9. POPULAR PRODUCTS (Shopify) --}}
@include('partials.shopify-products-slider')

{{-- 10. EDUCATIONAL AUTHORITY --}}
<section class="section bg-stone-50 border-t border-stone-100" aria-label="Education">
  <div class="container-site">
    <div class="max-w-3xl">
      <h2 class="heading-section text-stone-900 mb-4">{{ __('home.authority_title') }}</h2>
      <p class="text-lead text-stone-600 mb-8">{{ __('home.authority_copy') }}</p>
      <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="btn-primary inline-flex">
        {{ __('home.authority_cta') }}
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-12">
      @foreach([
        [__('nav.back_pain'), route('education.back-pain', ['locale' => $locale]), 'back-pain-edu.png'],
        [__('nav.sciatica'), route('education.sciatica', ['locale' => $locale]), 'sciatica-edu.png'],
        [__('nav.posture'), route('education.posture', ['locale' => $locale]), 'posture-edu.png'],
        [__('nav.neck_pain'), route('education.neck-pain', ['locale' => $locale]), 'neck-pain-edu.png'],
        [__('nav.mobility'), route('education.mobility', ['locale' => $locale]), 'mobility-edu.png'],
        [__('nav.recovery'), route('education.recovery', ['locale' => $locale]), 'recovery-edu.png'],
      ] as [$label, $href, $img])
      <a href="{{ $href }}" class="group block rounded-xl overflow-hidden ring-1 ring-stone-200/80 bg-white hover:ring-stone-300 transition-all">
        <div class="aspect-[4/3] overflow-hidden bg-stone-100">
          <img src="{{ asset('images/' . $img) }}" alt="{{ $label }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" width="200" height="150">
        </div>
        <span class="block text-xs font-medium text-stone-700 p-2.5 text-center">{{ $label }}</span>
      </a>
      @endforeach
    </div>
  </div>
</section>

{{-- 11. TRUST & GUARANTEE --}}
<section class="section bg-white" aria-label="Guarantee">
  <div class="container-site">
    <h2 class="heading-section text-center text-stone-900 mb-10 md:mb-12">{{ __('home.guarantee_title') }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 max-w-4xl mx-auto text-center">
      @foreach(['guarantee_1', 'guarantee_2', 'guarantee_3', 'guarantee_4', 'guarantee_5'] as $gKey)
      <p class="text-sm font-medium text-stone-700">{{ __('home.' . $gKey) }}</p>
      @endforeach
    </div>
  </div>
</section>

{{-- 12. ABOUT DAINELY --}}
<section class="section bg-stone-50 border-y border-stone-100" aria-label="About">
  <div class="container-narrow text-center">
    <h2 class="heading-section text-stone-900 mb-5">{{ __('home.about_title') }}</h2>
    <p class="text-lead text-stone-600 mb-8">{{ __('home.about_copy') }}</p>
    <a href="{{ route('about', ['locale' => $locale]) }}" class="btn-ghost text-stone-800 border border-stone-200 rounded-xl px-6 py-3 hover:bg-white">
      {{ __('home.about_cta') }}
    </a>
  </div>
</section>

{{-- 13. FINAL CTA --}}
<section class="section home-final-cta" aria-label="Call to action">
  <div class="container-narrow text-center">
    <h2 class="heading-section text-stone-900 mb-4">{{ __('home.cta_headline') }}</h2>
    <p class="text-lead text-stone-600 mb-8">{{ __('home.cta_desc') }}</p>
    <a href="{{ $beltUrl }}" class="btn-primary-lg" id="final-cta">
      {{ __('home.cta_button') }}
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </a>
    <p class="text-stone-500 text-sm mt-5">{{ __('home.cta_guarantee') }}</p>
  </div>
</section>

{{-- Mobile sticky purchase bar --}}
<div
  class="home-sticky-bar lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-stone-200 px-4 py-3 safe-area-pb"
  x-data="{ show: false }"
  x-init="
    const onScroll = () => { show = window.scrollY > 480 };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  "
  x-show="show"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="translate-y-full opacity-0"
  x-transition:enter-end="translate-y-0 opacity-100"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="translate-y-0 opacity-100"
  x-transition:leave-end="translate-y-full opacity-0"
  style="display: none;"
  aria-label="Quick shop"
>
  <div class="flex items-center gap-3 max-w-lg mx-auto">
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-stone-900 text-sm truncate">{{ $beltTitle }}</p>
      @if($beltPrice)
      <p class="text-stone-500 text-xs">From ${{ number_format($beltPrice, 2) }}</p>
      @endif
    </div>
    <a href="{{ $beltUrl }}" class="btn-primary shrink-0 px-5 py-2.5 text-sm">
      {{ __('home.sticky_cta') }}
    </a>
  </div>
</div>

@endsection
