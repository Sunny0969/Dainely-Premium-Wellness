@props(['title' => null, 'content' => null])
@php
    $raw = trim((string) $content);
    $url = $raw;
    // Allow bare YouTube/Vimeo URL or full iframe HTML
    if (! str_contains($raw, '<iframe') && preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w\-]+)~i', $raw, $m)) {
        $url = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (! str_contains($raw, '<iframe') && preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $raw, $m)) {
        $url = 'https://player.vimeo.com/video/' . $m[1];
    }
@endphp
<section class="video-block py-12 bg-slate-50 border-t border-gray-100">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-6 text-center">{{ $title }}</h2>
        @endif
        <div class="relative w-full overflow-hidden rounded-2xl shadow-lg bg-black aspect-video">
            @if(str_contains($raw, '<iframe'))
                <div class="absolute inset-0 [&_iframe]:w-full [&_iframe]:h-full [&_iframe]:absolute [&_iframe]:inset-0">
                    {!! $raw !!}
                </div>
            @elseif($url !== '')
                <iframe
                    src="{{ $url }}"
                    class="absolute inset-0 w-full h-full"
                    title="{{ $title ?: 'Video' }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            @else
                <div class="absolute inset-0 flex items-center justify-center text-white/70 text-sm">
                    Add a YouTube/Vimeo URL in this block’s content.
                </div>
            @endif
        </div>
    </div>
</section>
