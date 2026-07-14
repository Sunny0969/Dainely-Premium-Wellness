<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index()
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. Products manager offline.');
        }

        $products = SupabaseDb::run(fn() => Product::orderBy('title')->get(), collect());
        return view('admin.products.index', compact('products'));
    }

    public function edit(int $id)
    {
        if (!SupabaseDb::available()) {
            return redirect('/admin/products')->with('error', 'Database offline. Cannot edit product.');
        }

        return SupabaseDb::run(function () use ($id) {
            $product = Product::with('pageBlocks')->findOrFail($id);
            
            // Fetch or create the English, French, and German overlays
            $contents = [];
            foreach (['en', 'fr', 'de'] as $locale) {
                $contents[$locale] = ProductContent::firstOrCreate(
                    ['product_id' => $id, 'locale' => $locale],
                    ['seo_title' => $product->title]
                );
            }

            return view('admin.products.edit', compact('product', 'contents'));
        }, redirect('/admin/products')->with('error', 'Database query failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update product.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'contents'                   => 'required|array',
                'contents.*.overview'        => 'nullable|string',
                'contents.*.benefits'        => 'nullable|string',
                'contents.*.how_it_works'    => 'nullable|string',
                'contents.*.who_is_it_for'   => 'nullable|string',
                'contents.*.specifications'  => 'nullable|string',
                'contents.*.care'            => 'nullable|string',
                'contents.*.seo_title'       => 'nullable|string|max:255',
                'contents.*.seo_description' => 'nullable|string|max:1000',
                'contents.*.canonical_url'    => 'nullable|string|max:255',
            ]);

            foreach ($validated['contents'] as $locale => $data) {
                ProductContent::where('product_id', $id)
                    ->where('locale', $locale)
                    ->update($data);

                // Invalidate schema cache and detail cache
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
                \Illuminate\Support\Facades\Cache::forget("json_ld_product_{$id}_{$locale}");
            }

            return redirect('/admin/products')->with('success', 'Product local content overlay updated successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function addBlock(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add block.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'locale'     => 'required|string|size:2',
                'block_type' => 'required|string|in:benefits,how-it-works,testimonials,faqs,cta',
                'title'      => 'nullable|string|max:255',
                'content'    => 'nullable|string',
                'sort_order' => 'required|integer',
            ]);

            $product->pageBlocks()->create([
                'locale'     => $validated['locale'],
                'block_type' => $validated['block_type'],
                'title'      => $validated['title'],
                'content'    => $validated['content'],
                'sort_order' => $validated['sort_order'],
                'visible'    => true,
            ]);

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
            }

            return back()->with('success', 'Product page content block added successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function updateBlock(Request $request, int $id, int $blockId)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update block.');
        }

        return SupabaseDb::run(function () use ($request, $id, $blockId) {
            $product = Product::findOrFail($id);
            $block = \App\Models\Supabase\PageBlock::findOrFail($blockId);

            $validated = $request->validate([
                'title'      => 'nullable|string|max:255',
                'content'    => 'nullable|string',
                'sort_order' => 'required|integer',
                'visible'    => 'required|boolean',
            ]);

            $block->update($validated);

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
            }

            return back()->with('success', 'Product page content block updated successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function deleteBlock(int $id, int $blockId)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete block.');
        }

        return SupabaseDb::run(function () use ($id, $blockId) {
            $product = Product::findOrFail($id);
            $block = \App\Models\Supabase\PageBlock::findOrFail($blockId);
            $block->delete();

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
            }

            return back()->with('success', 'Product page content block deleted successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }
}
