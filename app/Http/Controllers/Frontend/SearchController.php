<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle search queries using PostgreSQL FTS.
     */
    public function search(Request $request, SearchService $searchService)
    {
        $q = $request->query('q', '');
        $locale = app()->getLocale();
        $results = collect();

        if (trim($q) !== '') {
            $results = $searchService->search($q, $locale);
        }

        $mapped = $results->map(function ($item) use ($locale) {
            // Eager load the polymorphic relation
            $model = $item->searchable;
            if (!$model) {
                return null;
            }

            $url = match (get_class($model)) {
                \App\Models\Supabase\Product::class => route('products.show', ['locale' => $locale, 'slug' => $model->handle]),
                \App\Models\Supabase\LandingPage::class => route('landing.show', ['locale' => $locale, 'slug' => $model->slug]),
                default => '#',
            };

            return [
                'title' => $item->title,
                'url'   => $url,
                'type'  => class_basename($item->searchable_type),
                'rank'  => $item->rank,
            ];
        })->filter();

        if ($request->wantsJson()) {
            return response()->json($mapped->values());
        }

        return view('pages.search', [
            'query'   => $q,
            'results' => $mapped,
            'locale'  => $locale,
        ]);
    }
}
