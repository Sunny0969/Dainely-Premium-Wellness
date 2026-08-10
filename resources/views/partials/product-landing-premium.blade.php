@php
  $galleryImages = $galleryImages ?? [];
  // Prefer Shopify CDN URLs for the product hero gallery
  $galleryRaw = array_values(array_filter(array_map(function ($img) {
      if (! is_string($img) || trim($img) === '') {
          return null;
      }
      $img = trim($img);
      if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '//')) {
          return $img;
      }

      return null;
  }, $galleryImages)));
  if ($galleryRaw === [] && ! empty($mainImg) && (str_starts_with((string) $mainImg, 'http') || str_starts_with((string) $mainImg, '//'))) {
      $galleryRaw = [$mainImg];
  }
  $galleryImages = array_map(
      static fn (string $u): string => \App\Support\ProductLandingAssets::cdnSized($u, 960),
      $galleryRaw
  );
  $galleryThumbs = array_map(
      static fn (string $u): string => \App\Support\ProductLandingAssets::cdnSized($u, 240),
      $galleryRaw
  );
  $lifestyleImages = $lifestyleImages ?? [];
  $scienceImage = $scienceImage ?? 'spine-anatomy.png';
  $scienceSrc = str_starts_with((string) $scienceImage, 'http')
      ? \App\Support\ProductLandingAssets::cdnSized($scienceImage, 900)
      : asset('images/'.$scienceImage);
  $displayPrice = $price ?? 0;
  $displayCompare = $compareAt ?? null;
  $landingList = fn (string $field): array => \App\Support\ProductLandingLang::landingList($langKey, $field);
  $landingRootList = fn (string $field): array => \App\Support\ProductLandingLang::landingList('product_landing', $field);
  $t = fn (string $key, array $replace = []): string => \App\Support\ProductLandingLang::line($langKey, ltrim($key, '.'), $replace);
  $purchaseOptions = $purchaseOptions ?? [
      'cartAddUrl'     => $cartAddUrl,
      'checkoutUrl'    => $checkoutUrl,
      'requiresOption' => $requiresOption,
      'options'        => $variants,
      'optionType'     => 'shopify',
      'optionLabel'    => $t('select_option'),
      'showSizeGuide'  => $showSizeGuide ?? false,
      'sizeGuideHref'  => $sizeGuideHref ?? '#size-guide',
      'addToCartText'  => $t('add_to_cart'),
      'orderNowText'   => $t('order_now'),
  ];
  $breadcrumbName = !empty($cmsSeoTitle) ? $cmsSeoTitle : $t('product_name');
  if (! is_string($breadcrumbName) || trim($breadcrumbName) === '' || str_contains($breadcrumbName, '{{')) {
      $breadcrumbName = $productTitle ?? $t('hero_headline');
  }
@endphp

<div x-data="productPurchase({{ ($purchaseOptions['requiresOption'] ?? $requiresOption) ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl), @js($checkoutUrl))" class="product-landing pb-24 lg:pb-0">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors shrink-0">{{ __('nav.home') }}</a>
      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors shrink-0">{{ __('nav.products') }}</a>
      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium break-anywhere min-w-0">{{ $breadcrumbName }}</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white pt-4 sm:pt-5 pb-8 sm:pb-12 lg:pb-16" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="productLandingGallery(@js($galleryImages), @js($galleryThumbs))" class="min-w-0 lg:sticky lg:top-24">
        <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          @if(!empty($galleryImages))
          <img
            src="{{ $galleryImages[0] }}"
            alt="{{ $t('product_name') }}"
            class="w-full h-full object-cover transition-all duration-500"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            width="640"
            height="640"
            x-bind:src="images.length ? images[active] : @js($galleryImages[0])"
          >
          @else
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-3 left-3 right-3 flex items-start justify-between gap-2 pointer-events-none">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-emerald-500 text-white shrink-0">{{ $t('badge_best_seller') }}</span>
            <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-sm rounded-lg sm:rounded-xl px-2 py-1 sm:px-3 sm:py-1.5 shadow text-sage-700 text-[10px] sm:text-xs font-semibold shrink min-w-0 max-w-[55%] sm:max-w-none">
              <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              <span class="break-anywhere leading-tight">{{ $t('badge_clinical') }}</span>
            </span>
          </div>
        </div>
        <div class="grid grid-cols-4 gap-2" x-show="images.length > 1">
          <template x-for="(img, i) in images" :key="i">
            <button type="button" @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-400'" class="rounded-xl overflow-hidden aspect-square focus:outline-none transition-all">
              <img :src="thumbs[i] || img" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async" width="120" height="120">
            </button>
          </template>
        </div>
        <div class="grid grid-cols-3 gap-2 sm:gap-3 mt-4 sm:mt-5 p-3 sm:p-4 bg-slate-50 rounded-2xl">
          @foreach($landingList('trust_strip') as $index => [$label, $sub])
          @php $trustColors = ['sage', 'navy', 'gold']; $c = $trustColors[$index] ?? 'sage'; @endphp
          <div class="text-center min-w-0">
            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-{{ $c }}-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-{{ $c }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <p class="text-slate-700 text-[10px] sm:text-xs font-semibold break-anywhere leading-tight">{{ $label }}</p>
            <p class="text-slate-500 text-[9px] sm:text-[10px] break-anywhere leading-tight">{{ $sub }}</p>
          </div>
          @endforeach
        </div>
      </div>

      {{-- RIGHT: Product Info --}}
      <div class="min-w-0">
        <p class="text-xs sm:text-sm font-bold uppercase tracking-widest text-navy-500 mb-3 break-anywhere">{{ $t('eyebrow') }}</p>
        <h1 class="font-display font-bold text-navy-950 mb-4 text-3xl sm:text-4xl lg:text-5xl leading-tight break-anywhere">
          {{ $t('hero_headline') }}
        </h1>

        <div
          class="flex flex-wrap items-center gap-x-2 gap-y-2 mb-6"
          x-data="productReviewHeader(@js([
            'average_rating' => (float) ($reviewStats['average_rating'] ?? 0),
            'total_reviews' => (int) ($reviewStats['total_reviews'] ?? 0),
          ]), @js($t('verified_reviews', ['count' => ':count'])))"
        >
          <div class="flex gap-0.5 shrink-0">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm shrink-0" x-text="average > 0 ? average : '—'">{{ ($reviewStats['average_rating'] ?? 0) > 0 ? $reviewStats['average_rating'] : '—' }}</span>
          <a href="#reviews" class="text-slate-500 text-xs sm:text-sm hover:text-navy-700 underline underline-offset-2 break-anywhere" x-text="label">{{ $t('verified_reviews', ['count' => number_format($reviewStats['total_reviews'] ?? 0)]) }}</a>
          <span class="text-slate-300 hidden sm:inline">|</span>
          <span class="text-emerald-600 text-xs sm:text-sm font-semibold shrink-0">{{ $t('in_stock') }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div class="min-w-0">
            <span class="font-display font-bold text-3xl sm:text-4xl text-navy-900">{{ $fmt($displayPrice) }}</span>
            @if($displayCompare)
            <span class="text-slate-400 line-through text-base sm:text-lg ml-2">{{ $fmt($displayCompare) }}</span>
            @endif
          </div>
          @if($displayCompare && $displayCompare > $displayPrice)
          <div class="shrink-0">
            @php
              $savingPercent = round((($displayCompare - $displayPrice) / $displayCompare) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-xs sm:text-sm font-bold px-3 py-1 rounded-full whitespace-nowrap">{{ __('home.save_percent', ['percent' => $savingPercent]) }}</span>
          </div>
          @endif
        </div>

        <div class="cms-richtext text-slate-600 text-sm sm:text-base mb-6 break-anywhere">
          @if(!empty($cmsOverview))
            {!! \App\Support\CmsHtml::normalize($cmsOverview) !!}
          @else
            <p>{{ $t('description') }}</p>
          @endif
        </div>

        @php
          $cmsBenefitsRaw = trim((string) ($cmsBenefits ?? ''));
          $cmsBenefitItems = [];
          if ($cmsBenefitsRaw !== '' && $cmsBenefitsRaw === strip_tags($cmsBenefitsRaw)) {
              $cmsBenefitItems = array_values(array_filter(array_map(
                  static fn (string $line): string => trim(preg_replace('/^[-*•]\s+/u', '', $line) ?? $line),
                  preg_split('/\r\n|\r|\n/', $cmsBenefitsRaw) ?: []
              )));
          }
        @endphp
        @if($cmsBenefitsRaw !== '')
          @if(count($cmsBenefitItems) > 0)
          <ul class="space-y-2.5 mb-8">
            @foreach($cmsBenefitItems as $benefit)
            @php $benefitColor = $loop->last ? 'gold' : 'sage'; @endphp
            <li class="flex items-start gap-3 min-w-0">
              <svg class="w-5 h-5 mt-0.5 text-{{ $benefitColor }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
              <span class="text-slate-700 text-sm break-anywhere min-w-0">{{ $benefit }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="cms-richtext text-slate-700 text-sm sm:text-base mb-8 break-anywhere">
            {!! \App\Support\CmsHtml::normalize($cmsBenefitsRaw) !!}
          </div>
          @endif
        @endif

        @include('partials.product-purchase', $purchaseOptions)

        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">{{ $t('guarantee_title') }}</p>
            <p class="text-sage-600 text-xs">{!! $t('guarantee_desc') !!}</p>        </div>

        <div class="flex flex-wrap gap-4 mt-5 text-xs text-slate-500">
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> {{ $t('micro_secure') }}</span>
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> {{ $t('micro_shipping') }}</span>
          <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-sage-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $t('micro_trusted') }}</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 1b. BOTANICAL INGREDIENTS (optional per product) ─────── --}}
@php
  $botanicalItems = collect($landingList('botanical_items') ?? [])
      ->map(fn ($row) => is_string($row) ? trim($row) : trim((string) (is_array($row) ? ($row[0] ?? '') : '')))
      ->filter()
      ->values();
  $botanicalTitle = trim((string) $t('botanical_title'));
  $botanicalIntro = trim((string) $t('botanical_intro'));
  $botanicalNote = trim((string) $t('botanical_note'));
  $showBotanical = $botanicalItems->isNotEmpty()
      && $botanicalTitle !== ''
      && ! str_contains($botanicalTitle, 'product_landing.');
@endphp
@if($showBotanical)
<section class="section bg-white border-t border-slate-100" aria-labelledby="botanical-heading" id="botanical-ingredients">
  <div class="container-site max-w-3xl">
    <h2 id="botanical-heading" class="heading-section mb-4">{{ $botanicalTitle }}</h2>
    @if($botanicalIntro !== '' && ! str_contains($botanicalIntro, 'product_landing.'))
      <p class="text-body text-stone-600 mb-6">{{ $botanicalIntro }}</p>
    @endif
    <ul class="grid sm:grid-cols-2 gap-x-8 gap-y-2.5 mb-6">
      @foreach($botanicalItems as $item)
        <li class="flex items-start gap-2.5 text-stone-700 text-sm sm:text-base">
          <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-sage-600 flex-shrink-0" aria-hidden="true"></span>
          <span>{{ $item }}</span>
        </li>
      @endforeach
    </ul>
    @if($botanicalNote !== '' && ! str_contains($botanicalNote, 'product_landing.'))
      <p class="text-sm text-stone-500 leading-relaxed">{{ $botanicalNote }}</p>
    @endif
  </div>
</section>
@endif

{{-- ── 2. CMS DETAIL GRID (How / Who / Specs / Care) ──────────── --}}
@php
  $cmsDetailCards = array_values(array_filter([
      [
          'title' => \App\Support\ProductLandingLang::line('product_landing', 'cms_specs_title'),
          'body'  => trim((string) ($cmsSpecifications ?? '')),
      ],
      [
          'title' => \App\Support\ProductLandingLang::line('product_landing', 'cms_care_title'),
          'body'  => trim((string) ($cmsCare ?? '')),
      ],
      [
          'title' => \App\Support\ProductLandingLang::line('product_landing', 'cms_how_title'),
          'body'  => trim((string) ($cmsHowItWorks ?? '')),
      ],
      [
          'title' => \App\Support\ProductLandingLang::line('product_landing', 'cms_who_title'),
          'body'  => trim((string) ($cmsWhoIsItFor ?? '')),
      ],
  ], static fn (array $card): bool => $card['body'] !== ''));
@endphp
@if(count($cmsDetailCards) > 0)
<section class="bg-white border-y border-slate-100 py-12 md:py-16" aria-label="Product details">
  <div class="container-site">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-8">
      @foreach($cmsDetailCards as $card)
      <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-6 md:p-8 h-full">
        <h3 class="font-display font-bold text-navy-950 text-lg md:text-xl mb-3">{{ $card['title'] }}</h3>
        <div class="cms-richtext text-slate-600 text-sm md:text-base break-anywhere">
          {!! \App\Support\CmsHtml::normalize($card['body']) !!}
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── 3. LIFESTYLE POSITIONING ──────────────────────────────── --}}
<section class="section bg-stone-50" aria-label="Lifestyle">
  <div class="container-site">
    <div class="max-w-2xl mb-12">
      <p class="eyebrow mb-3">{{ $t('lifestyle_eyebrow') }}</p>
      <h2 class="heading-section text-stone-900 mb-4">{{ $t('lifestyle_title') }}</h2>
      <p class="text-body text-stone-600">{{ $t('lifestyle_copy') }}</p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach($landingList('lifestyle_cards') as $index => [$cap, $sub])
      @php $img = $lifestyleImages[$index] ?? 'recovery-edu.webp'; @endphp
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ str_starts_with($img, 'http') ? $img : asset('images/' . $img) }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" decoding="async" width="400" height="500">
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

{{-- ── 4. HOW IT WORKS ───────────────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">{{ $t('how_eyebrow') }}</p>
      <h2 class="heading-section mb-4">{{ $t('how_title') }}</h2>
      <p class="text-lead max-w-5xl mx-auto">{{ $t('how_copy') }}</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach($landingList('how_steps') as [$num, $title, $desc])
      @php $stepColors = ['navy', 'gold', 'sage']; $color = $stepColors[(int) $num - 1] ?? 'navy'; @endphp
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{!! $desc !!}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4b. SAFETY / IMPORTANT INFORMATION (optional) ─────────── --}}
@php
  $safetyItems = collect($landingList('safety_items') ?? [])
      ->map(fn ($row) => is_string($row) ? trim($row) : trim((string) (is_array($row) ? ($row[0] ?? '') : '')))
      ->filter()
      ->values();
  $safetyTitle = trim((string) $t('safety_title'));
  $showSafety = $safetyItems->isNotEmpty()
      && $safetyTitle !== ''
      && ! str_contains($safetyTitle, 'product_landing.');
@endphp
@if($showSafety)
<section class="section bg-stone-50 border-y border-stone-100" aria-labelledby="safety-heading" id="important-information">
  <div class="container-site max-w-3xl">
    <h2 id="safety-heading" class="heading-section mb-6">{{ $safetyTitle }}</h2>
    <ul class="space-y-3">
      @foreach($safetyItems as $item)
        <li class="flex items-start gap-3 text-stone-700 text-sm sm:text-base">
          <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-navy-600 flex-shrink-0" aria-hidden="true"></span>
          <span>{{ $item }}</span>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endif

{{-- ── 5. SCIENCE / AUTHORITY ────────────────────────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">{{ $t('science_eyebrow') }}</p>
        <h2 class="heading-section text-white mb-6">{{ $t('science_title') }}</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">{{ $t('science_p1') }}</p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">{{ $t('science_p2') }}</p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach($landingList('stats') as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ $scienceSrc }}" alt="{{ $t('science_title') }}" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" decoding="async" width="600" height="500">
        <div class="absolute -bottom-4 sm:-bottom-6 right-0 sm:-right-6 bg-white rounded-2xl shadow-lg p-4 z-20 max-w-[calc(100%-1rem)] sm:max-w-xs">
          @php
            $doctorName = trim((string) $t('doctor_name'));
            $doctorTitle = trim((string) $t('doctor_title'));
            $showDoctorPhoto = $doctorTitle !== '' && str_starts_with($doctorName, 'Dr');
          @endphp
          <div class="flex items-center gap-2 mb-2">
            @if($showDoctorPhoto)
            <img src="{{ asset('images/trust-doctor.png') }}" alt="{{ $doctorName }}" class="w-10 h-10 rounded-full object-cover" loading="lazy" decoding="async" width="40" height="40">
            @endif
            <div>
              <p class="text-navy-900 text-xs font-bold">{{ $doctorName }}</p>
              @if($doctorTitle !== '')
              <p class="text-slate-400 text-[10px]">{{ $doctorTitle }}</p>
              @endif
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"{{ $t('doctor_quote') }}"</p>
        </div>
      </div>
    </div>
  </div>
</section>

@if($showSizeGuide ?? false)
{{-- ── SIZE GUIDE (belt) ─────────────────────────────────────── --}}
<section id="size-guide" class="section bg-white" aria-label="Size guide">
  <div class="container-site">
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">{{ __('product_landing.size_guide_eyebrow') }}</p>
      <h2 class="heading-section mb-4">{{ __('product_landing.size_guide_title') }}</h2>
    </div>
    <div class="max-w-2xl mx-auto overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
      <table class="w-full text-sm text-left">
        <thead class="bg-navy-50 text-navy-700 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3 font-semibold">{{ __('product_landing.size_guide_col_size') }}</th>
            <th class="px-5 py-3 font-semibold">{{ __('product_landing.size_guide_col_waist') }}</th>
            <th class="px-5 py-3 font-semibold">{{ __('product_landing.size_guide_col_for') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($landingRootList('size_guide_rows') as [$size, $waist, $rec])
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
@endif

{{-- ── 5b. ADMIN PAGE LAYOUT BLOCKS (stacked in order) ────────── --}}
@php
  $cmsPageBlocks = collect($pageBlocks ?? [])
      ->filter(fn ($b) => (bool) ($b->visible ?? true))
      ->sortBy(fn ($b) => (int) ($b->sort_order ?? 0))
      ->values();
@endphp
@foreach($cmsPageBlocks as $block)
  @php $blockView = 'components.blocks.' . ($block->block_type ?? ''); @endphp
  @includeIf($blockView, [
      'title'   => $block->title,
      'content' => $block->content,
  ])
@endforeach

{{-- ── 6. FAQ (Phase 2 §6.4: semantic HTML, not JS-only) ── --}}
@include('partials.reviews-lazy', ['handle' => $handle])

@php
  $cmsFaqs = collect($cmsFaqs ?? [])->map(function ($row) {
      return (object) [
          'question' => trim((string) (is_object($row) ? ($row->question ?? '') : ($row['question'] ?? ''))),
          'answer'   => trim((string) (is_object($row) ? ($row->answer ?? '') : ($row['answer'] ?? ''))),
      ];
  })->filter(fn ($f) => $f->question !== '' && $f->answer !== '')->values();

  $landingFaqs = collect($landingList('faqs') ?? [])->map(function ($row) {
      return (object) ['question' => $row[1] ?? '', 'answer' => $row[2] ?? ''];
  })->filter(fn ($f) => $f->question !== '' && $f->answer !== '');

  // Once CMS FAQs exist for this product, never fall back to lang-file defaults
  // (otherwise deletes look like they "didn't publish").
  $allFaqs = (! empty($preferCmsFaqs) || $cmsFaqs->isNotEmpty())
      ? $cmsFaqs
      : $landingFaqs;
@endphp

@if($allFaqs->isNotEmpty())
<section class="faq-section section bg-stone-50" id="faq" aria-labelledby="premium-faq-heading">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">{{ $t('faq_eyebrow') }}</p>
      <h2 id="premium-faq-heading" class="heading-section mb-4">{{ $t('faq_title') }}</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach($allFaqs as $faq)
      <details class="group bg-white rounded-2xl border border-slate-200 px-6 py-4 open:shadow-sm">
        <summary class="cursor-pointer list-none font-semibold text-slate-800 text-sm flex items-center justify-between gap-4">
          <span>{{ $faq->question }}</span>
          <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0 group-open:rotate-180 group-open:text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-3 text-slate-600 text-sm leading-relaxed cms-richtext">
          {!! \App\Support\CmsHtml::normalize($faq->answer) !!}
        </div>
      </details>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
@php
  $ctaLovePoints = collect($landingList('cta_love_points') ?? [])
      ->map(function ($row) {
          if (is_string($row)) {
              return trim($row);
          }
          if (is_array($row)) {
              return trim((string) ($row[0] ?? $row['text'] ?? ''));
          }

          return '';
      })
      ->filter()
      ->values();
  $ctaEyebrow = trim((string) $t('cta_eyebrow'));
  $ctaCopy = trim((string) $t('cta_copy'));
@endphp
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    @if($ctaEyebrow !== '' && ! str_contains($ctaEyebrow, 'product_landing.'))
      <p class="eyebrow mb-4">{{ $ctaEyebrow }}</p>
    @endif
    <h2 class="heading-section mb-4">{{ $t('cta_title') }}</h2>

    @if($ctaLovePoints->isNotEmpty())
      <ul class="max-w-md mx-auto text-left space-y-3 mb-8">
        @foreach($ctaLovePoints as $point)
          <li class="flex items-start gap-3 text-stone-700 text-sm sm:text-base">
            <span class="mt-0.5 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-sage-100 text-sage-700 text-xs font-bold" aria-hidden="true">✓</span>
            <span>{{ $point }}</span>
          </li>
        @endforeach
      </ul>
    @elseif($ctaCopy !== '' && ! str_contains($ctaCopy, 'product_landing.'))
      <p class="text-lead text-stone-600 mb-3">{{ $ctaCopy }}</p>
    @endif

    <div class="mb-6">
      <span class="font-display font-bold text-4xl sm:text-5xl text-navy-900">{{ $fmt($displayPrice) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="addToCart($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        {{ $t('cta_button') }}
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>{{ $t('cta_guarantee') }}</span>
    </div>
  </div>
</section>

{{-- Mobile sticky Order Now — fixed at bottom while scrolling --}}
<div
  id="sticky-order-bar"
  class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-[0_-4px_24px_rgba(0,0,0,0.12)]"
  style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));"
  aria-label="Quick order bar"
>
  <div class="container-site pt-3">
    <div class="flex items-center gap-3">
      <div class="min-w-0 flex-1">
        <p class="font-bold text-navy-900 text-sm truncate">{{ $t('product_name') }}</p>
        <p class="text-navy-700 font-bold text-base">{{ $fmt($displayPrice) }}</p>
      </div>
      <button
        type="button"
        @click="goToCheckout($event)"
        :class="loading ? 'bg-gold-400 opacity-70 cursor-wait' : 'bg-gold-400 hover:bg-gold-500'"
        :disabled="loading"
        class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 text-white font-bold px-5 py-3 rounded-xl transition-colors text-sm shadow-md min-w-[8.5rem]"
      >
        {{ $purchaseOptions['orderNowText'] ?? $t('order_now') }}
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </button>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  (function () {
    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
    }
    const scrollTop = () => window.scrollTo(0, 0);
    scrollTop();
    document.addEventListener('DOMContentLoaded', scrollTop, { once: true });
    window.addEventListener('pageshow', (event) => {
      if (event.persisted) scrollTop();
    });
  })();
</script>
@endpush
