<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BlogPost extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'blog_category_id','featured_image','author_name',
        'author_avatar','author_title','is_published','published_at','related_product_ids',
    ];
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'related_product_ids' => 'array',
    ];
    public function translations() { return $this->hasMany(BlogPostTranslation::class); }
    public function category() { return $this->belongsTo(BlogCategory::class, 'blog_category_id'); }
    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }
    public function resolveFeaturedImage(): string
    {
        $img = $this->featured_image;

        // No image set — return default placeholder
        if (empty($img)) {
            return 'blog-hero-back-pain.jpg';
        }

        // Always return the database value directly.
        // The admin upload saves to public/images/ so the file will exist on production.
        // Only attempt a legacy fallback if the exact file is missing AND a slug-match exists.
        if (!file_exists(public_path('images/' . $img))) {
            $cleanName = preg_replace('/^\d+-/', '', $img);
            $slug = pathinfo($cleanName, PATHINFO_FILENAME);
            if (!empty($slug)) {
                $matches = glob(public_path('images/*' . $slug . '.*'));
                if (!empty($matches)) {
                    return basename($matches[0]);
                }
            }
        }

        return $img;
    }
}
