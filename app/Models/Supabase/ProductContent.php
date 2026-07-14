<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasLocalizedContent;

class ProductContent extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'product_content';

    protected $fillable = [
        'product_id',
        'locale',
        'overview',
        'benefits',
        'how_it_works',
        'who_is_it_for',
        'specifications',
        'care',
        'seo_title',
        'seo_description',
        'canonical_url',
    ];

    /**
     * Get the product that owns this content.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Boot the model and attach event listeners for indexing.
     */
    protected static function booted()
    {
        static::saved(function ($content) {
            $product = $content->product;
            if ($product && $product->status === 'active') {
                app(\App\Services\SearchService::class)->index(
                    Product::class,
                    $content->product_id,
                    $content->locale,
                    $product->title,
                    $content->overview . ' ' . $content->benefits . ' ' . $content->how_it_works . ' ' . $content->seo_description
                );
            }
        });

        static::deleted(function ($content) {
            SearchIndex::where('searchable_type', Product::class)
                ->where('searchable_id', $content->product_id)
                ->where('locale', $content->locale)
                ->delete();
        });
    }
}
