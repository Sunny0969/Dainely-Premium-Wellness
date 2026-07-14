<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Global Currency Config --}}
  @php
    $appLocale = app()->getLocale();
    $appCurrencyCode = app(App\Services\CurrencyService::class)->getCurrencyForLocale($appLocale);
    $appCurrencySymbol = config("currency.supported.{$appCurrencyCode}.symbol", '$');
    $appExchangeRate = app(App\Services\CurrencyService::class)->convert(1.0, $appCurrencyCode);
  @endphp
  <script data-cfasync="false">
    window.Currency = {
        code: @json($appCurrencyCode),
        symbol: @json($appCurrencySymbol),
        rate: @json($appExchangeRate),
        format: function(amount) {
            return this.symbol + (parseFloat(amount) * this.rate).toFixed(2);
        }
    };
  </script>

  {{-- SEO Meta --}}
  <title>@yield('title', 'Dainely — Premium Wellness for Back Pain & Sciatica Relief')</title>
  <meta name="description" content="@yield('meta_description', 'Clinically developed wellness solutions for back pain, sciatica, and posture. Trusted by thousands worldwide.')">
  @if (View::hasSection('meta_canonical'))
    @yield('meta_canonical')
  @else
    <link rel="canonical" href="{{ request()->url() }}">
  @endif
  
  {{-- Automatic Hreflang Tag Generator for Multilingual SEO --}}
  @php
    $supportedLocales = ['en', 'fr', 'de'];
    $currentRouteName = Route::currentRouteName();
    $currentRouteParams = Route::current() ? Route::current()->parameters() : [];
  @endphp
  @if($currentRouteName)
    @foreach($supportedLocales as $lang)
      <link rel="alternate" hreflang="{{ $lang }}" href="{{ route($currentRouteName, array_merge($currentRouteParams, ['locale' => $lang])) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route($currentRouteName, array_merge($currentRouteParams, ['locale' => 'en'])) }}">
  @endif

  @yield('meta_schema')

  {{-- Open Graph --}}
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:title" content="@yield('og_title', 'Dainely — Premium Wellness')">
  <meta property="og:description" content="@yield('og_description', 'Clinically developed wellness solutions for back pain and sciatica.')">
  <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:site_name" content="Dainely">

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'Dainely — Premium Wellness')">
  <meta name="twitter:description" content="@yield('og_description', 'Clinically developed wellness solutions.')">
  <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

  {{-- Preconnect for fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  {{-- Favicon --}}
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

  {{-- Page config must load before the Vite bundle --}}
  @stack('head_scripts')

  {{-- Vite Assets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- JSON-LD Structured Data Stack --}}
  @stack('json-ld')
</head>
<body class="min-h-screen flex flex-col" x-data="scrollTop()">

{{-- Skip to content (accessibility) --}}
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 btn-primary z-50">
  Skip to content
</a>

{{-- Site Header --}}
@include('partials.header')

{{-- Flash Messages --}}
@if(session('success'))
<div class="container-site pt-4" x-data="{ show: true }" x-show="show" x-transition>
  <div class="alert-success">
    <svg class="w-5 h-5 flex-shrink-0 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-sm font-medium">{{ session('success') }}</p>
    <button @click="show = false" class="ml-auto text-sage-600 hover:text-sage-800"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
</div>
@endif

@if(session('error'))
<div class="container-site pt-4" x-data="{ show: true }" x-show="show" x-transition>
  <div class="alert-error">
    <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-sm font-medium">{{ session('error') }}</p>
    <button @click="show = false" class="ml-auto text-red-600 hover:text-red-800"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
</div>
@endif

{{-- Main Content --}}
<main id="main-content" class="flex-1 page-content">
  @yield('content')
</main>

{{-- Site Footer --}}
@include('partials.footer')

@include('partials.cart-drawer')

{{-- Scroll to Top Button --}}
<button
  x-show="visible"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 translate-y-4"
  x-transition:enter-end="opacity-100 translate-y-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  @click="scrollToTop()"
  class="fixed bottom-6 right-6 z-50 w-11 h-11 rounded-full bg-navy-600 text-white shadow-navy flex items-center justify-center hover:bg-navy-700 transition-all duration-200"
  aria-label="Scroll to top"
>
  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
</button>

{{-- GA4 Tracking --}}
@if(config('app.ga4_measurement_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.ga4_measurement_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('app.ga4_measurement_id') }}', {
    'custom_map': {'dimension1': 'locale', 'dimension2': 'currency'}
  });
  gtag('event', 'page_view', {
    'locale': '{{ app()->getLocale() }}',
  });
</script>
@endif

@stack('scripts')
</body>
</html>
