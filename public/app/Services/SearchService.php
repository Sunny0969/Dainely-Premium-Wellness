<?php

namespace App\Services;

use App\Jobs\UpdateSearchIndexJob;
use App\Models\Catalog\BlogPost;
use App\Models\Catalog\EducationPage;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\SearchIndex;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2 §10 — full-text search + index maintenance helpers.
 */
class SearchService
{
    /**
     * @return Collection<int, array{title:string,url:string,type:string,rank:float}>
     */
    public function search(string $query, string $locale): Collection
    {
        $query = trim($query);
        if ($query === '' || ! SupabaseDb::available()) {
            return collect();
        }

        $rows = DB::connection('supabase')->table('search_index')
            ->select('*')
            ->selectRaw("ts_rank(tsv, plainto_tsquery('simple', ?)) as rank", [$query])
            ->where('locale', $locale)
            ->whereRaw("tsv @@ plainto_tsquery('simple', ?)", [$query])
            ->orderByDesc('rank')
            ->limit(20)
            ->get();

        $productHandles = Product::whereIn(
            'id',
            $rows->where('searchable_type', Product::class)->pluck('searchable_id')
        )->pluck('handle', 'id');

        $landingSlugs = LandingPage::whereIn(
            'id',
            $rows->where('searchable_type', LandingPage::class)->pluck('searchable_id')
        )->pluck('slug', 'id');

        return $rows->map(fn ($row) => [
            'title' => $row->title,
            'url' => $this->resolveUrl($row, $locale, $productHandles, $landingSlugs),
            'type' => $this->typeLabel((string) $row->searchable_type),
            'rank' => (float) ($row->rank ?? 0),
        ]);
    }

    public function resolveUrl(
        object $row,
        ?string $locale = null,
        ?Collection $productHandles = null,
        ?Collection $landingSlugs = null,
    ): string {
        $locale = $locale ?: ($row->locale ?? app()->getLocale());
        $type = (string) $row->searchable_type;
        $id = (int) $row->searchable_id;

        return match ($type) {
            Product::class => $this->productUrl($id, $locale, $productHandles),
            LandingPage::class => $this->landingUrl($id, $locale, $landingSlugs),
            EducationPage::class, 'education' => $this->educationUrl($id, $locale),
            BlogPost::class, 'blog' => $this->blogUrl($id, $locale),
            default => '#',
        };
    }

    public function typeLabel(string $searchableType): string
    {
        return match ($searchableType) {
            Product::class => 'Product',
            LandingPage::class => 'Landing Page',
            EducationPage::class, 'education' => 'Education',
            BlogPost::class, 'blog' => 'Blog',
            default => class_basename($searchableType),
        };
    }

    public function deindex(string $searchableType, int $searchableId, ?string $locale = null): void
    {
        if (! SupabaseDb::available()) {
            return;
        }

        $q = SearchIndex::query()
            ->where('searchable_type', $searchableType)
            ->where('searchable_id', $searchableId);

        if ($locale) {
            $q->where('locale', $locale);
        }

        $q->delete();
    }

    /**
     * Queue index updates for all locales (or one locale).
     */
    public function queueIndex(string $searchableType, int $searchableId, ?string $locale = null): void
    {
        foreach ($this->locales($locale) as $loc) {
            UpdateSearchIndexJob::dispatch($searchableType, $searchableId, $loc);
        }
    }

    /**
     * Immediate index (used by search:reindex / verify).
     */
    public function indexNow(string $searchableType, int $searchableId, ?string $locale = null): void
    {
        foreach ($this->locales($locale) as $loc) {
            UpdateSearchIndexJob::dispatchSync($searchableType, $searchableId, $loc);
        }
    }

    /**
     * Full rebuild for product, landing, education, blog.
     *
     * @return array{product:int,landing_page:int,education:int,blog:int}
     */
    public function reindexAll(bool $sync = true): array
    {
        $counts = ['product' => 0, 'landing_page' => 0, 'education' => 0, 'blog' => 0];

        if (! SupabaseDb::available()) {
            return $counts;
        }

        $run = $sync
            ? fn (string $type, int $id, ?string $locale = null) => $this->indexNow($type, $id, $locale)
            : fn (string $type, int $id, ?string $locale = null) => $this->queueIndex($type, $id, $locale);

        Product::query()->where('status', 'active')->orderBy('id')->chunkById(50, function ($products) use ($run, &$counts) {
            foreach ($products as $product) {
                $run(Product::class, (int) $product->id);
                $counts['product']++;
            }
        });

        LandingPage::query()->where('published', true)->orderBy('id')->chunkById(50, function ($pages) use ($run, &$counts) {
            foreach ($pages as $page) {
                $run(LandingPage::class, (int) $page->id, $page->locale);
                $counts['landing_page']++;
            }
        });

        foreach (ContentCatalog::educationPages() as $edu) {
            $run(EducationPage::class, (int) $edu['id']);
            $counts['education']++;
        }

        foreach (ContentCatalog::blogPosts() as $post) {
            $run(BlogPost::class, (int) $post['id']);
            $counts['blog']++;
        }

        return $counts;
    }

    /** @return list<string> */
    protected function locales(?string $locale): array
    {
        return $locale ? [$locale] : ['en', 'fr', 'de'];
    }

    protected function productUrl(int $id, string $locale, ?Collection $handles = null): string
    {
        $handle = $handles?->get($id) ?? Product::where('id', $id)->value('handle');

        return $handle
            ? route('products.show', ['locale' => $locale, 'slug' => $handle])
            : '#';
    }

    protected function landingUrl(int $id, string $locale, ?Collection $slugs = null): string
    {
        $slug = $slugs?->get($id);
        if (! $slug) {
            $page = LandingPage::find($id);
            if (! $page) {
                return '#';
            }
            $slug = $page->slug;
        }

        return route('landing.show', ['locale' => $locale, 'slug' => $slug]);
    }

    protected function educationUrl(int $id, string $locale): string
    {
        $page = ContentCatalog::educationById($id);

        return $page ? route($page['route'], ['locale' => $locale]) : '#';
    }

    protected function blogUrl(int $id, string $locale): string
    {
        $post = ContentCatalog::blogById($id);

        return $post
            ? route('blog.show', ['locale' => $locale, 'slug' => $post['slug']])
            : '#';
    }
}
