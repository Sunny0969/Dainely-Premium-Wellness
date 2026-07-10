@props([
    'cartAddUrl',
    'checkoutUrl' => null,
    'requiresOption' => false,
    'options' => [],
    'optionType' => 'static',
    'optionLabel' => 'Select Option',
    'showSizeGuide' => false,
    'sizeGuideHref' => '#size-guide',
    'addToCartText' => __('products.add_to_cart'),
    'orderNowText' => __('products.order_now'),
    'optionErrorMessage' => __('products.select_option'),
])

<form x-ref="checkoutForm" method="POST" action="{{ $cartAddUrl }}" class="hidden">
  @csrf
  <input type="hidden" name="product_id">
  <input type="hidden" name="title">
  <input type="hidden" name="subtitle">
  <input type="hidden" name="image">
  <input type="hidden" name="price">
  <input type="hidden" name="compare_at_price">
  <input type="hidden" name="quantity">
  <input type="hidden" name="option_label">
  <input type="hidden" name="option_value">
  <input type="hidden" name="variant_id">
  <input type="hidden" name="handle">
  <input type="hidden" name="source">
  <input type="hidden" name="intent" value="add">
</form>

@if($requiresOption && count($options) > 0)
<div
  class="mb-6 rounded-2xl transition-colors duration-200"
  x-ref="optionBlock"
  tabindex="-1"
  :class="optionError ? 'ring-2 ring-red-500 ring-offset-2 bg-red-50/50 p-3 sm:p-4' : ''"
>
  <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
    <label class="form-label mb-0" :class="optionError ? 'text-red-700' : ''">{{ $optionLabel }}</label>
    @if($showSizeGuide)
    <a href="{{ $sizeGuideHref }}" class="text-navy-600 text-sm underline underline-offset-2 hover:text-navy-800 shrink-0">Size Guide</a>
    @endif
  </div>
  <div class="grid grid-cols-1 sm:flex sm:flex-wrap gap-2">
    @if($optionType === 'shopify')
      @foreach($options as $variant)
      @php $optionValue = $loop->index; @endphp
      <button
        type="button"
        @click="selectOption({{ $optionValue }})"
        :class="optionClasses({{ $optionValue }})"
        class="w-full sm:w-auto border-2 font-semibold py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm transition-all duration-200 focus:outline-none text-left whitespace-normal break-words"
      >
        {{ $variant['title'] ?? 'Option' }}
        @if(!empty($variant['price']))
        — ${{ number_format((float) $variant['price'], 2) }}
        @endif
      </button>
      @endforeach
    @else
      @foreach($options as $option)
      @php $optionValue = is_array($option) ? ($option['value'] ?? $option['label']) : $option; @endphp
      <button
        type="button"
        @click="selectOption(@js($optionValue))"
        :class="optionClasses(@js($optionValue))"
        class="w-full sm:w-auto border-2 font-semibold py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm transition-all duration-200 focus:outline-none text-left whitespace-normal break-words"
      >
        {{ is_array($option) ? ($option['label'] ?? $optionValue) : $option }}
      </button>
      @endforeach
    @endif
  </div>
  <p
    x-show="optionError"
    x-cloak
    class="mt-3 text-sm font-medium text-red-600"
    role="alert"
    x-text="optionErrorMessage"
  >{{ $optionErrorMessage }}</p>
</div>
@endif

<div>
  <div class="flex items-center gap-2 sm:gap-4 mb-4">
    <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden flex-shrink-0">
      <button
        type="button"
        @click="decrementQty()"
        class="px-3 sm:px-4 py-2.5 sm:py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-base sm:text-lg"
      >−</button>
      <span class="px-3 sm:px-5 py-2.5 sm:py-3 font-semibold text-navy-900 text-base sm:text-lg border-x-2 border-slate-200 min-w-[2.5rem] sm:min-w-[3rem] text-center" x-text="qty">1</span>
      <button
        type="button"
        @click="incrementQty()"
        class="px-3 sm:px-4 py-2.5 sm:py-3 text-slate-600 hover:text-navy-700 hover:bg-slate-50 transition-colors font-bold text-base sm:text-lg"
      >+</button>
    </div>
    <button
      type="button"
      @click="addToCart($event)"
      :class="purchaseLinkClasses()"
      :disabled="loading"
      class="btn-primary-lg flex-1 justify-center transition-opacity text-xs sm:text-sm min-w-0 px-2 sm:px-8"
    >
      <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-16H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      <span class="sm:hidden">Add to Cart</span>
      <span class="hidden sm:inline">{{ $addToCartText }}</span>
    </button>
  </div>

  <button
    type="button"
    @click="goToCheckout($event)"
    :class="purchaseLinkClasses()"
    :disabled="loading"
    class="btn-gold-lg w-full justify-center mb-6 transition-opacity text-sm sm:text-base"
  >
    {{ $orderNowText }}
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
  </button>
</div>
