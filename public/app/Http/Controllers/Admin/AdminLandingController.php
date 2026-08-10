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
        'benefits',
        'how-it-works',
        'testimonials',
        'faqs',
        'cta',
        'video',
        'comparison',
        'bundle',
    ];

    public function index()
    {
        $this->flashIfSupabaseOffline('Landing pages manager');

        $landings = SupabaseDb::run(
            fn () => LandingPage::query()
                ->select([
                    'id', 'slug', 'locale', 'title', 'published', 'created_at',
                ])
                ->orderByDesc('created_at')
                ->get(),
            collect()
        );

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
                'locale' => 'required|string|size:2',
                'title' => 'required|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:1000',
                'canonical_url' => 'nullable|string|max:255',
                'shopify_product_id' => 'nullable|string|max:255',
                'bundle_id' => 'nullable|integer',
                'cta_label' => 'nullable|string|max:255',
                'discount_code' => 'nullable|string|max:50',
                'published' => 'required|boolean',
            ]);

            LandingPage::create($validated);
            $this->forgetAdminCatalogCaches();

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
                'canonical_url' => 'nullable|string|max:255',
                'shopify_product_id' => 'nullable|string|max:255',
                'bundle_id' => 'nullable|integer',
                'cta_label' => 'nullable|string|max:255',
                'discount_code' => 'nullable|string|max:50',
                'published' => 'required|boolean',
            ]);

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

            $page->update($validated);

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            return redirect('/dainely-admin-panel/landings/'.$id.'/edit')->with('success', 'Landing page updated successfully!');
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
            ]);

            $page->pageBlocks()->create([
                'locale' => $page->locale,
                'block_type' => $validated['block_type'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'sort_order' => $validated['sort_order'],
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
            ]);

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
}
