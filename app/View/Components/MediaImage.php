<?php

namespace App\View\Components;

use App\Models\Media;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class MediaImage extends Component
{
    public ?Media $media;
    public string $alt;
    public string $class;
    public string $loading;
    public ?string $fetchpriority;

    public function __construct(
        $mediaId,
        $alt = '',
        $class = '',
        $loading = 'lazy',
        $fetchpriority = null
    ) {
        if ($mediaId) {
            $this->media = Cache::remember('media_' . $mediaId, 3600, function () use ($mediaId) {
                return Media::find($mediaId);
            });
        } else {
            $this->media = null;
        }

        $this->alt = $alt ?: ($this->media->alt_text ?? '');
        $this->class = $class;
        $this->loading = $loading;
        $this->fetchpriority = $fetchpriority;
    }

    public function render()
    {
        return view('components.media-image');
    }
}