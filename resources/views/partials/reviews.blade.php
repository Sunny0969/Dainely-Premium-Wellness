{{-- ══════════════════════════════════════════════════════════════════════
     REVIEWS PARTIAL — Dynamic Judge.me Reviews
     ──────────────────────────────────────────────────────────────────────
     Expects:
       $reviews     — array of mapped review objects from ReviewService
       $reviewStats — ['average_rating', 'total_reviews', 'rating_breakdown']
     ══════════════════════════════════════════════════════════════════════ --}}

@php
  $defaultBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
  $avgRating       = $reviewStats['average_rating'] ?? 0;
  $totalReviews    = $reviewStats['total_reviews'] ?? 0;
  $breakdown       = $reviewStats['rating_breakdown'] ?? $defaultBreakdown;
  if (! is_array($breakdown) || $breakdown === []) {
    $breakdown = $defaultBreakdown;
  }
  $hasReviews      = $totalReviews > 0 && !empty($reviews);
  $fullStars       = (int) floor($avgRating);
  $hasHalf         = ($avgRating - $fullStars) >= 0.3;
  $maxBar          = max(1, max(array_values($breakdown)));
@endphp

<section id="reviews" class="section bg-section-alt" aria-label="Customer reviews"
         x-data="{
           visibleCount: 6,
           lightboxOpen: false,
           lightboxSrc: '',
           lightboxIsVideo: false,
           openLightbox(src) {
             this.lightboxSrc = src;
             this.lightboxIsVideo = false;
             this.lightboxOpen = true;
             document.body.classList.add('overflow-hidden');
           },
           openVideoLightbox(src) {
             this.lightboxSrc = src;
             this.lightboxIsVideo = true;
             this.lightboxOpen = true;
             document.body.classList.add('overflow-hidden');
             this.$nextTick(() => this.playLightboxVideo());
           },
           playLightboxVideo() {
             const video = this.$refs.lightboxVideo;
             if (!video) return;
             video.load();
             video.play().catch(() => {});
           },
           playInlineReviewVideo(event, src) {
             const frame = event.currentTarget;
             const video = frame.querySelector('video');
             const overlay = frame.querySelector('.review-media-play');
             if (!video) {
               this.openVideoLightbox(src);
               return;
             }
             overlay?.classList.add('hidden');
             video.controls = true;
             video.muted = false;
             video.playsInline = true;
             if (video.paused) {
               video.play().catch(() => this.openVideoLightbox(src));
             }
           },
           closeLightbox() {
             const video = this.$refs.lightboxVideo;
             if (video) {
               video.pause();
               video.currentTime = 0;
             }
             this.lightboxOpen = false;
             this.lightboxSrc = '';
             this.lightboxIsVideo = false;
             document.body.classList.remove('overflow-hidden');
           },
         }">
  <div class="container-site">

    {{-- ── Section Header ──────────────────────────────────────────── --}}
    <div class="text-center mb-10">
      <p class="eyebrow mb-3">Real Customer Experiences</p>
      <h2 class="heading-section mb-4">What Our Customers Say</h2>

      @if($hasReviews)
      <div class="flex items-center justify-center gap-3 mb-6">
        {{-- Stars --}}
        <div class="flex gap-0.5">
          @for ($i = 0; $i < 5; $i++)
            @if($i < $fullStars)
              <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @elseif($hasHalf && $i === $fullStars)
              {{-- Half star --}}
              <svg class="w-5 h-5 text-amber-400" viewBox="0 0 20 20">
                <defs><linearGradient id="halfGrad"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#e2e8f0"/></linearGradient></defs>
                <path fill="url(#halfGrad)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            @else
              <svg class="w-5 h-5 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endif
          @endfor
        </div>
        <span class="text-slate-700 font-bold">{{ $avgRating }} / 5</span>
        <span class="text-slate-400">·</span>
        <span class="text-slate-500 text-sm">{{ number_format($totalReviews) }} verified reviews</span>
      </div>

      {{-- ── Rating Breakdown Bars ───────────────────────────────── --}}
      <div class="max-w-sm mx-auto mb-8">
        @foreach([5, 4, 3, 2, 1] as $star)
          @php 
            $count = $breakdown[$star] ?? $breakdown[(string)$star] ?? 0;
            $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0; 
          @endphp
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-semibold text-slate-500 w-4 text-right">{{ $star }}</span>
            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all duration-700 ease-out {{ $star >= 4 ? 'bg-amber-400' : ($star === 3 ? 'bg-amber-300' : 'bg-slate-300') }}"
                   style="width: {{ $pct }}%"></div>
            </div>
            <span class="text-xs text-slate-400 w-10 text-left">{{ $count }}</span>
          </div>
        @endforeach
      </div>
      @endif
    </div>

    @if($hasReviews)
    {{-- ── Review Cards Grid ───────────────────────────────────────── --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($reviews as $index => $review)
      <div class="testimonial-card"
           x-show="{{ $index }} < visibleCount"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0"
           style="{{ $index >= 6 ? 'display:none;' : '' }}">

        {{-- Star rating + Verified badge --}}
        <div class="flex items-start justify-between mb-3">
          <div class="flex gap-0.5">
            @for ($i = 0; $i < ($review['rating'] ?? 5); $i++)
            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
            @for ($i = ($review['rating'] ?? 5); $i < 5; $i++)
            <svg class="w-4 h-4 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
          </div>
          @if($review['verified'] ?? false)
          <span class="trust-badge text-sage-700 bg-sage-50 border-sage-200 text-[10px]">✓ Verified</span>
          @endif
        </div>

        {{-- Review title --}}
        @if(!empty($review['title']))
        <h4 class="font-semibold text-slate-800 text-sm mb-2">{{ $review['title'] }}</h4>
        @endif

        {{-- Review body --}}
        <p class="text-slate-700 text-sm leading-relaxed mb-4">{{ Str::limit($review['body'] ?? '', 220) }}</p>

        {{-- Media (photos & videos) — full card width, taller frame --}}
        @if(!empty($review['pictures']) || !empty($review['videos']))
        <div class="review-media-list">
          {{-- Photos --}}
          @if(!empty($review['pictures']))
            @foreach($review['pictures'] as $pic)
            <button @click="openLightbox(@js($pic['original'] ?: $pic['thumb']))"
                    type="button"
                    class="review-media-frame">
              <img
                src="{{ $pic['original'] ?: $pic['thumb'] }}"
                alt="Customer photo"
                loading="lazy"
                decoding="async"
              >
            </button>
            @endforeach
          @endif

          {{-- Videos --}}
          @if(!empty($review['videos']))
            @foreach($review['videos'] as $vid)
            @php $videoSrc = $vid['mp4'] ?: ($vid['url'] ?? ''); @endphp
            @if($videoSrc)
            <button @click.stop="playInlineReviewVideo($event, @js($videoSrc))"
                    type="button"
                    class="review-media-frame review-media-frame--video bg-black">
              <video src="{{ $videoSrc }}" muted preload="metadata" playsinline webkit-playsinline></video>
              <div class="review-media-play" aria-hidden="true">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M4.25 5.614L12.5 10l-8.25 4.386V5.614z"/></svg>
              </div>
            </button>
            @endif
            @endforeach
          @endif
        </div>
        @endif

        {{-- Reviewer info + date --}}
        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
          {{-- Avatar placeholder (initials) --}}
          @php
            $initials = collect(explode(' ', $review['reviewer_name'] ?? 'A'))
                        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->take(2)->join('');
          @endphp
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-navy-100 to-navy-200 flex items-center justify-center ring-2 ring-slate-100 flex-shrink-0">
            <span class="text-navy-700 font-bold text-xs">{{ $initials }}</span>
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-slate-800 text-sm truncate">{{ $review['reviewer_name'] ?? 'Anonymous' }}</p>
            <p class="text-slate-400 text-xs">{{ $review['time_ago'] ?? '' }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- ── Load More Button ──────────────────────────────────────── --}}
    @if(count($reviews) > 6)
    <div class="text-center mt-8" x-show="visibleCount < {{ count($reviews) }}">
      <button @click="visibleCount += 6"
              class="btn-outline border-slate-300 text-slate-700 hover:bg-navy-700 hover:text-white hover:border-navy-700">
        Load More Reviews
        <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
    </div>
    @endif

    @else
    {{-- ── Empty State ───────────────────────────────────────────── --}}
    <div class="text-center py-16">
      <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-700 mb-2">No reviews yet</h3>
      <p class="text-slate-500 text-sm">Be the first to share your experience with this product.</p>
    </div>
    @endif

    {{-- ── Media Lightbox Overlay ────────────────────────────────── --}}
    <div x-show="lightboxOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeLightbox()"
         @keydown.escape.window="closeLightbox()"
         class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
      <button type="button"
              @click="closeLightbox()"
              class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>

      <video
        x-show="lightboxIsVideo"
        x-ref="lightboxVideo"
        :src="lightboxSrc"
        :key="lightboxSrc"
        controls
        playsinline
        webkit-playsinline
        class="max-w-full max-h-[85vh] w-full rounded-2xl shadow-2xl bg-black"
        @click.stop
      ></video>
      <img
        x-show="!lightboxIsVideo && lightboxSrc"
        :src="lightboxSrc"
        alt="Customer review photo"
        class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl object-contain"
        @click.stop
      >
    </div>

  </div>
</section>
