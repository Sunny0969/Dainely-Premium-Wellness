<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'sku','shopify_product_id','price_usd','compare_price_usd',
        'is_active','is_featured','type','sort_order',
        'main_image','gallery_images','video_urls','meta',
    ];
    protected $casts = [
        'price_usd'         => 'decimal:2',
        'compare_price_usd' => 'decimal:2',
        'is_active'         => 'boolean',
        'is_featured'       => 'boolean',
        'gallery_images'    => 'array',
        'video_urls'        => 'array',
        'meta'              => 'array',
    ];
    public function translations() { return $this->hasMany(ProductTranslation::class); }
    public function testimonials() { return $this->hasMany(Testimonial::class); }
    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }
    public function getNameAttribute(): string
    {
        return $this->translation()?->name ?? $this->sku;
    }
    public function getSavingsPercentAttribute(): int
    {
        if (!$this->compare_price_usd || $this->compare_price_usd <= $this->price_usd) return 0;
        return (int) round((($this->compare_price_usd - $this->price_usd) / $this->compare_price_usd) * 100);
    }
}
