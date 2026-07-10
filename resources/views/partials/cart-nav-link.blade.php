@php
  $count = (int) ($cartItemCount ?? \App\Support\CheckoutCart::itemCount());
  $cartAriaLabel = $count > 0
    ? __('nav.cart_with_count', ['count' => $count])
    : __('nav.cart');
  $badgeLabel = $count > 99 ? '99+' : (string) $count;
  $showLabel = $showLabel ?? true;
@endphp

<a
  href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
  class="relative inline-flex flex-shrink-0 items-center gap-2 rounded-xl px-2.5 py-2 text-slate-700 hover:text-navy-700 hover:bg-navy-50 transition-all {{ $count > 0 ? 'bg-navy-50/80 ring-1 ring-navy-100' : '' }} {{ $class ?? '' }}"
  aria-label="{{ $cartAriaLabel }}"
  data-testid="header-cart-link"
>
  <span class="relative inline-flex items-center justify-center w-9 h-9">
    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
  @if($count > 0)
    <span data-cart-count-wrap class="absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 bg-navy-600 text-white text-[11px] font-bold leading-none rounded-full flex items-center justify-center ring-2 ring-white">
      <span data-cart-count>{{ $badgeLabel }}</span>
    </span>
  @else
    <span data-cart-count-wrap class="hidden absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 bg-navy-600 text-white text-[11px] font-bold leading-none rounded-full items-center justify-center ring-2 ring-white">
      <span data-cart-count>0</span>
    </span>
  @endif
  </span>
  @if($showLabel)
    <span class="hidden md:inline text-sm font-semibold">{{ __('nav.cart') }}</span>
  @endif
</a>
