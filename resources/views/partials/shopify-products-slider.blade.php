{{-- Shopify products: one row per slide, 3 columns — above Real Results --}}
@php
  $slideCount = count($shopifyProductSlides ?? []);
@endphp

<section
  class="section bg-gradient-to-b from-slate-50 to-white border-y border-slate-100"
  aria-label="{{ __('home.shop_slider_headline') }}"
  @if($slideCount > 0)
    x-data="productSlider({{ $slideCount }})"
  @endif
>
  <div class="container-site">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
      <div class="max-w-2xl">
        <p class="eyebrow mb-2">{{ __('home.shop_slider_eyebrow') }}</p>
        <h2 class="heading-section mb-3">{{ __('home.shop_slider_headline') }}</h2>
        <p class="text-body text-sm md:text-base">{{ __('home.shop_slider_desc') }}</p>
      </div>
      <a href="{{ route('shop.index') }}" class="btn-outline self-start md:self-auto shrink-0">
        {{ __('home.shop_slider_view_all') }}
      </a>
    </div>

    @if(!empty($shopifyProductsError))
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 text-sm mb-6">
      <p>{{ $shopifyProductsError }}</p>
    </div>
    @endif

    @if($slideCount > 0)
    <div class="relative">
      {{-- Slides --}}
      <div class="overflow-hidden">
        @foreach($shopifyProductSlides as $slideIndex => $slideProducts)
        <div
          x-show="current === {{ $slideIndex }}"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 translate-x-4"
          x-transition:enter-end="opacity-100 translate-x-0"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-x-0"
          x-transition:leave-end="opacity-0 -translate-x-4"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
          @if($slideIndex > 0) style="display: none;" @endif
        >
@foreach($slideProducts as $product)
          <article class="card group overflow-hidden flex flex-col h-full animate-on-scroll">
            <a href="{{ route('shop.show', ['id' => $product['id'] ?? ($product['shopify_id'] ?? '')]) }}" class="block relative overflow-hidden">
              @if($product['image'])
              <img
                src="{{ $product['image'] }}"
                alt="{{ $product['title'] }}"
                class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
              >
              @else
              <div class="w-full aspect-[4/3] bg-slate-100 flex items-center justify-center text-slate-400 text-sm">No image</div>
              @endif
              @if(($product['status'] ?? '') === 'active')
              <span class="absolute top-3 left-3 product-badge">{{ ucfirst($product['status']) }}</span>
              @endif
            </a>
            <div class="p-5 flex flex-col flex-1">
<h3 class="heading-card text-base mb-2 line-clamp-2">
            <a href="{{ route('shop.show', ['id' => $product['id'] ?? ($product['shopify_id'] ?? '')]) }}" class="hover:text-navy-700 transition-colors">
                  {{ $product['title'] }}
                </a>
              </h3>
<div class="flex items-baseline gap-2 mb-4 mt-auto">
                @if($product['price'])
                <span class="font-display font-bold text-xl text-navy-900">${{ number_format((float) $product['price'], 2) }}</span>
                @endif
                @if(!empty($product['compare_at']) && (float) $product['compare_at'] > (float) ($product['price'] ?? 0))
                <span class="text-slate-400 line-through text-sm">${{ number_format((float) $product['compare_at'], 2) }}</span>
                @endif
</div>
            <a href="{{ route('shop.show', ['id' => $product['id'] ?? ($product['shopify_id'] ?? '')]) }}" class="btn-primary w-full justify-center text-sm">
                {{ __('home.shop_add_to_cart') }}
              </a>
            </div>
          </article>
          @endforeach

          {{-- Pad incomplete last row to keep 3-column layout --}}
          @for ($pad = count($slideProducts); $pad < 3; $pad++)
          <div class="hidden lg:block" aria-hidden="true"></div>
          @endfor
        </div>
        @endforeach
      </div>

      @if($slideCount > 1)
      <div class="flex items-center justify-center gap-4 mt-8">
        <button
          type="button"
          @click="prev()"
          class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-navy-900 shadow-sm hover:bg-navy-50 transition-colors"
          aria-label="{{ __('home.shop_slider_prev') }}"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <div class="flex gap-2">
          @for ($i = 0; $i < $slideCount; $i++)
          <button
            type="button"
            @click="goTo({{ $i }})"
            class="h-2.5 rounded-full transition-all duration-300"
            :class="current === {{ $i }} ? 'w-8 bg-navy-800' : 'w-2.5 bg-slate-300 hover:bg-slate-400'"
            aria-label="Slide {{ $i + 1 }}"
          ></button>
          @endfor
        </div>

        <button
          type="button"
          @click="next()"
          class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-navy-900 shadow-sm hover:bg-navy-50 transition-colors"
          aria-label="{{ __('home.shop_slider_next') }}"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
      @endif
    </div>
    @else
    <p class="text-center text-slate-500 py-8">{{ __('home.shop_slider_empty') }}</p>
    @endif
  </div>
</section>
