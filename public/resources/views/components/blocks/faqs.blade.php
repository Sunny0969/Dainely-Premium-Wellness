@props(['title', 'content', 'block' => null])
@php
    $bg = $block && $block->bg_color ? 'background-color: ' . $block->bg_color . ';' : '';
    $text = $block && $block->text_color ? 'color: ' . $block->text_color . ';' : '';
    $style = $bg . $text;
@endphp
<section class="faqs-block py-12" style="{{$style}}">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold mb-8 text-center" style="{{$text}}">{{ $title }}</h2>
        @endif
        <div class="space-y-4 cms-richtext" style="{{$text}}">
            {!! $content !!}
        </div>
    </div>
</section>