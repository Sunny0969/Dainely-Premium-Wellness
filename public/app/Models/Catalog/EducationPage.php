<?php

namespace App\Models\Catalog;

use App\Contracts\SearchableEntity;
use App\Models\Supabase\Faq;
use App\Models\Supabase\PageBlock;
use App\Support\ContentCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Virtual searchable entity for static education pages (ContentCatalog).
 * Not DB-backed; morph target for page_blocks / faqs via catalog id.
 */
class EducationPage extends Model implements SearchableEntity
{
    public $timestamps = false;

    protected $guarded = [];

    /** Morph alias used in page_blocks / faqs. */
    public const MORPH_KEY = self::class;

    /** @var array{id:int,slug:string,route:string,title:string,titles?:array<string,string>}|null */
    protected ?array $catalog = null;

    public static function findCatalog(int $id): ?self
    {
        $data = ContentCatalog::educationById($id);
        if (! $data) {
            return null;
        }

        $model = new self([
            'id' => $data['id'],
            'slug' => $data['slug'],
            'route' => $data['route'],
            'title' => $data['title'],
        ]);
        $model->exists = true;
        $model->catalog = $data;

        return $model;
    }

    public function getTranslatedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $data = $this->catalog ?? ContentCatalog::educationById((int) $this->id);
        if (! $data) {
            return (string) $this->title;
        }

        return ContentCatalog::educationTitle($data, $locale);
    }

    public function getPlainTextContent(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
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

    public function pageBlocks(?string $locale = null): Collection
    {
        $q = PageBlock::query()
            ->where('blockable_type', self::MORPH_KEY)
            ->where('blockable_id', (int) $this->id)
            ->orderBy('sort_order');

        if ($locale) {
            $q->where('locale', $locale);
        }

        return $q->get();
    }

    public function faqs(?string $locale = null, bool $approvedOnly = false): Collection
    {
        $q = Faq::query()
            ->where('faqable_type', self::MORPH_KEY)
            ->where('faqable_id', (int) $this->id)
            ->orderBy('sort_order');

        if ($locale) {
            $q->where('locale', $locale);
        }

        if ($approvedOnly) {
            $q->approved();
        }

        return $q->get();
    }
}
