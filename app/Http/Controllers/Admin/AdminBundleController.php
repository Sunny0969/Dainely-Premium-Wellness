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
                ->with('items.product')
                ->orderByDesc('created_at')
                ->get(),
            collect()
        );

        $products = SupabaseDb::run(
            fn () => Product::query()->select(['id', 'title', 'shopify_product_id'])->where('status', 'active')->orderBy('title')->get(),
            collect()
        );

        return view('admin.bundles.index', compact('bundles', 'products'));
    }

    public function store(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create bundle.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'locale' => 'required|string|size:2',
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'components' => 'nullable|array',
                'components.*.product_id' => 'required_with:components|integer',
                'components.*.quantity' => 'required_with:components|integer|min:1',
            ]);

            $imageArray = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '-' . \Illuminate\Support\Str::slug($validated['title']) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/bundles', $filename, 's3');
                $filename = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
                $imageArray = ['url' => '/images/bundles/' . $filename];
            }

            $bundle = ProductBundle::create([
                'slug' => \Illuminate\Support\Str::slug($validated['title']),
                'bundle_shopify_product_id' => 'gid://shopify/Product/Bundle-' . uniqid(),
                'title' => $validated['title'],
                'locale' => $validated['locale'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'images' => $imageArray,
            ]);

            if (!empty($validated['components'])) {
                foreach ($validated['components'] as $component) {
                    if (!empty($component['product_id'])) {
                        ProductBundleItem::create([
                            'bundle_id' => $bundle->id,
                            'product_id' => $component['product_id'],
                            'quantity' => $component['quantity'] ?? 1,
                        ]);
                    }
                }
            }

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
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'components' => 'nullable|array',
                'components.*.product_id' => 'required_with:components|integer',
                'components.*.quantity' => 'required_with:components|integer|min:1',
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '-' . \Illuminate\Support\Str::slug($validated['title']) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/bundles', $filename, 's3');
                $filename = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
                $validated['images'] = ['url' => '/images/bundles/' . $filename];
            }

            $bundle->update([
                'slug' => \Illuminate\Support\Str::slug($validated['title']),
                'title' => $validated['title'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'images' => $validated['images'] ?? $bundle->images,
            ]);

            // Sync components
            ProductBundleItem::where('bundle_id', $bundle->id)->delete();
            if (!empty($validated['components'])) {
                foreach ($validated['components'] as $component) {
                    if (!empty($component['product_id'])) {
                        ProductBundleItem::create([
                            'bundle_id' => $bundle->id,
                            'product_id' => $component['product_id'],
                            'quantity' => $component['quantity'] ?? 1,
                        ]);
                    }
                }
            }

            $this->forgetAdminCatalogCaches();

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
                'quantity' => 'required|integer|min:1',
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

    public function delete(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete bundle.');
        }

        return SupabaseDb::run(function () use ($id) {
            $bundle = ProductBundle::findOrFail($id);
            ProductBundleItem::where('bundle_id', $bundle->id)->delete();
            $bundle->delete();
            
            $this->forgetAdminCatalogCaches();

            return redirect('/dainely-admin-panel/bundles')->with('success', 'Bundle successfully deleted.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }
}

