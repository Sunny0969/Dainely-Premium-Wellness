@extends('layouts.app')
@section('title', 'All Products — Dainely Wellness')
@section('meta_description', 'Shop all Dainely wellness products: the Dainely Belt lumbar support system and the Daily Relief System bundle.')

@section('content')
<section class="section bg-section-alt">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Our Products</p>
      <h1 class="heading-section mb-4">Medical-Grade Wellness Solutions</h1>
      <p class="text-lead max-w-xl mx-auto">Every product is clinically developed with board-certified physiotherapists to address the root causes of back pain — not just the symptoms.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
      @foreach($products as $product)
      @php $t = $product->translation(app()->getLocale()); @endphp
      @if($t)
      <div class="card overflow-hidden group animate-on-scroll">
        <div class="relative overflow-hidden">
          @if($product->is_featured)
          <div class="absolute top-4 left-4 z-10">
            <span class="product-badge">{{ $product->type === 'bundle' ? 'Complete Bundle' : 'Best Seller' }}</span>
          </div>
          @endif
          <img
            src="{{ asset($product->main_image) }}"
            alt="{{ $t->name }}"
            class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          >
        </div>
        <div class="p-8">
          <h2 class="heading-card mb-2">{{ $t->name }}</h2>
          <p class="text-body text-sm mb-5">{{ $t->short_description }}</p>
          <div class="flex items-center gap-3 mb-6">
            <span class="font-display font-bold text-3xl text-navy-900">${{ number_format($product->price_usd, 2) }}</span>
            @if($product->compare_price_usd)
            <span class="text-slate-400 line-through text-lg">${{ number_format($product->compare_price_usd, 2) }}</span>
            <span class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-1 rounded-full">Save {{ $product->savings_percent }}%</span>
            @endif
          </div>
          <div class="stars flex gap-0.5 mb-5">
            @for ($i = 0; $i < 5; $i++)
            <svg class="w-4 h-4 star" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
            <span class="text-slate-500 text-sm ml-1">(1,247)</span>
          </div>
          <div class="flex gap-3">
            <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $t->slug]) }}" class="btn-primary flex-1 justify-center">
              View Product
            </a>
            <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="btn-gold-lg justify-center">
              Buy Now
            </a>
          </div>
        </div>
      </div>
      @endif
      @endforeach
    </div>
  </div>
</section>

{{-- Trust section --}}
<section class="section bg-white">
  <div class="container-site">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      @foreach([['50,000+','Customers Helped'],['4.8★','Average Rating'],['30 Day','Money-Back Guarantee'],['Free Ship','Orders Over $75']] as [$val,$label])
      <div class="p-6 bg-navy-50 rounded-2xl">
        <p class="font-display font-bold text-2xl text-navy-900 mb-1">{{ $val }}</p>
        <p class="text-slate-500 text-sm">{{ $label }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endsection
