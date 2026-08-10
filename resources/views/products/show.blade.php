@extends('layouts.app')

@push('json-ld')
    <script type="application/ld+json">
        {!! $productJsonLd !!}
    </script>
@endpush

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
  $currencySvc = app(\App\Services\CurrencyService::class);
  $fmt = fn (float|int|string|null $amountUsd) => $currencySvc->formatForLocale((float) ($amountUsd ?? 0), $locale);
  $checkoutUrl = route('checkout.index', ['locale' => $locale]);
  $cartAddUrl  = route('cart.store',    ['locale' => $locale]);
  $requiresOption = count($variants) > 1
      || \App\Support\ProductRequiresSize::check((string) ($product['id'] ?? $handle), $title, $handle);

  // Detect Dainely Belt variants
  $isDainelyBelt = in_array($handle, ['dainely-belt', 'dainely-comfort-belt', 'dainely-belt-2-b', 'dainely-belt-2-c']);

  // Detect Dainely Ball Massager
  $isBallMassager = in_array($handle, ['dainely-ball-massager', 'dainely™-ball-massager', 'dainely-ball-massager-1']);

  // Detect Neck Cloud
  $isNeckCloud = in_array($handle, ['neck-pain']);

  // Detect Back Pain Patches
  $isBackPatches = in_array($handle, ['back-pain-relief-patches-20-pcs']);

  // Detect Heated Jacket
  $isHeatedJacket = in_array($handle, ['dainely-unisex-heated-jacket']);

  // Detect Foot Massager
  $isFootMassager = in_array($handle, ['dainely-foot-massager', 'dainely™-foot-massager']);

  // Detect Knee Brace
  $isKneeBrace = in_array($handle, ['brace', 'dainely-knee-brace']);

  // Detect Dainely Massager
  $isDainelyMassager = in_array($handle, ['dainely-massager', 'dainely™-massager']);

  // Detect Shoulder Brace
  $isShoulderBrace = in_array($handle, ['shoulder-brace', 'dainely-shoulder-brace']);

  // Detect Neck Stretcher
  $isNeckStretcher = in_array($handle, ['stretcher', 'dainely-neck-stretcher']);

  // Detect Back Stretcher
  $isBackStretcher = in_array($handle, ['dainely™-orthopedic-back-stretcher', 'dainely-orthopedic-back-stretcher', 'back-stretcher']);

  // Detect RelaxaLeg System
  $isRelaxaLeg = in_array($handle, ['leg-massager', 'relaxaleg-system', 'dainely-relaxaleg-system', 'relaxaleg']);

  // Detect Tourmaline Belt
  $isTourmalineBelt = in_array($handle, ['dainely™-tourmaline-belt', 'dainely-tourmaline-belt', 'tourmaline-belt']);

  // Detect DMEDE Daily Support & Recovery System
  $isDmedeSystem = in_array($handle, ['dainely-daily-comfort-system', 'daily-relief-system', 'dmede-daily-support', 'dmede-daily-support-recovery-system']);

  // Detect ErgoCushion
  $isErgoCushion = in_array($handle, ['cushion', 'dainely-cushion', 'ergocushion']);

  // Detect Functional Mushroom Coffee
  $isMushroomCoffee = in_array($handle, ['functional-mushroom-coffee', 'mushroom-coffee', 'coffee']);
  // Resolve premium landing translation key
  $productLangKey = \App\Support\ProductLandingLang::resolveLangKey($handle, [
    'isDainelyBelt'     => $isDainelyBelt,
    'isBallMassager'    => $isBallMassager,
    'isNeckCloud'       => $isNeckCloud,
    'isBackPatches'     => $isBackPatches,
    'isHeatedJacket'    => $isHeatedJacket,
    'isFootMassager'    => $isFootMassager,
    'isKneeBrace'       => $isKneeBrace,
    'isDainelyMassager' => $isDainelyMassager,
    'isShoulderBrace'   => $isShoulderBrace,
    'isNeckStretcher'   => $isNeckStretcher,
    'isBackStretcher'   => $isBackStretcher,
    'isRelaxaLeg'       => $isRelaxaLeg,
    'isTourmalineBelt'  => $isTourmalineBelt,
    'isDmedeSystem'     => $isDmedeSystem,
    'isErgoCushion'     => $isErgoCushion,
    'isMushroomCoffee'  => $isMushroomCoffee,
  ]);
  $productLangPrefix = \App\Support\ProductLandingLang::translationPrefix($productLangKey);
  // Product gallery / cart / OG — Shopify CDN only (no local product photo overrides)
  $galleryUrls = \App\Support\ProductLandingAssets::shopifyImageUrls(
      is_array($images) ? $images : [],
      is_string($mainImg) ? $mainImg : null
  );
  $shopifyMainImg = $galleryUrls[0] ?? null;
  // Keep $mainImg as Shopify primary so legacy schema / sticky bar stay consistent
  $mainImg = $shopifyMainImg;

  $mappedVariants = \App\Support\ProductLandingAssets::mapVariantsForCart($variants);
  $cartTitle = $productLangPrefix
    ? \App\Support\ProductLandingLang::line($productLangPrefix, 'product_name')
    : $title;
  $cartSubtitle = $productLangPrefix
    ? \App\Support\ProductLandingLang::line($productLangPrefix, 'eyebrow')
    : (\Illuminate\Support\Str::limit($plainDesc, 100) ?: __('home.premium_subtitle'));

  $cartProduct = [
    'id'              => (string) ($product['id'] ?? $handle),
    'handle'          => $handle,
    'title'           => $cartTitle,
    'subtitle'        => $cartSubtitle,
    'image'           => $shopifyMainImg ?: '',
    'price'           => (float) ($price ?? 0),
    'compare_at_price'=> $compareAt ? (float) $compareAt : null,
    'variants'        => $mappedVariants,
    'source'          => 'shopify',
    'messages'        => [
      'selectOption' => __('products.select_option'),
    ],
  ];
@endphp

@php
  // Defaults: Shopify title + description
  $seoTitle = $title . ' — ' . config('app.name');
  $seoDesc = \Illuminate\Support\Str::limit($plainDesc, 160) ?: 'View product details.';

  // Premium landing copy (lang files) when no CMS overlay
  if ($productLangPrefix) {
    $seoTitle = \App\Support\ProductLandingLang::line($productLangPrefix, 'seo_title');
    $seoDesc = \App\Support\ProductLandingLang::line($productLangPrefix, 'seo_desc');
  }

  // Admin Product Overlay wins when filled; empty fields keep defaults above
  $cmsSeoTitle = trim((string) ($product['seo_title'] ?? ''));
  $cmsSeoDesc = trim((string) ($product['seo_description'] ?? ''));
  $cmsCanonical = trim((string) ($product['canonical_url'] ?? ($product['localized_content']['canonical_url'] ?? '')));
  if ($cmsSeoTitle !== '') {
    $seoTitle = $cmsSeoTitle;
  }
  if ($cmsSeoDesc !== '') {
    $seoDesc = $cmsSeoDesc;
  }
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDesc)
@section('og_image', $shopifyMainImg ?? '')
@if($cmsCanonical !== '')
@section('meta_canonical')
<link rel="canonical" href="{{ $cmsCanonical }}">
@endsection
@endif

@if($isDainelyBelt)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely Belt",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Premium everyday lower back stabilization designed for modern movement and long daily routines.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float) ($price ?? 0), 2, '.', '') }}",
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
@elseif($isBallMassager)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Ball Massager",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Eliminate neck and shoulder pain in 10 minutes a day with the Dainely™ Ball Massager.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 39.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "864"
  }
}
</script>
@endsection
@elseif($isNeckCloud)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Neck Cloud™️",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Eliminate neck pain, tension headaches, and stiffness in just 10 minutes a day.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 39.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "342"
  }
}
</script>
@endsection
@elseif($isBackPatches)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Back Pain Relief Patches",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Soothe tight lower back muscles and relieve lumbar soreness with 8-hour active herbal warming patches.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 19.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.7",
    "reviewCount": "194"
  }
}
</script>
@endsection
@elseif($isHeatedJacket)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Unisex Heated Jacket",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Stay warm in any weather with the Dainely™ Unisex Heated Jacket. Features smart carbon fiber heating elements.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 99.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "254"
  }
}
</script>
@endsection
@elseif($isFootMassager)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Foot Massager",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Alleviate foot neuropathy, swelling, and chronic aches in just 15 minutes a day.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 49.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "412"
  }
}
</script>
@endsection
@elseif($isKneeBrace)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Knee Brace",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Stabilize knee joints, relieve meniscus and patella pressure, and walk without pain.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 39.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "624"
  }
}
</script>
@endsection
@elseif($isDainelyMassager)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Massager",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Professional deep tissue percussion massager designed for muscle stiffness, soreness, and quick recovery.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 59.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "286"
  }
}
</script>
@endsection
@elseif($isShoulderBrace)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Shoulder Brace",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Premium shoulder compression sleeve with adjustable straps for rotator cuff support, AC joint stability, and pain relief.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 34.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "246"
  }
}
</script>
@endsection
@elseif($isNeckStretcher)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Neck Stretcher",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Ergonomic cervical traction device designed to restore natural neck posture, relieve tension headaches, and decompress spinal discs.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 39.90), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "384"
  }
}
</script>
@endsection
@elseif($isBackStretcher)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Orthopedic Back Stretcher",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Ergonomic multi-level lumbar support device designed to decompress the spine, relieve lower back pain, and improve overall posture.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 34.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "512"
  }
}
</script>
@endsection
@elseif($isRelaxaLeg)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ RelaxaLeg™ System",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Premium pneumatic air compression leg massager wraps with soothing carbon heat therapy designed for restless leg syndrome, edema, and heavy, tired legs.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 199.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "648"
  }
}
</script>
@endsection
@elseif($isTourmalineBelt)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Tourmaline Belt",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Premium self-heating magnetic therapy support wrap designed to decompress the spine, relieve lower back stiffness, and improve lumbar posture.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 32.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "314"
  }
}
</script>
@endsection
@elseif($isDmedeSystem)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "DMEDE™ Daily Support & Recovery System",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Align your posture, stabilize your SI joint, and accelerate recovery with the DMEDE™ Daily Support & Recovery System.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 89.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "246"
  }
}
</script>
@endsection
@elseif($isErgoCushion)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "ErgoCushion® - Pressure Relief Seat Cushion",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Premium orthopedic memory foam seat cushion designed to decompress the tailbone, relieve sciatica, and align seated posture.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 69.99), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "512"
  }
}
</script>
@endsection
@elseif($isMushroomCoffee)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Functional Mushroom Coffee",
  "image": "{{ $shopifyMainImg ?? '' }}",
  "description": "Morning ritual reimagined. 6 powerful adaptogenic mushrooms blended with premium Arabica coffee for sustained clear mind and focus.",
  "brand": { "@type": "Brand", "name": "Dainely" },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": "{{ number_format((float)($price ?? 34.95), 2, '.', '') }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "194"
  }
}
</script>
@endsection
@endif

@section('content')

{{-- ============================================================
     PREMIUM PRODUCT LANDING (translated via product_landing / products_fm)
     ============================================================ --}}
@if($productLangPrefix)
@php
  $landingAssets = \App\Support\ProductLandingAssets::forProduct(
    $productLangKey,
    $handle,
    $shopifyMainImg,
    $variants,
    $requiresOption,
    (float) ($price ?? 0),
    $compareAt ? (float) $compareAt : null,
    $cartAddUrl,
    $checkoutUrl,
    $productLangPrefix,
    $galleryUrls,
  );
@endphp
@include('partials.product-landing-premium', [
  'langKey'          => $productLangPrefix,
  'cartProduct'      => $cartProduct,
  'cartAddUrl'       => $cartAddUrl,
  'checkoutUrl'      => $checkoutUrl,
  'requiresOption'   => $landingAssets['purchaseOptions']['requiresOption'] ?? $requiresOption,
  'variants'         => $landingAssets['purchaseOptions']['options'] ?? $variants,
  'handle'           => $handle,
  'mainImg'          => $shopifyMainImg,
  'price'            => $landingAssets['price'] ?? $price,
  'compareAt'        => $landingAssets['compareAt'] ?? $compareAt,
  'reviewStats'      => $reviewStats,
  'locale'           => $locale,
  'fmt'              => $fmt,
  'galleryImages'    => $landingAssets['galleryImages'] ?? [],
  'scienceImage'     => $landingAssets['scienceImage'] ?? 'spine-anatomy.png',
  'lifestyleImages'  => $landingAssets['lifestyleImages'] ?? [],
  'purchaseOptions'  => $landingAssets['purchaseOptions'] ?? null,
  'showSizeGuide'    => $landingAssets['showSizeGuide'] ?? false,
  'sizeGuideHref'    => $landingAssets['sizeGuideHref'] ?? '#size-guide',
  'productTitle'     => $title,
  'faqItems'         => $faqItems ?? collect(),
  'cmsFaqs'          => $faqs ?? collect(),
  // Resolved FAQs are already locale-correct (CMS or auto-translated).
  'preferCmsFaqs'    => ($faqs ?? collect())->isNotEmpty(),
  'pageBlocks'       => $pageBlocks ?? collect(),
  'relatedLinks'     => $relatedLinks ?? collect(),
  'breadcrumbs'      => $breadcrumbs ?? [],
  'cmsOverview'      => $product['localized_content']['overview'] ?? null,
  'cmsBenefits'      => $product['localized_content']['benefits'] ?? null,
  'cmsHowItWorks'    => $product['localized_content']['how_it_works'] ?? null,
  'cmsWhoIsItFor'    => $product['localized_content']['who_is_it_for'] ?? null,
  'cmsSpecifications'=> $product['localized_content']['specifications'] ?? null,
  'cmsCare'          => $product['localized_content']['care'] ?? null,
  'cmsSeoTitle'      => $product['seo_title'] ?? null,
])

@else

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl), @js($checkoutUrl))">

{{-- Breadcrumb --}}
@include('components.breadcrumbs', ['items' => $breadcrumbs ?? []])
@if(empty($breadcrumbs))
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">{{ __('products.breadcrumb_home') }}</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">{{ __('nav.products') }}</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">{{ \Illuminate\Support\Str::limit($title, 40, '…') }}</span>
    </nav>
  </div>
</div>
@endif

{{-- Standard product hero --}}
<section class="bg-white pt-4 sm:pt-5 pb-12 md:pb-16 product-landing" aria-label="Product detail">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-20 items-start">

      {{-- Left: Image --}}
      <div x-data="productGallery(@js($galleryUrls))" class="min-w-0 lg:sticky lg:top-24">
        <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-50 shadow-md mb-4">
          @if(!empty($galleryUrls))
          <img
            src="{{ $galleryUrls[0] }}"
            alt="{{ $title }}"
            class="w-full aspect-square object-cover transition-all duration-500"
            loading="eager"
            width="640"
            height="640"
            x-bind:src="images.length ? images[active] : @js($galleryUrls[0])"
          >
          @else
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-3 left-3 right-3 flex items-start justify-between gap-2 pointer-events-none">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold {{ $status === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white' }} shrink-0">{{ ucfirst($status) }}</span>
          @if($vendor)
            <span class="inline-flex items-center bg-white/90 backdrop-blur-sm rounded-lg sm:rounded-xl px-2 py-1 sm:px-3 sm:py-1.5 shadow text-navy-700 text-[10px] sm:text-xs font-semibold shrink min-w-0 max-w-[55%] sm:max-w-none break-anywhere leading-tight">{{ $vendor }}</span>
          @endif
          </div>
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
          @php
            $freeShipBadge = 'Over ' . $fmt($currencySvc->freeShippingThresholdUsd());
          @endphp
          @foreach([['30-Day', 'Guarantee', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'sage'], ['Free Ship', $freeShipBadge, 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'navy'], ['Secure', 'Payment', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'gold']] as [$label, $sub, $path, $c])
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
      <div class="min-w-0">
        @if($vendor)<p class="eyebrow mb-3 break-anywhere">{{ $vendor }}</p>@endif
        <h1 class="font-display font-bold text-navy-950 mb-4 text-2xl sm:text-3xl lg:text-4xl leading-tight break-anywhere">{{ $title }}</h1>
        <div
          class="flex flex-wrap items-center gap-x-2 gap-y-2 mb-6"
          x-data="productReviewHeader(@js([
            'average_rating' => (float) ($reviewStats['average_rating'] ?? 0),
            'total_reviews' => (int) ($reviewStats['total_reviews'] ?? 0),
          ]), @js(__('products.verified_reviews', ['count' => ':count'])))"
        >
          <div class="flex gap-0.5 shrink-0">
            @for($i=0;$i<5;$i++)<svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
          </div>
          <span class="text-navy-800 font-bold text-sm shrink-0" x-text="average > 0 ? average : '—'">{{ ($reviewStats['average_rating'] ?? 0) > 0 ? $reviewStats['average_rating'] : '—' }}</span>
          <a href="#reviews" class="text-slate-500 text-xs sm:text-sm hover:text-navy-700 underline underline-offset-2 break-anywhere" x-text="label">{{ __('products.verified_reviews', ['count' => number_format($reviewStats['total_reviews'] ?? 0)]) }}</a>
          <span class="text-slate-300 hidden sm:inline">|</span>
          <span class="text-emerald-600 text-xs sm:text-sm font-semibold shrink-0">{{ __('products.in_stock') }}</span>
        </div>
        @if($price)
        <div class="flex flex-wrap items-center gap-3 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div class="min-w-0">
            <span class="font-display font-bold text-3xl sm:text-4xl text-navy-900">{{ $fmt($price) }}</span>
            @if($compareAt && (float)$compareAt > (float)$price)
            <span class="text-slate-400 line-through text-base sm:text-lg ml-2">{{ $fmt($compareAt) }}</span>
            @endif
          </div>
          @if($compareAt && (float)$compareAt > (float)$price)
          <div class="shrink-0">
            @php $saving = round((((float)$compareAt - (float)$price) / (float)$compareAt) * 100); @endphp
            <span class="bg-red-100 text-red-600 text-xs sm:text-sm font-bold px-3 py-1 rounded-full whitespace-nowrap">{{ __('home.save_percent', ['percent' => $saving]) }}</span>
          </div>
          @endif
        </div>
        @endif
        @if($plainDesc)
        <div class="cms-richtext text-slate-600 text-sm sm:text-base mb-6 break-anywhere">{!! \App\Support\CmsHtml::normalize($desc) !!}</div>
        @endif
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => __('products.select_option_short'),
          'addToCartText' => __('products.add_to_cart'),
          'orderNowText'  => __('products.order_now'),
        ])
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">{{ __('products.guarantee_title') }}</p>
            <p class="text-sage-600 text-xs">{{ __('products.guarantee_desc') }}</p>
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
      <h2 class="heading-section mb-4">{{ __('products.all_variants') }}</h2>
    </div>
    <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
          <tr><th class="px-4 py-3 font-medium">{{ __('products.table_option') }}</th><th class="px-4 py-3 font-medium">{{ __('products.table_sku') }}</th><th class="px-4 py-3 font-medium">{{ __('products.table_price') }}</th><th class="px-4 py-3 font-medium">{{ __('products.table_compare') }}</th><th class="px-4 py-3 font-medium">{{ __('products.table_available') }}</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($variants as $variant)
          <tr class="hover:bg-slate-50/80">
            <td class="px-4 py-3 font-medium text-navy-900">{{ $variant['title'] ?? '—' }}</td>
            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant['sku'] ?? '—' }}</td>
            <td class="px-4 py-3 font-semibold text-navy-800">@if(!empty($variant['price'])) {{ $fmt($variant['price']) }} @else — @endif</td>
            <td class="px-4 py-3 text-slate-400 line-through text-sm">@if(!empty($variant['compare_at_price'])) {{ $fmt($variant['compare_at_price']) }} @else — @endif</td>
            <td class="px-4 py-3">
              @if(($variant['inventory_quantity'] ?? 1) > 0)
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800">{{ __('products.in_stock') }}</span>
              @else
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700">{{ __('products.out_of_stock') }}</span>
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

@include('partials.reviews-lazy', ['handle' => $handle])

{{-- Phase 2 §6.4: Semantic FAQ HTML (server-rendered, progressive enhancement) --}}
@php $faqItems = $faqItems ?? collect(); @endphp
@if($faqItems->isNotEmpty())
<section class="faq-section section bg-slate-50 border-t border-slate-100" id="faq" aria-labelledby="faq-heading">
  <div class="container-site max-w-3xl mx-auto">
    <h2 id="faq-heading" class="font-display text-2xl md:text-3xl font-bold text-navy-900 mb-6 text-center">
      {{ __('product_landing.faq_eyebrow') }}
    </h2>
    <div class="space-y-3">
      @foreach($faqItems as $faq)
        <details class="group bg-white rounded-xl border border-slate-200 px-5 py-4 open:shadow-sm">
          <summary class="cursor-pointer list-none font-semibold text-navy-900 flex items-center justify-between gap-4">
            <span>{{ $faq->question }}</span>
            <span class="text-slate-400 group-open:rotate-180 transition-transform" aria-hidden="true">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </span>
          </summary>
          <div class="mt-3 text-slate-600 text-sm leading-relaxed cms-richtext prose prose-slate max-w-none">
            {!! \App\Support\CmsHtml::normalize($faq->answer) !!}
          </div>
        </details>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="py-8 bg-white border-t border-slate-100">
  <div class="container-site text-center">
    <a href="{{ route('products.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-navy-600 hover:text-navy-800 font-semibold transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      {{ __('products.back_to_products') }}
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
      <button type="button" @click="goToCheckout($event)" :class="loading ? 'bg-navy-700 opacity-70 cursor-wait' : 'bg-navy-700 hover:bg-navy-800'" :disabled="loading" class="flex-shrink-0 inline-flex items-center gap-1.5 text-white font-bold px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-colors text-xs sm:text-sm shadow-md">Order Now</button>
      <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-slate-100 hover:bg-navy-100 text-slate-600 hover:text-navy-700 flex items-center justify-center transition-colors" title="Back to top">
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
      </button>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}

@php
  $cmsPageBlocks = collect($pageBlocks ?? [])
      ->filter(fn ($b) => (bool) ($b->visible ?? true))
      ->sortBy(fn ($b) => (int) ($b->sort_order ?? 0))
      ->values();
@endphp
@foreach($cmsPageBlocks as $block)
  @includeIf('components.blocks.' . ($block->block_type ?? ''), [
      'title'   => $block->title,
      'content' => $block->content,
  ])
@endforeach

@include('components.related-content', [
  'title' => __('Related Resources'),
  'links' => $relatedLinks ?? [],
])

@push('scripts')
<script>
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.querySelector('section[aria-label="Product detail"]');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
</script>
@endpush

@endif

@if(!empty($productLangPrefix))
  @include('components.related-content', [
    'title' => __('Related Resources'),
    'links' => $relatedLinks ?? [],
  ])
@endif

@endsection
