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
<div class="landing-page-container py-12">
    <div class="container-site max-w-4xl mx-auto px-4">
        <h1 class="text-4xl font-extrabold text-navy-900 mb-8 text-center">{{ $page->title }}</h1>
    </div>

    {{-- Flexible page blocks rendering --}}
    @foreach($blocks as $block)
        <div class="page-block-wrapper my-6" id="block-{{ $block->id }}">
            @includeIf('components.blocks.' . $block->block_type, [
                'title' => $block->title,
                'content' => $block->content
            ])
        </div>
    @endforeach
</div>
@endsection
