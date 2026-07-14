<?php

namespace App\Services;

use App\Models\Supabase\SearchIndex;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Index or update a model in full-text search.
     */
    public function index(string $searchableType, int $searchableId, string $locale, string $title, string $content): void
    {
        $searchIndex = SearchIndex::updateOrCreate(
            [
                'searchable_type' => $searchableType,
                'searchable_id'   => $searchableId,
                'locale'          => $locale,
            ],
            [
                'title'      => $title,
                'body_plain' => $content,
            ]
        );

        // Update the tsvector column using raw SQL
        DB::connection('supabase')->statement(
            "UPDATE search_index 
             SET tsv = setweight(to_tsvector('english', coalesce(title, '')), 'A') || 
                       setweight(to_tsvector('english', coalesce(body_plain, '')), 'B') 
             WHERE id = ?",
            [$searchIndex->id]
        );
    }

    /**
     * Remove a model from full-text search.
     */
    public function deindex(string $searchableType, int $searchableId): void
    {
        SearchIndex::where('searchable_type', $searchableType)
            ->where('searchable_id', $searchableId)
            ->delete();
    }

    /**
     * Perform full-text search.
     */
    public function search(string $term, ?string $locale = null): \Illuminate\Support\Collection
    {
        $locale = $locale ?? app()->getLocale();

        // Perform Postgres full text search and rank results
        return SearchIndex::localized($locale)
            ->selectRaw("
                searchable_type, 
                searchable_id, 
                title, 
                ts_rank(tsv, plainto_tsquery('english', ?)) as rank
            ", [$term])
            ->whereRaw("tsv @@ plainto_tsquery('english', ?)", [$term])
            ->orderByDesc('rank')
            ->get();
    }
}
