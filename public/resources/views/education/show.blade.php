@extends('layouts.app')
@section('title', $page->title)

@section('content')
<main class="min-h-screen bg-slate-50">

  {{-- Hero Section --}}
  @if(!empty($page->hero_title))
  <section class="bg-navy-900 text-white py-16 md:py-24">
    <div class="container-site grid md:grid-cols-2 gap-12 items-center">
      <div>
        <div class="text-gold-500 font-bold text-sm tracking-wider uppercase mb-4">MEDICAL EDUCATION SERVICE</div>
        <h1 class="font-display text-white text-4xl md:text-5xl font-bold mb-6">{{ $page->hero_title }}</h1>
        
        @if(!empty($page->hero_description))
        <div class="cms-richtext text-lg text-slate-300 mb-8 leading-relaxed max-w-none">{!! $page->hero_description !!}</div>
        @endif
        
        @if(!empty($page->author_name))
        <div class="flex items-center gap-4 mt-8 pt-8 border-t border-navy-800">
          @if(!empty($page->author_image))
          <img src="{{ Str::startsWith($page->author_image, ['http://', 'https://']) ? $page->author_image : asset('images/' . $page->author_image) }}" alt="{{ $page->author_name }}" class="w-12 h-12 rounded-full object-cover">
          @endif
          <div>
            <div class="font-bold">{{ $page->author_name }}</div>
            <div class="text-slate-400 text-sm">{{ $page->author_role ?? '' }} ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ {{ $page->read_time ?? '' }}</div>
          </div>
        </div>
        @endif
      </div>
      
      @if(!empty($page->hero_image))
      <div class="rounded-2xl overflow-hidden relative w-full flex items-center justify-center" style="min-height: 400px;">
        <img src="{{ Str::startsWith($page->hero_image, ['http://', 'https://']) ? $page->hero_image : asset('images/' . $page->hero_image) }}" alt="{{ $page->hero_title }}" class="absolute inset-0 w-full h-full object-contain">
      </div>
      @endif
    </div>
  </section>
  @endif

  @php
      $defaultOrder = ['figures', 'content_blocks', 'root_causes', 'treatments'];
      $layoutOrder = is_array($page->layout_order) && count($page->layout_order) > 0 ? $page->layout_order : $defaultOrder;
  @endphp

  @foreach($layoutOrder as $sectionName)
  
    {{-- Figures Section --}}
    @if($sectionName === 'figures' && !empty($page->figures) && is_array($page->figures))
    <section class="py-12 bg-white border-b border-slate-100">
      <div class="container-site">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-left divide-x divide-slate-100">
          @foreach($page->figures as $figure)
          <div class="px-4">
            <div class="text-4xl font-display font-bold text-navy-900 mb-2">{{ $figure['value'] ?? '' }}</div>
            <div class="cms-richtext max-w-none text-slate-500 text-sm leading-relaxed">{!! $figure['label'] ?? '' !!}</div>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    {{-- Content Blocks (e.g. What is Nerve Discomfort) --}}
    @if($sectionName === 'content_blocks' && !empty($page->content_blocks) && is_array($page->content_blocks))
    <section class="py-16 container-site max-w-3xl">
      @foreach($page->content_blocks as $block)
      <div class="mb-12 last:mb-0">
        <h2 class="text-3xl font-display font-bold text-navy-900 mb-6">{{ $block['title'] ?? '' }}</h2>
        <div class="cms-richtext text-lg max-w-none text-slate-600">
          {!! $block['content'] ?? '' !!}
        </div>
      </div>
      @endforeach
    </section>
    @endif

    {{-- Root Causes Section --}}
    @if($sectionName === 'root_causes' && !empty($page->root_causes) && is_array($page->root_causes))
    <section class="py-16 bg-slate-50">
      <div class="container-site max-w-4xl">
        <h2 class="text-3xl font-display font-bold text-navy-900 mb-10">{{ $page->root_causes_title ?? 'Root Causes' }}</h2>
        <div class="space-y-4">
          @foreach($page->root_causes as $cause)
          <div class="bg-indigo-50/50 rounded-xl p-6 border-l-4 border-indigo-500">
            <h3 class="font-bold text-navy-900 text-xl mb-3">{{ $cause['title'] ?? '' }}</h3>
            <div class="cms-richtext max-w-none text-slate-600 leading-relaxed">{!! $cause['description'] ?? '' !!}</div>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    {{-- Treatments Section --}}
    @if($sectionName === 'treatments' && !empty($page->treatments) && is_array($page->treatments))
    <section class="py-16 bg-white">
      <div class="container-site max-w-3xl">
        <h2 class="text-3xl font-display font-bold text-navy-900 mb-6">{{ $page->treatments_title ?? 'Evidence-Based Treatments' }}</h2>
        
        @if(!empty($page->treatments_description))
        <div class="cms-richtext text-lg max-w-none text-slate-600 mb-8">{!! $page->treatments_description !!}</div>
        @endif

        <ul class="space-y-4">
          @foreach($page->treatments as $treatment)
          <li class="flex items-start gap-4">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2.5 shrink-0"></div>
            <div class="cms-richtext text-lg max-w-none text-slate-700 leading-relaxed">
              {!! $treatment !!}
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </section>
    @endif

  @endforeach

  {{-- Related Products Section --}}
  @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
  <section class="py-16 bg-slate-50 border-t border-slate-100">
    <div class="container-site max-w-6xl">
      <h2 class="text-3xl font-display font-bold text-navy-900 mb-8 text-center">Recommended For You</h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($relatedProducts as $product)
          @php
            $productUrl = route('products.show', ['locale' => $locale, 'slug' => $product->handle ?? $product->id]);
            $price = $product->price ?? 0;
            $comparePrice = $product->compare_at_price ?? null;
            $image = $product->featured_image ?? null;
          @endphp
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-navy-200 transition-all flex flex-col overflow-hidden group">
            <a href="{{ $productUrl }}" class="block aspect-[4/3] bg-slate-100 relative overflow-hidden">
              @if($image)
                <img src="{{ Str::startsWith($image, ['http://', 'https://']) ? $image : asset('images/' . $image) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              @else
                <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                  <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
              @endif
            </a>
            <div class="p-5 flex flex-col flex-grow">
              <a href="{{ $productUrl }}" class="font-bold text-lg text-navy-900 group-hover:text-navy-600 transition-colors mb-2 line-clamp-2">{{ $product->title }}</a>
              <div class="flex items-center gap-2 mt-auto pt-4">
                <span class="font-display font-bold text-xl text-navy-900">${{ number_format((float)$price, 2) }}</span>
                @if($comparePrice && $comparePrice > $price)
                  <span class="text-slate-400 line-through text-sm">${{ number_format((float)$comparePrice, 2) }}</span>
                @endif
              </div>
              <a href="{{ $productUrl }}" class="mt-4 w-full bg-white border border-slate-300 text-slate-700 hover:bg-navy-900 hover:text-white hover:border-navy-900 text-center font-bold text-sm py-2.5 rounded-xl transition-colors">
                View Details
              </a>
            </div>
          </div>
        @endforeach
      </div>
      
    </div>
  </section>
  @endif

  {{-- Legacy Page Blocks (if any) --}}
  @if($pageBlocks->isNotEmpty())
    @foreach($pageBlocks as $block)
      @include("blocks.{$block->block_type}", ['block' => $block])
    @endforeach
  @endif

</main>
@endsection
