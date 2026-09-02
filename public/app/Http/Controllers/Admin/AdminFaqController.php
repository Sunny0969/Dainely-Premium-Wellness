<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog\EducationPage;
use App\Models\Supabase\Faq;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Support\ContentCatalog;
use App\Support\ProductLandingLang;
use App\Support\StorefrontCache;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminFaqController extends AdminController
{
    public function index(Request $request)
    {
        $online = $this->flashIfSupabaseOffline('FAQs manager');

        $faqableType = (string) $request->query('faqable_type', Product::class);
        $faqableId = (int) $request->query('faqable_id', 0);
        $locale = (string) $request->query('locale', 'en');

        if (! in_array($faqableType, $this->allowedTypes(), true)) {
            $faqableType = Product::class;
        }
        if (! in_array($locale, ['en', 'fr', 'de'], true)) {
            $locale = 'en';
        }

        $products = $online
            ? $this->cachedProductsForSelect(['id', 'title', 'handle', 'shopify_product_id'])
            : collect();
        $landingPages = $online
            ? $this->cachedLandingsForSelect(['id', 'title', 'slug', 'locale'])
            : collect();
        $educationPages = ContentCatalog::educationPages();

        if ($faqableId < 1) {
            $faqableId = (int) ($products->first()?->id ?? 0);
        }

        $faqs = collect();
        $autoSyncedCount = 0;
        $previewFaqs = [];

        if ($online && $faqableId > 0 && SupabaseDb::available()) {
            $synced = SupabaseDb::run(function () use ($faqableType, $faqableId, $locale, $products) {
                $rows = Faq::query()
                    ->where('faqable_type', $faqableType)
                    ->where('faqable_id', $faqableId)
                    ->where('locale', $locale)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                $imported = 0;
                if ($rows->isEmpty() && $faqableType === Product::class) {
                    $handle = (string) ($products->firstWhere('id', $faqableId)?->handle ?? '');
                    $imported = $this->syncDefaultsForProduct($faqableId, $handle, $locale);
                    if ($imported > 0) {
                        $rows = Faq::query()
                            ->where('faqable_type', Product::class)
                            ->where('faqable_id', $faqableId)
                            ->where('locale', $locale)
                            ->orderBy('sort_order')
                            ->orderBy('id')
                            ->get();
                    }
                }

                $this->attachFaqables($rows);

                return ['faqs' => $rows, 'imported' => $imported];
            }, ['faqs' => collect(), 'imported' => 0]);

            $faqs = $synced['faqs'] ?? collect();
            $autoSyncedCount = (int) ($synced['imported'] ?? 0);
        }

        // Offline / empty CMS: still show live page FAQs from lang (read-only preview).
        if ($faqs->isEmpty() && $faqableType === Product::class) {
            $handle = (string) ($products->firstWhere('id', $faqableId)?->handle ?? '');
            if ($handle === '' && $faqableId < 1) {
                // Keep empty when no product selected and catalog offline.
                $handle = '';
            }
            if ($handle !== '') {
                $previewFaqs = ProductLandingLang::defaultFaqsForHandle($handle, $locale);
            }
        }

        $selectedTitle = $this->resolveSelectedTitle(
            $faqableType,
            $faqableId,
            $products,
            $landingPages,
            $educationPages
        );

        return view('admin.faqs.index', compact(
            'faqs',
            'products',
            'landingPages',
            'educationPages',
            'faqableType',
            'faqableId',
            'locale',
            'autoSyncedCount',
            'previewFaqs',
            'selectedTitle',
            'online'
        ));
    }

    /**
     * JSON list for Alpine filter (same filters as index).
     */
    public function forTarget(Request $request)
    {
        if (! SupabaseDb::available()) {
            return response()->json(['ok' => false, 'error' => 'Database offline.', 'faqs' => []], 503);
        }

        $validated = $request->validate([
            'faqable_type' => 'required|string|in:'.implode(',', $this->allowedTypes()),
            'faqable_id'   => 'required|integer|min:1',
            'locale'       => 'required|string|in:en,fr,de',
        ]);

        return SupabaseDb::run(function () use ($validated) {
            if ($validated['faqable_type'] === Product::class) {
                $handle = (string) (Product::query()->where('id', $validated['faqable_id'])->value('handle') ?? '');
                $this->syncDefaultsForProduct((int) $validated['faqable_id'], $handle, $validated['locale']);
            }

            $faqs = Faq::query()
                ->where('faqable_type', $validated['faqable_type'])
                ->where('faqable_id', $validated['faqable_id'])
                ->where('locale', $validated['locale'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'question', 'answer', 'sort_order', 'approved', 'locale']);

            return response()->json([
                'ok' => true,
                'faqs' => $faqs->values(),
            ]);
        }, fn () => response()->json(['ok' => false, 'error' => 'Database operation failed.', 'faqs' => []], 500));
    }

    public function store(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create FAQ.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'faqable_type' => 'required|string|in:'.implode(',', $this->allowedTypes()),
                'faqable_id'   => 'required|integer|min:1',
                'locale'       => 'required|string|in:en,fr,de',
                'question'     => 'required|string|max:2000',
                'answer'       => 'required|string|max:20000',
            ]);

            $validated['answer'] = \App\Support\CmsHtml::normalize($validated['answer']);
            if (trim(strip_tags($validated['answer'])) === '') {
                return back()->withInput()->with('error', 'Answer cannot be empty. Add some text (you can use bold, italics, and lists).');
            }

            if ($validated['faqable_type'] === EducationPage::class
                && ContentCatalog::educationById((int) $validated['faqable_id']) === null) {
                return back()->with('error', 'Education page not found.');
            }

            $nextOrder = (int) Faq::query()
                ->where('faqable_type', $validated['faqable_type'])
                ->where('faqable_id', $validated['faqable_id'])
                ->where('locale', $validated['locale'])
                ->max('sort_order');

            Faq::create(array_merge($validated, [
                'sort_order' => $nextOrder + 1,
                'approved'   => true,
            ]));

            $this->publishTarget(
                $validated['faqable_type'],
                (int) $validated['faqable_id'],
                $validated['locale']
            );

            return redirect($this->indexUrl($validated))->with('success', 'FAQ created and published to the customer page.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update FAQ.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $faq = Faq::findOrFail($id);

            $validated = $request->validate([
                'question' => 'required|string|max:2000',
                'answer'   => 'required|string|max:20000',
                'approved' => 'required|in:0,1',
            ]);

            $validated['answer'] = \App\Support\CmsHtml::normalize($validated['answer']);
            if (trim(strip_tags($validated['answer'])) === '') {
                return back()->withInput()->with('error', 'Answer cannot be empty. Add some text (you can use bold, italics, and lists).');
            }

            $faq->update([
                'question' => $validated['question'],
                'answer'   => $validated['answer'],
                'approved' => (bool) (int) $validated['approved'],
            ]);
            $this->publishTarget($faq->faqable_type, (int) $faq->faqable_id, (string) $faq->locale);

            return redirect($this->indexUrl([
                'faqable_type' => $faq->faqable_type,
                'faqable_id'   => $faq->faqable_id,
                'locale'       => $faq->locale,
            ]))->with('success', 'FAQ updated and published to the customer page.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function destroy(int $id)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete FAQ.');
        }

        return SupabaseDb::run(function () use ($id) {
            $faq = Faq::findOrFail($id);
            $redirect = $this->indexUrl([
                'faqable_type' => $faq->faqable_type,
                'faqable_id'   => $faq->faqable_id,
                'locale'       => $faq->locale,
            ]);
            $this->publishTarget($faq->faqable_type, (int) $faq->faqable_id, (string) $faq->locale);
            $faq->delete();
            // Clear again after delete so overlay cannot keep the removed row.
            $this->publishTarget($faq->faqable_type, (int) $faq->faqable_id, (string) $faq->locale);

            return redirect($redirect)->with('success', 'FAQ deleted and published. Refresh the product page to verify.');
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    /**
     * Explicit "Save & Publish" — clears storefront cache so customer page matches admin.
     */
    public function publish(Request $request)
    {
        if (! SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot publish FAQs.');
        }

        $validated = $request->validate([
            'faqable_type' => 'required|string|in:'.implode(',', $this->allowedTypes()),
            'faqable_id'   => 'required|integer|min:1',
            'locale'       => 'required|string|in:en,fr,de',
        ]);

        $count = $this->publishTarget(
            $validated['faqable_type'],
            (int) $validated['faqable_id'],
            $validated['locale']
        );

        return redirect($this->indexUrl($validated))->with(
            'success',
            "Published to customer site. {$count} FAQ(s) will show on the product page (hard-refresh the page)."
        );
    }

    public function reorder(Request $request)
    {
        if (! SupabaseDb::available()) {
            return response()->json(['ok' => false, 'error' => 'Database offline.'], 503);
        }

        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
        ]);

        return SupabaseDb::run(function () use ($validated) {
            $ids = array_values(array_map('intval', $validated['ids']));
            $faqs = Faq::query()->whereIn('id', $ids)->get()->keyBy('id');

            if ($faqs->count() !== count($ids)) {
                return response()->json(['ok' => false, 'error' => 'One or more FAQs were not found.'], 422);
            }

            $first = $faqs->get($ids[0]);
            foreach ($ids as $index => $id) {
                $row = $faqs->get($id);
                if (! $row
                    || $row->faqable_type !== $first->faqable_type
                    || (int) $row->faqable_id !== (int) $first->faqable_id
                    || $row->locale !== $first->locale) {
                    return response()->json(['ok' => false, 'error' => 'FAQs must belong to the same target.'], 422);
                }
                $row->update(['sort_order' => $index]);
            }

            StorefrontCache::forgetForFaq($first);
            if ($first->faqable_type === Product::class || str_ends_with((string) $first->faqable_type, '\\Product')) {
                $handle = (string) (Product::query()->where('id', $first->faqable_id)->value('handle') ?? '');
                StorefrontCache::publishProductFaqs((int) $first->faqable_id, $handle !== '' ? $handle : null);
            }

            return response()->json(['ok' => true, 'message' => 'Order saved & published.']);
        }, fn () => response()->json(['ok' => false, 'error' => 'Database operation failed.'], 500));
    }

    /**
     * Clear storefront caches for the selected target so customer pages refresh.
     */
    protected function publishTarget(string $type, int $id, string $locale): int
    {
        $count = (int) Faq::query()
            ->where('faqable_type', $type)
            ->where('faqable_id', $id)
            ->where('locale', $locale)
            ->where('approved', true)
            ->count();

        if ($type === Product::class || str_ends_with($type, '\\Product')) {
            $handle = (string) (Product::query()->where('id', $id)->value('handle') ?? '');
            StorefrontCache::publishProductFaqs($id, $handle !== '' ? $handle : null);
            StorefrontCache::forgetForFaq((object) [
                'faqable_type' => Product::class,
                'faqable_id'   => $id,
                'handle'       => $handle,
            ]);
        } else {
            StorefrontCache::forgetForFaq((object) [
                'faqable_type' => $type,
                'faqable_id'   => $id,
            ]);
        }

        return $count;
    }

    /**
     * Auto-load live page FAQs into CMS when this product/locale has none yet.
     * Returns how many rows were created (0 if already synced or no defaults).
     */
    protected function syncDefaultsForProduct(int $productId, string $handle, string $locale): int
    {
        if ($productId < 1 || $handle === '') {
            return 0;
        }

        $existing = Faq::query()
            ->where('faqable_type', Product::class)
            ->where('faqable_id', $productId)
            ->where('locale', $locale)
            ->count();

        if ($existing > 0) {
            return 0;
        }

        $defaults = ProductLandingLang::defaultFaqsForHandle($handle, $locale);
        if ($defaults === []) {
            return 0;
        }

        // If locale file still has English FAQ copies, translate from EN before saving to CMS.
        if ($locale !== 'en') {
            $enDefaults = ProductLandingLang::defaultFaqsForHandle($handle, 'en');
            $looksEnglish = $enDefaults !== [] && collect($defaults)->pluck('question')->map(fn ($q) => mb_strtolower((string) $q))->values()->all()
                === collect($enDefaults)->pluck('question')->map(fn ($q) => mb_strtolower((string) $q))->values()->all();

            if ($looksEnglish && $enDefaults !== []) {
                try {
                    $translator = app(\App\Services\ContentTranslationService::class);
                    $translated = [];
                    foreach ($enDefaults as $row) {
                        $translated[] = [
                            'question' => $translator->translateContent($row['question'], 'en', $locale),
                            'answer'   => $translator->translateContent($row['answer'], 'en', $locale),
                        ];
                    }
                    $defaults = $translated;
                } catch (\Throwable $e) {
                    // Keep English defaults rather than blocking admin.
                }
            }
        }

        foreach ($defaults as $index => $row) {
            Faq::create([
                'faqable_type' => Product::class,
                'faqable_id'   => $productId,
                'locale'       => $locale,
                'question'     => $row['question'],
                'answer'       => $row['answer'],
                'sort_order'   => $index,
                'approved'     => true,
            ]);
        }

        StorefrontCache::forgetForFaq((object) [
            'faqable_type' => Product::class,
            'faqable_id'   => $productId,
            'locale'       => $locale,
        ]);

        return count($defaults);
    }

    /**
     * @return list<string>
     */
    protected function allowedTypes(): array
    {
        return [
            Product::class,
            LandingPage::class,
            EducationPage::class,
        ];
    }

    /**
     * @param  array{faqable_type?:string,faqable_id?:int|string,locale?:string}  $parts
     */
    protected function indexUrl(array $parts): string
    {
        return '/dainely-admin-panel/faqs?'.http_build_query([
            'faqable_type' => $parts['faqable_type'] ?? Product::class,
            'faqable_id'   => $parts['faqable_id'] ?? '',
            'locale'       => $parts['locale'] ?? 'en',
        ]);
    }

    /**
     * @param  Collection<int, Faq>  $faqs
     */
    protected function attachFaqables($faqs): void
    {
        $productIds = $faqs->where('faqable_type', Product::class)->pluck('faqable_id')->unique()->filter();
        $landingIds = $faqs->where('faqable_type', LandingPage::class)->pluck('faqable_id')->unique()->filter();

        $products = $productIds->isNotEmpty()
            ? Product::whereIn('id', $productIds)->get()->keyBy('id')
            : collect();
        $landings = $landingIds->isNotEmpty()
            ? LandingPage::whereIn('id', $landingIds)->get()->keyBy('id')
            : collect();

        foreach ($faqs as $faq) {
            $parent = match ($faq->faqable_type) {
                Product::class => $products->get((int) $faq->faqable_id),
                LandingPage::class => $landings->get((int) $faq->faqable_id),
                EducationPage::class => EducationPage::findCatalog((int) $faq->faqable_id),
                default => null,
            };

            $faq->setRelation('faqable', $parent);
        }
    }

    protected function resolveSelectedTitle(
        string $type,
        int $id,
        Collection $products,
        Collection $landingPages,
        $educationPages
    ): string {
        if ($id < 1) {
            return 'Select a resource';
        }

        return match ($type) {
            Product::class => (string) ($products->firstWhere('id', $id)?->title ?? "Product #{$id}"),
            LandingPage::class => (string) ($landingPages->firstWhere('id', $id)?->title ?? "Landing #{$id}"),
            EducationPage::class => (string) (collect($educationPages)->firstWhere('id', $id)['title'] ?? "Education #{$id}"),
            default => "Resource #{$id}",
        };
    }
}
