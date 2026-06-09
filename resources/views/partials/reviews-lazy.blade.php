{{-- Lazy-loaded reviews — fetched via AJAX when section enters viewport --}}
@php
  $reviewsHandle = $handle ?? ($product['handle'] ?? '');
  $reviewsUrl = route('products.reviews', ['locale' => app()->getLocale(), 'handle' => $reviewsHandle]);
@endphp

<div
  x-data="lazyReviews(@js($reviewsUrl))"
  x-init="init()"
  class="min-h-[200px]"
  data-reviews-lazy
>
  <template x-if="loading">
    <section class="section bg-section-alt" aria-label="Loading reviews">
      <div class="container-site py-16 text-center">
        <div class="inline-flex flex-col items-center gap-4">
          <div class="w-10 h-10 border-2 border-navy-200 border-t-navy-600 rounded-full animate-spin"></div>
          <p class="text-slate-500 text-sm">Loading customer reviews…</p>
        </div>
      </div>
    </section>
  </template>

  <template x-if="error && !loading">
    <section class="section bg-section-alt" aria-label="Reviews unavailable">
      <div class="container-site py-12 text-center">
        <p class="text-slate-500 text-sm">Reviews could not be loaded. Please refresh the page.</p>
      </div>
    </section>
  </template>

  <div x-show="loaded && !loading" x-ref="content" x-html="html"></div>
</div>
