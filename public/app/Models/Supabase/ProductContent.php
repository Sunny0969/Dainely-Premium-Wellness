<?php

namespace App\Models\Supabase;

use App\Services\SearchService;
use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ProductContent $content) {
            $product = $content->product;
            if (! $product || $product->status !== 'active') {
                return;
            }

            $productId = (int) $content->product_id;
            $locale = $content->locale;

            // QUEUE_CONNECTION=sync would otherwise block the Admin HTTP response
            // on remote Supabase search_index updates.
            dispatch(function () use ($productId, $locale) {
                try {
                    app(SearchService::class)->queueIndex(Product::class, $productId, $locale);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred product search index failed: '.$e->getMessage());
                }
            })->afterResponse();
        });

        static::deleted(function (ProductContent $content) {
            $productId = (int) $content->product_id;
            $locale = $content->locale;

            dispatch(function () use ($productId, $locale) {
                try {
                    app(SearchService::class)->deindex(Product::class, $productId, $locale);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred product search deindex failed: '.$e->getMessage());
                }
            })->afterResponse();
        });
    }
}
