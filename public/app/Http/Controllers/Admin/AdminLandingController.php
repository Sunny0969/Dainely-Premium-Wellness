<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\Faq;
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

        $page = SupabaseDb::run(fn () => LandingPage::with(['pageBlocks', 'faqs'])->findOrFail($id));
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

            $validated['bundle_id'] = ! empty($validated['bundle_id'] ?? null) ? (int) $validated['bundle_id'] : null;
            $validated['shopify_product_id'] = ! empty($validated['shopify_product_id'] ?? null) ? $validated['shopify_product_id'] : null;
            $validated['discount_code'] = ! empty($validated['discount_code'] ?? null) ? strtoupper(trim($validated['discount_code'])) : null;

            $oldSlug = (string) $page->slug;

            $page->update($validated);

            // Save / Sync FAQs when FAQ section was submitted
            if ($request->has('faqs_synced')) {
                $rawFaqs = (array) $request->input('faqs', []);
                $submittedIds = [];
                $order = 0;

                foreach ($rawFaqs as $fData) {
                    $question = trim($fData['question'] ?? '');
                    $answer = trim($fData['answer'] ?? '');
                    if ($question === '' && $answer === '') {
                        continue;
                    }

                    $faqId = ! empty($fData['id']) ? (int) $fData['id'] : null;

                    if ($faqId) {
                        $faq = Faq::where('faqable_type', LandingPage::class)
                            ->where('faqable_id', $page->id)
                            ->where('id', $faqId)
                            ->first();

                        if ($faq) {
                            $faq->update([
                                'question'   => $question,
                                'answer'     => $answer,
                                'sort_order' => $order,
                                'approved'   => true,
                                'locale'     => $page->locale ?: 'en',
                            ]);
                            $submittedIds[] = $faq->id;
                            $order++;
                            continue;
                        }
                    }

                    $newFaq = Faq::create([
                        'faqable_type' => LandingPage::class,
                        'faqable_id'   => $page->id,
                        'locale'       => $page->locale ?: 'en',
                        'question'     => $question,
                        'answer'       => $answer,
                        'sort_order'   => $order,
                        'approved'     => true,
                    ]);
                    $submittedIds[] = $newFaq->id;
                    $order++;
                }

                // Delete any FAQs that were removed from the UI
                Faq::where('faqable_type', LandingPage::class)
                    ->where('faqable_id', $page->id)
                    ->whereNotIn('id', $submittedIds)
                    ->delete();
            }

            // Save / Sync Layout Blocks when blocks are submitted
            if ($request->has('blocks_synced')) {
                $rawBlocks = (array) $request->input('blocks', []);
                $submittedBlockIds = [];
                $order = 1;

                foreach ($rawBlocks as $bData) {
                    $blockId = ! empty($bData['id']) ? (int) $bData['id'] : null;
                    $title = isset($bData['title']) ? trim((string) $bData['title']) : null;
                    $blockType = ! empty($bData['block_type']) ? trim((string) $bData['block_type']) : 'rich';
                    $content = isset($bData['content']) ? (string) $bData['content'] : '';
                    $bgColor = ! empty($bData['bg_color']) ? trim((string) $bData['bg_color']) : null;
                    $displayPosition = ! empty($bData['display_position']) ? trim((string) $bData['display_position']) : 'content_only';
                    $imageUrl = ! empty($bData['image_url']) ? trim((string) $bData['image_url']) : null;
                    $visible = !empty($bData['visible']) ? true : false;
                    $sortOrder = isset($bData['sort_order']) && $bData['sort_order'] !== '' ? (int) $bData['sort_order'] : $order;

                    if (!empty($content)) {
                        $content = \App\Support\CmsHtml::cleanMediaUrls($content);
                        $content = \App\Services\HtmlImageProcessor::processBase64Images($content, 'landing-pages/' . $page->slug);
                    }

                    if ($imageUrl) {
                        $imageUrl = \App\Support\CmsHtml::cleanMediaUrls($imageUrl);
                    }

                    if ($blockId) {
                        $block = PageBlock::where('blockable_type', LandingPage::class)
                            ->where('blockable_id', $page->id)
                            ->where('id', $blockId)
                            ->first();

                        if ($block) {
                            $block->update([
                                'block_type'       => $blockType ?: 'rich',
                                'title'            => $title ?: null,
                                'content'          => $content,
                                'bg_color'         => $bgColor,
                                'display_position' => $displayPosition,
                                'image_url'        => $imageUrl,
                                'visible'          => $visible,
                                'sort_order'       => $sortOrder,
                            ]);
                            $submittedBlockIds[] = $block->id;
                            $order++;
                            continue;
                        }
                    }

                    // Only create if there's either title, content, or an image
                    if (!empty($title) || !empty($content) || !empty($imageUrl)) {
                        $newBlock = PageBlock::create([
                            'blockable_type'   => LandingPage::class,
                            'blockable_id'     => $page->id,
                            'locale'           => $page->locale ?: 'en',
                            'block_type'       => $blockType ?: 'rich',
                            'title'            => $title ?: null,
                            'content'          => $content,
                            'bg_color'         => $bgColor,
                            'display_position' => $displayPosition,
                            'image_url'        => $imageUrl,
                            'visible'          => $visible,
                            'sort_order'       => $sortOrder,
                        ]);
                        $submittedBlockIds[] = $newBlock->id;
                        $order++;
                    }
                }

                // If any blocks were marked for deletion
                if ($request->has('deleted_blocks')) {
                    $deletedIds = array_filter(array_map('intval', (array) $request->input('deleted_blocks', [])));
                    if (!empty($deletedIds)) {
                        PageBlock::where('blockable_type', LandingPage::class)
                            ->where('blockable_id', $page->id)
                            ->whereIn('id', $deletedIds)
                            ->delete();
                    }
                }
            }

            \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, (string) $page->slug);

            // Renamed slug: purge the old URL's HTML snapshot too.
            if ($oldSlug !== '' && $oldSlug !== (string) $page->slug) {
                \App\Support\StorefrontCache::forgetLanding((int) $id, (string) $page->locale, $oldSlug);
            }

            return back()->with('success', 'Landing page and layout blocks saved successfully!');
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
                'block_type' => 'nullable|string|in:'.implode(',', $this->blockTypes),
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'sort_order' => 'nullable|integer',
                'bg_color' => 'nullable|string|max:7',
                'text_color' => 'nullable|string|max:7',
                'display_position' => 'nullable|string|max:100',
                'image_url' => 'nullable|string|max:1000',
            ]);

            $content = $validated['content'] ?? '';
            if (!empty($content)) {
                $content = \App\Support\CmsHtml::cleanMediaUrls($content);
                $content = \App\Services\HtmlImageProcessor::processBase64Images($content, 'landing-pages/' . $page->slug);
            }

            $imageUrl = $validated['image_url'] ?? null;
            if ($imageUrl) {
                $imageUrl = \App\Support\CmsHtml::cleanMediaUrls($imageUrl);
            }

            $page->pageBlocks()->create([
                'locale' => $page->locale,
                'block_type' => $validated['block_type'] ?? 'rich',
                'title' => $validated['title'] ?? null,
                'content' => $content,
                'sort_order' => !empty($validated['sort_order']) ? (int) $validated['sort_order'] : 1,
                'bg_color' => $validated['bg_color'] ?? null,
                'text_color' => $validated['text_color'] ?? null,
                'display_position' => $validated['display_position'] ?? 'content_only',
                'image_url' => $imageUrl,
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
                'sort_order' => 'nullable|integer',
                'visible' => 'nullable|boolean',
                'bg_color' => 'nullable|string|max:7',
                'text_color' => 'nullable|string|max:7',
                'display_position' => 'nullable|string|max:100',
                'image_url' => 'nullable|string|max:1000',
            ]);

            $updateData = [];

            if ($request->has('title')) {
                $updateData['title'] = $validated['title'];
            }

            if ($request->has('content')) {
                $content = $validated['content'];
                if (!empty($content)) {
                    $content = \App\Support\CmsHtml::cleanMediaUrls($content);
                    $content = \App\Services\HtmlImageProcessor::processBase64Images($content, 'landing-pages/' . $page->slug);
                }
                $updateData['content'] = $content;
            }

            if ($request->has('sort_order')) {
                $updateData['sort_order'] = (int) $validated['sort_order'];
            }

            if ($request->has('visible')) {
                $updateData['visible'] = (bool) $validated['visible'];
            }

            if ($request->has('bg_color')) {
                $updateData['bg_color'] = $validated['bg_color'];
            }

            if ($request->has('text_color')) {
                $updateData['text_color'] = $validated['text_color'];
            }

            if ($request->has('display_position')) {
                $updateData['display_position'] = $validated['display_position'];
            }

            if ($request->has('image_url')) {
                $imageUrl = $validated['image_url'];
                if ($imageUrl) {
                    $imageUrl = \App\Support\CmsHtml::cleanMediaUrls($imageUrl);
                }
                $updateData['image_url'] = $imageUrl;
            }

            if (!empty($updateData)) {
                $block->update($updateData);
            }

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



