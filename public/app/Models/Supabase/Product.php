<?php

namespace App\Models\Supabase;

use App\Contracts\SearchableEntity;
use App\Services\SearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Product extends Model implements SearchableEntity
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

    public function getTranslatedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $content = $this->relationLoaded('productContents')
            ? ($this->productContents->firstWhere('locale', $locale)
                ?? $this->productContents->firstWhere('locale', 'en'))
            : ($this->productContents()->forLocale($locale)->first()
                ?? $this->productContents()->forLocale('en')->first());

        return $content?->seo_title ?: (string) $this->title;
    }

    public function getPlainTextContent(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $content = $this->relationLoaded('productContents')
            ? ($this->productContents->firstWhere('locale', $locale)
                ?? $this->productContents->firstWhere('locale', 'en'))
            : ($this->productContents()->forLocale($locale)->first()
                ?? $this->productContents()->forLocale('en')->first());

        $parts = [
            $this->title,
            $this->handle,
            $this->sku,
            $content?->overview,
            $content?->benefits,
            $content?->how_it_works,
            $content?->who_is_it_for,
            $content?->specifications,
            $content?->care,
            $content?->seo_title,
            $content?->seo_description,
        ];

        return Str::of(implode(' ', array_filter($parts)))
            ->stripTags()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    public function getSearchKeywords(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $parts = array_filter([
            $this->handle,
            $this->sku,
            'product',
            'dainely',
        ]);

        return $parts === [] ? null : implode(',', $parts);
    }

    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            $id = (int) $product->id;
            $active = $product->status === 'active';

            dispatch(function () use ($id, $active) {
                try {
                    $search = app(SearchService::class);
                    if (! $active) {
                        $search->deindex(self::class, $id);

                        return;
                    }
                    $search->queueIndex(self::class, $id);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred product search index failed: '.$e->getMessage());
                }
            })->afterResponse();
        });

        static::deleted(function (Product $product) {
            $id = (int) $product->id;
            dispatch(function () use ($id) {
                try {
                    app(SearchService::class)->deindex(self::class, $id);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred product search deindex failed: '.$e->getMessage());
                }
            })->afterResponse();
        });
    }
}
