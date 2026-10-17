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
        onerror="this.onerror=null;this.srcset='';this.src='https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=1200&q=80';"
    >
@endif