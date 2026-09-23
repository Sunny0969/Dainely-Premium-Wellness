<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk',
        'storage_path',
        'original_path',
        'filename',
        'mime_type',
        'width',
        'height',
        'alt_text',
        'size_bytes',
        'responsive_widths'
    ];

    protected $casts = [
        'responsive_widths' => 'array',
    ];

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->storage_path);
    }

    public function getSrcsetAttribute()
    {
        if (empty($this->responsive_widths)) {
            return $this->url . ' 100vw';
        }

        $srcsets = [];
        $baseDir = dirname($this->storage_path);
        $basename = pathinfo($this->storage_path, PATHINFO_FILENAME);
        
        foreach ($this->responsive_widths as $width) {
            $path = $baseDir . '/' . $basename . '-' . $width . '.webp';
            $url = Storage::disk($this->disk)->url($path);
            $srcsets[] = $url . ' ' . $width . 'w';
        }

        return implode(', ', $srcsets);
    }
}
