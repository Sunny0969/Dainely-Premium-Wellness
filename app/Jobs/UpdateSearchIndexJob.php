<?php

namespace App\Jobs;

use App\Contracts\SearchableEntity;
use App\Models\Catalog\BlogPost;
use App\Models\Catalog\EducationPage;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\SearchIndex;
use App\Support\SupabaseDb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Phase 2 §10.1 — when any searchable entity is created/updated, upsert search_index.
 */
class UpdateSearchIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $searchableType,
        public int $searchableId,
        public string $locale,
    ) {}

    public static function forModel(Model $searchable, string $locale): self
    {
        return new self(get_class($searchable), (int) $searchable->getKey(), $locale);
    }

    public function handle(): void
    {
        if (! SupabaseDb::available()) {
            Log::warning('UpdateSearchIndexJob skipped: Supabase unavailable', [
                'type' => $this->searchableType,
                'id' => $this->searchableId,
            ]);

            return;
        }

        $searchable = $this->resolveSearchable();

        if (! $searchable instanceof SearchableEntity || ! $this->shouldIndex($searchable)) {
            SearchIndex::query()
                ->where('searchable_type', $this->searchableType)
                ->where('searchable_id', $this->searchableId)
                ->where('locale', $this->locale)
                ->delete();

            return;
        }

        $data = [
            'title' => $searchable->getTranslatedTitle($this->locale),
            'body_plain' => $searchable->getPlainTextContent($this->locale),
            'keywords' => $searchable->getSearchKeywords($this->locale),
            'locale' => $this->locale,
        ];

        $row = SearchIndex::updateOrCreate(
            [
                'searchable_type' => $this->searchableType,
                'searchable_id' => $this->searchableId,
                'locale' => $this->locale,
            ],
            $data
        );

        // Phase 2: simple config for all languages (docs §10.2 note).
        DB::connection('supabase')->statement(
            "UPDATE search_index
             SET tsv = setweight(to_tsvector('simple', coalesce(title, '')), 'A')
                      || setweight(to_tsvector('simple', coalesce(body_plain, '')), 'B')
                      || setweight(to_tsvector('simple', coalesce(keywords, '')), 'C')
             WHERE id = ?",
            [$row->id]
        );
    }

    protected function resolveSearchable(): ?Model
    {
        return match ($this->searchableType) {
            Product::class => Product::with('productContents')->find($this->searchableId),
            LandingPage::class => LandingPage::with('pageBlocks')->find($this->searchableId),
            EducationPage::class, 'education' => EducationPage::findCatalog($this->searchableId),
            BlogPost::class, 'blog' => BlogPost::findCatalog($this->searchableId),
            default => null,
        };
    }

    protected function shouldIndex(Model $searchable): bool
    {
        if ($searchable instanceof Product) {
            return $searchable->status === 'active';
        }

        if ($searchable instanceof LandingPage) {
            return (bool) $searchable->published;
        }

        return true;
    }

    public function failed(?Throwable $e): void
    {
        Log::error('UpdateSearchIndexJob failed', [
            'type' => $this->searchableType,
            'id' => $this->searchableId,
            'locale' => $this->locale,
            'error' => $e?->getMessage(),
        ]);
    }
}
