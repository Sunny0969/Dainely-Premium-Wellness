{{-- Slide-up confirmation after Add to Cart --}}
<div
  x-data
  x-show="$store.cartDrawer.open"
  x-cloak
  @keydown.escape.window="$store.cartDrawer.dismiss()"
  class="fixed inset-0 z-[60] flex items-end sm:items-start sm:justify-end p-0 sm:p-6 pointer-events-none"
  aria-live="polite"
>
  <div
    x-show="$store.cartDrawer.open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="absolute inset-0 bg-navy-900/40 pointer-events-auto"
    @click="$store.cartDrawer.dismiss()"
  ></div>

  <div
    x-show="$store.cartDrawer.open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full opacity-0"
    x-transition:enter-end="translate-y-0 sm:translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 sm:translate-x-0 opacity-100"
    x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full opacity-0"
    class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-100 pointer-events-auto overflow-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cart-drawer-title"
  >
    <div class="flex items-start gap-4 p-5 border-b border-slate-100">
      <div class="w-12 h-12 rounded-full bg-sage-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-6 h-6 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      </div>
      <div class="flex-1 min-w-0">
        <p id="cart-drawer-title" class="font-display font-bold text-navy-900 text-lg">{{ __('products.cart_drawer_title') }}</p>
        <p class="text-slate-600 text-sm mt-1" x-text="$store.cartDrawer.message"></p>
        <p class="text-slate-400 text-xs mt-1">
          <span x-text="$store.cartDrawer.itemCount"></span> {{ __('products.cart_drawer_items_suffix') }}
        </p>
      </div>
      <button type="button" @click="$store.cartDrawer.dismiss()" class="text-slate-400 hover:text-slate-600 p-1" aria-label="{{ __('products.cart_drawer_close') }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="p-5 flex flex-col sm:flex-row gap-3">
      <button type="button" @click="$store.cartDrawer.dismiss()" class="btn-outline w-full justify-center sm:flex-1">
        {{ __('products.continue_shopping') }}
      </button>
      <a :href="$store.cartDrawer.checkoutUrl" class="btn-primary w-full justify-center sm:flex-1">
        {{ __('products.view_cart_checkout') }}
      </a>
    </div>
  </div>
</div>
