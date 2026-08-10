<?php

namespace App\Models\Supabase;

use App\Contracts\SearchableEntity;
use App\Services\SearchService;
use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class LandingPage extends Model implements SearchableEntity
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'landing_pages';

    protected $fillable = [
        'parent_id',
        'slug',
        'locale',
        'title',
        'meta_title',
        'meta_description',
        'canonical_url',
        'shopify_product_id',
        'bundle_id',
        'cta_label',
        'discount_code',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function pageBlocks(): MorphMany
    {
        return $this->morphMany(PageBlock::class, 'blockable')->orderBy('sort_order');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'bundle_id');
    }

    public function getTranslatedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->locale === $locale) {
            return (string) ($this->meta_title ?: $this->title);
        }

        $sibling = static::query()
            ->where('slug', $this->slug)
            ->where('locale', $locale)
            ->first();

        if ($sibling) {
            return (string) ($sibling->meta_title ?: $sibling->title);
        }

        return (string) ($this->meta_title ?: $this->title);
    }

    public function getPlainTextContent(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $page = $this;

        if ($this->locale !== $locale) {
            $sibling = static::query()
                ->with('pageBlocks')
                ->where('slug', $this->slug)
                ->where('locale', $locale)
                ->first();
            $page = $sibling ?: $this;
        }

        if (! $page->relationLoaded('pageBlocks')) {
            $page->load('pageBlocks');
        }

        $blockText = $page->pageBlocks
            ->where('visible', true)
            ->map(function (PageBlock $block) {
                $content = $block->content;
                if (is_array($content) || is_object($content)) {
                    $content = json_encode($content);
                }

                return trim(($block->title ?? '').' '.strip_tags((string) $content));
            })
            ->filter()
            ->implode(' ');

        $parts = [
            $page->title,
            $page->meta_title,
            $page->meta_description,
            $page->cta_label,
            $page->slug,
            $blockText,
        ];

        return Str::of(implode(' ', array_filter($parts)))
            ->stripTags()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    public function getSearchKeywords(?string $locale = null): ?string
    {
        return implode(',', array_filter([
            $this->slug,
            'landing',
            $this->cta_label,
        ]));
    }

    protected static function booted(): void
    {
        static::saved(function (LandingPage $page) {
            $id = (int) $page->id;
            $locale = $page->locale;
            $published = (bool) $page->published;

            dispatch(function () use ($id, $locale, $published) {
                try {
                    $search = app(SearchService::class);
                    if ($published) {
                        $search->queueIndex(self::class, $id, $locale);
                    } else {
                        $search->deindex(self::class, $id, $locale);
                    }
                } catch (\Throwable $e) {
                    logger()->warning('Deferred landing search index failed: '.$e->getMessage());
                }
            })->afterResponse();
        });

        static::deleted(function (LandingPage $page) {
            $id = (int) $page->id;
            dispatch(function () use ($id) {
                try {
                    app(SearchService::class)->deindex(self::class, $id);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred landing search deindex failed: '.$e->getMessage());
                }
            })->afterResponse();
        });
    }
}
