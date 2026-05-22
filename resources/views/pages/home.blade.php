@extends('layouts.app')

@section('title', __('home.meta_title'))
@section('meta_description', __('home.meta_description'))
@section('og_image', asset('images/og-default.jpg'))
@section('og_title', 'Dainely — End Back Pain. Reclaim Your Life.')
@section('og_description', 'Medical-grade lumbar support systems developed with spine specialists. Trusted by 50,000+ customers worldwide.')

@section('meta_canonical')
<link rel="canonical" href="{{ url()->current() }}">
@endsection

@section('meta_hreflang')
<link rel="alternate" hreflang="en" href="{{ url('/en') }}">
<link rel="alternate" hreflang="fr" href="{{ url('/fr') }}">
<link rel="alternate" hreflang="de" href="{{ url('/de') }}">
<link rel="alternate" hreflang="x-default" href="{{ url('/en') }}">
@endsection

@section('content')

{{-- ============================================================
     HERO SECTION
     Premium above-the-fold experience with trust signals
     ============================================================ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Hero">
  {{-- Background pattern --}}
  <div class="absolute inset-0 opacity-5">
    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
  </div>
  {{-- Gradient orbs --}}
  <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full bg-navy-600/30 blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
  <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-gold-400/10 blur-3xl translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>

  <div class="container-site relative z-10">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center py-20 md:py-28">

      {{-- Left: Content --}}
      <div class="animate-on-scroll">
        {{-- Eyebrow --}}
        <div class="flex items-center gap-3 mb-6">
          <span class="trust-badge bg-white/10 border-white/20 text-white">
            <svg class="w-3 h-3 text-sage-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ __('home.hero_badge') }}
          </span>
        </div>

        {{-- Headline --}}
        <h1 class="font-display font-bold text-white mb-6" style="font-size: clamp(2.5rem, 5vw, 3.75rem); line-height: 1.1; letter-spacing: -0.02em;">
          {!! __('home.hero_headline') !!}
        </h1>

        {{-- Sub-headline --}}
        <p class="text-navy-200 text-lg md:text-xl leading-relaxed mb-8 max-w-lg">
          {{ __('home.hero_subheadline') }}
        </p>

        {{-- Stats row --}}
        <div class="flex flex-wrap gap-6 mb-8">
          <div>
            <p class="font-display font-bold text-3xl text-white">50K+</p>
            <p class="text-navy-300 text-sm">{{ __('home.stat_customers') }}</p>
          </div>
          <div class="w-px bg-navy-700"></div>
          <div>
            <p class="font-display font-bold text-3xl text-white">4.8★</p>
            <p class="text-navy-300 text-sm">{{ __('home.stat_rating') }}</p>
          </div>
          <div class="w-px bg-navy-700"></div>
          <div>
            <p class="font-display font-bold text-3xl text-white">30</p>
            <p class="text-navy-300 text-sm">{{ __('home.stat_guarantee') }}</p>
          </div>
        </div>

        {{-- CTA Buttons --}}
        <div class="flex flex-wrap gap-4">
          <a href="#" id="hero-cta-primary" class="btn-gold-lg">
            {{ __('home.hero_cta_primary') }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </a>
          <a href="#learn-more" id="hero-cta-secondary" class="btn flex items-center gap-2 text-white hover:text-gold-300 text-base font-semibold transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('home.hero_cta_secondary') }}
          </a>
        </div>

        {{-- Trustpilot strip --}}
        <div class="flex items-center gap-3 mt-8 pt-8 border-t border-navy-700">
          <div class="flex items-center gap-1">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-4 h-4 text-[#00b67a]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <p class="text-navy-300 text-sm"><span class="text-white font-semibold">{{ __('home.trustpilot_score') }}</span> {{ __('home.trustpilot_label') }}</p>
        </div>
      </div>

      {{-- Right: Hero lifestyle + product image --}}
      <div class="relative animate-on-scroll delay-200 hidden lg:block">
        <div class="relative">
          {{-- Glow behind image --}}
          <div class="absolute inset-0 bg-gold-400/20 blur-3xl rounded-full scale-75 pointer-events-none"></div>

          {{-- Hero lifestyle photo --}}
          <div class="relative z-10 rounded-3xl overflow-hidden shadow-strong">
            <img
              src="{{ asset('images/hero-lifestyle.png') }}"
              alt="Person with great posture and pain-free back wearing Dainely support system"
              class="w-full aspect-square object-cover object-center"
              loading="eager"
              width="560" height="560"
            >
            {{-- Overlay gradient for depth --}}
            <div class="absolute inset-0 bg-gradient-to-t from-navy-900/40 via-transparent to-transparent pointer-events-none"></div>

            {{-- Product floating badge on image --}}
            <div class="absolute top-5 right-5 bg-white/95 backdrop-blur-sm rounded-2xl shadow-medium p-3 flex items-center gap-2.5">
              <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
                <img src="{{ asset('images/dainely-belt-product.png') }}" alt="Dainely Belt" class="w-full h-full object-cover">
              </div>
              <div>
                <p class="text-navy-900 text-xs font-bold leading-tight">Dainely Belt</p>
                <div class="flex items-center gap-0.5 mt-0.5">
                  @for ($i = 0; $i < 5; $i++)
                  <svg class="w-2.5 h-2.5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                  @endfor
                </div>
              </div>
            </div>
          </div>

          {{-- Floating verified review card --}}
          <div class="absolute -bottom-6 -left-8 bg-white rounded-2xl shadow-medium p-4 max-w-[210px] z-20">
            <div class="flex items-center gap-2 mb-2">
              <img src="{{ asset('images/testimonial-sarah.jpg') }}" alt="Sarah M." class="w-8 h-8 rounded-full object-cover flex-shrink-0">
              <div>
                <p class="text-slate-800 text-xs font-semibold leading-none">Sarah M.</p>
                <p class="text-slate-400 text-[10px] mt-0.5">Texas, USA</p>
              </div>
            </div>
            <div class="flex items-center gap-0.5 mb-1.5">
              @for ($i = 0; $i < 5; $i++)
              <svg class="w-3 h-3 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              @endfor
            </div>
            <p class="text-slate-700 text-xs leading-snug">"Finally pain-free after 3 years!"</p>
            <div class="flex items-center gap-1 mt-2">
              <svg class="w-3 h-3 text-sage-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              <span class="text-sage-600 text-[10px] font-semibold">Verified Purchase</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     TRUST BAR
     Social proof and credibility signals
     ============================================================ --}}
<section class="bg-white border-b border-trust-line" aria-label="Trust signals">
  <div class="container-site py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-sage-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <p class="font-semibold text-slate-800 text-sm">{{ __('home.trust1_title') }}</p>
          <p class="text-slate-500 text-xs">{{ __('home.trust1_desc') }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-navy-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
          <p class="font-semibold text-slate-800 text-sm">{{ __('home.trust2_title') }}</p>
          <p class="text-slate-500 text-xs">{{ __('home.trust2_desc') }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gold-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div>
          <p class="font-semibold text-slate-800 text-sm">{{ __('home.trust3_title') }}</p>
          <p class="text-slate-500 text-xs">{{ __('home.trust3_desc') }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-navy-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
          <p class="font-semibold text-slate-800 text-sm">{{ __('home.trust4_title') }}</p>
          <p class="text-slate-500 text-xs">{{ __('home.trust4_desc') }}</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     EDUCATIONAL POSITIONING
     Why Dainely is different
     ============================================================ --}}
<section id="learn-more" class="section bg-white" aria-label="Why Dainely">
  <div class="container-site">
    <div class="text-center mb-16">
      <p class="eyebrow mb-3">{{ __('home.edu_eyebrow') }}</p>
      <h2 class="heading-section mb-4">{{ __('home.edu_headline') }}</h2>
      <p class="text-lead max-w-2xl mx-auto">{{ __('home.edu_subheadline') }}</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      {{-- Card 1 --}}
      <div class="card p-8 animate-on-scroll">
        <div class="w-14 h-14 bg-sage-50 rounded-2xl flex items-center justify-center mb-6">
          <svg class="w-7 h-7 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <h3 class="heading-card mb-3">{{ __('home.edu_card1_title') }}</h3>
        <p class="text-body">{{ __('home.edu_card1_desc') }}</p>
      </div>

      {{-- Card 2 --}}
      <div class="card p-8 animate-on-scroll delay-200">
        <div class="w-14 h-14 bg-navy-50 rounded-2xl flex items-center justify-center mb-6">
          <svg class="w-7 h-7 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="heading-card mb-3">{{ __('home.edu_card2_title') }}</h3>
        <p class="text-body">{{ __('home.edu_card2_desc') }}</p>
      </div>

      {{-- Card 3 --}}
      <div class="card p-8 animate-on-scroll delay-400">
        <div class="w-14 h-14 bg-gold-50 rounded-2xl flex items-center justify-center mb-6">
          <svg class="w-7 h-7 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h3 class="heading-card mb-3">{{ __('home.edu_card3_title') }}</h3>
        <p class="text-body">{{ __('home.edu_card3_desc') }}</p>
      </div>
    </div>
  </div>
</section>

@include('partials.shopify-products-slider')

{{-- ============================================================
     TESTIMONIALS (Real Results)
     ============================================================ --}}
<section class="section bg-white" aria-label="Customer testimonials">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">{{ __('home.testimonials_eyebrow') }}</p>
      <h2 class="heading-section mb-4">{{ __('home.testimonials_headline') }}</h2>
      <div class="flex items-center justify-center gap-2">
        <div class="stars">
          @for ($i = 0; $i < 5; $i++)<svg class="w-5 h-5 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
        </div>
        <span class="text-slate-600 text-sm">4.8 / 5 — {{ __('home.testimonials_count') }}</span>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['Sarah M.', 'Texas, USA', 5, 'I\'ve had chronic lower back pain for 3 years. After just 2 weeks with the Dainely Belt, I\'m finally sleeping through the night. This is genuinely life-changing.', 'Verified Purchase', 'testimonial-sarah.jpg'],
        ['Jean-Pierre D.', 'Paris, France', 5, 'Le Ceinture Dainely est incroyable. Ma sciatique a disparu en moins d\'un mois. Je recommande à tous mes patients.', 'Verified Purchase', 'testimonial-jean.jpg'],
        ['Klaus H.', 'Munich, Germany', 5, 'Nach Jahren mit Rückenschmerzen hat mir der Dainely Gürtel wirklich geholfen. Qualität ist ausgezeichnet, sehr empfehlenswert.', 'Verified Purchase', 'testimonial-klaus.jpg'],
      ] as [$name, $location, $rating, $review, $badge, $avatar])
      <div class="testimonial-card animate-on-scroll">
        <div class="flex items-start justify-between">
          <div class="stars">@for ($i = 0; $i < $rating; $i++)<svg class="w-4 h-4 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
          <svg class="w-6 h-6 text-slate-200 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
        </div>
        <p class="text-slate-700 text-sm leading-relaxed flex-1">{{ $review }}</p>
        <div class="flex items-center gap-3 pt-3 border-t border-trust-line">
          <img
            src="{{ asset('images/' . $avatar) }}"
            alt="{{ $name }}"
            class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-slate-100"
            loading="lazy"
            width="40" height="40"
          >
          <div>
            <p class="font-semibold text-slate-800 text-sm">{{ $name }}</p>
            <p class="text-slate-400 text-xs">{{ $location }} · {{ $badge }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ============================================================
     EDUCATION TEASER
     ============================================================ --}}
<section class="section bg-gradient-to-br from-navy-900 to-navy-950 text-white" aria-label="Education">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow text-gold-400 mb-3">{{ __('home.edu_teaser_eyebrow') }}</p>
      <h2 class="heading-section text-white mb-4">{{ __('home.edu_teaser_headline') }}</h2>
      <p class="text-navy-300 text-lead max-w-xl mx-auto">{{ __('home.edu_teaser_desc') }}</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      @foreach([
        [__('nav.back_pain'),  '#', 'back-pain-edu.png'],
        [__('nav.sciatica'),   '#', 'sciatica-edu.png'],
        [__('nav.posture'),    '#', 'posture-edu.png'],
        [__('nav.neck_pain'),  '#', 'spine-anatomy.png'],
        [__('nav.mobility'),   '#', 'blog-hero-back-pain.jpg'],
        [__('nav.recovery'),   '#', 'about-team.jpg'],
      ] as [$label, $href, $img])
      <a href="{{ $href }}" class="card-glass group flex flex-col items-center gap-3 p-0 text-center hover:bg-white/15 transition-all duration-300 overflow-hidden rounded-2xl">
        <div class="w-full h-28 overflow-hidden">
          <img
            src="{{ asset('images/' . $img) }}"
            alt="{{ $label }} education"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            loading="lazy"
          >
        </div>
        <span class="text-white text-sm font-medium pb-4 px-2">{{ $label }}</span>
      </a>
      @endforeach
    </div>
  </div>
</section>

{{-- ============================================================
     FINAL CTA
     ============================================================ --}}
<section class="section bg-white" aria-label="Call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">{{ __('home.cta_eyebrow') }}</p>
    <h2 class="heading-section mb-6">{!! __('home.cta_headline') !!}</h2>
    <p class="text-lead mb-8">{{ __('home.cta_desc') }}</p>
    <div class="flex flex-wrap items-center justify-center gap-4">
      <a href="#" class="btn-primary-lg" id="final-cta">
        {{ __('home.cta_button') }}
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
      <p class="text-slate-500 text-sm">{{ __('home.cta_guarantee') }}</p>
    </div>
  </div>
</section>

@endsection
