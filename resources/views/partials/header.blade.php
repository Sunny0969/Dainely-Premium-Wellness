{{-- 
  Dainely Site Header
  Sticky, responsive, with mega nav and language switcher
--}}
@php
  $shopNowUrl = !empty($headerShopifyProducts[0]['handle'])
    ? route('products.show', ['locale' => app()->getLocale(), 'slug' => $headerShopifyProducts[0]['handle']])
    : route('products.index', ['locale' => app()->getLocale()]);
@endphp
<header
  id="site-header"
  class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-100 transition-all duration-300"
  x-data="mobileNav()"
>
  {{-- Top info bar --}}
  <div class="bg-navy-900 text-white text-xs py-2 hidden md:block">
    <div class="container-site flex items-center justify-between">
      <p class="text-navy-200">{{ __('header.free_shipping') }}</p>
      <div class="flex items-center gap-6">
        <a href="tel:+1-800-DAINELY" class="text-navy-200 hover:text-white transition-colors flex items-center gap-1.5">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
          1-800-DAINELY
        </a>
        <span class="text-navy-700">|</span>
        <a href="mailto:hello@dainely.com" class="text-navy-200 hover:text-white transition-colors">hello@dainely.com</a>
      </div>
    </div>
  </div>

  {{-- Main navigation --}}
  <nav class="container-site" aria-label="Main navigation">
    <div class="flex items-center justify-between h-16 md:h-20">

      {{-- Logo --}}
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="flex items-center gap-3 group" aria-label="Dainely Home">
        <div class="w-8 h-8 bg-navy-600 rounded-lg flex items-center justify-center group-hover:bg-navy-700 transition-colors">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <span class="font-display font-bold text-xl text-navy-900 tracking-tight">Dainely</span>
      </a>

      {{-- Desktop Nav Links --}}
      <div class="hidden lg:flex items-center gap-1">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50">{{ __('nav.home') }}</a>

        {{-- Products dropdown --}}
        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
          <button class="nav-link px-3 py-2 rounded-lg hover:bg-slate-50 flex items-center gap-1" :aria-expanded="open">
            {{ __('nav.products') }}
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
            class="absolute top-full left-0 mt-1 w-72 bg-white rounded-2xl shadow-medium border border-slate-100 p-2 z-50"
          >
            @if(!empty($headerShopifyProducts))
            @foreach($headerShopifyProducts as $product)
              @php
                $slugForPage = \App\Support\ProductSlugResolver::resolveForShopifyProduct($product);
              @endphp
                <a
                  href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $slugForPage]) }}"
                  class="flex items-start gap-3 p-3 rounded-xl hover:bg-navy-50 group transition-colors"
                >
                  <div class="w-10 h-10 bg-navy-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-navy-200 transition-colors overflow-hidden">
                    @if(!empty($product['image']))
                      <img
                        src="{{ $product['image'] }}"
                        alt="{{ $product['title'] }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                      >
                    @else
                      <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                    @endif
                  </div>
                  <div>
                    <p class="font-semibold text-slate-800 text-sm group-hover:text-navy-700">{{ $product['title'] }}</p>
                    @if(!empty($product['status']))
                      <p class="text-xs text-slate-500 mt-0.5">{{ ucfirst($product['status']) }}</p>
                    @endif
                  </div>
                </a>
              @endforeach
            @else
              <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-navy-50 group transition-colors">
                <div class="w-10 h-10 bg-navy-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                  <p class="font-semibold text-slate-800 text-sm group-hover:text-navy-700">{{ __('footer.all_products') }}</p>
                </div>
              </a>

            @endif

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
      <div class="flex items-center gap-3">

        {{-- Language Switcher --}}
        <div class="relative" x-data="langSwitcher()" @click.away="close()">
          <button
            @click="toggle()"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 hover:text-navy-600 hover:bg-slate-50 transition-all"
            aria-label="Switch language"
          >
            <span class="text-base">{{ match(app()->getLocale()) { 'fr' => '🇫🇷', 'de' => '🇩🇪', default => '🇺🇸' } }}</span>
            <span class="uppercase text-xs font-bold">{{ strtoupper(app()->getLocale()) }}</span>
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
            <a href="/en{{ request()->getPathInfo() }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'en' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇺🇸</span> English
            </a>
            <a href="/fr{{ request()->getPathInfo() }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'fr' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇫🇷</span> Français
            </a>
            <a href="/de{{ request()->getPathInfo() }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'de' ? 'font-semibold text-navy-700 bg-navy-50' : 'text-slate-700' }}">
              <span>🇩🇪</span> Deutsch
            </a>
          </div>
        </div>

        {{-- CTA Button --}}
        <a href="{{ $shopNowUrl }}" class="hidden sm:inline-flex btn-primary text-xs px-5 py-2.5">
          {{ __('nav.shop_now') }}
        </a>

        {{-- Mobile CTA (responsive) --}}
        <a href="{{ $shopNowUrl }}" class="sm:hidden btn-primary text-xs px-4 py-2.5">
          {{ __('nav.shop_now') }}
        </a>


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
        <p class="px-3 pt-2 pb-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('nav.products') }}</p>
        @if(!empty($headerShopifyProducts))
            @foreach($headerShopifyProducts as $product)
              @php
                $slugForPage = \App\Support\ProductSlugResolver::resolveForShopifyProduct($product);
              @endphp
            <a
              href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $slugForPage]) }}"
              class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6"
            >
              {{ $product['title'] }}
            </a>
          @endforeach
        @else
          <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('footer.all_products') }}</a>
        @endif

        <p class="px-3 pt-2 pb-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('nav.education') }}</p>
        <a href="{{ route('education.back-pain', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.back_pain') }}</a>
        <a href="{{ route('education.sciatica', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.sciatica') }}</a>
        <a href="{{ route('education.posture', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors pl-6">{{ __('nav.posture') }}</a>
        <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors">{{ __('nav.blog') }}</a>
        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-navy-700 transition-colors">{{ __('nav.about') }}</a>
        <div class="pt-2 px-3">
          <a href="{{ $shopNowUrl }}" class="btn-primary w-full justify-center text-sm">{{ __('nav.shop_now') }}</a>
        </div>
      </div>
    </div>
  </nav>
</header>

