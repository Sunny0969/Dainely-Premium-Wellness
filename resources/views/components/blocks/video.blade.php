@props(['title' => null, 'content' => null])
@php
    $raw = trim((string) $content);
    $url = '';
    $isVertical = false;
    
    // Check if the user pasted an iframe and extract the URL
    if (str_contains(strtolower($raw), '<iframe') && preg_match('/src=["\']([^"\']+)["\']/i', $raw, $matches)) {
        $url = $matches[1];
        
        // Check for dimensions in the raw HTML to determine orientation
        if (preg_match('/width=["\']?(\d+)/i', $raw, $w) && preg_match('/height=["\']?(\d+)/i', $raw, $h)) {
            if (intval($h[1]) > intval($w[1])) {
                $isVertical = true;
            }
        }
    } else {
        $url = $raw; // Bare URL fallback
    }
    
    // Normalize YouTube URLs
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([\w\-]+)~i', $url, $m)) {
        $videoId = $m[1];
        $url = 'https://www.youtube.com/embed/' . $videoId . '?controls=1&mute=0&rel=0&modestbranding=1&playsinline=1';
        
        // If the original URL or raw string had "/shorts/", treat as vertical
        if (str_contains(strtolower($raw), '/shorts/')) {
            $isVertical = true;
        }
    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
        $url = 'https://player.vimeo.com/video/' . $m[1];
    }

    // Secondary vertical check (TikTok etc)
    if (str_contains(strtolower($url), 'tiktok.com')) {
        $isVertical = true;
    }
    
    $aspectClass = $isVertical ? 'aspect-[9/16] max-w-sm mx-auto' : 'aspect-video';
@endphp
<section class="video-block py-12 bg-slate-50 border-t border-gray-100">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-6 text-center">{{ $title }}</h2>
        @endif
        <div class="video-block-container relative w-full overflow-hidden rounded-2xl shadow-lg bg-black {{ $aspectClass }}">
            @if($url !== '')
                <iframe
                    src="{{ $url }}"
                    class="absolute inset-0 w-full h-full border-0"
                    title="{{ $title ?: 'Video' }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            @else
                <div class="absolute inset-0 flex items-center justify-center text-white/70 text-sm">
                    Add a valid video URL or iframe code.
                </div>
            @endif
        </div>
    </div>
</section>