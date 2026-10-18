<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id', 'featured_image', 'author_name',
        'author_avatar', 'author_title', 'is_published', 'published_at', 'related_product_ids',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'related_product_ids' => 'array',
    ];

    public function translations()
    {
        return $this->hasMany(BlogPostTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }

    public function resolveFeaturedImage(): string
    {
        $img = trim((string) $this->featured_image);

        if (empty($img)) {
            return asset('images/blog-hero-back-pain.jpg');
        }

        // Convert media.dainely.com CDN URLs to local or storage proxy to avoid 403
        if (str_contains($img, 'media.dainely.com/dainely-media/')) {
            $subPath = explode('media.dainely.com/dainely-media/', $img)[1] ?? '';
            $subPath = ltrim($subPath, '/');
            if (file_exists(public_path('images/' . $subPath))) {
                return asset('images/' . $subPath);
            }
            return url('/storage/s3/' . $subPath);
        }

        if (\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '//'])) {
            return $img;
        }

        if (\Illuminate\Support\Str::startsWith($img, 'images/')) {
            return asset($img);
        }

        if (file_exists(public_path('images/' . $img))) {
            return asset('images/' . $img);
        }

        if (file_exists(public_path('images/blogs/' . $img))) {
            return asset('images/blogs/' . $img);
        }

        if (file_exists(public_path('blogs/' . $img))) {
            return asset('blogs/' . $img);
        }

        try {
            if (\Illuminate\Support\Facades\Storage::disk('s3')->exists('blogs/' . $img)) {
                return url('/storage/s3/blogs/' . $img);
            }
            if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($img)) {
                return url('/storage/s3/' . $img);
            }
        } catch (\Throwable $e) {}

        // Fallback for mobility-specific posts if file is missing
        if (str_contains($img, 'mobility')) {
            if (file_exists(public_path('images/mobility-edu.png'))) {
                return asset('images/mobility-edu.png');
            }
            if (file_exists(public_path('images/hero-lifestyle.png'))) {
                return asset('images/hero-lifestyle.png');
            }
        }

        return asset('images/blog-hero-back-pain.jpg');
    }
}