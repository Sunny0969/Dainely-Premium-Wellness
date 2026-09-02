<?php

namespace App\Models\Catalog;

use App\Contracts\SearchableEntity;
use App\Models\Supabase\Faq;
use App\Models\Supabase\PageBlock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * DB-backed model for dynamic education pages.
 * Replaces the old ContentCatalog static system.
 */
class EducationPage extends Model implements SearchableEntity
{
    public $timestamps = true;

    protected $table = 'education_pages';

    protected $guarded = [];



    protected $casts = [
        'figures' => 'array',
        'root_causes' => 'array',
        'treatments' => 'array',
        'content_blocks' => 'array',
        'layout_order' => 'array',
        'related_products' => 'array',
        'is_active' => 'boolean',
    ];

    /** Morph alias used in page_blocks / faqs (legacy support). */
    public const MORPH_KEY = self::class;

    public function getTranslatedTitle(?string $locale = null): string
    {
        return $this->title ?? 'Education Page';
    }

    public function getSearchUrl(string $locale): string
    {
        return route('education.show', ['locale' => $locale, 'slug' => $this->slug]);
    }

    public function getSearchTitle(string $locale): string
    {
        return $this->getTranslatedTitle($locale);
    }

    public function getSearchDescription(string $locale): string
    {
        return $this->hero_description ?? '';
    }

    public function getSearchImage(): ?string
    {
        return $this->hero_image;
    }

    public function getPlainTextContent(?string $locale = null): string
    {
        $title = $this->getTranslatedTitle($locale);
        $slug = (string) ($this->slug ?? '');

        return trim(implode(' ', array_filter([
            $title,
            str_replace('-', ' ', $slug),
            'education',
            'dainely wellness',
            $slug,
        ])));
    }

    public function getSearchKeywords(?string $locale = null): ?string
    {
        $slug = (string) ($this->slug ?? '');

        return implode(',', array_filter([
            $slug,
            'education',
            str_replace('-', ' ', $slug),
        ]));
    }

    /**
     * Legacy support for faqs.
     */
    public function faqs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable', 'faqable_type', 'faqable_id');
    }

    /**
     * Legacy support for page blocks.
     */
    public function pageBlocks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(PageBlock::class, 'blockable', 'blockable_type', 'blockable_id')
            ->orderBy('sort_order');
    }
}
