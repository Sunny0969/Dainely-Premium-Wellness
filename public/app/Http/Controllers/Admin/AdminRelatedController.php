<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\RelatedContent;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminRelatedController extends AdminController
{
    public function index()
    {
        $this->flashIfSupabaseOffline('Internal links manager');

        $relations = SupabaseDb::run(function () {
            return RelatedContent::query()
                ->select(['id', 'source_type', 'source_id', 'related_type', 'related_id', 'display_order'])
                ->orderBy('source_type')
                ->orderBy('display_order')
                ->get();
        }, collect());

        $products = $this->cachedProductsForSelect(['id', 'title', 'handle', 'shopify_product_id']);
        $landingPages = $this->cachedLandingsForSelect(['id', 'title', 'slug', 'locale']);
        $educationPages = ContentCatalog::educationPages();
        $blogPosts = ContentCatalog::blogPosts();

        return view('admin.related.index', compact(
            'relations',
            'products',
            'landingPages',
            'educationPages',
            'blogPosts'
        ));
    }

    public function store(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create link.');
        }

        return SupabaseDb::run(function () use ($request) {
            $types = 'product,landing_page,education,blog';

            $validated = $request->validate([
                'source_type' => "required|string|in:{$types}",
                'source_id' => 'required|integer|min:1',
                'related_type' => "required|string|in:{$types}",
                'related_id' => 'required|integer|min:1',
                'display_order' => 'required|integer',
            ]);

            if (
                $validated['source_type'] === $validated['related_type']
                && (int) $validated['source_id'] === (int) $validated['related_id']
            ) {
                return back()->with('error', 'Cannot link a resource to itself.');
            }

            if (! $this->entityExists($validated['source_type'], (int) $validated['source_id'])) {
                return back()->with('error', 'Source entity not found.');
            }

            if (! $this->entityExists($validated['related_type'], (int) $validated['related_id'])) {
                return back()->with('error', 'Related entity not found.');
            }

            $exists = RelatedContent::where('source_type', $validated['source_type'])
                ->where('source_id', $validated['source_id'])
                ->where('related_type', $validated['related_type'])
                ->where('related_id', $validated['related_id'])
                ->exists();

            if ($exists) {
                return back()->with('error', 'This content relationship link already exists.');
            }

            RelatedContent::create($validated);

            \App\Support\StorefrontCache::forgetRelated($validated['source_type'], (int) $validated['source_id']);

            return back()->with('success', 'Content relationship link created successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update link.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $rel = RelatedContent::findOrFail($id);

            $validated = $request->validate([
                'display_order' => 'required|integer',
            ]);

            $rel->update($validated);
            \App\Support\StorefrontCache::forgetRelated((string) $rel->source_type, (int) $rel->source_id);

            return back()->with('success', "Link #{$id} order updated.");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function destroy(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot remove link.');
        }

        return SupabaseDb::run(function () use ($id) {
            $rel = RelatedContent::findOrFail($id);
            \App\Support\StorefrontCache::forgetRelated((string) $rel->source_type, (int) $rel->source_id);
            $rel->delete();

            return back()->with('success', 'Content relationship link deleted.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    protected function entityExists(string $type, int $id): bool
    {
        return match ($type) {
            'product' => Product::where('id', $id)->exists(),
            'landing_page' => LandingPage::where('id', $id)->exists(),
            'education' => ContentCatalog::educationById($id) !== null,
            'blog' => ContentCatalog::blogById($id) !== null,
            default => false,
        };
    }
}
