@extends('layouts.app')

@section('title', 'Dainely Belt — Medical-Grade Lumbar Support Belt')
@section('meta_description', 'The Dainely Belt is a clinically developed lumbar decompression belt targeting sciatic nerve relief and posture correction. Free shipping on orders over $75.')
@section('og_image', asset('images/dainely-belt-product.png'))

@section('content')

{{-- Breadcrumb --}}
<div class="bg-slate-50 border-b border-slate-100">
  <div class="container-site py-3">
    <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
      <a href="#" class="hover:text-navy-700 transition-colors">Home</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <a href="#" class="hover:text-navy-700 transition-colors">Products</a>
      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-navy-800 font-medium">Dainely Belt</span>
    </nav>
  </div>
</div>

{{-- ============================================================
     PRODUCT HERO
     ============================================================ --}}
<section class="section bg-white" aria-label="Product detail">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

      {{-- LEFT: Image Gallery --}}
      <div x-data="productGallery()" class="sticky top-24">
        {{-- Main image --}}
        <div class="relative rounded-3xl overflow-hidden bg-slate-50 shadow-medium mb-4 group">
          <img
            :src="images[active]"
            :alt="'Dainely Belt view ' + (active + 1)"
            class="w-full aspect-square object-cover object-center transition-all duration-500"
            id="main-product-img"
            width="640" height="640"
          >
          {{-- Badge --}}
          <div class="absolute top-5 left-5">
            <span class="product-badge text-sm px-3 py-1.5">Best Seller</span>
          </div>
          {{-- Clinically Developed badge --}}
          <div class="absolute top-5 right-5 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 flex items-center gap-1.5 shadow-soft">
            <svg class="w-4 h-4 text-sage-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sage-700 text-xs font-semibold">Clinically Developed</span>
          </div>
        </div>
        {{-- Thumbnails --}}
        <div class="grid grid-cols-4 gap-3">
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

        {{-- Trust strip below gallery --}}
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
        {{-- Eyebrow --}}
        <p class="eyebrow mb-3">Medical-Grade Lumbar Support</p>

        {{-- Title --}}
        <h1 class="font-display font-bold text-navy-950 mb-4" style="font-size: clamp(2rem, 4vw, 2.75rem); line-height: 1.1;">
          Dainely Belt
        </h1>

        {{-- Rating row --}}
        <div class="flex items-center gap-3 mb-6">
          <div class="stars flex items-center gap-0.5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-5 h-5 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="text-navy-800 font-semibold text-sm">4.8</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">1,247 verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-sage-600 text-sm font-semibold">✓ In Stock</span>
        </div>

        {{-- Price block --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-navy-50 rounded-2xl">
          <div>
            <span class="font-display font-bold text-4xl text-navy-900">$89</span>
            <span class="text-slate-400 line-through text-lg ml-2">$119</span>
          </div>
          <div class="ml-auto">
            <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-full">Save 25%</span>
          </div>
        </div>

        {{-- Short description --}}
        <p class="text-slate-600 text-base leading-relaxed mb-6">
          A medical-grade lumbar decompression belt developed with board-certified physiotherapists. Engineered to decompress vertebrae, relieve sciatic pressure, and restore natural spinal alignment — not just mask pain.
        </p>

        {{-- Key benefits bullets --}}
        <ul class="space-y-3 mb-8">
          @foreach([
            ['Decompresses lumbar vertebrae to relieve nerve pressure', 'sage'],
            ['Reduces sciatic pain in as little as 2 weeks', 'sage'],
            ['Adjustable compression zones for personalized support', 'sage'],
            ['Breathable medical-grade fabric — wear all day comfortably', 'sage'],
            ['Clinically developed with spine specialists', 'gold'],
          ] as [$benefit, $color])
          <li class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 text-{{ $color }}-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            <span class="text-slate-700 text-sm">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>

        {{-- Size selector --}}
        <div class="mb-6">
          <div class="flex items-center justify-between mb-3">
            <label class="form-label">Select Size</label>
            <a href="#size-guide" class="text-navy-600 text-sm underline underline-offset-2 hover:text-navy-800">Size Guide</a>
          </div>
          <div class="grid grid-cols-4 gap-2" id="size-selector">
            @foreach(['S/M', 'L/XL', '2XL', '3XL'] as $size)
            <button
              onclick="document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected')); this.classList.add('selected')"
              class="size-btn border-2 border-slate-200 hover:border-navy-600 text-slate-700 hover:text-navy-700 font-semibold py-2.5 rounded-xl text-sm transition-all duration-200 focus:outline-none"
              style=""
            >{{ $size }}</button>
            @endforeach
          </div>
        </div>

        {{-- Quantity + Add to Cart --}}
        <div class="flex items-center gap-4 mb-4">
          <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden">
            <button class="px-4 py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-lg"
              x-data x-on:click="$dispatch('decrement-qty')">−</button>
            <span class="px-5 py-3 font-semibold text-navy-900 text-lg border-x-2 border-slate-200" x-data="{qty:1}" x-on:increment-qty.window="qty++" x-on:decrement-qty.window="qty = Math.max(1, qty-1)" x-text="qty" id="qty-display">1</span>
            <button class="px-4 py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-lg"
              x-data x-on:click="$dispatch('increment-qty')">+</button>
          </div>
          <button id="add-to-cart-btn" class="btn-primary-lg flex-1 justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Add to Cart — $89
          </button>
        </div>

        {{-- Buy Now --}}
        <a href="#checkout" class="btn-gold-lg w-full justify-center mb-6">
          Buy Now — Free Shipping
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
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     CLINICAL VALIDATION SECTION
     ============================================================ --}}
<section class="section bg-gradient-to-br from-navy-950 via-navy-900 to-navy-800 text-white" aria-label="Clinical validation">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="eyebrow text-gold-400 mb-4">The Science Behind It</p>
        <h2 class="heading-section text-white mb-6">Developed by Spine Specialists, Not Marketers</h2>
        <p class="text-navy-200 text-base leading-relaxed mb-8">
          The Dainely Belt is the result of 3 years of clinical development with physiotherapists and orthopedic consultants. Every component — from compression zone placement to fabric selection — is backed by biomechanical research.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
          @foreach([
            ['87%', 'Report measurable pain reduction within 4 weeks'],
            ['94%', 'Would recommend to a friend or family member'],
            ['3 yrs', 'Clinical development timeline'],
            ['50K+', 'Customers helped worldwide'],
          ] as [$stat, $label])
          <div class="bg-white/10 rounded-2xl p-5">
            <p class="font-display font-bold text-3xl text-gold-300 mb-1">{{ $stat }}</p>
            <p class="text-navy-300 text-sm">{{ $label }}</p>
          </div>
          @endforeach
        </div>
        <a href="#learn-more" class="btn-outline border-white/30 text-white hover:bg-white/10">Read the Research</a>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img
          src="{{ asset('images/spine-anatomy.png') }}"
          alt="Spine anatomy showing how Dainely Belt decompresses lumbar vertebrae"
          class="relative z-10 w-full rounded-3xl shadow-gold"
          loading="lazy"
          width="600" height="500"
        >
        <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-medium p-4 z-20">
          <div class="flex items-center gap-2 mb-2">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-navy-900 text-xs font-bold">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-[10px]">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-700 text-xs italic">"Clinically validated approach to lumbar decompression."</p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     HOW IT WORKS
     ============================================================ --}}
<section class="section bg-white" aria-label="How it works">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Step-by-Step</p>
      <h2 class="heading-section mb-4">How the Dainely Belt Works</h2>
      <p class="text-lead max-w-xl mx-auto">Three targeted mechanisms work together to relieve pain and restore spinal health.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([
        ['01', 'Decompress', 'Inflatable air cells gently lift and separate lumbar vertebrae, reducing disc pressure and nerve compression instantly upon inflation.', 'navy'],
        ['02', 'Stabilise', 'Dual-layer support panels maintain proper spinal curvature throughout daily activities, retraining your postural muscles over time.', 'gold'],
        ['03', 'Relieve', 'Reduced nerve compression means less inflammation, less sciatica, and measurable pain relief — typically within 2 weeks of consistent use.', 'sage'],
      ] as [$num, $title, $desc, $color])
      <div class="card p-8 text-center animate-on-scroll">
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

{{-- ============================================================
     REVIEWS
     ============================================================ --}}
<section id="reviews" class="section bg-section-alt" aria-label="Customer reviews">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">Verified Reviews</p>
      <h2 class="heading-section mb-4">What Our Customers Say</h2>
      <div class="flex items-center justify-center gap-3">
        <div class="stars flex gap-0.5">
          @for ($i = 0; $i < 5; $i++)
          <svg class="w-6 h-6 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          @endfor
        </div>
        <span class="text-slate-700 font-semibold">4.8 / 5</span>
        <span class="text-slate-400">·</span>
        <span class="text-slate-500 text-sm">1,247 verified reviews</span>
      </div>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['Sarah M.', 'Texas, USA', 'testimonial-sarah.jpg', '"I have had chronic lower back pain for 3 years. After just 2 weeks with the Dainely Belt, I am finally sleeping through the night. The difference is extraordinary — I can walk my dog again without wincing."', 5, 'Dainely Belt · Size L/XL'],
        ['Jean-Pierre D.', 'Paris, France', 'testimonial-jean.jpg', '"Ma sciatique a littéralement disparu en 3 semaines. Je suis thérapeute et je recommande maintenant ce produit à mes propres patients. La qualité est médicale, pas simplement commerciale."', 5, 'Dainely Belt · Size M'],
        ['Klaus H.', 'Munich, Germany', 'testimonial-klaus.jpg', '"Nach Jahren mit chronischen Rückenschmerzen habe ich dieses Produkt ausprobiert. Innerhalb von zwei Wochen konnte ich wieder Sport treiben. Absolute Empfehlung!"', 5, 'Dainely Belt · Size XL'],
      ] as [$name, $location, $avatar, $review, $stars, $product])
      <div class="testimonial-card">
        <div class="flex items-start justify-between mb-3">
          <div class="stars flex gap-0.5">
            @for ($i = 0; $i < $stars; $i++)
            <svg class="w-4 h-4 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          <span class="trust-badge text-sage-700 bg-sage-50 border-sage-200 text-[10px]">✓ Verified</span>
        </div>
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

{{-- RELATED PRODUCT --}}
<section class="section bg-white" aria-label="Related products">
  <div class="container-narrow text-center">
    <p class="eyebrow mb-3">Complete Your Wellness System</p>
    <h2 class="heading-section mb-6">Upgrade to the Daily Relief System</h2>
    <div class="card overflow-hidden max-w-2xl mx-auto">
      <div class="grid sm:grid-cols-2 gap-0">
        <img src="{{ asset('images/daily-relief-system.png') }}" alt="Daily Relief System" class="w-full h-48 sm:h-full object-cover" loading="lazy">
        <div class="p-6 text-left flex flex-col justify-center">
          <span class="product-badge bg-sage-500 mb-3 inline-flex w-fit">Complete System</span>
          <h3 class="heading-card mb-2">Daily Relief System</h3>
          <p class="text-body text-sm mb-4">Belt + foam roller + resistance bands + recovery guide. Save $40 vs buying separately.</p>
          <div class="flex items-center gap-3 mb-4">
            <span class="font-display font-bold text-2xl text-navy-900">$149</span>
            <span class="text-slate-400 line-through">$189</span>
            <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">Save $40</span>
          </div>
          <a href="#" class="btn-primary text-sm">View System</a>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  // Size selector highlight
  document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.size-btn').forEach(b => {
        b.style.borderColor = '';
        b.style.backgroundColor = '';
        b.style.color = '';
      });
      this.style.borderColor = '#1e3a8a';
      this.style.backgroundColor = '#eff6ff';
      this.style.color = '#1e3a8a';
    });
  });

  // Alpine productGallery images
  document.addEventListener('alpine:init', () => {
    Alpine.data('productGallery', () => ({
      active: 0,
      images: [
        '{{ asset("images/dainely-belt-product.png") }}',
        '{{ asset("images/spine-anatomy.png") }}',
        '{{ asset("images/hero-lifestyle.png") }}',
        '{{ asset("images/posture-edu.png") }}',
      ],
      setActive(i) { this.active = i; },
      prev() { this.active = (this.active - 1 + this.images.length) % this.images.length; },
      next() { this.active = (this.active + 1) % this.images.length; },
    }));
  });
</script>
@endpush

@endsection
