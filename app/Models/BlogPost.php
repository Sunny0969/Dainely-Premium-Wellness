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
}
