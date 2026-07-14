<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\PageBlock;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminLandingController extends Controller
{
    public function index()
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. Landing pages manager offline.');
        }

        $landings = SupabaseDb::run(fn() => LandingPage::orderBy('created_at', 'desc')->get(), collect());
        return view('admin.landings.index', compact('landings'));
    }

    public function store(Request $request)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create landing page.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'slug'             => 'required|string|max:255',
                'locale'           => 'required|string|size:2',
                'title'            => 'required|string|max:255',
                'meta_title'       => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:1000',
                'canonical_url'    => 'nullable|string|max:255',
                'published'        => 'required|boolean',
            ]);

            LandingPage::create($validated);

            return back()->with('success', 'Landing page created successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function edit(int $id)
    {
        if (!SupabaseDb::available()) {
            return redirect('/admin/landings')->with('error', 'Database offline. Cannot edit landing page.');
        }

        $page = SupabaseDb::run(fn() => LandingPage::with('pageBlocks')->findOrFail($id));
        if (!$page) {
            return redirect('/admin/landings')->with('error', 'Page not found.');
        }

        return view('admin.landings.edit', compact('page'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update landing page.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $page = LandingPage::findOrFail($id);
            
            $validated = $request->validate([
                'slug'             => 'required|string|max:255',
                'title'            => 'required|string|max:255',
                'meta_title'       => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:1000',
                'canonical_url'    => 'nullable|string|max:255',
                'published'        => 'required|boolean',
            ]);

            $page->update($validated);

            // Invalidate sitemaps and landing page caches
            \Illuminate\Support\Facades\Cache::forget("landing_{$id}_{$page->locale}");

            return redirect('/admin/landings')->with('success', 'Landing page updated successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function addBlock(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add page block.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $page = LandingPage::findOrFail($id);

            $validated = $request->validate([
                'block_type' => 'required|string|in:benefits,how-it-works,testimonials,faqs,cta',
                'title'      => 'nullable|string|max:255',
                'content'    => 'nullable|string',
                'sort_order' => 'required|integer',
            ]);

            $page->pageBlocks()->create([
                'locale'     => $page->locale,
                'block_type' => $validated['block_type'],
                'title'      => $validated['title'],
                'content'    => $validated['content'],
                'sort_order' => $validated['sort_order'],
                'visible'    => true,
            ]);

            \Illuminate\Support\Facades\Cache::forget("landing_{$id}_{$page->locale}");

            return back()->with('success', 'Page block added successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function updateBlock(Request $request, int $id, int $blockId)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update page block.');
        }

        return SupabaseDb::run(function () use ($request, $id, $blockId) {
            $page = LandingPage::findOrFail($id);
            $block = PageBlock::findOrFail($blockId);

            $validated = $request->validate([
                'title'      => 'nullable|string|max:255',
                'content'    => 'nullable|string',
                'sort_order' => 'required|integer',
                'visible'    => 'required|boolean',
            ]);

            $block->update($validated);

            \Illuminate\Support\Facades\Cache::forget("landing_{$id}_{$page->locale}");

            return back()->with('success', 'Page block updated successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function deleteBlock(int $id, int $blockId)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete page block.');
        }

        return SupabaseDb::run(function () use ($id, $blockId) {
            $page = LandingPage::findOrFail($id);
            $block = PageBlock::findOrFail($blockId);
            $block->delete();

            \Illuminate\Support\Facades\Cache::forget("landing_{$id}_{$page->locale}");

            return back()->with('success', 'Page block deleted successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }
}
