<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\Product;
use App\Models\Supabase\ProductBundle;
use App\Models\Supabase\ProductBundleItem;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBundleController extends AdminController
{
    public function index()
    {
        $this->flashIfSupabaseOffline('Bundles manager');

        $bundles = SupabaseDb::run(
            fn () => ProductBundle::query()
                ->select(['id', 'title', 'locale', 'description', 'bundle_shopify_product_id', 'created_at'])
                ->orderByDesc('created_at')
                ->get(),
            collect()
        );

        return view('admin.bundles.index', compact('bundles'));
    }

    public function store(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create bundle.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'bundle_shopify_product_id' => [
                    'required',
                    'string',
                    Rule::unique('product_bundles', 'bundle_shopify_product_id')->connection('supabase'),
                ],
                'locale' => 'required|string|size:2',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            ProductBundle::create($validated);
            $this->forgetAdminCatalogCaches();

            return back()->with('success', 'Product bundle created successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function edit(int $id)
    {
        if (! SupabaseDb::available()) {
            return redirect('/dainely-admin-panel/bundles')->with('error', 'Database offline. Cannot edit bundle.');
        }

        return SupabaseDb::run(function () use ($id) {
            $bundle = ProductBundle::with('items.product')->findOrFail($id);
            $products = $this->cachedProductsForSelect(['id', 'title', 'handle', 'shopify_product_id']);

            return view('admin.bundles.edit', compact('bundle', 'products'));
        }, fn () => redirect('/dainely-admin-panel/bundles')->with('error', 'Database query failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update bundle.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $bundle = ProductBundle::findOrFail($id);

            $validated = $request->validate([
                'bundle_shopify_product_id' => [
                    'required',
                    'string',
                    Rule::unique('product_bundles', 'bundle_shopify_product_id')
                        ->connection('supabase')
                        ->ignore($id),
                ],
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $bundle->update($validated);

            return redirect('/dainely-admin-panel/bundles')->with('success', 'Product bundle updated successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function addItem(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add item to bundle.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $validated = $request->validate([
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1|max:10',
            ]);

            if (! Product::where('id', $validated['product_id'])->exists()) {
                return back()->with('error', 'Selected product was not found in catalog (dainely_products).');
            }

            $exists = ProductBundleItem::where('bundle_id', $id)
                ->where('product_id', $validated['product_id'])
                ->exists();

            if ($exists) {
                return back()->with('error', 'This product is already part of the bundle.');
            }

            ProductBundleItem::create([
                'bundle_id' => $id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);

            return back()->with('success', 'Component product added to bundle successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function deleteItem(int $id, int $itemId)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot remove item.');
        }

        return SupabaseDb::run(function () use ($id, $itemId) {
            $item = ProductBundleItem::where('bundle_id', $id)->findOrFail($itemId);
            $item->delete();

            return back()->with('success', 'Component product removed from bundle.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }
}
