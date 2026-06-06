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
      'image'           => $isDmedeSystem ? asset('images/daily-relief-system.png') : ($mainImg ?: asset('images/dainely-belt-product.png')),
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

@php
  $seoTitle = $title . ' — ' . config('app.name');
  $seoDesc = \Illuminate\Support\Str::limit($plainDesc, 160) ?: 'View product details.';
  
  if ($isDainelyBelt) {
    $seoTitle = 'Dainely Belt — Premium Lower Back Stabilization';
    $seoDesc = 'Premium everyday lower back stabilization designed for modern movement and long daily routines. Free shipping on orders over $75.';
  } elseif ($isBallMassager) {
    $seoTitle = 'Dainely™ Ball Massager — Targeted Pressure Point Relief';
    $seoDesc = 'Eliminate neck and shoulder pain in 10 minutes a day with the Dainely™ Ball Massager. Targeted pressure point therapy.';
  } elseif ($isNeckCloud) {
    $seoTitle = 'Neck Cloud™️ — Neck Support with Comfort & Relief';
    $seoDesc = 'Eliminate neck pain, tension headaches, and stiffness in just 10 minutes a day with the ergonomic Neck Cloud™️.';
  } elseif ($isBackPatches) {
    $seoTitle = 'Back Pain Relief Patches — Targeted Lumbar Comfort';
    $seoDesc = 'Soothe tight lower back muscles and relieve lumbar soreness with 8-hour active herbal warming patches.';
  } elseif ($isHeatedJacket) {
    $seoTitle = 'Dainely™ Unisex Heated Jacket — Smart Heat Therapy & Outdoor Comfort';
    $seoDesc = 'Stay warm in any weather with the Dainely™ Unisex Heated Jacket. Features smart carbon fiber heating elements and rechargeable battery warmth.';
  } elseif ($isFootMassager) {
    $seoTitle = 'Dainely™ Foot Massager — Intelligent EMS Acupressure Reflexology';
    $seoDesc = 'Alleviate foot neuropathy, swelling, and chronic aches in just 15 minutes a day with the Dainely™ Foot Massager.';
  } elseif ($isKneeBrace) {
    $seoTitle = 'Dainely™ Knee Brace — Premium Joint Compression & Patella Gel Support';
    $seoDesc = 'Stabilize knee joints, relieve meniscus and patella pressure, and walk without pain with the ergonomic Dainely™ Knee Brace.';
  } elseif ($isDainelyMassager) {
    $seoTitle = 'Dainely™ Percussion Massager — Professional Deep Tissue Recovery';
    $seoDesc = 'Relieve muscle soreness, dissolve tight knots, and speed up recovery in minutes with the professional-grade Dainely™ Deep Tissue Percussion Massager.';
  } elseif ($isShoulderBrace) {
    $seoTitle = 'Dainely™ Shoulder Brace — Premium Rotator Cuff & AC Joint Support';
    $seoDesc = 'Stabilize your shoulder, support rotator cuff and AC joint recovery, and relieve shoulder stiffness with the adjustable Dainely™ Shoulder Brace.';
  } elseif ($isNeckStretcher) {
    $seoTitle = 'Dainely™ Neck Stretcher — Premium Cervical Traction & Realignment';
    $seoDesc = 'Restore natural cervical posture, relieve neck pain and tension headaches, and decompress spinal discs in 10 minutes a day with the Dainely™ Neck Stretcher.';
  } elseif ($isBackStretcher) {
    $seoTitle = 'Dainely™ Orthopedic Back Stretcher — Multi-Level Lumbar Support';
    $seoDesc = 'Decompress your spine, restore the natural lumbar curve, and eliminate lower back stiffness and sciatica with the adjustable Dainely™ Back Stretcher.';
  } elseif ($isRelaxaLeg) {
    $seoTitle = 'Dainely™ RelaxaLeg™ System — Pneumatic Compression & Heat Therapy';
    $seoDesc = 'Boost leg circulation, ease heavy or achy leg soreness, and reduce swelling in just 10 minutes a day with the cordless Dainely™ RelaxaLeg™ System.';
  } elseif ($isTourmalineBelt) {
    $seoTitle = 'Dainely™ Tourmaline Belt — Self-Heating Magnetic Heat Therapy';
    $seoDesc = 'Alleviate lower back stiffness, support lumbar posture, and experience soothing deep-penetrating heat with the self-heating Dainely™ Tourmaline Belt.';
  } elseif ($isDmedeSystem) {
    $seoTitle = 'DMEDE™ Daily Support & Recovery System — Complete Lumbar Health';
    $seoDesc = 'Align your posture, stabilize your SI joint, and accelerate recovery with the DMEDE™ Daily Support System. Includes Dainely Belt, extender, and guided movement routines.';
  } elseif ($isErgoCushion) {
    $seoTitle = 'ErgoCushion® Seat Cushion — Premium Tailbone & Sciatica Relief';
    $seoDesc = 'Eliminate back pain, regain correct sitting posture, and relieve pressure on your tailbone with the orthopedic ErgoCushion® pressure relief seat cushion.';
  } elseif ($isMushroomCoffee) {
    $seoTitle = 'Functional Mushroom Coffee — Sustained Focus & Clear Mind';
    $seoDesc = 'Start your morning with DMEDE Functional Mushroom Coffee. 6 adaptogenic mushrooms combined with Arabica coffee for smooth energy without jitters or crashes.';
  }
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDesc)
@section('og_image', $isDmedeSystem ? asset('images/daily-relief-system.png') : ($mainImg ?? asset('images/dainely-belt-product.png')))

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
@elseif($isBallMassager)
@section('meta_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Dainely™ Ball Massager",
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/neck-stretcher-main.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/back-stretcher-main.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ asset('images/daily-relief-system.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
  "image": "{{ $mainImg ?? asset('images/dainely-belt-product.png') }}",
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
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
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
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])



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
@elseif($isBallMassager)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Ball Massager</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="shopifyGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Ball Massager" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Endorsed
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        @if(count($images) > 1)
        <div class="flex gap-2 overflow-x-auto pb-2 lg:grid lg:grid-cols-5">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'" class="rounded-xl overflow-hidden aspect-square w-14 h-14 flex-shrink-0 lg:w-auto lg:h-auto focus:outline-none transition-all">
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Pressure Point Trigger Therapy</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Eliminate neck pain & stiffness in 10 minutes.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.9' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 59.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 59.95) - ($price ?? 39.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 59.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Squeezing handles, rolling nodules, and poor cervical alignment can leave your neck and shoulders in constant discomfort.
          The Dainely™ Ball Massager features flexible targeted compression arms and soft-grip trigger point silicone spheres that act as a personal masseuse. Apply precise pressure exactly where you need it most.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Dual soft silicone spheres mimic a therapist\'s thumbs to dissolve knots', 'sage'],
            ['Flexible design lets you adjust width and squeeze tension for custom relief', 'sage'],
            ['Ideal for targeted cervical acupressure and suboccipital release', 'sage'],
            ['Instantly improves microcirculation to promote muscle oxygenation', 'sage'],
            ['Safe & 100% natural pain relief — use at home, office, or travel', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- size selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> false,
          'options'       => [],
          'optionType'    => 'static',
          'optionLabel'   => '',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Ball Massager',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Pain Relief Guarantee</p>
            <p class="text-sage-600 text-xs">Feel the difference in neck tension or get your money back. Risk-free purchase.</p>
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
        ['Targeted Suboccipital Release', 'Position balls right under the skull base to melt away posture-induced tension headaches.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Adjustable Compression Grip', 'Strong yet flexible handles let you squeeze and apply targeted pressure.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Silicone Trigger Nodules', 'Medical-grade, soft-touch silicone spheres replicate hand-roller kneading actions.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Compact & Travel Ready', 'Lightweight manual structure allows immediate tension relief anywhere.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Tension Relief on Demand</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve tension, whenever, wherever.</h2>
      <p class="text-body text-stone-600">
        In today\'s screen-filled world, the average neck is subjected to hours of forward head posture, causing heavy tension, soreness, and cervical pressure.
        The Dainely™ Ball Massager acts as an instant relief valve that fits directly into your daily routine.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['neck-pain-edu.png', 'At Your Desk', 'Relieve neck tension from screen fatigue with a 5-minute desktop stretch.'],
        ['posture-edu.png', 'After Commutes', 'Squeeze away shoulder stiffness built up during long, tense drives or travel.'],
        ['recovery-edu.png', 'Evening Recovery', 'Wind down before sleep by rolling suboccipital muscles to ease tension headaches.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Targeted Pressure Therapy</p>
      <h2 class="heading-section mb-4">How it dissolves tight muscle knots</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms combined to target trigger points and release tension.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Direct Acupressure', 'Dual silicone spheres align with trigger points in the neck and upper back, providing steady pressure directly to the origin of muscle pain.', 'navy'],
        ['02', 'Flexible Squeeze Control', 'By pulling the ergonomic handles closer together, you control the massage intensity, allowing gentle relief or deep tissue release.', 'gold'],
        ['03', 'Promote Fresh Circulation', 'Kneading tight muscle fibers breaks up lactic acid buildup and encourages freshly oxygenated blood flow, promoting speedy muscle recovery.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Cervical Trigger Points & Tension</p>
        <h2 class="heading-section text-white mb-6">Designed with Spine Specialists for Cervical Relaxation</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          The base of your skull contains suboccipital muscles. When tight, they constrict blood flow and irritate nerves, which is the primary cause of tension headaches and neck stiffness.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Ball Massager\'s dual-ball placement mimics a professional physical therapist\'s fingers, allowing you to perform suboccipital decompression at home without expensive therapy visits.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['10 min', 'Recommended daily duration for optimal neck relaxation'],
            ['100%', 'Control over pressure and trigger point location'],
            ['Dual Nodules', 'High-density silicone spheres for deep kneading'],
            ['BPA Free', 'Safe, medical-grade materials designed for skin contact'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/neck-pain-edu.png') }}" alt="Trigger point target region" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Trigger point therapy offers immediate microcirculation release."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['bm_faq1', 'How do I use the Dainely™ Ball Massager?', 'Position the silicone spheres on your neck or shoulders, then hold the handles and squeeze them gently to apply pressure. You can roll the spheres up and down, or hold them in a single spot to perform trigger point acupressure.'],
        ['bm_faq2', 'Will it hurt to apply deep pressure?', 'Since it is a manual massager, you have 100% control over the pressure. Squeeze the handles closer for a deeper massage, or release them for lighter relief. Start gently and increase tension as your muscles adapt.'],
        ['bm_faq3', 'Can I wash the massager?', 'Yes. The silicone spheres and flexible plastic handle can be easily wiped clean with soap and warm water.'],
        ['bm_faq4', 'How long should I use it daily?', 'We recommend using the massager for 5 to 10 minutes a day to break up muscle tension, relieve fatigue, and alleviate stiffness.'],
        ['bm_faq5', 'Does it help with tension headaches?', 'Yes! Placing the spheres at the base of your skull (suboccipital region) and applying steady pressure helps release the tight muscles that cause tension headaches.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Muscle Relief</p>
    <h2 class="heading-section mb-4">Roll away stiffness. Restore neck comfort.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for office screen workers, long travel, and physical recovery.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Pain Relief Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ High-Quality Silicone Nodes</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    // Check if shopifyGallery is already defined to prevent duplicates
    if (!Alpine.data('shopifyGallery')) {
      Alpine.data('shopifyGallery', () => ({
        active: 0,
        images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isNeckCloud)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Neck Cloud™️</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="shopifyGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Neck Cloud™️" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Endorsed
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        @if(count($images) > 1)
        <div class="flex gap-2 overflow-x-auto pb-2 lg:grid lg:grid-cols-5">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'" class="rounded-xl overflow-hidden aspect-square w-14 h-14 flex-shrink-0 lg:w-auto lg:h-auto focus:outline-none transition-all">
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Cervical Spine Decompression</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Eliminate neck pain & tension headaches in 10 minutes.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 59.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 59.95) - ($price ?? 39.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 59.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Years of forward head posture, long hours at a laptop, or poor sleep alignment can leave your neck and shoulders in constant pain.
          The Neck Cloud™️ features a dense, supportive foam curve that uses gravity to gently pull the skull base away from the shoulders. Relieve suboccipital tension and restore natural cervical curvature.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Cervical traction restores natural spine profile & alignment', 'sage'],
            ['Dual-sided stretching level: choose gentle concave or advanced convex support', 'sage'],
            ['High-density memory foam provides stable, comfortable cervical traction', 'sage'],
            ['Targeted acupressure nodes desensitize trigger points along the neck base', 'sage'],
            ['100% natural pain relief — ideal for desk fatigue & posture correction', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- size selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> false,
          'options'       => [],
          'optionType'    => 'static',
          'optionLabel'   => '',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Neck Cloud',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Pain Relief Guarantee</p>
            <p class="text-sage-600 text-xs">Feel the difference in neck tension or get your money back. Risk-free purchase.</p>
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
        ['Cervical Traction Support', 'Gently decompresses the spine to relieve nerve pressure and restore curvature.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Acupressure Nodes Grid', 'Strategically placed nodes target trigger points along the neck base.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Dual Stretch Modes', 'Switch orientation to choose between gentle concave or deep convex stretching.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Chiropractor Approved', 'Scientifically contoured to safely align and support cervical health.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Tension Relief on Demand</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve tension, whenever, wherever.</h2>
      <p class="text-body text-stone-600">
        Forward head posture forces your neck to bear up to 60 lbs of stress, resulting in chronic soreness, tension headaches, and poor posture.
        The Neck Cloud™️ acts as a daily release valve, restoring natural posture in just 10 minutes.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['neck-pain-edu.png', 'At Your Desk', 'Restore natural spine alignment after long hours facing a computer screen.'],
        ['posture-edu.png', 'After Long Commutes', 'Squeeze out stress and cervical compression built up during tense travel.'],
        ['recovery-edu.png', 'Evening Decompression', 'Wind down before bed by stretching suboccipital muscles for deeper sleep.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Cervical Curvature Recovery</p>
      <h2 class="heading-section mb-4">How it decompresses cervical tension</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms combined to restore the neck's natural C-curve alignment.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Cervical Traction Stretch', 'The contoured design matches the natural neck curvature, safely stretching the spine and opening compressed vertebrae spaces.', 'navy'],
        ['02', 'Acupressure Stimulation', 'Specially raised massage nodes align perfectly with tension trigger points on the neck, promoting muscle knot release.', 'gold'],
        ['03', 'Promote Fresh Circulation', 'Decompressing tight suboccipital muscles improves local microcirculation, bringing healing oxygenated blood to stiff areas.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Postural Stress & Cervical Health</p>
        <h2 class="heading-section text-white mb-6">Designed with Spine Specialists for Suboccipital Release</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          The suboccipital region houses muscles that control neck mobility and head tilt. Prolonged screen fatigue and bad posture lock these muscles, which constricts nerves and triggers severe tension headaches.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Neck Cloud™️ utilizes body weight and curvature geometry to stretch the neck, releasing pressure from discs and suboccipital muscle fibers. Enjoy professional-grade traction comfortably at home.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['10 min', 'Daily recommended duration for optimal curvature traction'],
            ['100% Natural', 'Utilizes your own body weight to gently decompress'],
            ['Dual Stretch Levels', 'Choose gentle concave or advanced convex support'],
            ['BPA Free', 'Premium high-density memory foam for safe skin contact'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/neck-pain-edu.png') }}" alt="Cervical spine alignment target region" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Cervical traction restores the natural spine curvature safely."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['nc_faq1', 'How do I use the Neck Cloud™️?', 'Lay the Neck Cloud on a flat surface, gently place your neck onto the contoured center, and relax your head back. Choose between the gentle traction side (concave curve facing neck) or advanced traction side (convex curve facing neck) for a deeper stretch.'],
        ['nc_faq2', 'Will it hurt to use cervical traction?', 'As your muscles and spine adapt to the correct curvature, you may feel mild discomfort. Start with 2 to 3 minutes per day and gradually increase to the recommended 10 minutes.'],
        ['nc_faq3', 'What is the Neck Cloud™️ made of?', 'The Neck Cloud is crafted from premium, high-density self-skinning memory foam. It is firm enough to provide traction, yet soft enough for comfortable skin contact.'],
        ['nc_faq4', 'Does it help with tension headaches?', 'Yes! Placing the spheres at the base of your skull (suboccipital region) and applying steady pressure helps release the tight muscles that cause tension headaches.'],
        ['nc_faq5', 'Can I wash the Neck Cloud™️?', 'Yes, you can wipe it down with a damp cloth and mild soap. Do not submerge it in water or wash it in a washing machine.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Posture Restoration</p>
    <h2 class="heading-section mb-4">Restore natural spine alignment. Relieve neck tension.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for office desk workers, phone posture alignment, and suboccipital release.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Pain Relief Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Ergonomic C-Curve Foam</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    // Check if shopifyGallery is already defined to prevent duplicates
    if (!Alpine.data('shopifyGallery')) {
      Alpine.data('shopifyGallery', () => ({
        active: 0,
        images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isBackPatches)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Back Pain Relief Patches</span>
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
          '{{ asset('images/back-pain-edu.png') }}',
          '{{ asset('images/lifestyle-desk-professional.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="Back Pain Relief Patches" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Tested
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Transdermal Lumbar Relief</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Soothing, fast-acting heat that lasts up to 8 hours.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.7' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 19.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 29.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 29.95) - ($price ?? 19.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 29.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Lumbar strains, muscle spasms, and cold drafts can trigger tight lower back pain that stops you in your tracks.
          Dainely™ Back Pain Relief Patches feature a self-heating mechanism loaded with natural herbal active extracts. They penetrate deep into stiff muscle groups for immediate relief.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Natural self-heating technology warms lumbar muscles to melt spasms', 'sage'],
            ['Up to 8 hours of continuous, targeted herbal active delivery', 'sage'],
            ['Flexible, conforming stretch fabric matches lower back movements', 'sage'],
            ['Transdermal absorption desensitizes pain receptors at the source', 'sage'],
            ['Safe, drug-free herbal formula — secure stick and easy peel-off', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- size selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> false,
          'options'       => [],
          'optionType'    => 'static',
          'optionLabel'   => '',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Relief Patches',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Pain Relief Guarantee</p>
            <p class="text-sage-600 text-xs">Feel the difference in muscle stiffness or get your money back. Risk-free purchase.</p>
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
        ['8h Continuous Relief', 'Active herbal extracts release slowly to soothe lumbar muscles for up to 8 hours.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Self-Heating Thermal', 'Warming active ingredients improve microcirculation and relax muscle stiffness.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['All-Natural Herbals', 'Formulated with safe Traditional Botanical extracts (Menthol, Capsaicin).', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Stay-Fit Elastic', 'Adheres strongly yet peels off cleanly, stretching with lower back muscles.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Lumbar Comfort on the Move</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve back stiffness, wherever you are.</h2>
      <p class="text-body text-stone-600">
        Whether you are working at an office desk, standing on your feet all day, or recovering from muscle fatigue, lower back aches can limit your mobility.
        Dainely™ Back Pain Relief Patches provide localized, long-lasting warmth to help you stay active and pain-free.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['back-pain-edu.png', 'Active Movement', 'Enjoy reliable pain desensitization during exercise, work, or daily routines.'],
        ['lifestyle-desk-professional.png', 'At Your Desk', 'Ease dull sitting aches and alleviate spinal tension at your office chair.'],
        ['recovery-edu.png', 'Overnight Healing', 'Apply before bed to relax tense lower back muscles and wake up refreshed.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Targeted Thermal Absorption</p>
      <h2 class="heading-section mb-4">How it desensitizes lower back stiffness</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms combined to target deep muscle tension and promote lumbar recovery.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Direct Warming Action', 'The self-heating herbal patch activates on skin contact, delivering soothing warmth directly to the painful lumbar region.', 'navy'],
        ['02', 'Transdermal Absorption', 'Natural botanical ingredients are absorbed deeply to block pain receptors at the source, reducing stiffness and spasms.', 'gold'],
        ['03', 'Promote Fresh Circulation', 'Localized heat dilates local blood vessels, washing away lactic acid buildup and supplying fresh oxygen to speed up tissue healing.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Transdermal Heat Science</p>
        <h2 class="heading-section text-white mb-6">Designed with Spine Specialists for Deep Muscle Release</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Strained lumbar muscles contract to protect the spine, resulting in painful spasms and restricted mobility. Oral pain relievers can take hours to circulate and may cause stomach irritation.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          Dainely™ Back Pain Relief Patches deliver anti-inflammatory botanicals and localized heat therapy directly through the skin. This immediate, targeted action eases spinal muscle tension without systemic side effects.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['8 Hours', 'Continuous active relief per patch'],
            ['100% Natural', 'Infused with premium botanical extracts'],
            ['Self-Heating', 'Natural warming effect starts within minutes'],
            ['Skin-Friendly', 'Hypoallergenic adhesive that leaves no sticky residue'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/back-pain-edu.png') }}" alt="Lumbar muscle target region" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Localized warming patches provide safe, targeted relief for lumbar fatigue."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['bp_faq1', 'How do I apply the Back Pain Relief Patch?', 'Clean and dry the painful area of your lower back. Peel off the backing film and apply the adhesive side directly over your pain point. Press down firmly to secure.'],
        ['bp_faq2', 'How long does a single patch last?', 'Each patch delivers continuous herbal active warmth for 6 to 8 hours. We recommend replacing the patch after 8 hours of wear.'],
        ['bp_faq3', 'Are there any skin irritations with the adhesive?', 'Our patches use a medical-grade, hypoallergenic adhesive designed to hold securely while peeling off cleanly without skin irritation. If you have extremely sensitive skin, test on a small area first.'],
        ['bp_faq4', 'What are the active ingredients?', 'The patches contain a proprietary warming formulation of Capsaicin (natural chili pepper extract), Camphor, and Menthol, traditionally used to alleviate joint and muscle stiffness.'],
        ['bp_faq5', 'Can I wear the patch while sleeping?', 'Yes. The self-heating action is safe for overnight wear and will help you sleep comfortably without waking up from lower back aches.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Lumbar Relief</p>
    <h2 class="heading-section mb-4">Soothe spasms. Keep moving without back soreness.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for daily movement, physical recovery, and desk strain relief.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 19.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Pain Relief Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ All-Natural Warming Herbals</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    // Check if shopifyGallery is already defined to prevent duplicates
    if (!Alpine.data('shopifyGallery')) {
      Alpine.data('shopifyGallery', () => ({
        active: 0,
        images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isHeatedJacket)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Unisex Heated Jacket</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="shopifyGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Unisex Heated Jacket" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Approved
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        @if(count($images) > 1)
        <div class="flex gap-2 overflow-x-auto pb-2 lg:grid lg:grid-cols-5">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'" class="rounded-xl overflow-hidden aspect-square w-14 h-14 flex-shrink-0 lg:w-auto lg:h-auto focus:outline-none transition-all">
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Therapeutic Active Warmth</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Stay warm in any cold weather, instantly.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 99.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 159.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 159.95) - ($price ?? 99.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 159.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Cold temperatures constrict blood vessels and tighten major back and chest muscles, leading to stiffness and joint fatigue.
          Dainely™ Unisex Heated Jacket integrates smart carbon fiber heating pads with rechargeable power bank support, targeting the core muscle groups to dilate vessels and keep you relaxed and mobile.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Smart carbon fiber heating zones target back, chest, and collar', 'sage'],
            ['One-touch control chest button cycles through 3 heat modes (35°C to 55°C)', 'sage'],
            ['Breathable, windproof, and water-resistant premium outer fabric layer', 'sage'],
            ['Therapeutic infrared heat desensitizes muscular fatigue & tightness', 'sage'],
            ['Washable & durable design — gentle machine wash safe (remove battery)', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Size',
          'showSizeGuide' => true,
          'sizeGuideHref' => '#size-chart',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Heated Jacket',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Satisfaction Guarantee</p>
            <p class="text-sage-600 text-xs">Stay completely cozy and warm in winter or get your money back. Risk-free purchase.</p>
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
        ['Multi-Zone Heat', 'Targeted carbon fiber heating elements warm your collar, back, and chest.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['One-Click Controls', 'Intelligent chest button cycles low, medium, and high heat settings instantly.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Wind & Water Shield', 'Specially treated outer poly-shell repels wind, rain, and cold drafts.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['8 Hours Heating', 'Insulated low-voltage circuitry provides steady, safe battery warmth.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Tension Relief on Demand</p>
      <h2 class="heading-section text-stone-900 mb-4">Conquer winter drafts, outdoors or commuting.</h2>
      <p class="text-body text-stone-600">
        Cold weather forces muscles to contract, causing back soreness and joint tightness.
        The Dainely™ Heated Jacket active thermal relief fits perfectly into your cold-season routines, keeping your joints loose and cozy.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['recovery-edu.png', 'Outdoor Work', 'Active warmth keeps muscles relaxed during cold outdoor tasks or hobbies.'],
        ['lifestyle-travel-commute.png', 'Daily Commutes', 'Click the button for immediate comfort during freezing mornings.'],
        ['lifestyle-everyday-movement.png', 'Active Recreation', 'Perfect insulation for winter walks, hikes, skiing, and traveling.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works" id="size-chart">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Infrared Carbon Heating</p>
      <h2 class="heading-section mb-4">How smart heating provides comfort</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms combined to target stiff core muscles and lock out the cold.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Multi-Zone Heating Pads', 'Insulated carbon fiber elements generate safe infrared heat targeting key zones: back, chest, and neck collar.', 'navy'],
        ['02', 'Microchip Chest Control', 'Built-in controller lets you cycle low (Green: 35°C), medium (Blue: 45°C), and high (Red: 55°C) settings instantly.', 'gold'],
        ['03', 'Promote Fresh Circulation', 'Active warming dilates blood vessels around your core, keeping lumbar muscles relaxed and preventing cold-induced tension.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Size Chart Image Section --}}
@if($images && isset($images[5]['src']))
<section class="py-12 bg-stone-50 border-y border-stone-100">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-3">Find Your Perfect Fit</p>
    <h2 class="heading-section mb-6">Heated Jacket Size Guide</h2>
    <div class="max-w-xl mx-auto overflow-hidden rounded-2xl shadow-lg bg-white border border-stone-200 p-4">
      <img src="{{ $images[5]['src'] }}" alt="Size Chart" class="w-full h-auto object-contain mx-auto" loading="lazy">
    </div>
  </div>
</section>
@endif

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Core Temperature & Muscle Tension</p>
        <h2 class="heading-section text-white mb-6">Designed with Spine Specialists for Muscular Relaxation</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Exposure to cold forces your spine and back muscles to contract continuously to maintain body warmth. This involuntary contraction triggers severe posture stiffness, backaches, and muscle knots.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Heated Jacket provides active infrared heat to your core and spine. By dilating blood vessels and boosting oxygen supply, it stops cold-induced spasms and keeps your joints relaxed and mobile.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['3 Heat Zones', 'Carbon fiber heating zones for back, chest, and collar'],
            ['Low 5V Input', 'USB powered, safe, and shock-free low-voltage design'],
            ['Up to 8h', 'Continuous warming output per power bank charge'],
            ['Washable', 'Fully machine washable (disconnect battery first)'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/sciatica-edu.png') }}" alt="Lumbar core warm zone target" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Warming the lumbar spine prevents cold-induced muscle spasms."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['hj_faq1', 'How do I turn on the heating elements?', 'Connect any standard 5V/2A power bank to the USB cord inside the inner pocket. Place the power bank inside the pocket, zip it shut, then press and hold the chest power button for 3 seconds. The button will flash red, showing the jacket is warming up.'],
        ['hj_faq2', 'How long does the battery heat last?', 'RELIEF duration depends on battery capacity. A standard 10,000mAh power bank yields up to 8 hours of heat on Low (Green/35°C), 5 hours on Medium (Blue/45°C), and 3 hours on High (Red/55°C).'],
        ['hj_faq3', 'Is the jacket machine washable?', 'Yes! Remove the battery pack first. Tuck the USB cable back into the inner pocket and zip it shut. Place the jacket in a laundry bag and wash on a gentle cycle. Hang dry; do not wring or tumble dry.'],
        ['hj_faq4', 'Is it safe to wear in rain or snow?', 'Yes. The insulated carbon fiber wiring is 100% waterproof and operates on a harmless, low-voltage 5V USB output. You can wear it safely in rain or snow.'],
        ['hj_faq5', 'Does it come with a power bank?', 'The package includes the Unisex Heated Jacket. You can connect it to any standard USB power bank that you already own, or purchase one from our store.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Instant Heated Comfort</p>
    <h2 class="heading-section mb-4">Stay loose. Conquer winter freezing weather.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for winter walks, commuting, outdoor hobbies, and muscle relaxation.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 99.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Satisfaction Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Windproof & Waterproof Shield</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    // Check if shopifyGallery is already defined to prevent duplicates
    if (!Alpine.data('shopifyGallery')) {
      Alpine.data('shopifyGallery', () => ({
        active: 0,
        images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isFootMassager)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Foot Massager</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="footGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Foot Massager" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Endorsed
            </span>
          </div>
        </div>
        {{-- Thumbnails (4 Columns layout matching Dainely Belt) --}}
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">EMS Acupressure Reflexology</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Alleviate foot neuropathy & swelling in 15 minutes.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 49.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 79.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 79.95) - ($price ?? 49.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 79.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Poor blood circulation, prolonged standing, and nerve pressure can leave your feet throbbing with pain, numbness, and severe swelling.
          The Dainely™ Foot Massager utilizes advanced low-frequency electrical muscle stimulation (EMS) to activate foot reflexology points. This boosts local blood flow, contracts the calves, and instantly desensitizes hyperactive nerves to restore comfort.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Intelligent EMS micro-currents stimulate muscle pump circulation', 'sage'],
            ['8 diverse massage modes and 19 intensity levels for customized relief', 'sage'],
            ['Designed with targeted acupressure nodes to map foot reflex zones', 'sage'],
            ['Reduces swelling, fluid retention, and chronic neuropathic aches', 'sage'],
            ['Foldable, lightweight, and rechargeable — perfect for home or travel', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Foot Massager',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Pain Relief Guarantee</p>
            <p class="text-sage-600 text-xs">Feel complete relief from neuropathy aches and swelling, or get a full refund. Safe and secure purchase.</p>
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
        ['Intelligent EMS Micro-Currents', 'Active pulses trigger deep calf muscle pumps to boost circulation.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Acupressure Reflexology Mapping', 'Targets vital foot meridian trigger points to desensitize nerve tension.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['8 Modes & 19 Intensities', 'Highly adjustable low-frequency massage levels to suit any preference.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Foldable & Portable Mat', 'Premium lightweight, flexible design folds neatly for travel convenience.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Foot Circulation & Neuropathy Relief</p>
      <h2 class="heading-section text-stone-900 mb-4">Soothe throbbing sole pain, anywhere.</h2>
      <p class="text-body text-stone-600">
        Standing on your feet for long hours, sitting all day at an office desk, or suffering from diabetic neuropathy can cause heavy swelling and painful aches.
        The Dainely™ Foot Massager fits effortlessly into your daily routine, keeping your leg and foot circulation flowing.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['foot-massager-lifestyle.png', 'Active Muscle Pump', 'Soothes throbbing sole pain and heavy leg swelling in just 15 minutes.'],
        ['lifestyle-desk-professional.png', 'Under-Desk Workspace Support', 'Promotes constant venous circulation while seated for long working hours.'],
        ['recovery-edu.png', 'Bedtime Tension Release', 'Relaxes neuropathic nerve endings before bed to sleep comfortably.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Low-Frequency EMS Action</p>
      <h2 class="heading-section mb-4">How it resolves swelling and neuropathy</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms combined to target deep muscle fatigue and stimulate nerve pathways.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Acupressure Alignment', 'Place bare feet on the comfortable mat. The micro-current contact pads align exactly with traditional reflexology sole points.', 'navy'],
        ['02', 'Targeted Muscle Pump', 'Low-frequency electrical stimulation causes calf muscles to contract and relax. This active squeeze action pumps pooling blood back upward.', 'gold'],
        ['03', 'Promote Fresh Oxygenation', 'Improved blood flow reduces heavy swelling and washes away fluid retention, while gentle micro-currents calm throbbing neuropathic tingling.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">The Science of Foot Circulation</p>
        <h2 class="heading-section text-white mb-6">Designed with Spine & Nerve Specialists for Deep Reflexology</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          When blood pools in the feet due to gravity or nerve damage, local tissues expand, leading to heavy swelling, numbness, and radiating shooting pain. Traditional vibrating foot rollers only massage the skin surface, failing to address deep circulation issues.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Foot Massager triggers deep calf contractions using safe low-frequency EMS bio-currents. This acts as an auxiliary heart, pushing blood back up through the veins to eliminate swelling, alleviate neuropathy, and speed up leg recovery.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['15 Minutes', 'Recommended daily session for optimal neuropathic relief'],
            ['96%', 'Of patients reported reduced swelling and throbbing legs'],
            ['8 Programs', 'Varying wave frequencies for acupressure, kneading, and tapping'],
            ['100% Drug-Free', 'Completely natural, non-invasive bio-electric relief'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/foot-reflexology-chart.png') }}" alt="EMS reflexology nerve pathway illustration" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Low-frequency electrical stimulation triggers natural muscle pumps, directly reversing lower leg swelling."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['fm_faq1', 'How does the EMS technology feel on my feet?', 'EMS uses gentle low-frequency electrical impulses. At first, you will feel a light tingling sensation in your soles, which will progress to comfortable muscle contractions in your calves as you adjust the intensity. It is completely safe and non-painful.'],
        ['fm_faq2', 'Who should NOT use the Dainely™ Foot Massager?', 'Do not use the foot massager if you have an active pacemaker or implanted electronic medical device, if you are pregnant, or if you have deep vein thrombosis (DVT). If you have other medical concerns, consult your physician before use.'],
        ['fm_faq3', 'How often should I use it?', 'We recommend starting with one 15-minute session daily. As your muscles and circulation adapt, you can increase to 2 sessions per day, especially after long standing work or before sleeping.'],
        ['fm_faq4', 'How do I clean and maintain the mat?', 'The flexible mat is crafted from premium, skin-friendly PU leather. Simply wipe it clean with a damp cloth after use and let it dry. Do not submerge the main electronic controller in water.'],
        ['fm_faq5', 'Do I need to wear socks during use?', 'For the EMS pulses to transmit properly, you must place your bare feet directly on the black contact areas of the mat. Wearing socks or shoes will block the bio-electric currents.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Foot Relief</p>
    <h2 class="heading-section mb-4">Calm tingling nerves. Walk without throbbing foot pain.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for office sitting fatigue, standing work recovery, and chronic neuropathy relief.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 49.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Pain Relief Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Safe Low-Frequency EMS Mat</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('footGallery')) {
      Alpine.data('footGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/foot-massager-main.png') }}',
          '{{ asset('images/foot-massager-lifestyle.png') }}',
          '{{ asset('images/foot-reflexology-chart.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isKneeBrace)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Knee Brace</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="kneeGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Knee Brace" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Approved
            </span>
          </div>
        </div>
        {{-- Thumbnails (4 Columns layout matching Dainely Belt) --}}
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Ergonomic Patella & Joint Support</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Decompress knee joint pressure. Walk & run pain-free.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.9' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 69.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 69.95) - ($price ?? 39.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 69.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Grinding knee joints, meniscus strain, and patella instability can make daily walks and simple tasks agonizing.
          The Dainely™ Knee Brace features an anatomically shaped silicone gel patella shield surrounded by dual flexible side springs. This absorbs joint loading shocks and prevents painful knee misalignment during activity.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Anatomical gel pad cushions and locks patella tracking in place', 'sage'],
            ['Flexible lateral springs absorb impact forces when bending or loading', 'sage'],
            ['Medical-grade 3D elastic knit applies targeted, breathable compression', 'sage'],
            ['Double silicone wavy strips prevent sliding and rolling during movement', 'sage'],
            ['Helps recover from meniscus tears, arthritis swelling, and patellar strains', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Size',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Knee Brace',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Satisfaction Guarantee</p>
            <p class="text-sage-600 text-xs">Walk, jog, and climb stairs with total knee support, or get your money back. Risk-free purchase.</p>
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
        ['Contoured Patella Gel Pad', 'Surrounds the kneecap to lock joint tracking and prevent dislocation.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Lateral Spring Stabilizers', 'Side stabilizers absorb kinetic joint load during squats and bends.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Medical-Grade 3D Elastic Knit', 'Applies graduated pressure to decrease inflammation and joint swelling.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Wavy Non-Slip Silicone Strips', 'Double-layered grip ensures the brace stays in place during active sports.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Ergonomic Joint Protection</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve joint friction, wherever you move.</h2>
      <p class="text-body text-stone-600">
        Athletic activities, climbing stairs, or standing for long intervals can put immense loading stress on your knees.
        The Dainely™ Knee Brace provides steady structural support to keep you moving smoothly and pain-free.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['knee-brace-main.png', 'Workouts & Athletics', 'Safeguards your meniscus and patellar ligaments during sports, runs, and gym exercises.'],
        ['knee-brace-lifestyle.png', 'Stairs & Daily Walking', 'Absorbs kinetic impact forces to reduce grinding cartilage pain during daily routines.'],
        ['recovery-edu.png', 'Seated Joint Recovery', 'Keeps blood flow active around the joint caps to prevent stiffness when rising from chairs.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Tri-Shield Stabilization</p>
      <h2 class="heading-section mb-4">How it protects your knee from loading shock</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanical support systems working together to stabilize and decompress your knee joint.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Patellar Alignment Ring', 'The thick silicone gel pad cradles the kneecap. This holds the patella securely in its natural track, avoiding grinding friction.', 'navy'],
        ['02', 'Flexible Side Springs', 'Metal spring stabilizers located on both sides absorb joint compression forces when bending, reducing muscle strain.', 'gold'],
        ['03', 'Graduated Knit Compression', 'Graduated medical compression stimulates local tissue blood flow. This helps drain accumulated fluid and relieves inflammation.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Knee Decompression Mechanics</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic Specialists for Meniscus and Patellar Health</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Every step you take forces up to 4 times your body weight onto your knee joints. For individuals with weakened cartilage, patellar instability, or meniscus wear, this pressure leads to severe knee swelling, grinding pain, and bone-on-bone friction.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Knee Brace provides active mechanical decompression. By transferring the body load from the joint cartilage to the lateral steel spring stabilizers, it reduces meniscus pressure and stops patella wobbling, keeping the joint relaxed and mobile.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['3D Weave', 'High-density knit contouring perfectly to your leg shape'],
            ['94% Success', 'Of users reported reduced joint swelling and grinding'],
            ['2 Side Springs', 'Flexible stabilizers absorb load forces during bending'],
            ['Silicon Ring', 'Cradles the patella to lock joint tracking'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/knee-anatomy-diagram.png') }}" alt="Meniscus decompression zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Side stabilizers reduce the compression weight on the patella and meniscus regions, preventing bone-on-bone pain."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['kb_faq1', 'How do I choose the correct size for my knee brace?', 'Measure the circumference of your thigh 5 inches (13cm) above the center of your kneecap. Compare your measurement with our size chart options during selection. If you are between sizes, we recommend sizing up for comfortable daily wear.'],
        ['kb_faq2', 'Can I wear the Knee Brace under regular trousers?', 'Yes! The premium 3D knit fabric is lightweight and low-profile. It fits comfortably under loose trousers, sweatpants, or jeans without limiting your movement.'],
        ['kb_faq3', 'Will the brace slip down during running or workouts?', 'No. The top cuff of the brace features double-layered wavy silicone strips that hold firmly onto your skin, keeping it securely in place even during intense physical exercise.'],
        ['kb_faq4', 'How do I wash and maintain the brace?', 'We recommend hand washing with cold water and mild soap. Let the brace air dry in a flat position. Do not machine wash or tumble dry to preserve the lateral springs and patellar gel ring.'],
        ['kb_faq5', 'How long should I wear it daily?', 'You can wear it throughout workouts, daily walks, or long standing shifts. Start by wearing it for 2-3 hours and gradually increase as your knee joints adapt.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Joint Relief</p>
    <h2 class="heading-section mb-4">Protect your knees. Enjoy painless, active mobility.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for daily walking, sports workouts, arthritis comfort, and cartilage protection.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 39.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Joint Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Patella Gel Protection Ring</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('kneeGallery')) {
      Alpine.data('kneeGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/knee-brace-main.png') }}',
          '{{ asset('images/knee-brace-lifestyle.png') }}',
          '{{ asset('images/knee-anatomy-diagram.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isDainelyMassager)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Massager</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="massagerGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Massager" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Approved
            </span>
          </div>
        </div>
        {{-- Thumbnails (4 Columns layout matching Dainely Belt) --}}
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Professional Deep Tissue Percussion</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Dissolve deep muscle knots. Speed up recovery in minutes.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 59.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 99.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 99.95) - ($price ?? 59.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 99.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Desk sitting posture tension, heavy athletic workouts, and daily stress build up tight trigger point knots in your muscles, restricting your natural mobility.
          The Dainely™ Percussion Massager fires rapid high-frequency pulses that penetrate 12mm deep into target tissue, melting away muscle stiffness and flushing out lactic acid.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['12mm amplitude deep tissue penetration for serious knot release', 'sage'],
            ['6 interchangeable massage heads target specific muscle regions', 'sage'],
            ['Brushless Quiet-Glide motor delivers powerful percussion silently', 'sage'],
            ['Rechargeable lithium battery provides up to 6 hours of use', 'sage'],
            ['30 adjustable speed levels up to 3200 RPM for custom recovery', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Dainely Massager',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Melt away muscle tension and pain in the comfort of your home, or get a full refund. Safe and secure purchase.</p>
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
        ['12mm Percussion Depth', 'High amplitude pulses penetrate deep into fascia to dissolve stubborn knots.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['6 Interchangeable Heads', 'Customizable attachments mapping to all body regions (back, legs, spine).', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['30 Smart Speeds', 'Varying wave frequencies up to 3200 RPM for progressive recovery levels.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['High-Capacity Battery', 'Rechargeable lithium system yields up to 6 hours of continuous use.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Targeted Fascia Recovery</p>
      <h2 class="heading-section text-stone-900 mb-4">Dissolve deep muscle soreness, on demand.</h2>
      <p class="text-body text-stone-600">
        Whether you are recovering from heavy athletic training, dealing with desk posture strain, or feeling general muscle fatigue, percussive therapy provides targeted muscle release to keep you active.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['massager-main.png', 'Post-Workout Relief', 'Flush out lactic acid buildup and speed up muscle tissue repair after heavy workouts.'],
        ['massager-lifestyle.png', 'Workspace Desk Tension', 'Instantly relieve shoulder knots and neck stiffness caused by long computer hours.'],
        ['recovery-edu.png', 'Deep Leg Relaxation', 'Release calf tightness and hamstring soreness to wind down comfortably in the evening.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Percussive Micro-Vibration</p>
      <h2 class="heading-section mb-4">How it desensitizes tight muscle fibers</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanical recovery phases to release muscle adhesions and promote healing.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Specialized Muscle Target', 'Choose the correct attachment: Round head for large muscle groups, bullet head for deep trigger points, fork head for spine regions.', 'navy'],
        ['02', 'High-Amplitude Percussion', 'The Quiet-Glide brushless motor fires rapid pulses up to 3200 RPM, sending percussive waves 12mm deep to break up stiff fibers.', 'gold'],
        ['03', 'Promote Circulation & Drainage', 'Vigorous micro-vibration dilates local blood vessels, flushing away lactic acid, reducing stiffness, and restoring full mobility.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Percussive Muscle Science</p>
        <h2 class="heading-section text-white mb-6">Designed with Physical Recovery Specialists for Deep Fascial Decompression</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Following strenuous physical activity or repetitive posture strain, muscle fibers suffer microscopic micro-tears, and waste products like lactic acid accumulate. If left untreated, these areas bind together into painful adhesions or "trigger points."
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Percussion Massager delivers targeted deep-tissue micro-waves. By combining rapid mechanical vibration with 12mm amplitude depth, it releases tight myofascial junctions, boosts local blood flow, and resets muscle tone without painful manual therapy.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['12mm Depth', 'Percussive wave muscle penetration amplitude'],
            ['3200 RPM', 'High-frequency brushless motor speed setting'],
            ['6 Attachments', 'Specialized therapy nodes for customized recovery'],
            ['2400mAh', 'High-capacity lithium battery for long sessions'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/massager-anatomy-diagram.png') }}" alt="Percussive wave penetration zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Percussive massage speeds up myofascial release and tissue oxygenation, bypassing typical post-exercise stiffness."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['m_faq1', 'How does percussive therapy work?', 'Percussive therapy delivers rapid, concentrated pulses of pressure deep into your muscle tissues. This action desensitizes the surrounding area, releases muscle tension, and promotes increased blood flow, which accelerates overall recovery.'],
        ['m_faq2', 'Which massage head attachment should I use?', 'Use the Round head for large muscle groups (quads, glutes); the Bullet head for pinpoint trigger points and deep joints; the Fork head for spine, neck, and Achilles; the Flat head for general full-body muscle relaxation.'],
        ['m_faq3', 'How many speed levels are available?', 'The Dainely™ Percussion Massager features 30 smart speed levels ranging from a light, soothing massage (1800 RPM) up to deep percussive physical recovery settings (3200 RPM). You can adjust speed levels via the back LCD touchscreen.'],
        ['m_faq4', 'How long does the rechargeable battery last?', 'Equipped with a high-capacity 2400mAh rechargeable lithium battery, the massage gun operates for up to 6 hours on low speed or 3-4 hours on high percussive settings.'],
        ['m_faq5', 'Is it quiet enough to use at the gym or office?', 'Yes! Our Quiet-Glide brushless motor keeps noise levels under 45 decibels, which is quieter than a normal office conversation. You can use it anywhere without causing disruption.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Muscle Relief</p>
    <h2 class="heading-section mb-4">Dissolve tension knots. Move with full structural mobility.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for physical workouts, office posture relief, and general body recovery.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 59.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Muscle Relief Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ 6 Specialized Attachment Heads</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('massagerGallery')) {
      Alpine.data('massagerGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/massager-main.png') }}',
          '{{ asset('images/massager-lifestyle.png') }}',
          '{{ asset('images/massager-anatomy-diagram.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isShoulderBrace)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Shoulder Brace</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="shoulderGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Shoulder Brace" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Approved
            </span>
          </div>
        </div>
        {{-- Thumbnails (4 Columns layout matching Dainely Belt) --}}
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Rotator Cuff & AC Joint Stability</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Stabilize your shoulder joint. Restore pain-free movement.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 34.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 59.95, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 59.95) - ($price ?? 34.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 59.95)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Rotator cuff strains, AC joint separations, labrum tears, and chronic frozen shoulder can turn daily reaches and workouts into a source of sharp pain.
          The Dainely™ Shoulder Brace provides professional-grade joint compression. Featuring an adjustable top pressure pad (perfect for ice/hot packs) and dual strap alignment controls, it stabilizes the humeral head to prevent subluxation and accelerate tendon recovery.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Rotator cuff sleeve limits harmful joint movement to encourage healing', 'sage'],
            ['Adjustable pressure pad holds hot/cold packs for targeted therapy', 'sage'],
            ['Premium, breathable neoprene blend keeps joints warm and flexible', 'sage'],
            ['Dual adjustment hook & loop straps customize size and arm compression', 'sage'],
            ['Unisex design fits comfortably on either the left or right shoulder', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Shoulder Brace',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Satisfaction Guarantee</p>
            <p class="text-sage-600 text-xs">Enjoy active movement and pain-free shoulder stability, or get your money back. Risk-free purchase.</p>
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
        ['Rotator Cuff Shield', 'Stabilizes the shoulder socket, limiting harmful over-extension movements.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Hot/Cold Pressure Pad', 'Built-in pocket holds ice or heating packs to speed up inflammation relief.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Breathable Thermal Neoprene', 'Flexible knit fabric traps core body heat to stimulate joint recovery.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Dual Secure Velcro Straps', 'Easily adjusts to customize the arm compression and chest width.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Ergonomic Joint Stabilization</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve joint friction, wherever you move.</h2>
      <p class="text-body text-stone-600">
        Athletic exercises, heavy lifting, or repetitive household tasks can overstretch your shoulder tendons.
        The Dainely™ Shoulder Brace provides adjustable compression and alignment to keep your joint moving naturally and safely.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['shoulder-brace-main.png', 'Workouts & Lifting', 'Prevents joint instability, subluxations, and strains during athletic movements or gym exercises.'],
        ['shoulder-brace-lifestyle.png', 'Active Daily Living', 'Reduces pressure on AC joints during driving, gardening, and daily chores.'],
        ['recovery-edu.png', 'Overnight Healing & Sleep', 'Soothes throbbing joint aches before bed to sleep comfortably without morning stiffness.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Structural Joint Support</p>
      <h2 class="heading-section mb-4">How it protects your shoulder from loading strain</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanical support systems working together to stabilize and compress your shoulder joint.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Rotator Cuff Compression', 'The high-elastic neoprene sleeve wraps around the upper arm, supporting subluxations and relieving tendon pressure.', 'navy'],
        ['02', 'Cushioned Pressure Pad', 'The top pressure pocket applies direct compression onto the AC joint, keeping it aligned while letting you insert ice or hot packs.', 'gold'],
        ['03', 'graduated Chest Fastening', 'Dual hook & loop chest straps anchor the brace firmly to the body, distributing movement loading and preventing slipping.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Joint Decompression Mechanics</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic Specialists for Rotator Cuff & AC Joint Health</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          The shoulder is the body\'s most mobile joint, making it highly vulnerable to dislocations, labral tears, and rotator cuff strains. Repetitive motions and heavy lifting overstretch surrounding ligaments, triggering painful inflammation and joint stiffness.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Shoulder Brace provides active mechanical compression. By holding the humeral head securely within the shoulder socket and stabilizing the AC joint, it minimizes joint loading strain and desensitizes hyperactive nerve endings to accelerate tissue recovery.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['Dual Straps', 'Chest and arm straps customize compression easily'],
            ['93% Relief', 'Of users reported reduced joint swelling and aching'],
            ['Pressure Pad', 'Top pouch holds ice or heat packs for targeted relief'],
            ['Unisex design', 'Fits left or right shoulder comfortably'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/shoulder-anatomy-diagram.png') }}" alt="Shoulder joint stability zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Targeted joint compression and AC joint alignment speed up rotator cuff recovery and reduce throbbing inflammation."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['sb_faq1', 'Does the Shoulder Brace fit both left and right shoulders?', 'Yes! The unisex design is fully symmetric, allowing you to wear it comfortably on either your left or right shoulder. Simply adjust the sleeve and chest strap direction.'],
        ['sb_faq2', 'How do I choose the correct size?', 'Measure your chest circumference around the chest under the armpits. Our highly adjustable dual strap design accommodates chest circumferences from 28 to 48 inches, making it a flexible one-size-fits-most support.'],
        ['sb_faq3', 'How does the top pressure pad work?', 'The top features a dedicated hook & loop flap pocket. You can insert an ice pack for cold compression therapy (reduces acute pain and swelling), or a warm heat pack (relaxes stiff tendons), and tighten the strap over it.'],
        ['sb_faq4', 'Can I wear the brace under my clothing?', 'Yes. The lightweight, breathable neoprene fabric is flat and low-profile. It fits comfortably under loose shirts, sweatshirts, or athletic gear.'],
        ['sb_faq5', 'How do I wash and clean the brace?', 'Hand wash with mild soap in cold water. Let it air dry completely. Do not machine wash or dry clean to maintain neoprene flexibility and strap durability.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Shoulder Relief</p>
    <h2 class="heading-section mb-4">Support your rotator cuff. Walk & exercise pain-free.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed for athletic protection, rotator cuff recovery, AC joint stability, and daily wear comfort.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 34.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Joint Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Hot/Cold Therapy Pressure Pad</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('shoulderGallery')) {
      Alpine.data('shoulderGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/shoulder-brace-main.png') }}',
          '{{ asset('images/shoulder-brace-lifestyle.png') }}',
          '{{ asset('images/shoulder-anatomy-diagram.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isNeckStretcher)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Neck Stretcher</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="neckStretcherGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Neck Stretcher" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Approved
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Cervical Traction & Realignment</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Restore your neck's natural curve. Relieve chronic tension in minutes.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 39.90, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 80.00, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 80.00) - ($price ?? 39.90);
              $savingPercent = round(($savingPrice / ($compareAt ?? 80.00)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Long hours at a computer, text neck, and poor sleep posture can flatten your cervical spine's natural curve, causing chronic neck pain, pinched nerves, and tension headaches. The Dainely™ Neck Stretcher utilizes dynamic cervical traction to decompress the spine, stretch tight neck muscles, and restore the healthy C-curve in just 10 minutes of passive relaxation.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['V-shaped ergonomic design matches and restores the natural 26° cervical spine curve', 'sage'],
            ['Acupressure node matrix targets sub-occipital muscles for deep trigger point release', 'sage'],
            ['Gravitational decompression creates physical joint space to relieve pinched nerves', 'sage'],
            ['Double-sided stretch modes: Convex (gentle adapt) and Concave (deep traction)', 'sage'],
            ['Dense, high-resilience polyurethane foam maintains its therapeutic shape', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Package',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Neck Stretcher',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Neck Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Feel the decompression stretch and muscle tension melting away, or get a full refund. Safe and risk-free.</p>
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
        ['Cervical Traction', 'Gently pulls the skull away from shoulders, expanding compressed spinal space.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Acupressure Node Matrix', '60 points stimulate trigger points to release tight muscle knots.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Adaptable Stretch Modes', 'Dual-sided levels. Convex for beginners, concave for a deep traction stretch.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['High-Density Foam', 'Premium medical-grade polyurethane foam provides strong, comfortable support.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Ergonomic Spine Decompression</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve screen strain and postural tension</h2>
      <p class="text-body text-stone-600">
        Working long hours on computers, looking down at mobile phones, or carrying heavy backpacks forces your neck forward.
        The Dainely™ Neck Stretcher is designed to counter-act forward head posture, returning your neck to its healthy shape.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['neck-stretcher-main.png', 'Desk & Screen Fatigue', 'Decompresses cervical vertebrae after long, static computer sessions to relieve stiffness.'],
        ['neck-stretcher-lifestyle.png', 'Post-Workout Stretch', 'Relaxes hyper-contracted neck and shoulder muscles after lifting weights or running.'],
        ['recovery-edu.png', 'Bedtime Tension Melt', 'Lying down for 10 minutes before bed dissolves daily stress, helping you fall asleep comfortably.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Cervical Spine Recovery</p>
      <h2 class="heading-section mb-4">How it restores healthy posture alignment</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanisms working together to stretch, decompress, and relax your cervical spine.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Cervical Traction Stretch', 'The curved wedge anchors the base of your head, using gravity to pull the skull gently away from the collarbone, expanding compressed disc space.', 'navy'],
        ['02', 'Restoration of C-Curve', 'By molding the neck into the healthy 26-degree curve, it counter-acts forward head posture and reduces muscle strain.', 'gold'],
        ['03', 'Sub-Occipital Stimulation', '60 acupressure points press directly onto tense muscle groups, promoting oxygen-rich blood flow to dissolve chronic stiffness.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Cervical Spine Decompression</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic Specialists to Counter "Text Neck" & Cervical Compression</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Your cervical spine is designed to have a natural C-shaped arch that absorbs shock and supports your head. Modern posture habits—leaning over laptops and staring down at phones—force the neck into an unnatural straight or forward-bent alignment. This constant strain compresses discs, restricts blood flow, and fatigues supporting muscles.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Neck Stretcher uses progressive cervical traction. By matching the optimal 26-degree cervical arch, it gently pulls the skull away from the collarbone, creating physical space between compressed joints. This allows disc tissue to rehydrate, releases trapped nerve roots, and lets tense neck muscles fully relax.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['26° Arc Support', 'Perfectly matches healthy cervical spine curvature'],
            ['95% Relieved', 'Reported significant reduction in tension headaches'],
            ['10 Minutes Use', 'Daily passive relaxation is all that is required'],
            ['Zero Electricity', 'Safe, natural gravitational traction healing'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/neck-stretcher-anatomy.png') }}" alt="Cervical curve alignment diagram" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Using passive gravitational traction restores cervical curves, rehydrates compressed spinal discs, and relaxes tight sub-occipital muscles."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['ns_faq1', 'How long should I lie on the Neck Stretcher daily?', 'We recommend starting with 5 minutes per session twice daily. As your muscles and spine adjust, you can gradually increase to 10-15 minutes. Most users find 10 minutes once a day is the sweet spot.'],
        ['ns_faq2', 'Is it normal to feel a bit of stiffness or soreness at first?', 'Yes, this is completely normal. Your neck is adapting to its healthy, natural curvature. It typically takes 1 to 3 days of consistent use for muscles to adapt. Start with the gentle convex curve side first.'],
        ['ns_faq3', 'Can I sleep on this pillow overnight?', 'No. The Dainely™ Neck Stretcher is designed as a targeted cervical traction therapy device, not an overnight sleeping pillow. Sleeping on it for long hours can cause neck muscles to over-stretch.'],
        ['ns_faq4', 'What is the difference between the convex and concave sides?', 'The double-sided design offers two stretch levels. The convex side provides a gentle stretch, ideal for beginners. The concave side provides a deep traction stretch, ideal for advanced users.'],
        ['ns_faq5', 'Is it comfortable for people of all heights?', 'Yes. The traction contour is designed based on universal cervical spine dimensions, and fits comfortably for both men and women of various heights.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Neck Relief</p>
    <h2 class="heading-section mb-4">Realign your neck. Melt away chronic tension.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed to restore healthy posture, decompress cervical discs, and eliminate tension headaches.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 39.90, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Neck Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Ergonomic 26° Curve Traction</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('neckStretcherGallery')) {
      Alpine.data('neckStretcherGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/neck-stretcher-main.png') }}',
          '{{ asset('images/neck-stretcher-lifestyle.png') }}',
          '{{ asset('images/neck-stretcher-anatomy.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isBackStretcher)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Orthopedic Back Stretcher</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="backStretcherGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ Orthopedic Back Stretcher" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Orthopedic Approved
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Multi-Level Spine Decompression</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Decompress your spine. Restore natural lumbar alignment.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 34.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 69.90, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 69.90) - ($price ?? 34.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 69.90)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Sedentary lifestyles, poor sitting posture at desks, and repetitive heavy lifting compress the lumbar spine, causing chronic lower back stiffness, muscle spasms, and shooting sciatica discomfort. The Dainely™ Orthopedic Back Stretcher provides a targeted passive stretch. With 3 adjustable slot levels and a grid of acupressure trigger nodes, it gently expands intervertebral space, releases pinched nerves, and decompresses spinal loading in just 5 to 10 minutes a day.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Multi-level arch adjusts to 3 heights to match progressive lower back flexibility', 'sage'],
            ['88 acupressure nodes stimulate local trigger points to release tight muscle spasms', 'sage'],
            ['Passive spinal decompression relieves load off pinched lumbar nerve roots', 'sage'],
            ['Comfortable foam spine protective strip cushions vertebrae from hard plastic friction', 'sage'],
            ['High-tensile medical grade ABS plastic supports up to 300 lbs / 135 kg safely', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Package',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Back Stretcher',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Spine Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Feel your lower back muscles relax and spinal decompression relief, or get your money back. Risk-free purchase.</p>
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
        ['3-Level Adjust Arch', 'Adjusts height slots to customize spinal traction and flexibility stretch.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['88 Acupressure Nodes', 'Massages tight back muscles to release hyper-contracted spasms.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Passive Decompression', 'Uses body weight to naturally expand intervertebral space.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['High-Tensile ABS Plastic', 'Structural design supports up to 300 lbs / 135 kg without bending.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Ergonomic Posture Restoration</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve lumbar loading, anytime at home or work</h2>
      <p class="text-body text-stone-600">
        Slouching in office chairs, standing on hard floors, or lifting heavy weights strains your lower back and compresses nerve roots.
        The Dainely™ Orthopedic Back Stretcher is highly versatile, providing active support and decompression stretch wherever you need it.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        ['back-stretcher-main.png', 'Chair Lumbar Support', 'Use the included strap to attach the stretcher to your office chair to maintain active posture.'],
        ['back-pain-edu.png', 'Daily Lumbar Stretch', 'Lie on it for 10 minutes on the living room floor to decompress your spine after work.'],
        ['recovery-edu.png', 'Bedtime Decompression', 'Passive stretching before bed relaxes tight hip flexors and back muscles for a restful night.'],
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Lumbar Spine Adaptation</p>
      <h2 class="heading-section mb-4">How it relieves lower back loading pressure</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanical alignment features that stretch and decompress lumbar joints.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Adjustable 3-Level Arch', 'Allows you to customize the traction angle. Level 1 starts with a gentle 7cm arch, progressing up to Level 3 (11cm arch) for an advanced stretch.', 'navy'],
        ['02', 'Vertebral Spine Protection', 'The soft foam cushion strip runs down the center of the arch, protecting your spinal processes from painful plastic friction.', 'gold'],
        ['03', 'Gravitational Decompression', 'Lying on the arch stretches your lumbar region, expanding intervertebral disc spaces to release compression on the sciatic nerve.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Lumbar lordosis realignment</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic Specialists to Restore Natural Lumbar Curvature</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Your lower spine has a natural inward curve called lumbar lordosis. Hours of sitting in slouched chairs or hunching over screens flattens this curve, placing massive pressure on the discs and surrounding nerves.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ Orthopedic Back Stretcher acts as a mechanical orthotic. Lying on the structured arch applies physical traction to the lumbar vertebrae. By lifting the lower back, it counter-acts gravity and stretches tight hip flexors and back muscles, encouraging compressed discs to rehydrate. Over time, consistent daily use helps realign the spine and correct overall posture.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['3 Arch Levels', 'Customizes traction to match your flexible adaptation'],
            ['88 Trigger Nodes', 'Direct acupressure targets tight lower back knots'],
            ['94% Relief Rate', 'Of users reported reduction in sciatica discomfort'],
            ['300 lbs Support', 'Strong structural ABS plastic prevents bowing'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img src="{{ asset('images/spine-anatomy.png') }}" alt="Spine decompression lordosis diagram" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Restoring lumbar lordosis via passive gravity traction takes pressure off pinched nerve roots and improves sitting posture."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['bs_faq1', 'How often and for how long should I use the Back Stretcher?', 'We recommend starting with 5 minutes per session twice daily on Level 1. As your spine adapts and becomes more flexible, you can increase the duration to 10 minutes, and adjust the arch to Level 2 or 3.'],
        ['bs_faq2', 'Is there a maximum weight limit for this device?', 'Yes, the Dainely™ Orthopedic Back Stretcher is built from high-strength structural ABS plastic and supports body weights up to 300 lbs (135 kg) without breaking or bowing.'],
        ['bs_faq3', 'Can I use this stretcher while sitting in a chair?', 'Absolutely! The stretcher comes with an attachment strap, allowing you to tie it securely to your office chair or driver seat for active lumbar posture support while working or driving.'],
        ['bs_faq4', 'Why does it feel a bit uncomfortable or sore initially?', 'Your back muscles and spine have been compressed for years. Stretching them to restore natural posture can trigger mild soreness in the first 2-3 days of adaptation. If it feels too firm, place a folded towel over the arch for cushioning.'],
        ['bs_faq5', 'Can I sleep on this device overnight?', 'No. The Dainely™ Orthopedic Back Stretcher is a targeted lumbar traction therapy device. We recommend using it for a maximum of 10-15 minutes per session, not for sleeping.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Lumbar Decompression</p>
    <h2 class="heading-section mb-4">Decompress your spine. Move with full freedom.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed to restore natural alignment, release pinched nerves, and dissolve chronic lower back tension.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 34.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Lower Back Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ 3-Level Height Adjustments</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('backStretcherGallery')) {
      Alpine.data('backStretcherGallery', () => ({
        active: 0,
        images: [
          '{{ $mainImg ?: asset('images/back-stretcher-main.png') }}',
          '{{ asset('images/back-pain-edu.png') }}',
          '{{ asset('images/spine-anatomy.png') }}',
          '{{ asset('images/recovery-edu.png') }}'
        ],
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isRelaxaLeg)

<div x-data="productPurchase({{ $requiresOption ? 'true' : 'false' }}, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ RelaxaLeg™ System</span>
    </nav>
  </div>
</div>

{{-- ── 1. HERO ───────────────────────────────────────────────── --}}
<section class="bg-white py-12 lg:py-20" aria-label="Product detail" id="product-hero">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Gallery --}}
      <div x-data="relaxaLegGallery()" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <template x-if="images.length > 0">
            <img :src="images[active]" alt="Dainely™ RelaxaLeg™ System" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          </template>
          @if(!$mainImg)
          <div class="w-full aspect-square flex items-center justify-center bg-slate-100">
            <svg class="w-24 h-24 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          @endif
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Seller</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Clinically Proven
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        <div class="grid grid-cols-4 gap-2">
          <template x-for="(img, i) in images.slice(0, 4)" :key="i">
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-500 mb-3">Pneumatic Compression & Heat</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.15;">
          Calm your restless legs. Melt away chronic swelling.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.9' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($price ?? 199.95, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($compareAt ?? 399.00, 2) }}</span>
          </div>
          <div class="ml-auto">
            @php
              $savingPrice = ($compareAt ?? 399.00) - ($price ?? 199.95);
              $savingPercent = round(($savingPrice / ($compareAt ?? 399.00)) * 100);
            @endphp
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $savingPercent }}%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          If sore, heavy legs, chronic restless leg syndrome, or swollen feet are slowing you down, the RelaxaLeg™ System by Dainely is your at-home solution for fast, lasting relief. Gentle heat and targeted sequential air compression work together to boost blood flow, helping to ease swelling, stiffness, and discomfort caused by everyday fatigue, long standing routines, or diabetic circulation issues.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Sequential air compression pumps stagnant fluids upward to reduce swelling', 'sage'],
            ['Soothing carbon fiber heat pads stimulate deep vein blood circulation', 'sage'],
            ['3 specialized massage modes and 2 intensity levels mimic hand kneading', 'sage'],
            ['Cordless, lightweight rechargeable wraps give you complete movement freedom', 'sage'],
            ['Includes 2 free extension pads fitting calf circumferences up to 26 inches', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- variant selector + purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> $requiresOption,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Package',
          'showSizeGuide' => false,
          'sizeGuideHref' => '',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your RelaxaLeg™ System',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">60-Day Leg Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Calm restless nerves and eliminate swelling, or get a full refund. 100% risk-free trial.</p>
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
        ['Pneumatic Air Compression', '3 sequence chambers inflate in cycles to pump fluid and relieve leg swelling.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Soothing Heat Therapy', 'Carbon fiber elements deliver quick, regulated warmth to stimulate blood vessels.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['3 Custom Massage Modes', 'Sequence, Circulation, and Whole-leg settings mimic manual massage.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Cordless Rechargeable Wraps', 'Integrated battery controller provides up to 2 hours of therapeutic use.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Vascular Health & Recovery</p>
      <h2 class="heading-section text-stone-900 mb-4">Relieve heavy legs, wherever you sit</h2>
      <p class="text-body text-stone-600">
        Standing on hard surfaces all day, sitting statically at a desk, or recovering from athletic workouts pooling stagnant blood in lower limbs. The RelaxaLeg™ wraps apply dynamic sequential pressure to revive tired legs.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @if(count($images) >= 3)
        @php
          $img1 = $images[1]['src'] ?? '';
          $img2 = $images[2]['src'] ?? '';
        @endphp
        @foreach([
          [$img1, 'Desk & Office Relief', 'Slip them on under your desk to maintain active blood flow while working sitting down.'],
          [$img2, 'Post-Standing Recovery', 'Perfect for nurses, teachers, retail staff, and runners to reduce painful lower leg swelling.'],
          ['recovery-edu.png', 'Restless Leg Calmer', 'Warm heat and air compression calms throbbing leg nerves before bed for deep sleep.'],
        ] as [$img, $cap, $sub])
        <figure class="group">
          <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
            <img src="{{ (str_contains($img, '/') || str_contains($img, 'http')) ? $img : asset('images/' . $img) }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
          </div>
          <figcaption>
            <p class="font-semibold text-stone-800 text-sm mb-0.5">{{ $cap }}</p>
            <p class="text-stone-500 text-xs">{{ $sub }}</p>
          </figcaption>
        </figure>
        @endforeach
      @endif
    </div>
  </div>
</section>

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Circulation Optimization</p>
      <h2 class="heading-section mb-4">How RelaxaLeg™ pumps fluid out of tired muscles</h2>
      <p class="text-lead max-w-xl mx-auto">Three sequential steps working together to calm restless legs and eliminate swelling.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Sequential Compression', '3 separate air chambers inflate sequentially starting from the ankle, pushing lymphatic fluids and deoxygenated blood upward.', 'navy'],
        ['02', 'Carbon Thermal Heat', 'Dual carbon fiber heating elements distribute uniform warmth across the calves, relaxing tense, stiff tendons immediately.', 'gold'],
        ['03', 'Calm Restless Nerves', 'Cyclic inflation pressure desensitizes hyperactive nerve endings in the calves, eliminating the throbbing crawl sensation.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Boosting Deep Vein Circulation</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic & Vascular Specialists to Alleviate Edema and Restless Legs</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Veins in your lower legs must fight gravity to pump blood back to your heart. Long periods of standing, sitting, or vascular conditions weaken vein valves, causing blood to pool in the calves and ankles. This pooling causes swelling, throbbing aches, and hyper-sensitizes nerves (restless leg syndrome).
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely™ RelaxaLeg™ System delivers active mechanical compression. By sequentially compressing the calves from bottom to top, it mechanically squeezes pooled blood back into systemic circulation, reducing tissue edema, stimulating fluid drainage, and calming restless nerves.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['3 Air Chambers', 'Sequential cycling mimicking manual lymphatic drainage'],
            ['96% Swell Reduction', 'Of users reported reduced leg heaviness and aching'],
            ['Carbon Heating', 'Soothing warm thermal therapy relaxes stiff tendons'],
            ['Extension Pads', 'Adjustable velcro sleeves fit calf sizes up to 26"'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        @if(count($images) >= 2)
          <img src="{{ $images[1]['src'] ?? '' }}" alt="Air compression leg recovery zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        @endif
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Sequential pneumatic compression pumps stagnant fluid out of lower limbs, accelerating lymphatic drainage and easing restless leg aches."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['rl_faq1', 'How often and for how long should I use the RelaxaLeg System?', 'We recommend starting with a single 10-15 minute session daily. You can use it up to twice a day if needed. Do not wear the wraps for more than 30 minutes at a time.'],
        ['rl_faq2', 'Will these wraps fit wider calves or thighs?', 'Yes. The wraps feature highly adjustable hook-and-loop velcro straps. We also include 2 free extension pads in the package, allowing the wraps to comfortably fit calf circumferences up to 26 inches (66 cm).'],
        ['rl_faq3', 'Are there any medical contraindications for using leg compression wraps?', 'Yes. Air compression wraps are contraindicated for individuals with active Deep Vein Thrombosis (DVT), severe varicose veins, pacemakers, active skin infections, or pulmonary edema. Please consult your physician if you have underlying cardiovascular conditions.'],
        ['rl_faq4', 'How long does the rechargeable controller battery last?', 'The integrated rechargeable lithium-ion battery provides up to 2 hours of continuous therapy on a single charge. A USB charging cable is included in the package.'],
        ['rl_faq5', 'Can I clean the inner lining of the leg wraps?', 'Yes. Simply wipe the inner lining down with a damp cloth and mild sanitizing spray. Let the wraps air-dry completely before storing them. Do not submerge them in water or machine wash.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Vascular Comfort</p>
    <h2 class="heading-section mb-4">Calm your restless legs. Melt away chronic swelling.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed to boost blood circulation, reduce edema, and speed up muscle recovery in just 10 minutes a day.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($price ?? 199.95, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 60-Day Leg Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Sequential Pneumatic Compression</span>
    </div>
  </div>
</section>

</div>{{-- /x-data productPurchase --}}

@push('scripts')
<script>
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
  
  document.addEventListener('alpine:init', () => {
    if (!Alpine.data('relaxaLegGallery')) {
      Alpine.data('relaxaLegGallery', () => ({
        active: 0,
        images: @json(array_values(array_map(fn($img) => $img['src'] ?? '', $images))),
        setActive(i) { this.active = i; },
      }));
    }
  });
</script>
@endpush

@elseif($isTourmalineBelt)

@php
  $tourmalineImages = array_values(array_map(fn($img) => $img['src'] ?? '', $images));
  while (count($tourmalineImages) < 4) {
    $tourmalineImages[] = $mainImg ?: asset('images/dainely-belt-product.png');
  }
  $tourmalinePrice = (float)($price ?? 32.95);
  $tourmalineCompare = (float)($compareAt ?? 65.90);
  $tourmalineSaving = round((($tourmalineCompare - $tourmalinePrice) / $tourmalineCompare) * 100);
@endphp

<div x-data="productPurchase(false, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely™ Tourmaline Belt</span>
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
        images: @js($tourmalineImages),
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="Dainely™ Tourmaline Belt" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-500 text-white">Winter Special</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
              Self-Heating Therapy
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
        <p class="text-sm font-bold uppercase tracking-widest text-orange-600 mb-3">Self-Heating Far-Infrared Joint Care</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.1;">
          Soothing warmth.<br>Targeted magnetic relief.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-orange-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($tourmalinePrice, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($tourmalineCompare, 2) }}</span>
          </div>
          <div class="ml-auto">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $tourmalineSaving }}%</span>
          </div>
        </div>
        <p class="text-slate-500 text-xs mb-5">Or 4 interest-free payments of ${{ number_format($tourmalinePrice/4, 2) }} with Square.</p>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Uncomfortable lower back tension and muscle stiffness shouldn't limit your daily routine. 
          The Dainely™ Tourmaline Belt combines self-heating thermal dots with integrated magnetic therapy nodes to stimulate natural blood circulation, relax stiff lumbar tendons, and restore everyday posture.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Natural tourmaline self-heating dots activate with body heat (no cords or batteries)', 'orange'],
            ['Integrated magnetic therapy nodes designed to support blood circulation', 'orange'],
            ['Adjustable dual-compression velcro straps for a personalized, snug fit', 'orange'],
            ['Breathable, lightweight inner lining designed for comfortable daily wear', 'orange'],
            ['Multi-purpose wrap suitable for lower back, waist, or abdominal support', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> false,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your Tourmaline Belt',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">60-Day Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Test the soothing self-heating relief at home. Not completely satisfied? Easy full refund.</p>
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
        ['Self-Heating Matrix', 'Tourmaline dots generate safe, deep-penetrating heat naturally through body contact.', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['Magnetic Therapy Nodes', 'Strategically placed magnets designed to stimulate local micro-circulation.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Targeted Joint Support', 'Helps decompress the spine and stabilize the lumbar lower back.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Breathable Soft Lining', 'Ergonomic, lightweight material fits discreetly underneath everyday clothing.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
      ] as [$title, $copy, $path])
      <div class="group">
        <div class="w-12 h-12 bg-slate-50 group-hover:bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
          <svg class="w-6 h-6 text-slate-500 group-hover:text-orange-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
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
      <p class="eyebrow mb-3">Continuous Lumbar Care</p>
      <h2 class="heading-section text-stone-900 mb-4">Therma-Stabilization that keeps up with you</h2>
      <p class="text-body text-stone-600">
        Bulkier heating pads bind you to a wall plug or restrict your range of motion. The Dainely™ Tourmaline Belt delivers cordless, wireless heat therapy that conforms to your movement — providing lower back relief while you work, commute, or relax.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        [$tourmalineImages[1], 'At the Standing or Sitting Desk', 'Provides stabilizing lower back support and soothing micro-warmth during long office routines.'],
        [$tourmalineImages[2], 'During Daily Movement', 'Comfortable stretch fabric conforms naturally to walks, household errands, and chores.'],
        [$tourmalineImages[3], 'Targeted Lumbar Comfort', 'Ideal for recovery after intense movement, long drives, or when feeling lower back stiffness.'],
      ] as [$img, $cap, $sub])
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ $img }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Thermal Posture Alignment</p>
      <h2 class="heading-section mb-4">How Tourmaline & Magnetic therapy works</h2>
      <p class="text-lead max-w-xl mx-auto">Three synergistic systems working together to relieve lumbar tension and restore movement confidence.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Far-Infrared Heating', 'Body heat reacts with the tourmaline mineral matrix, activating safe, natural far-infrared thermal wavelengths that penetrate deep into muscles.', 'orange'],
        ['02', 'Magnetic Field Nodes', 'Integrated magnetic therapy nodes sit against acupressure points along the lower lumbar spine, encouraging local blood circulation and oxygen transport.', 'navy'],
        ['03', 'Compressive Stabilization', 'Dual elastic straps provide structural support to the lumbar spine, taking pressure off the SI joint and stabilizing your center.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Far-Infrared Tissue Recovery</p>
        <h2 class="heading-section text-white mb-6">Designed with Physical & Orthopedic Therapists to Support Everyday Lumbar Comfort</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Unlike ordinary compression wraps that simply restrict movement, the Dainely™ Tourmaline Belt provides therapeutic warmth. Natural tourmaline particles absorb body thermal energy and re-emit it back as far-infrared rays, which penetrate deeply to soothe muscles and joints.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          This deep warmth increases blood circulation, which helps deliver oxygen and nutrients to tired lumbar tissues while aiding in the removal of metabolic waste. Combined with strategically positioned magnetic dots, it targets stiffness and discomfort directly at the source.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['Natural Tourmaline', 'Activates heating naturally without cords or battery plugs'],
            ['92% User Comfort', 'Reported reduced lower back tension within 2 weeks'],
            ['Magnetic Nodes', 'Stimulate micro-circulation along key lumbar lines'],
            ['Dual Velcro Straps', 'Highly adjustable compression fit for waist sizes up to 45"'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-orange-400/10 blur-3xl rounded-full"></div>
        <img src="{{ $tourmalineImages[0] }}" alt="Tourmaline Belt self-heating zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. K. Aris</p>
              <p class="text-slate-400 text-[10px]">Orthopedic Wellness Advisor</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Self-heating tourmaline wrap promotes gentle, local blood circulation, which helps relieve joint stiffness and supports posture awareness."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['t_faq1', 'How does the self-heating feature work? Does it require batteries?', 'No batteries, plugs, or charging cables are required! The belt uses a matrix of natural tourmaline stones that react with your body heat to generate gentle, deep-penetrating far-infrared warmth naturally within 15-30 minutes of contact.'],
        ['t_faq2', 'Should I wear the Tourmaline Belt directly on my skin or over my clothes?', 'For the self-heating feature to activate most effectively, the belt should be worn in direct contact with your skin. If the heat feels too intense, you can wear it over a thin layer of clothing to reduce the warmth.'],
        ['t_faq3', 'How long should I wear the belt each day?', 'We recommend starting with 15-30 minutes per session, once or twice daily. As your body adjusts, you can wear it for longer periods. Do not wear it overnight or while sleeping.'],
        ['t_faq4', 'Is the belt adjustable, and what sizes does it fit?', 'Yes, the belt features high-quality, adjustable hook-and-loop velcro straps that provide a snug, supportive compression fit. It is designed to fit waist sizes up to 45 inches comfortably.'],
        ['t_faq5', 'How do I clean and wash the Tourmaline Belt?', 'Hand wash only. Gently wipe the tourmaline self-heating panels with a damp cloth. Do not use harsh detergents, do not submerge in water for extended periods, and do not machine wash or dry to protect the magnetic nodes and tourmaline matrix.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Thermal Lumbar Care</p>
    <h2 class="heading-section mb-4">Relieve lower back stiffness. Experience soothing heat.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed to boost blood circulation, relax stiff lumbar muscles, and support your posture in just 20 minutes a day.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($tourmalinePrice, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 60-Day Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Self-Heating Tourmaline Matrix</span>
    </div>
  </div>
</section>

{{-- Sticky Bottom order bar for Tourmaline Belt --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-3 sm:gap-4">
      <img src="{{ $tourmalineImages[0] }}" alt="Dainely™ Tourmaline Belt" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">Dainely™ Tourmaline Belt</p>
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold text-sm">${{ number_format($tourmalinePrice, 2) }}</span>
          <span class="text-slate-400 line-through text-xs">${{ number_format($tourmalineCompare, 2) }}</span>
          <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">-{{ $tourmalineSaving }}%</span>
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
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
</script>
@endpush

@elseif($isDmedeSystem)

@php
  $dmedeImages = [
    asset('images/daily-relief-system.png'),
    asset('images/dainely-belt-product.png'),
    asset('images/hero-lifestyle.png'),
    asset('images/lifestyle-desk-professional.png'),
    asset('images/recovery-edu.png')
  ];
  $dmedePrice = (float)($price ?? 89.95);
  $dmedeCompare = (float)($compareAt ?? 149.95);
  $dmedeSaving = $dmedeCompare > 0 ? round((($dmedeCompare - $dmedePrice) / $dmedeCompare) * 100) : 0;
@endphp

<div x-data="productPurchase(true, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">DMEDE™ Daily Support & Recovery System</span>
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
        images: @js($dmedeImages),
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="DMEDE™ Daily Support & Recovery System" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500 text-white">Best Value System</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Complete Back Protocol
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        @if(count($dmedeImages) > 1)
        <div class="flex gap-2 overflow-x-auto pb-2 lg:grid lg:grid-cols-5">
          <template x-for="(img, i) in images" :key="i">
            <button @click="setActive(i)" :class="active === i ? 'ring-2 ring-navy-600 ring-offset-2' : 'ring-1 ring-slate-200 hover:ring-navy-300'" class="rounded-xl overflow-hidden aspect-square w-14 h-14 flex-shrink-0 lg:w-auto lg:h-auto focus:outline-none transition-all">
              <img :src="img" :alt="'View ' + (i+1)" class="w-full h-full object-cover">
            </button>
          </template>
        </div>
        @endif
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
        <p class="text-sm font-bold uppercase tracking-widest text-emerald-600 mb-3">Longevity Performance System</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.1;">
          Everyday lumbar support.<br>Guided active recovery.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.9' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-emerald-50 rounded-2xl">
          <div>
            {{-- Display dynamic selected variant price if options are selected, otherwise display default --}}
            <span class="font-display font-bold text-4xl text-navy-900" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($dmedePrice, 2) }}'"></span>
            <span class="text-slate-400 line-through text-lg ml-2" x-show="selectedVariant && selectedVariant.compare_at_price" x-text="selectedVariant ? '$' + Number(selectedVariant.compare_at_price).toFixed(2) : ''"></span>
          </div>
          <div class="ml-auto" x-show="selectedVariant && selectedVariant.compare_at_price">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full" x-text="selectedVariant ? 'Save ' + Math.round(((selectedVariant.compare_at_price - selectedVariant.price) / selectedVariant.compare_at_price) * 100) + '%' : ''"></span>
          </div>
        </div>
        <p class="text-slate-500 text-xs mb-5">Or 4 interest-free payments with Square.</p>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Why does your back feel fine in the morning, but achy and compressed by evening? Throughout the day, gravity, prolonged sitting, and micro-movements fatigue your core. The DMEDE™ System delivers structured stabilization and guided active movement protocols to support your posture all day and accelerate recovery at night.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Premium Dainely Belt: Structured SI joint stabilization designed for daily wear', 'emerald'],
            ['Custom-Fit Sizing Extender included to provide breathing room and sizing range comfort', 'emerald'],
            ['Lifetime access to guided Posture Recovery movement routines (digital resets)', 'emerald'],
            ['Made from medical-grade, lightweight, breathable mesh for discreet under-clothes fit', 'emerald'],
            ['Adjustable dual-tension straps to transition from sitting to standing support', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Purchase actions with sizing dropdown --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> true,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Sizing / Package Option',
          'addToCartText' => 'Add System to Cart — Free Shipping',
          'orderNowText'  => 'Get the DMEDE System Now',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">60-Day Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Test the DMEDE System during your daily work and relaxation routines. Not completely right? Full refund, hassle-free.</p>
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
        ['Everyday SI Joint Support', 'Stabilizes the pelvic foundation to take strain off lower back vertebrae.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Custom-Fit Extender', 'Included extender belt pads provide micro-adjustability and size adaptability.', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
        ['Guided Posture Recovery', 'Lifetime digital access to movement resets and mobility decompression routines.', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['Low-Profile Comfort Mesh', 'Medical-grade, breathable materials designed to sit discreetly under standard shirts.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
      ] as [$title, $copy, $path])
      <div class="group">
        <div class="w-12 h-12 bg-slate-50 group-hover:bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
          <svg class="w-6 h-6 text-slate-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
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
      <p class="eyebrow mb-3">Support that moves with you</p>
      <h2 class="heading-section text-stone-900 mb-4">Fits effortlessly into your standard routine</h2>
      <p class="text-body text-stone-600">
        Braces shouldn't restrict you to the sidelines. The DMEDE™ System stabilizes your lumbar region while allowing natural movement — making it the ideal partner for long work hours, standing shifts, and everyday movement.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        [asset('images/lifestyle-desk-professional.png'), 'Office Desk & Sitting Strain', 'Keeps your pelvis stabilized during long sitting hours, preventing the late-afternoon posture slump.'],
        [asset('images/lifestyle-travel-commute.png'), 'Extended Standing Hours', 'Absorbs micro-shocks and relieves lumbar stress during long hours on your feet.'],
        [asset('images/lifestyle-everyday-movement.png'), 'Active Daily Errands', 'Low-profile mesh moves with your body during chores, lifting, or walking routines.'],
      ] as [$img, $cap, $sub])
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ $img }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
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

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Complete Back Protocol</p>
      <h2 class="heading-section mb-4">How the Daily Support & Recovery System works</h2>
      <p class="text-lead max-w-xl mx-auto">Three integrated components working together to support your lower back and accelerate joint recovery.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'SI Joint Decompression', 'The structured compression belt wraps around the hip bones, taking mechanical stress off the Sacroiliac (SI) joint to reduce chronic inflammation.', 'navy'],
        ['02', 'Sizing Extender Versatility', 'The included extender pad attaches seamlessly to the main belt, allowing you to widen the support surface or adapt the belt to changing waist sizes.', 'emerald'],
        ['03', 'Guided Mobility Routines', 'A specialized digital exercise protocol containing simple 5-minute movements to release tight hip flexors and decompress compressed vertebrae.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Pelvic Grid Retraining</p>
        <h2 class="heading-section text-white mb-6">Designed with Orthopedic Advisors to Re-establish Lumbar Equilibrium</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          When the Sacroiliac (SI) joint is unstable, your lower back muscles are forced to overwork to stabilize your spine. This constant mechanical compensation is what leads to evening tightness, soreness, and sciatic discomfort.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The DMEDE™ Support System delivers targeted circumferential compression. By locking the SI joint in its natural anatomical position, it allows hyperactive lower back muscles to relax, while the guided mobility protocols retrain your core to support healthy spinal alignment.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['SI Joint Target', 'Stabilizes the pelvic girdle to unload gravity stress'],
            ['Sizing Extender', 'Included to adapt support to any waist shape'],
            ['94% User Relief', 'Reported reduced lower back aching in 3 weeks'],
            ['Digital Library', 'Lifetime guided movements to reverse sitting fatigue'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-emerald-400/10 blur-3xl rounded-full"></div>
        <img src="{{ $dmedeImages[0] }}" alt="DMEDE System box and support details" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. H. Vance</p>
              <p class="text-slate-400 text-[10px]">Lumbar Posture Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Targeted pelvic compression stabilizes the lower lumbar region, allowing overworked spinal muscles to recover and restoring posture awareness."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['d_faq1', 'What is included in the DMEDE Daily Support & Recovery System?', 'The system is a complete back health protocol containing: 1) The Dainely Belt for active SI joint compression, 2) A custom-fit extender pad for size adjustments, and 3) Lifetime access to our Daily Recovery Routines (guided movement and stretching guides).'],
        ['d_faq2', 'How do I know which size to select?', 'We offer two main size ranges: 1) Waist sizes 30–42" (75–110 cm), and 2) Waist sizes 42–55" (110–140 cm). Every package includes a custom-fit extender pad, giving you extra breathing room and adaptability if you are between sizes.'],
        ['d_faq3', 'Can I wear the support belt underneath my clothes?', 'Yes! The belt features a low-profile, lightweight design made from breathable medical-grade mesh that fits discreetly under standard shirts, trousers, or office wear.'],
        ['d_faq4', 'How long should I wear the belt each day?', 'We recommend starting with 2 hours per day to allow your lumbar muscles and SI joint to adapt. Gradually increase the duration based on your comfort. You can wear it during desk work, long drives, or standing routines.'],
        ['d_faq5', 'How do I access the Daily Recovery movement guides?', 'Upon order confirmation, you will receive a digital link and a QR code in your package to access our guided recovery routine videos and PDFs, showing you simple movement resets.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Complete Back Wellness Protocol</p>
    <h2 class="heading-section mb-4">Stabilize your spine. Decompress your lumbar.</h2>
    <p class="text-lead text-stone-600 mb-3">A complete three-in-one daily support system designed to align posture, unload SI joint pressure, and accelerate core recovery.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($dmedePrice, 2) }}'"></span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add System to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 60-Day Back Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Sizing Extender Pad Included</span>
      <span>✓ Guided Recovery Routines Access</span>
    </div>
  </div>
</section>

{{-- Sticky Bottom order bar for DMEDE System --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-3 sm:gap-4">
      <img src="{{ asset('images/daily-relief-system.png') }}" alt="DMEDE System" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">DMEDE™ Daily Support System</p>
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold text-sm" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($dmedePrice, 2) }}'"></span>
          <span class="text-slate-400 line-through text-xs" x-show="selectedVariant && selectedVariant.compare_at_price" x-text="selectedVariant ? '$' + Number(selectedVariant.compare_at_price).toFixed(2) : ''"></span>
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
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
</script>
@endpush

@elseif($isErgoCushion)

@php
  $cushionImages = array_values(array_map(fn($img) => $img['src'] ?? '', $images));
  while (count($cushionImages) < 4) {
    $cushionImages[] = $mainImg ?: asset('images/dainely-belt-product.png');
  }
  $cushionPrice = (float)($price ?? 69.99);
  $cushionCompare = (float)($compareAt ?? 139.98);
  $cushionSaving = $cushionCompare > 0 ? round((($cushionCompare - $cushionPrice) / $cushionCompare) * 100) : 0;
@endphp

<div x-data="productPurchase(false, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">ErgoCushion® - Pressure Relief Seat Cushion</span>
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
        images: @js($cushionImages),
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="ErgoCushion® - Pressure Relief Seat Cushion" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-navy-600 text-white">Posture Correction</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5 text-navy-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Orthopedic Approved
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        <div class="grid grid-cols-4 gap-2">
          <template x-for="(img, i) in images.slice(0, 4)" :key="i">
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
        <p class="text-sm font-bold uppercase tracking-widest text-navy-600 mb-3">Ergonomic Seating Decompression</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.1;">
          Regain your posture.<br>Relieve seated tailbone pain.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">${{ number_format($cushionPrice, 2) }}</span>
            <span class="text-slate-400 line-through text-lg ml-2">${{ number_format($cushionCompare, 2) }}</span>
          </div>
          <div class="ml-auto">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save {{ $cushionSaving }}%</span>
          </div>
        </div>
        <p class="text-slate-500 text-xs mb-5">Or 4 interest-free payments of ${{ number_format($cushionPrice/4, 2) }} with Square.</p>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Uncomfortable desk chairs or long drives shouldn\'t dictate your physical wellness. The ErgoCushion® is an orthopedic seat pad designed to elevate your posture, suspend the tailbone (coccyx) to avoid compression, and eliminate chronic tailbone aches, sciatica, and lower back strain within 2 weeks.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['Orthopedic cutout suspends tailbone to eliminate pressure points', 'navy'],
            ['High-density premium memory foam contouring responds to body shape', 'navy'],
            ['Regain naturally correct sitting alignment without expensive chairs', 'navy'],
            ['Breathable cooling cover prevents seat sweat during long office routines', 'navy'],
            ['Non-slip bottom grid keeps the cushion in place on desk chairs or car seats', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Purchase actions --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> false,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Option',
          'addToCartText' => 'Add to Cart — Free Shipping',
          'orderNowText'  => 'Get Your ErgoCushion®',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">60-Day Sitting Comfort Guarantee</p>
            <p class="text-sage-600 text-xs">Try it on your desk chair or during your commutes. Not completely comfortable? Full refund.</p>
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
        ['Coccyx Indentation', 'Ergonomic cutout suspends the tailbone, eliminating physical contact pressure.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Premium Memory Foam', 'High-density orthopedic foam contouring responds and adapts to body shape.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Cooling Air Grid', 'Breathable cover promotes air circulation, preventing seated heat buildup.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Non-Slip Grippers', 'Structured bottom mesh holds the cushion firmly in place during daily chair movements.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
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
      <p class="eyebrow mb-3">Ergonomic Seated Comfort</p>
      <h2 class="heading-section text-stone-900 mb-4">Relief that fits any seat in your day</h2>
      <p class="text-body text-stone-600">
        Office chairs can be rigid, car seats can be unsupportive, and home chairs lack decompression arches. The ErgoCushion® transforms any seating surface into a balanced, body-conforming posture alignment grid.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        [$cushionImages[1], 'Office & Desk Routines', 'Provides orthotic coccyx unloading, reducing fatigue and boosting productivity during long office shifts.'],
        [$cushionImages[2], 'Commuting & Road Travel', 'Conforms to car seats to cushion vibrations and prevent thigh/sciatica compression during morning drives.'],
        [$cushionImages[3], 'Everyday Home Relaxation', 'Transforms kitchen, dining, or lounge chairs into structured orthopedic wellness seats.'],
      ] as [$img, $cap, $sub])
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ $img }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
        </div>
        <figcaption>
          <p class="font-semibold text-stone-800 text-sm mb-0.5 font-display">{{ $cap }}</p>
          <p class="text-stone-500 text-xs">{{ $sub }}</p>
        </figcaption>
      </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Orthopedic Decompression</p>
      <h2 class="heading-section mb-4">How ErgoCushion® aligns your seated spine</h2>
      <p class="text-lead max-w-xl mx-auto">Three mechanical steps working together to eliminate compression and retrain natural posture.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Tailbone Suspension', 'The U-shaped tailbone cutout suspends your coccyx in the air, preventing body weight from compressing the base of your spine.', 'navy'],
        ['02', 'Contoured Support Arch', 'Contoured curves cradle your thighs and hips, distributing body weight evenly across the cushion to avoid sciatic nerve pinch.', 'emerald'],
        ['03', 'Pelvic Tilt Realignment', 'The slight forward wedge angle encourages your pelvis to tilt forward, naturally restoring your lumbar spine\'s correct curve.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Unloading Spinal Compression</p>
        <h2 class="heading-section text-white mb-6">Designed with Ergonomic Specialists to Eliminate Daily Sitting Stress</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          When you sit in an unsupportive chair, your lower back vertebrae carry your entire upper body weight. Over hours, this gravity load compresses spinal discs, squeezes the sciatic nerve, and forces your pelvis backward, leading to a slumped posture.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The ErgoCushion® delivers mechanical tailbone suspension and pelvic tilt correction. By elevating your seating base and angling the pelvis, it encourages your vertebrae to stack naturally, taking mechanical strain off discs and restoring sciatic micro-circulation.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['Coccyx Cutout', 'Unloads 100% of contact tailbone pressure'],
            ['96% Posture Score', 'Of users reported improved spine alignment comfort'],
            ['Memory Foam Cores', 'Premium rebound foam retains density and shape'],
            ['Universal Seat Fit', 'Fits office chairs, car seats, and home stools'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-navy-400/10 blur-3xl rounded-full"></div>
        <img src="{{ $cushionImages[0] }}" alt="ErgoCushion orthopedic support zones" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. J. Carter</p>
              <p class="text-slate-400 text-[10px]">Ergonomic Health Adviser</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Suspending the tailbone during extended sitting sessions prevents the base vertebrae from loading micro-impacts, which helps prevent sciatica flares."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['c_faq1', 'Will the ErgoCushion fit standard office chairs or car seats?', 'Yes! The ErgoCushion features a universal contoured design that fits comfortably on office chairs, computer chairs, dining chairs, kitchen stools, car seats, and even airplane seats.'],
        ['c_faq2', 'Can it support tailbone pain and sciatica recovery?', 'Yes. The U-shaped coccyx cutout is specifically designed to suspend your tailbone, which helps alleviate direct pressure on the spinal column and sciatic nerve pathways.'],
        ['c_faq3', 'Does the cushion go flat after extended sitting sessions?', 'No. We use premium, high-density rebound memory foam that responds to your body heat and weight, providing firm support and returning to its original shape after each use.'],
        ['c_faq4', 'How do I wash the cushion and keep it clean?', 'The ErgoCushion cover features a hidden zipper so you can easily remove it. The cover is machine washable in cold water. Do not wash the memory foam core itself.'],
        ['c_faq5', 'Is there a weight limit for the seat cushion?', 'The premium memory foam core is engineered to support body weights up to 250 lbs (110 kg) while maintaining its orthopedic compression and spinal alignment benefits.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Immediate Seated Comfort</p>
    <h2 class="heading-section mb-4">Cushion your tailbone. Eliminate seated strain.</h2>
    <p class="text-lead text-stone-600 mb-3">Designed to suspend the tailbone, distribute body weight evenly, and align your lower spine for pain-free sitting.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900">${{ number_format($cushionPrice, 2) }}</span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 60-Day Sitting Comfort Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ Premium Orthopedic Rebound Memory Foam</span>
    </div>
  </div>
</section>

{{-- Sticky Bottom order bar for ErgoCushion --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-3 sm:gap-4">
      <img src="{{ $cushionImages[0] }}" alt="ErgoCushion®" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">ErgoCushion® - Pressure Seat Cushion</p>
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold text-sm">${{ number_format($cushionPrice, 2) }}</span>
          <span class="text-slate-400 line-through text-xs">${{ number_format($cushionCompare, 2) }}</span>
          <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">-{{ $cushionSaving }}%</span>
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
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
</script>
@endpush

@elseif($isMushroomCoffee)

@php
  $coffeeImages = array_values(array_map(fn($img) => $img['src'] ?? '', $images));
  while (count($coffeeImages) < 4) {
    $coffeeImages[] = $mainImg ?: asset('images/dainely-belt-product.png');
  }
  $coffeePrice = (float)($price ?? 34.95);
  $coffeeCompare = $compareAt ? (float)$compareAt : 69.90;
  $coffeeSaving = $coffeeCompare > 0 ? round((($coffeeCompare - $coffeePrice) / $coffeeCompare) * 100) : 0;
@endphp

<div x-data="productPurchase(true, @js($cartProduct), @js($cartAddUrl))">

{{-- ── 0. BREADCRUMB ─────────────────────────────────────────── --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Functional Mushroom Coffee</span>
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
        images: @js($coffeeImages),
        setActive(i) { this.active = i; }
      }" class="lg:sticky lg:top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-lg mb-4 group aspect-square">
          <img :src="images[active]" alt="Functional Mushroom Coffee" class="w-full h-full object-cover transition-all duration-500" width="640" height="640">
          <div class="absolute top-5 left-5">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-600 text-white">Daily Energizer</span>
          </div>
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 shadow">
            <span class="text-sage-700 text-xs font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
              6 Adaptogens Blends
            </span>
          </div>
        </div>
        {{-- Thumbnails --}}
        <div class="grid grid-cols-4 gap-2">
          <template x-for="(img, i) in images.slice(0, 4)" :key="i">
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
        <p class="text-sm font-bold uppercase tracking-widest text-amber-700 mb-3">Reimagine Your Morning Ritual</p>
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem,4vw,2.75rem); line-height: 1.1;">
          Sustained mental energy.<br>Zero jitters, crashes, or anxiety.
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.9' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-amber-50 rounded-2xl">
          <div>
            {{-- Display dynamic selected variant price if options are selected, otherwise display default --}}
            <span class="font-display font-bold text-4xl text-navy-900" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($coffeePrice, 2) }}'"></span>
            <span class="text-slate-400 line-through text-lg ml-2" x-show="selectedVariant && selectedVariant.compare_at_price" x-text="selectedVariant ? '$' + Number(selectedVariant.compare_at_price).toFixed(2) : ''"></span>
          </div>
          <div class="ml-auto" x-show="selectedVariant && selectedVariant.compare_at_price">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full" x-text="selectedVariant ? 'Save ' + Math.round(((selectedVariant.compare_at_price - selectedVariant.price) / selectedVariant.compare_at_price) * 100) + '%' : ''"></span>
          </div>
        </div>
        <p class="text-slate-500 text-xs mb-5">Or 4 interest-free payments with Square.</p>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          Traditional coffee spikes your energy and leaves you flat by midday. DMEDE Functional Mushroom Coffee blends premium Arabica beans with six powerful adaptogenic mushrooms (Lion\'s Mane, Cordyceps, Reishi, Chaga, Shiitake, and Maitake) to provide clear focus, smooth sustained energy, and cognitive support without the jitters, crashes, or digestive upset.
        </p>

        {{-- Key benefits --}}
        <ul class="space-y-2.5 mb-8">
          @foreach([
            ['6 adaptogenic mushrooms: Lion\'s Mane, Cordyceps, Reishi, Chaga, Shiitake, Maitake', 'amber'],
            ['Sustained focus: Arabica coffee beans paired with L-theanine properties', 'amber'],
            ['Anxiety & Jitters free: Adaptogens balance cortisol response naturally', 'amber'],
            ['Smooth on stomach: Reduced acidity for comfortable digestion', 'amber'],
            ['Multi-pack bundles available for daily mastery or home office setups', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Purchase actions with packages dropdown --}}
        @include('partials.product-purchase', [
          'cartAddUrl'    => $cartAddUrl,
          'checkoutUrl'   => $checkoutUrl,
          'requiresOption'=> true,
          'options'       => $variants,
          'optionType'    => 'shopify',
          'optionLabel'   => 'Select Package Options',
          'addToCartText' => 'Add Coffee to Cart — Free Shipping',
          'orderNowText'  => 'Get Mushroom Coffee Now',
        ])

        {{-- Guarantee strip --}}
        <div class="flex items-center gap-3 p-4 border-2 border-sage-200 bg-sage-50 rounded-2xl">
          <svg class="w-10 h-10 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">30-Day Taste & Focus Guarantee</p>
            <p class="text-sage-600 text-xs">Re-imagine your morning. If you don\'t experience cleaner focus and steady energy, we\'ll refund you fully.</p>
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
        ['6 Adaptogenic Extracts', 'Lion\'s Mane, Cordyceps, Reishi, Chaga, Shiitake, and Maitake extracts.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Zero Jitters or Crashes', 'L-theanine and adaptogenic buffers balance adrenaline spikes naturally.', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['100% Premium Arabica', 'Grown at high altitudes for smooth taste and natural digestive comfort.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
        ['Enhanced Clear Mind', 'Lion\'s mane extracts support brain pathways and cognitive focus.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
      ] as [$title, $copy, $path])
      <div class="group">
        <div class="w-12 h-12 bg-slate-50 group-hover:bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3 transition-colors">
          <svg class="w-6 h-6 text-slate-500 group-hover:text-amber-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
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
      <p class="eyebrow mb-3">Re-imagine your ritual</p>
      <h2 class="heading-section text-stone-900 mb-4">Clean energy designed for modern routines</h2>
      <p class="text-body text-stone-600">
        Say goodbye to the afternoon rollercoaster. Traditional coffee leaves you tired and anxious. DMEDE Functional Mushroom Coffee balances your system, supporting clear concentration from morning work to evening wind-down.
      </p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @foreach([
        [$coffeeImages[0], 'Morning Wake-Up Ritual', 'Delivers Arabica taste and active adaptogens to establish clear focus and alertness without stomach acidity.'],
        [$coffeeImages[1], 'Deep Work & Concentration', 'Lion\'s mane support holds focus levels steady through long office sessions, spreadsheets, or meetings.'],
        [$coffeeImages[0], 'Afternoon Post-Crash Reset', 'Ideal alternative to standard espresso shots, avoiding adrenal fatigue and promoting stress adaptability.'],
      ] as [$img, $cap, $sub])
      <figure class="group">
        <div class="overflow-hidden rounded-2xl aspect-[4/5] bg-stone-100 mb-3">
          <img src="{{ $img }}" alt="{{ $cap }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" width="400" height="500">
        </div>
        <figcaption>
          <p class="font-semibold text-stone-800 text-sm mb-0.5 font-display">{{ $cap }}</p>
          <p class="text-stone-500 text-xs">{{ $sub }}</p>
        </figcaption>
      </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4. HOW IT WORKS / ANATOMY ─────────────────────────────── --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Adaptogenic Bio-Chemistry</p>
      <h2 class="heading-section mb-4">How Adaptogenic Coffee works</h2>
      <p class="text-lead max-w-xl mx-auto">Three synergistic systems working together to balance your nervous system and support sustained concentration.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Smooth Caffeine Release', 'Adaptogenic buffer compounds slow the absorption of caffeine, creating a gentle energy curve instead of a sudden spike.', 'amber'],
        ['02', 'Cortisol Balance Support', 'Reishi and Chaga adaptogens support your adrenal glands, helping regulate your stress response and preventing post-caffeine jitters.', 'navy'],
        ['03', 'Gut & Digestion Comfort', 'Premium Arabica beans are blended with prebiotic active mushroom fibers, reducing acidity and supporting comfortable digestion.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-{{ $color }}-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <span class="font-display font-bold text-2xl text-{{ $color }}-600">{{ $num }}</span>
        </div>
        <h3 class="heading-card mb-3">{{ $title }}</h3>
        <p class="text-body text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 5. CLINICAL VALIDATION / HEALTH AUTHORITY ─────────────── --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Educational authority">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="text-gold-400 text-xs font-bold uppercase tracking-widest mb-4">Adaptogenic Focus Pathways</p>
        <h2 class="heading-section text-white mb-6">Designed with Nutrition & Cognitive Advisors to Support Mental Stamina</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-6">
          Ordinary caffeine stimulates the central nervous system, releasing a rush of adrenaline. While this increases short-term alertness, it often triggers cortisol spikes, leaving you with jitters and fatigue once it wears off.
        </p>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          DMEDE Functional Mushroom Coffee utilizes adaptogens to support stress response. Active polysaccharides in Lion\'s Mane support brain pathway efficiency, while Cordyceps encourages oxygen uptake, giving you clear focus and steady energy.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['6 Mushrooms Blend', 'Lion\'s Mane, Cordyceps, Reishi, Chaga, Shiitake, Maitake'],
            ['Sustained Energy', 'Sustained focus without the afternoon crash'],
            ['Adrenal Support', 'Adaptogens help regulate cortisol response'],
            ['Instant Prep', 'Dissolves easily in hot water for a quick, premium cup'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-2xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-xs">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-amber-400/10 blur-3xl rounded-full"></div>
        <img src="{{ $coffeeImages[0] }}" alt="Mushroom Coffee blend details" class="relative z-10 w-full rounded-3xl shadow-lg" loading="lazy" width="600" height="500">
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-lg p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. E. Thorne</p>
              <p class="text-slate-400 text-[10px]">Cognitive Health Advisor</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Blending L-theanine containing Arabica coffee with adaptogenic mushrooms slows down caffeine metabolization, providing sustained focus."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 6. FAQ ───────────────────────────────────────────────── --}}
@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Frequently Asked Questions</p>
      <h2 class="heading-section mb-4">FAQ</h2>
    </div>
    <div class="max-w-2xl mx-auto space-y-3">
      @foreach([
        ['co_faq1', 'Does Functional Mushroom Coffee taste like mushrooms?', 'No! It tastes like premium, smooth Arabica coffee. The organic adaptogenic mushroom extracts are flavorless, so you enjoy the rich coffee taste you love, without any mushroom flavor.'],
        ['co_faq2', 'Will I experience jitters, anxiety, or an afternoon crash?', 'No. The adaptogens (Reishi, Chaga) support your adrenal glands to balance your stress response, providing clean energy without jitters or sudden crashes.'],
        ['co_faq3', 'Which functional mushrooms are used in the blend?', 'Our blend includes six certified organic extracts: Lion\'s Mane (cognitive support), Cordyceps (energy), Reishi (calm), Chaga (immune support), Shiitake (wellness), and Maitake (balance).'],
        ['co_faq4', 'How do I prepare Functional Mushroom Coffee?', 'It is instant coffee! Simply dissolve 1 teaspoon (about 3g) of the blend in 8-10 oz of hot water. Add milk, sweetener, or enjoy it black.'],
        ['co_faq5', 'Is it safe to drink daily, and does it contain gluten?', 'Yes. It is gluten-free, dairy-free, vegan-friendly, and safe for daily use. If you are pregnant or have medical concerns, please consult your physician before use.'],
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

{{-- ── 7. FINAL CTA ─────────────────────────────────────────── --}}
<section class="section bg-gradient-to-b from-stone-50 to-white" aria-label="Final call to action">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-4">Re-imagine Your Morning Ritual</p>
    <h2 class="heading-section mb-4">Steady focus. Smooth taste. Natural energy.</h2>
    <p class="text-lead text-stone-600 mb-3">Arabica coffee beans blended with 6 adaptogens to support focus and energy all day long.</p>

    <div class="mb-6">
      <span class="font-display font-bold text-5xl text-navy-900" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($coffeePrice, 2) }}'"></span>
    </div>

    <div class="max-w-sm mx-auto space-y-3">
      <button type="button" @click="goToCheckout($event)" class="btn-primary-lg w-full justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Add Coffee to Cart — Free Shipping
      </button>
    </div>

    <div class="flex flex-wrap gap-5 justify-center mt-8 text-xs text-slate-500">
      <span>✓ 30-Day Taste & Focus Guarantee</span>
      <span>✓ Free Shipping Over $75</span>
      <span>✓ Secure Checkout</span>
      <span>✓ 6 Organic Adaptogenic Mushrooms extracts</span>
    </div>
  </div>
</section>

{{-- Sticky Bottom order bar for Coffee --}}
<div id="sticky-order-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-navy-100 shadow-[0_-4px_24px_rgba(0,0,0,0.10)] transform translate-y-full transition-transform duration-300 ease-in-out" aria-label="Quick order bar">
  <div class="container-site py-2 sm:py-3">
    <div class="flex items-center gap-3 sm:gap-4">
      <img src="{{ $mainImg }}" alt="Mushroom Coffee" class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0 ring-2 ring-slate-100 hidden sm:block">
      <div class="flex-1 min-w-0">
        <p class="font-bold text-navy-900 text-xs sm:text-sm truncate">Functional Mushroom Coffee</p>
        <div class="flex items-center gap-2">
          <span class="text-navy-700 font-bold text-sm" x-text="selectedVariant ? '$' + Number(selectedVariant.price).toFixed(2) : '${{ number_format($coffeePrice, 2) }}'"></span>
          <span class="text-slate-400 line-through text-xs" x-show="selectedVariant && selectedVariant.compare_at_price" x-text="selectedVariant ? '$' + Number(selectedVariant.compare_at_price).toFixed(2) : ''"></span>
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
  // Sticky order bar trigger
  (function() {
    const stickyBar = document.getElementById('sticky-order-bar');
    const heroSection = document.getElementById('product-hero');
    function updateStickyBar() {
      if (!heroSection || !stickyBar) return;
      stickyBar.classList.toggle('translate-y-full', heroSection.getBoundingClientRect().bottom >= 0);
    }
    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  })();
</script>
@endpush

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
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats['average_rating'] ?? '4.8' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats['total_reviews'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
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

@include('partials.reviews', ['reviews' => $reviews, 'reviewStats' => $reviewStats])

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
