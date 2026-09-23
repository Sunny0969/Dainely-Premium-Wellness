@props([
    'title' => null,
    'content' => null,
    'ctaUrl' => null,
    'ctaLabel' => null,
    'block' => null
])
@php
    $url = $ctaUrl ?: route('products.index', ['locale' => app()->getLocale()]);
    $label = $ctaLabel ?: __('Shop Now');
    $bg = $block && $block->bg_color ? 'background-color: ' . $block->bg_color . ';' : '';
    $text = $block && $block->text_color ? 'color: ' . $block->text_color . ';' : '';
    $style = $bg . $text;
@endphp
<section class="cta-block py-16 text-center" style="{{ $style ?: 'background-color: #0f172a; color: #ffffff;' }}">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-4xl font-extrabold mb-4" style="{{$text}}">{{ $title }}</h2>
        @endif
        @if(!empty($content))
            <div class="cms-richtext max-w-none mb-8" style="{{ $text ?: 'color: #cbd5e1;' }}">
                {!! \App\Support\CmsHtml::normalize($content) !!}
            </div>
        @endif
        <div class="flex justify-center gap-4">
            <a href="{{ $url }}" class="btn font-bold px-8 py-3 rounded-lg transition duration-150" style="{{ $block && $block->text_color ? 'background-color: ' . $block->text_color . '; color: ' . ($block->bg_color ?: '#fff') : 'background-color: #fff; color: #0f172a;' }}">
                {{ $label }}
            </a>
        </div>
    </div>
</section>