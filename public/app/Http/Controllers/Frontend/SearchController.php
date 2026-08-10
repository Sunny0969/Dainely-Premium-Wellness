<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Phase 2 §10.2 — PostgreSQL FTS over search_index.
     */
    public function search(Request $request, SearchService $searchService)
    {
        $q = (string) $request->query('q', '');
        $locale = app()->getLocale();
        $results = trim($q) !== ''
            ? $searchService->search($q, $locale)
            : collect();

        if ($request->wantsJson()) {
            return response()->json($results->values());
        }

        return view('pages.search', [
            'query' => $q,
            'results' => $results,
            'locale' => $locale,
        ]);
    }
}
