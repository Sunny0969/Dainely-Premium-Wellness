<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\LandingPage;
use App\Models\Supabase\PageBlock;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductBundle;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminLandingController extends AdminController
{
    protected array $blockTypes = [
        'rich',
        'benefits',
        'how-it-works',
        'testimonials',
        'faqs',
        'cta',
        'video',
        'comparison',
        'bundle',
    ];

    public function index(Request $request)
    {
        $this->flashIfSupabaseOffline('Landing pages manager');

        $landings = SupabaseDb::run(function () use ($request) {
            $query = LandingPage::query()->select(['id', 'slug', 'locale', 'title', 'published', 'created_at']);
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', '%' . $search . '%')
                      ->orWhere('slug', 'ilike', '%' . $search . '%');
                });
            }

            return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20));

        return view('admin.landings.index', compact('landings'));
    }

    public function store(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create landing page.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'slug' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:1000',
                'shopify_product_id' => 'nullable|string|max:255',
                'bundle_id' => 'nullable|integer',
                'cta_label' => 'nullable|string|max:255',
                'discount_code' => 'nullable|string|max:50',
                'published' => 'required|boolean',
            ]);

            // Admin authors in English only — FR/DE are produced by the storefront.
            $validated['locale'] = 'en';
            $validated['canonical_url'] = $this->autoCanonical('en', (string) $validated['slug']);

            $page = LandingPage::create($validated);
            $this->forgetAdminCatalogCaches();

            // Drop any stale full-page snapshot left over from an earlier visit
            // (e.g. the slug previously 404'd or rendered an empty page).
            \App\Support\StorefrontCache::forgetLanding((int) $page->id, 'en', (string) $validated['slug']);

            return back()->with('success', 'Landing page created successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function edit(int $id)
    {
        if (! SupabaseDb::available()) {
            return redirect('/dainely-admin-panel/landings')->with('error', 'Database offline. Cannot edit landing page.');
        }

        $page = SupabaseDb::run(fn () => LandingPage::with('pageBlocks')->findOrFail($id));
        if (! $page) {
            return redirect('/dainely-admin-panel/landings')->with('error', 'Page not found.');
        }

        $products = $this->cachedProductsForSelect(['id', 'title', 'handle', 'shopify_product_id']);
        $bundles = $this->cachedBundlesForSelect(['id', 'title', 'locale']);

        return view('admin.landings.edit', compact('page', 'products', 'bundles'));
    }

    public function update(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update landing page.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $page = LandingPage::findOrFail($id);

            $validated = $request->validate([
                'slug' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:1000',
                'shopify_product_id' => 'nullable|string|max:255',
                'bundle_id' => 'nullable|integer',
                'cta_label' => 'nullable|string|max:255',
                'discount_code' => 'nullable|string|max:50',
                'published' => 'required|boolean',
            ]);

            $validated['canonical_url'] = $this->autoCanonical(
                (string) ($page->locale ?: 'en'),
                (string) $validated['slug']
            );

            // Prefer one offer: if both provided, bundle wins when bundle_id set
            if (! empty($validated['bundle_id'])) {
                $validated['bundle_id'] = (int) $validated['bundle_id'];
            } else {
                $validated['bundle_id'] = null;
            }

            if (empty($validated['shopify_product_id'])) {
                $validated['shopify_product_id'] = null;
            }

            if (empty($validated['discount_code'])) {
                $validated['discount_code'] = null;
            } else {
                $validated['discount_code'] = strtoupper(trim($validated['discount_code']));
            }

            $oldSlug = (string) $page->slug;

            $page->update($validated);

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            // Renamed slug: purge the old URL's HTML snapshot too.
            if ($oldSlug !== '' && $oldSlug !== (string) $page->slug) {
                \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, $oldSlug);
            }

            return back()->with('success', 'Landing page updated successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function addBlock(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add page block.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $page = LandingPage::findOrFail($id);

            $validated = $request->validate([
                'block_type' => 'required|string|in:'.implode(',', $this->blockTypes),
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'sort_order' => 'required|integer',
                'bg_color' => 'nullable|string|max:7',
                'text_color' => 'nullable|string|max:7',
            ]);

            if (!empty($validated['content'])) {
                $validated['content'] = \App\Services\HtmlImageProcessor::processBase64Images($validated['content'], 'landing-pages/' . $page->slug);
            }

            $page->pageBlocks()->create([
                'locale' => $page->locale,
                'block_type' => $validated['block_type'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'sort_order' => $validated['sort_order'],
                'bg_color' => $validated['bg_color'] ?? null,
                'text_color' => $validated['text_color'] ?? null,
                'visible' => true,
            ]);

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            return back()->with('success', 'Page block added successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function updateBlock(Request $request, int $id, int $blockId)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update page block.');
        }

        return SupabaseDb::run(function () use ($request, $id, $blockId) {
            $page = LandingPage::findOrFail($id);
            $block = PageBlock::findOrFail($blockId);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'sort_order' => 'required|integer',
                'visible' => 'required|boolean',
                'bg_color' => 'nullable|string|max:7',
                'text_color' => 'nullable|string|max:7',
            ]);

            if (!empty($validated['content'])) {
                $validated['content'] = \App\Services\HtmlImageProcessor::processBase64Images($validated['content'], 'landing-pages/' . $page->slug);
            }

            $block->update($validated);

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            return back()->with('success', 'Page block updated successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function deleteBlock(int $id, int $blockId)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete page block.');
        }

        return SupabaseDb::run(function () use ($id, $blockId) {
            $page = LandingPage::findOrFail($id);
            $block = PageBlock::findOrFail($blockId);
            $block->delete();

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            return back()->with('success', 'Page block deleted successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function delete(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete landing page.');
        }

        return SupabaseDb::run(function () use ($id) {
            $page = LandingPage::findOrFail($id);
            $page->pageBlocks()->delete();
            $page->delete();

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            return back()->with('success', 'Landing page deleted successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    /**
     * Canonical is derived server-side: {domain}/{locale}/{slug}.
     * Admin never supplies it, so it can never drift from the real URL.
     */
    protected function autoCanonical(string $locale, string $slug): string
    {
        $locale = in_array($locale, ['en', 'fr', 'de'], true) ? $locale : 'en';
        $slug = trim($slug, '/');

        return rtrim(request()->getSchemeAndHttpHost(), '/').'/'.$locale.'/'.$slug;
    }
}



