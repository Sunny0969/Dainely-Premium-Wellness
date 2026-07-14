<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\ProductBundle;
use App\Models\Supabase\ProductBundleItem;
use App\Models\Supabase\Product;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminBundleController extends Controller
{
    public function index()
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. Bundles manager offline.');
        }

        $bundles = SupabaseDb::run(fn() => ProductBundle::orderBy('created_at', 'desc')->get(), collect());
        return view('admin.bundles.index', compact('bundles'));
    }

    public function store(Request $request)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create bundle.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'bundle_shopify_product_id' => 'required|string|unique:product_bundles',
                'locale'                    => 'required|string|size:2',
                'title'                     => 'required|string|max:255',
                'description'               => 'nullable|string|max:1000',
            ]);

            ProductBundle::create($validated);

            return back()->with('success', 'Product bundle created successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function edit(int $id)
    {
        if (!SupabaseDb::available()) {
            return redirect('/admin/bundles')->with('error', 'Database offline. Cannot edit bundle.');
        }

        return SupabaseDb::run(function () use ($id) {
            $bundle = ProductBundle::with('items.product')->findOrFail($id);
            $products = Product::orderBy('title')->get();
            return view('admin.bundles.edit', compact('bundle', 'products'));
        }, redirect('/admin/bundles')->with('error', 'Database query failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update bundle.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $bundle = ProductBundle::findOrFail($id);

            $validated = $request->validate([
                'bundle_shopify_product_id' => 'required|string|unique:product_bundles,bundle_shopify_product_id,' . $id,
                'title'                     => 'required|string|max:255',
                'description'               => 'nullable|string|max:1000',
            ]);

            $bundle->update($validated);

            return redirect('/admin/bundles')->with('success', 'Product bundle updated successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function addItem(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add item to bundle.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity'   => 'required|integer|min:1|max:10',
            ]);

            // Check if product is already in this bundle
            $exists = ProductBundleItem::where('bundle_id', $id)
                ->where('product_id', $validated['product_id'])
                ->exists();

            if ($exists) {
                return back()->with('error', 'This product is already part of the bundle.');
            }

            ProductBundleItem::create([
                'bundle_id'  => $id,
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
            ]);

            return back()->with('success', 'Component product added to bundle successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function deleteItem(int $id, int $itemId)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot remove item.');
        }

        return SupabaseDb::run(function () use ($id, $itemId) {
            $item = ProductBundleItem::where('bundle_id', $id)->findOrFail($itemId);
            $item->delete();

            return back()->with('success', 'Component product removed from bundle.');
        }, back()->with('error', 'Database operation failed.'));
    }
}
