{{-- 
  Dainely Site Header
  Sticky, responsive, with mega nav and language switcher
--}}

@php
  $shopNowUrl = !empty($headerShopifyProducts[0]['handle'])
    ? route('products.show', ['locale' => app()->getLocale(), 'slug' => $headerShopifyProducts[0]['handle']])
    : route('products.index', ['locale' => app()->getLocale()]);
  $headerCartCount = (int) ($cartItemCount ?? \App\Support\CheckoutCart::itemCount());
  $freeShipBannerAmount = app(\App\Services\CurrencyService::class)->formatForLocale(
      app(\App\Services\CurrencyService::class)->freeShippingThresholdUsd(),
      app()->getLocale()
  );
@endphp

<header
  id="site-header"
  class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-100 transition-all duration-300"
  x-data="mobileNav()"
>
  {{-- Top info bar --}}
  <div class="bg-navy-900 text-white text-xs py-2 hidden md:block">
    <div class="container-site flex items-center justify-between">
      <p class="text-navy-200">{{ __('header.free_shipping', ['amount' => $freeShipBannerAmount]) }}</p>
      <div class="flex items-center gap-6">
        <a href="tel:{{ config('company.phone_tel') }}" class="text-navy-200 hover:text-white transition-colors flex items-center gap-1.5">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
          {{ config('company.phone_display') }}
        </a>
        <span class="text-navy-700">|</span>
        <a href="mailto:{{ config('company.email') }}" class="text-navy-200 hover:text-white transition-colors">{{ config('company.email') }}</a>
      </div>
    </div>
  </div>

  {{-- Main navigation --}}
  <nav class="container-site" aria-label="Main navigation">
    <div class="flex items-center justify-between h-16 md:h-20">

      {{-- Logo --}}
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="flex items-center group" aria-label="Dainely Home">
        <img src="{{ asset('images/Dainelycut.png') }}" alt="Dainely" class="h-10 md:h-12 w-auto">
      </a>

      {{-- Desktop Nav Links --}}
      <div class="hidden lg:flex items-center gap-1">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50">{{ __('nav.home') }}</a>

        {{-- Products dropdown --}}
        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
          <a
            href="{{ route('products.index', ['locale' => app()->getLocale()]) }}"
            class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50 flex items-center gap-1"
            :aria-expanded="open"
          >
            {{ __('nav.products') }}
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </a>
          <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute top-full left-0 mt-1 w-72 max-h-[min(70vh,420px)] overflow-y-auto bg-white rounded-2xl shadow-medium border border-slate-100 p-2 z-50 flex flex-col"
          >
            @if(!empty($headerShopifyProducts))
              @foreach($headerShopifyProducts as $product)
              <a
                href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $product['handle'] ?? '']) }}"
                class="flex items-center gap-3 p-3 rounded-xl hover:bg-navy-50 group transition-colors w-full"
              >
                <div class="w-10 h-10 bg-stone-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:bg-stone-200 transition-colors">
                  @if(!empty($product['image']))
                  <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="w-full h-full object-cover" loading="lazy" width="40" height="40">
                  @else
                  <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  @endif
                </div>
                <div class="min-w-0 flex-1">
                  <p class="font-semibold text-slate-800 text-sm group-hover:text-navy-700 truncate">{{ $product['title'] }}</p>
                  @if(!empty($product['price']))
                  <p class="text-xs text-slate-500 mt-0.5">${{ number_format((float) $product['price'], 2) }}</p>
                  @endif
                </div>
              </a>
              @endforeach
            @else
              <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'dainely-belt']) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-navy-50 group transition-colors">
                <div class="w-10 h-10 bg-navy-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-navy-200 transition-colors">
                  <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div>
                  <p class="font-semibold text-slate-800 text-sm group-hover:text-navy-700">{{ __('nav.dainely_belt') }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ __('nav.dainely_belt_desc') }}</p>
                </div>
              </a>
            @endif
            <div class="border-t border-slate-100 mt-1 pt-1">
              <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-navy-700 hover:bg-navy-50 transition-colors text-center">
                {{ __('nav.view_all_products') }}
              </a>
            </div>
          </div>
        </div>


        {{-- Education dropdown --}}
        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
          <button class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50 flex items-center gap-1" :aria-expanded="open">
            {{ __('nav.education') }}
            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute top-full left-0 mt-1 w-56 bg-white rounded-2xl shadow-medium border border-slate-100 p-2 z-50"
          >
            @foreach([['back-pain', __('nav.back_pain')], ['sciatica', __('nav.sciatica')], ['posture', __('nav.posture')], ['neck-pain', __('nav.neck_pain')], ['mobility', __('nav.mobility')], ['recovery', __('nav.recovery')]] as [$slug, $label])
            <a href="{{ route('education.' . $slug, ['locale' => app()->getLocale()]) }}" class="block px-3 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-navy-50 hover:text-navy-700 font-medium transition-colors">{{ $label }}</a>
            @endforeach
          </div>
        </div>

        <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50">{{ __('nav.blog') }}</a>
        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50">{{ __('nav.about') }}</a>
      </div>

      {{-- Right side actions --}}
      <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">

        {{-- Language Switcher --}}
        <div class="relative" x-data="langSwitcher()" @click.away="close()">
          <button
            @click="toggle()"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 hover:text-navy-600 hover:bg-slate-50 transition-all"
            aria-label="Switch language"
          >
            <span class="text-base">{{ app()->getLocale() === 'fr' ? 'FR - Français' : (app()->getLocale() === 'de' ? 'DE - Deutsch' : 'US - English') }}</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute top-full right-0 mt-1 w-40 bg-white rounded-2xl shadow-medium border border-slate-100 p-1.5 z-50"
          >
            <a href="{{ '/en'.preg_replace('#^/(en|fr|de)(/|$)#', '/', request()->getPathInfo()) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'en' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇺🇸</span> US - English
            </a>
            <a href="{{ '/fr'.preg_replace('#^/(en|fr|de)(/|$)#', '/', request()->getPathInfo()) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'fr' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇫🇷</span> FR - Français
            </a>
            <a href="{{ '/de'.preg_replace('#^/(en|fr|de)(/|$)#', '/', request()->getPathInfo()) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'de' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇩🇪</span> DE - Deutsch
            </a>
          </div>
        </div>

        {{-- CTA Button (desktop) --}}
        <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'dainely-belt']) }}" class="hidden lg:inline-flex btn-primary text-xs px-5 py-2.5">
          {{ __('nav.shop_now') }}
        </a>

        {{-- Cart (always visible, top-right) --}}
        @include('partials.cart-nav-link', ['showLabel' => false])

        {{-- Mobile menu toggle --}}
        <button
          @click="toggle()"
          class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 transition-colors"
          :aria-expanded="open"
          aria-label="Toggle menu"
        >
          <svg x-show="!open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg x-show="open" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  

    {{-- Mobile menu --}}
    <div
      x-show="open"
      x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-4"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="lg:hidden pb-4 border-t border-slate-100 pt-4"
    >
      <div class="flex flex-col gap-1">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors">{{ __('nav.home') }}</a>
        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="px-3 pt-2 pb-1 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-navy-700 transition-colors block">{{ __('nav.products') }}</a>
        @if(!empty($headerShopifyProducts))
          @foreach($headerShopifyProducts as $product)
          <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $product['handle'] ?? '']) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6 truncate block">{{ $product['title'] }}</a>
          @endforeach
        @else
          <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'dainely-belt']) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.dainely_belt') }}</a>
        @endif
        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="px-3 py-2 rounded-xl text-sm font-semibold text-navy-700 hover:bg-navy-50 transition-colors pl-6">{{ __('nav.view_all_products') }}</a>

        <p class="px-3 pt-2 pb-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('nav.education') }}</p>
        <a href="{{ route('education.back-pain', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.back_pain') }}</a>
        <a href="{{ route('education.sciatica', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.sciatica') }}</a>
        <a href="{{ route('education.posture', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.posture') }}</a>
        <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors">{{ __('nav.blog') }}</a>
        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors">{{ __('nav.about') }}</a>
        <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors flex items-center justify-between gap-3">
          <span>{{ __('nav.cart') }}</span>
          @if($headerCartCount > 0)
            <span class="min-w-[1.25rem] h-5 px-1.5 bg-navy-600 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $headerCartCount > 99 ? '99+' : $headerCartCount }}</span>
          @endif
        </a>
        <div class="pt-2 px-3">
          <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'dainely-belt']) }}" class="btn-primary w-full justify-center text-sm">{{ __('nav.shop_now') }}</a>
        </div>
      </div>
    </div>
  </nav>
</header>

