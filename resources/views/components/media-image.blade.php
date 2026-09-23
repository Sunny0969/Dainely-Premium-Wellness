@if($media)
    <img 
        src="{{ $media->url }}" 
        @if($media->srcset) srcset="{{ $media->srcset }}" @endif
        @if($media->width) width="{{ $media->width }}" @endif
        @if($media->height) height="{{ $media->height }}" @endif
        @if($media->responsive_widths) sizes="(max-width: {{ max($media->responsive_widths) }}px) 100vw, {{ max($media->responsive_widths) }}px" @endif
        alt="{{ $alt }}"
        class="{{ $class }}"
        loading="{{ $loading }}"
        @if($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        decoding="async"
    >
@endif