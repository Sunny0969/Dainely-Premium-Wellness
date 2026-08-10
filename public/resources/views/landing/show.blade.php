@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)

@if($page->canonical_url)
    @section('meta_canonical')
        <link rel="canonical" href="{{ $page->canonical_url }}">
    @endsection
@endif

@push('json-ld')
    @if(isset($schemaJson))
        <script type="application/ld+json">
            {!! $schemaJson !!}
        </script>
    @endif
@endpush

@section('content')
@include('components.breadcrumbs', ['items' => $breadcrumbs ?? []])
<div class="landing-page-container py-12">
    <div class="container-site max-w-4xl mx-auto px-4">
        <h1 class="text-4xl font-extrabold text-navy-900 mb-4 text-center">{{ $page->title }}</h1>
        @if(!empty($offer) && in_array($offer['type'] ?? null, ['product', 'bundle'], true))
            <p class="text-center text-sm text-slate-500 mb-8">
                @if(($offer['type'] ?? null) === 'product' && !empty($offer['product']))
                    Offer: {{ $offer['product']->title }}
                @elseif(($offer['type'] ?? null) === 'bundle' && !empty($offer['bundle']))
                    Bundle: {{ $offer['bundle']->title }}
                @endif
            </p>
        @endif
    </div>

    @foreach($blocks as $block)
        <div class="page-block-wrapper my-6" id="block-{{ $block->id }}">
            @includeIf('components.blocks.' . $block->block_type, [
                'title' => $block->title,
                'content' => $block->content,
                'ctaUrl' => $offer['checkout_url'] ?? null,
                'ctaLabel' => $offer['label'] ?? null,
                'bundleView' => ($bundleViews[$block->id] ?? null),
            ])
        </div>
    @endforeach

    {{-- Sticky / fallback checkout CTA when offer is linked --}}
    @if(!empty($offer['checkout_url']) && in_array($offer['type'] ?? null, ['product', 'bundle'], true))
        <div class="container-site max-w-4xl mx-auto px-4 mt-10 text-center">
            <a href="{{ $offer['checkout_url'] }}"
               class="inline-flex items-center justify-center bg-navy-800 hover:bg-navy-900 text-white font-bold px-8 py-3.5 rounded-xl shadow-md transition">
                {{ $offer['label'] ?? __('Shop Now') }}
            </a>
        </div>
    @endif
</div>

@include('components.related-content', [
    'title' => __('Related Resources'),
    'links' => $relatedLinks ?? [],
])
@endsection
