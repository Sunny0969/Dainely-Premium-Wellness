@props(['title' => null, 'content' => null])
@php
    // The band behind this block decides which text colour is safe, so work it out first.
    $hex = preg_replace('/[^0-9a-f]/i', '', (string) ($block->bg_color ?? ''));
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $darkBlock = false;
    if (strlen($hex) === 6) {
        $blockLum = (
            0.2126 * hexdec(substr($hex, 0, 2))
            + 0.7152 * hexdec(substr($hex, 2, 2))
            + 0.0722 * hexdec(substr($hex, 4, 2))
        ) / 255;
        $darkBlock = $blockLum < 0.4;
    }

    $ink = $darkBlock ? '#f8fafc' : '#0f172a';

    // Word paste keeps its sizing/weight but never its typeface, and every
    // text/background pair is pushed to at least 4.5:1 before it ships.
    $html = \App\Support\CmsHtml::ensureContrast(
        \App\Support\CmsHtml::normalize((string) $content, true),
        $darkBlock
    );
@endphp
<section class="rich-block py-12">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold mb-6 text-center" style="color: {{ $ink }}">{{ $title }}</h2>
        @endif
        <div class="cms-richtext landing-richtext{{ $darkBlock ? ' landing-richtext--dark' : '' }}"
             style="color: {{ $ink }}">{!! $html !!}</div>
    </div>
</section>
