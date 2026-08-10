{{-- CMS overlays from admin (education page_blocks + faqs) --}}
@php
  $eduBlocks = ($pageBlocks ?? collect())->filter(fn ($b) => (bool) ($b->visible ?? true))->values();
  $eduFaqs = $faqItems ?? collect();
@endphp

@if($eduBlocks->isNotEmpty() || $eduFaqs->isNotEmpty())
<section class="section bg-slate-50" aria-label="Education CMS content">
  <div class="container-site space-y-10">
    @foreach($eduBlocks as $block)
      @php $blockView = 'components.blocks.' . $block->block_type; @endphp
      @if(view()->exists($blockView))
        @include($blockView, ['block' => $block, 'title' => $block->title, 'content' => $block->content])
      @else
        <div class="max-w-3xl">
          @if($block->title)
            <h2 class="font-display text-2xl font-bold text-navy-900 mb-3">{{ $block->title }}</h2>
          @endif
          <div class="cms-richtext prose prose-slate max-w-none text-slate-600">{!! \App\Support\CmsHtml::normalize($block->content) !!}</div>
        </div>
      @endif
    @endforeach

    @if($eduFaqs->isNotEmpty())
      <div class="max-w-3xl">
        <h2 class="font-display text-2xl font-bold text-navy-900 mb-6">FAQ</h2>
        <div class="space-y-4">
          @foreach($eduFaqs as $faq)
            <details class="group bg-white border border-slate-200 rounded-xl px-5 py-4">
              <summary class="font-semibold text-navy-900 cursor-pointer list-none flex justify-between gap-4">
                <span>{{ $faq->question }}</span>
                <span class="text-slate-400 group-open:rotate-180 transition">▾</span>
              </summary>
              <p class="mt-3 text-slate-600 text-sm leading-relaxed">{{ $faq->answer }}</p>
            </details>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</section>
@endif
