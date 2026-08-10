{{-- Compact Judge.me rating for product cards. Expects $stats from ReviewService. --}}
@php
  $avgRating    = (float) ($stats['average_rating'] ?? 0);
  $totalReviews = (int) ($stats['total_reviews'] ?? 0);
  $fullStars    = (int) floor($avgRating);
  $hasHalf      = ($avgRating - $fullStars) >= 0.3;
  $ratingId     = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($ratingId ?? '0'));
@endphp

@if($totalReviews > 0)
<div class="flex items-center gap-1 mb-2">
  @for($s = 0; $s < 5; $s++)
    @if($s < $fullStars)
    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
    @elseif($hasHalf && $s === $fullStars)
    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 20 20" aria-hidden="true">
      <defs><linearGradient id="halfGrad-{{ $ratingId }}-{{ $s }}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#e2e8f0"/></linearGradient></defs>
      <path fill="url(#halfGrad-{{ $ratingId }}-{{ $s }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
    </svg>
    @else
    <svg class="w-3.5 h-3.5 text-slate-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
    @endif
  @endfor
  <span class="text-slate-500 text-[10px] ml-0.5">{{ number_format($avgRating, 1) }} ({{ number_format($totalReviews) }})</span>
</div>
@endif
