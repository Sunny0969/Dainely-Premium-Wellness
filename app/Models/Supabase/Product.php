<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    protected $connection = 'supabase';

    protected $table = 'dainely_products';

    protected $fillable = [
        'shopify_product_id', 'variant_id', 'sku', 'handle',
        'title', 'status', 'price', 'compare_at_price',
        'inventory', 'featured_image', 'synced_at',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'synced_at'        => 'datetime',
    ];

    public function productContents(): HasMany
    {
        return $this->hasMany(ProductContent::class, 'product_id');
    }

    public function knowledgeSignals(): HasMany
    {
        return $this->hasMany(ProductKnowledgeSignal::class, 'product_id');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    public function pageBlocks(): MorphMany
    {
        return $this->morphMany(PageBlock::class, 'blockable');
    }

    /**
     * Boot the model and attach event listeners for indexing.
     */
    protected static function booted()
    {
        static::saved(function ($product) {
            if ($product->status !== 'active') {
                app(\App\Services\SearchService::class)->deindex(self::class, $product->id);
            } else {
                foreach ($product->productContents as $content) {
                    app(\App\Services\SearchService::class)->index(
                        self::class,
                        $product->id,
                        $content->locale,
                        $product->title,
                        $content->overview . ' ' . $content->benefits . ' ' . $content->how_it_works . ' ' . $content->seo_description
                    );
                }
            }
        });

        static::deleted(function ($product) {
            app(\App\Services\SearchService::class)->deindex(self::class, $product->id);
        });
    }
}
