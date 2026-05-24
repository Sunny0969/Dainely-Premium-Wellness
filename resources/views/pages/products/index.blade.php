@extends('layouts.app')
@section('title', 'All Products — Dainely Wellness')
@section('meta_description', 'Shop all Dainely wellness products synced from our Shopify store.')

@section('content')
<section class="section bg-section-alt">
  <div class="container-site">
    <div class="text-center mb-14">
      <p class="eyebrow mb-3">Our Products</p>
      <h1 class="heading-section mb-4">Medical-Grade Wellness Products</h1>
      <p class="text-lead max-w-xl mx-auto">Browse our full catalog — live from Shopify with real-time pricing and availability.</p>
    </div>

    @if(!empty($error))
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 text-sm mb-8 max-w-2xl mx-auto">
      <p>{{ $error }}</p>
    </div>
    @endif

    @if(count($products) > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      @foreach($products as $product)
      @php
        $slug = $product['handle'] ?? '';
        $price = (float) ($product['price'] ?? 0);
        $compareAt = !empty($product['compare_at']) ? (float) $product['compare_at'] : null;
        $savings = ($compareAt && $compareAt > $price) ? round((($compareAt - $price) / $compareAt) * 100) : null;
      @endphp
      <div class="card overflow-hidden group animate-on-scroll">
        <div class="relative overflow-hidden">
          @if($product['image'])
          <img
            src="{{ $product['image'] }}"
            alt="{{ $product['title'] }}"
            class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          >
          @else
          <div class="w-full h-64 bg-slate-100 flex items-center justify-center text-slate-400 text-sm">No image</div>
          @endif
        </div>
        <div class="p-8">
          <h2 class="heading-card mb-2">{{ $product['title'] }}</h2>
          <div class="flex items-center gap-3 mb-6">
            @if($price > 0)
            <span class="font-display font-bold text-3xl text-navy-900">${{ number_format($price, 2) }}</span>
            @endif
            @if($compareAt && $compareAt > $price)
            <span class="text-slate-400 line-through text-lg">${{ number_format($compareAt, 2) }}</span>
            @if($savings)
            <span class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-1 rounded-full">Save {{ $savings }}%</span>
            @endif
            @endif
          </div>
          <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $slug]) }}" class="btn-primary w-full justify-center">
            View Product
          </a>
        </div>
      </div>
      @endforeach
    </div>
    @elseif(empty($error))
    <p class="text-center text-slate-500 py-12">No products available at the moment.</p>
    @endif
  </div>
</section>

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
