<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Supabase\LandingPage;
use App\Services\JsonLdBuilder;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $page = LandingPage::query()
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->where('published', true)
            ->firstOrFail();

        $blocks = $page->pageBlocks()
            ->where('locale', $locale)
            ->visible()
            ->orderBy('sort_order')
            ->get();

        $jsonLd = Cache::remember("landing_{$page->id}_{$locale}", 86400, fn() =>
            app(JsonLdBuilder::class)->buildForLandingPage($page, $locale)
        );

        return view('landing.show', [
            'page'       => $page,
            'blocks'     => $blocks,
            'schemaJson' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'locale'     => $locale,
        ]);
    }
}
