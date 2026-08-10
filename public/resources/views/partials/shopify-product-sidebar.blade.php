@if(!empty($product) && !empty($product->handle))
@php
  $img = $product->main_image ?? '';
  $imgSrc = str_starts_with($img, 'http') ? $img : asset($img);
@endphp
<div class="card p-6 sticky top-24">
  @if($imgSrc)
  <img src="{{ $imgSrc }}" alt="{{ $product->title }}" class="w-full h-48 object-cover rounded-xl mb-5">
  @endif
  <h3 class="font-semibold text-navy-900 text-lg mb-2">{{ $heading ?? $product->title }}</h3>
  @if(!empty($description))
  <p class="text-sm text-slate-600 mb-4">{{ $description }}</p>
  @endif
  <div class="flex items-center gap-2 mb-5">
    @if($product->price_usd > 0)
    <span class="font-bold text-2xl text-navy-900">${{ number_format($product->price_usd, 2) }}</span>
    @endif
    @if($product->compare_price_usd)
    <span class="text-slate-400 line-through">${{ number_format($product->compare_price_usd, 2) }}</span>
    @endif
  </div>
  <a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => $product->handle]) }}" class="btn-primary w-full justify-center mb-3">View Product</a>
  <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="btn-gold-lg w-full justify-center">Buy Now — Free Shipping</a>
</div>
@endif
