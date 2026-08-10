<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog\EducationPage;
use App\Models\Supabase\PageBlock;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

/**
 * Phase 2 §13 — manage page_blocks for education entities (catalog IDs).
 */
class AdminEducationController extends AdminController
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
        $pages = ContentCatalog::educationPages();

        return view('admin.education.index', compact('pages'));
    }

    public function edit(int $id)
    {
        $page = EducationPage::findCatalog($id);
        if (! $page) {
            return redirect('/dainely-admin-panel/education')->with('error', 'Education page not found.');
        }

        if (! $this->flashIfSupabaseOffline('Education blocks')) {
            return redirect('/dainely-admin-panel/education');
        }

        $blocks = $page->pageBlocks();
        $blockTypes = $this->blockTypes;

        return view('admin.education.edit', compact('page', 'blocks', 'blockTypes'));
    }

    public function addBlock(Request $request, int $id)
    {
        if (! EducationPage::findCatalog($id)) {
            return back()->with('error', 'Education page not found.');
        }

        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add block.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $validated = $request->validate([
                'locale' => 'required|string|size:2',
                'block_type' => 'required|string|in:'.implode(',', $this->blockTypes),
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'sort_order' => 'required|integer',
            ]);

            PageBlock::create([
                'blockable_type' => EducationPage::MORPH_KEY,
                'blockable_id' => $id,
                'locale' => $validated['locale'],
                'block_type' => $validated['block_type'],
                'title' => $validated['title'] ?? null,
                'content' => $validated['content'] ?? null,
                'sort_order' => $validated['sort_order'],
                'visible' => true,
            ]);

            \App\Support\StorefrontCache::forgetEducation((int) $id);

            return back()->with('success', 'Education page block added.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function updateBlock(Request $request, int $id, int $blockId)
    {
        if (! EducationPage::findCatalog($id)) {
            return back()->with('error', 'Education page not found.');
        }

        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update block.');
        }

        return SupabaseDb::run(function () use ($request, $id, $blockId) {
            $block = PageBlock::where('blockable_type', EducationPage::MORPH_KEY)
                ->where('blockable_id', $id)
                ->where('id', $blockId)
                ->firstOrFail();

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'sort_order' => 'required|integer',
                'visible' => 'required|boolean',
            ]);

            $block->update($validated);

            \App\Support\StorefrontCache::forgetEducation((int) $id);

            return back()->with('success', 'Education page block updated.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function deleteBlock(int $id, int $blockId)
    {
        if (! EducationPage::findCatalog($id)) {
            return back()->with('error', 'Education page not found.');
        }

        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete block.');
        }

        return SupabaseDb::run(function () use ($id, $blockId) {
            PageBlock::where('blockable_type', EducationPage::MORPH_KEY)
                ->where('blockable_id', $id)
                ->where('id', $blockId)
                ->delete();

            \App\Support\StorefrontCache::forgetEducation((int) $id);

            return back()->with('success', 'Education page block deleted.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }
}
