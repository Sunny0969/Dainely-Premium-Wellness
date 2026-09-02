<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Services\ContentTranslationService;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminProductController extends AdminController
{
    public function index()
    {
        $this->flashIfSupabaseOffline('Products manager');

        $products = $this->cachedProductsForSelect([
            'id', 'title', 'handle', 'sku', 'price', 'status', 'featured_image',
        ]);

        return view('admin.products.index', compact('products'));
    }

    public function edit(int $id)
    {
        if (!SupabaseDb::available()) {
            return redirect('/dainely-admin-panel/products')->with('error', 'Database offline. Cannot edit product.');
        }

        return SupabaseDb::run(function () use ($id) {
            $product = Product::with('pageBlocks')->findOrFail($id);

            // One query for existing overlays, then create only missing locales.
            $existing = ProductContent::where('product_id', $id)
                ->whereIn('locale', ['en', 'fr', 'de'])
                ->get()
                ->keyBy('locale');

            $contents = [];
            foreach (['en', 'fr', 'de'] as $locale) {
                $contents[$locale] = $existing->get($locale) ?? ProductContent::create([
                    'product_id' => $id,
                    'locale' => $locale,
                ]);
            }

            $globalBlocks = \App\Models\Supabase\PageBlock::where('blockable_type', Product::class)
                ->where('is_global', true)
                ->where('locale', 'en')
                ->orderBy('sort_order')
                ->get();

            $langKey = \App\Support\ProductLandingLang::langKeyForHandle($product->handle);
            $t = fn($k) => \App\Support\ProductLandingLang::line($langKey, $k, [], 'en');

            $pageHeadings = [
                'hero'      => $t('hero_headline') ?: 'Hero Section',
                'details'   => 'Product Details (Grid)',
                'lifestyle' => $t('lifestyle_title') ?: 'Lifestyle Section',
                'how'       => $t('how_title') ?: 'How to Use',
                'science'   => $t('science_title') ?: 'Science & Materials',
                'reviews'   => 'Customer Reviews',
                'faq'       => 'Frequently Asked Questions',
                'cta'       => $t('cta_title') ?: 'Final CTA',
            ];

            return view('admin.products.edit', compact('product', 'contents', 'globalBlocks', 'pageHeadings'));
        }, fn () => redirect('/dainely-admin-panel/products')->with('error', 'Database query failed.'));
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

            // Quiet model events during bulk update — search index runs once after response.
            ProductContent::withoutEvents(function () use ($validated, $id) {
                $richKeys = ['overview', 'benefits', 'how_it_works', 'who_is_it_for', 'specifications', 'care'];

                foreach ($validated['contents'] as $locale => $data) {
                    foreach ($data as $key => $value) {
                        if (is_string($value) && trim($value) === '') {
                            $data[$key] = null;
                            continue;
                        }

                        // Allow HTML formatting but never store embedded images.
                        // Normalize empty TinyMCE paragraphs so spacing stays consistent on the site.
                        if (is_string($value) && in_array($key, $richKeys, true)) {
                            $data[$key] = \App\Support\CmsHtml::normalize($value);
                            if ($data[$key] === '') {
                                $data[$key] = null;
                            }
                        }
                    }

                    ProductContent::where('product_id', $id)
                        ->where('locale', $locale)
                        ->update($data);

                    \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
                    \Illuminate\Support\Facades\Cache::forget("json_ld_product_{$id}_{$locale}");
                }
            });

            $this->forgetStorefrontOverlayCache($product);
            $this->forgetAdminCatalogCaches();

            $productId = (int) $id;
            dispatch(function () use ($productId) {
                try {
                    app(\App\Services\SearchService::class)->queueIndex(Product::class, $productId);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred search reindex failed: '.$e->getMessage());
                }
            })->afterResponse();

            return redirect('/dainely-admin-panel/products')->with('success', 'Product local content overlay updated successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    /**
     * Copy English overlay fields into FR + DE via translation API.
     * Keeps all three locales separately editable afterward.
     */
    public function translateFromEnglish(Request $request, int $id, ContentTranslationService $translator)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot translate.');
        }

        // Rich HTML × FR/DE can still take a bit; don't die mid-run on default 60s.
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        $validated = $request->validate([
            'overwrite' => 'nullable|boolean',
        ]);
        $overwrite = $request->boolean('overwrite');

        $started = microtime(true);

        return SupabaseDb::run(function () use ($id, $translator, $overwrite, $started) {
            $product = Product::findOrFail($id);

            $en = ProductContent::where('product_id', $id)->where('locale', 'en')->first();
            if (! $en) {
                return back()->with('error', 'English content row not found. Save English content first.');
            }

            $keys = [
                'overview', 'benefits', 'how_it_works', 'who_is_it_for',
                'specifications', 'care', 'seo_title', 'seo_description',
            ];

            $source = [];
            foreach ($keys as $key) {
                $source[$key] = $en->{$key};
            }

            if (collect($source)->filter(fn ($v) => is_string($v) && trim(strip_tags($v)) !== '')->isEmpty()) {
                return back()->with('error', 'English fields are empty. Write English content first, then translate.');
            }

            $translated = $translator->translateFields($source, ['fr', 'de']);
            $filled = 0;

            foreach (['fr', 'de'] as $locale) {
                $row = ProductContent::firstOrCreate(
                    ['product_id' => $id, 'locale' => $locale]
                );

                $payload = [];
                foreach ($keys as $key) {
                    $newVal = $translated[$locale][$key] ?? null;
                    if ($newVal === null || (is_string($newVal) && trim(strip_tags($newVal)) === '')) {
                        continue;
                    }

                    $existing = $row->{$key};
                    $isEmpty = ! is_string($existing) || trim(strip_tags($existing)) === '';

                    if ($overwrite || $isEmpty) {
                        if (in_array($key, ['overview', 'benefits', 'how_it_works', 'who_is_it_for', 'specifications', 'care'], true)) {
                            $newVal = \App\Support\CmsHtml::normalize($newVal);
                        }
                        $payload[$key] = $newVal;
                        $filled++;
                    }
                }

                if ($payload !== []) {
                    $row->update($payload);
                }
            }

            $this->forgetStorefrontOverlayCache($product);
            $this->forgetAdminCatalogCaches();

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
                \Illuminate\Support\Facades\Cache::forget("json_ld_product_{$id}_{$locale}");
                \Illuminate\Support\Facades\Cache::forget("storefront.pdp_overlay.{$product->handle}.{$locale}");
            }

            if ($filled === 0) {
                return back()->with(
                    'error',
                    'Nothing translated. French/German fields already have content — enable “Overwrite existing” or clear those fields first.'
                );
            }

            $seconds = max(1, (int) round(microtime(true) - $started));

            return redirect("/dainely-admin-panel/products/{$id}/edit")
                ->with('success', "Translated {$filled} field(s) into French & German in ~{$seconds}s. You can still edit each language in the tabs.");
        }, fn () => back()->with('error', 'Translation failed. Please try again.'));
    }

    public function addBlock(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot add block.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'block_type'       => 'required|string|in:benefits,how-it-works,testimonials,faqs,cta,video,comparison,bundle,disclaimer',
                'title'            => 'nullable|string|max:255',
                'content'          => 'nullable|string',
                'display_position' => 'nullable|string|max:100',
                'is_global'        => 'nullable|boolean',
            ]);

            // Admin writes English only — storefront auto-translates for FR/DE.
            $nextOrder = (int) $product->pageBlocks()
                ->where('locale', 'en')
                ->max('sort_order');

            $product->pageBlocks()->create([
                'locale'           => 'en',
                'block_type'       => $validated['block_type'],
                'title'            => $validated['title'],
                'content'          => $validated['content'],
                'display_position' => $validated['display_position'] ?? 'default',
                'is_global'        => $validated['is_global'] ?? false,
                'sort_order'       => $nextOrder + 1,
                'visible'          => true,
            ]);

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
            }

            $this->forgetStorefrontOverlayCache($product);

            return back()->with('success', 'Product page content block added successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
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
                'title'            => 'nullable|string|max:255',
                'content'          => 'nullable|string',
                'display_position' => 'nullable|string|max:100',
                'is_global'        => 'nullable|boolean',
                'visible'          => 'required|in:0,1',
            ]);

            $block->update([
                'title'            => $validated['title'],
                'content'          => $validated['content'],
                'display_position' => $validated['display_position'] ?? 'default',
                'is_global'        => $validated['is_global'] ?? false,
                'visible'          => (bool) (int) $validated['visible'],
                'locale'           => 'en',
            ]);

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
            }

            $this->forgetStorefrontOverlayCache($product);

            return back()->with('success', 'Product page content block updated successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
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

            $this->forgetStorefrontOverlayCache($product);

            return back()->with('success', 'Product page content block deleted successfully!');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    protected function forgetStorefrontOverlayCache(Product $product): void
    {
        \App\Support\StorefrontCache::forgetProduct(
            (string) $product->handle,
            (int) $product->id
        );
    }

    public function unpublish(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline.');
        }

        return SupabaseDb::run(function () use ($id) {
            $product = Product::findOrFail($id);
            $product->update(['status' => \App\Support\ProductVisibility::STATUS_UNPUBLISHED]);

            $this->forgetStorefrontOverlayCache($product);
            $this->forgetAdminCatalogCaches();
            \App\Support\ProductVisibility::forgetCache();
            app(\App\Services\ShopifyService::class)->forgetCatalogCaches((string) $product->handle);

            return back()->with('success', "“{$product->title}” unpublished — hidden from the live website.");
        }, fn () => back()->with('error', 'Could not unpublish product.'));
    }

    public function publish(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline.');
        }

        return SupabaseDb::run(function () use ($id) {
            $product = Product::findOrFail($id);
            $product->update(['status' => 'active']);

            $this->forgetStorefrontOverlayCache($product);
            $this->forgetAdminCatalogCaches();
            \App\Support\ProductVisibility::forgetCache();
            app(\App\Services\ShopifyService::class)->forgetCatalogCaches((string) $product->handle);

            return back()->with('success', "“{$product->title}” published — visible on the live website again.");
        }, fn () => back()->with('error', 'Could not publish product.'));
    }

    public function destroy(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline.');
        }

        return SupabaseDb::run(function () use ($id) {
            $product = Product::findOrFail($id);
            $title = $product->title;
            $handle = (string) $product->handle;

            // Remove related CMS rows first
            $product->productContents()->delete();
            $product->knowledgeSignals()->delete();
            $product->faqs()->delete();
            $product->pageBlocks()->delete();

            \App\Models\Supabase\RelatedContent::query()
                ->where(function ($q) use ($id) {
                    $q->where(function ($q2) use ($id) {
                        $q2->where('source_type', 'product')->where('source_id', $id);
                    })->orWhere(function ($q2) use ($id) {
                        $q2->where('related_type', 'product')->where('related_id', $id);
                    });
                })
                ->delete();

            $product->delete();

            foreach (['en', 'fr', 'de'] as $locale) {
                \Illuminate\Support\Facades\Cache::forget("storefront.pdp_overlay.{$handle}.{$locale}");
                \Illuminate\Support\Facades\Cache::forget("product_{$id}_{$locale}");
                \Illuminate\Support\Facades\Cache::forget("json_ld_product_{$id}_{$locale}");
            }
            $this->forgetAdminCatalogCaches();
            \App\Support\ProductVisibility::forgetCache();
            app(\App\Services\ShopifyService::class)->forgetCatalogCaches($handle);

            return redirect('/dainely-admin-panel/products')
                ->with('success', "“{$title}” deleted from the admin catalog.");
        }, fn () => back()->with('error', 'Could not delete product.'));
    }
}
