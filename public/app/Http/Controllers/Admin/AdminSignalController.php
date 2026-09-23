<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\ProductKnowledgeSignal;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSignalController extends AdminController
{
    public function index(Request $request)
    {
        $this->flashIfSupabaseOffline('AI signals manager');

        $signals = SupabaseDb::run(function() use ($request) {
            $query = ProductKnowledgeSignal::query()->with(['product:id,title']);

            // Always force English for the admin frontend UI
            $query->where('locale', 'en');

            if ($request->filled('approved')) {
                $query->where('approved', $request->boolean('approved'));
            }

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'ilike', '%' . $search . '%')
                      ->orWhere('answer', 'ilike', '%' . $search . '%');
                });
            }

            return $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        }, new LengthAwarePaginator([], 0, 20));

        $products = SupabaseDb::run(function() {
            return \App\Models\Supabase\Product::select('id', 'title')->orderBy('title')->get();
        }, collect());

        return view('admin.signals.index', compact('signals', 'products'));
    }

    public function store(Request $request)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create signal.');
        }

        $request->validate([
            'product_id' => 'required|integer',
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        return SupabaseDb::run(function () use ($request) {
            $basePayload = [
                'product_id' => $request->product_id,
                'speaker_type' => 'manual',
                'approved' => true,
                'confidence' => 1.0,
                'source' => 'admin_manual',
            ];

            // Save English
            ProductKnowledgeSignal::create(array_merge($basePayload, [
                'locale' => 'en',
                'question' => $request->question,
                'answer' => $request->answer,
            ]));

            // Translate to other locales
            $translator = app(\App\Services\ContentTranslationService::class);
            $locales = ['fr', 'nl', 'de'];

            foreach ($locales as $loc) {
                try {
                    $tQ = $translator->translateContent($request->question, 'en', $loc);
                    $tA = $translator->translateContent($request->answer, 'en', $loc);
                    
                    ProductKnowledgeSignal::create(array_merge($basePayload, [
                        'locale' => $loc,
                        'question' => $tQ,
                        'answer' => $tA,
                    ]));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to translate knowledge signal to {$loc}: " . $e->getMessage());
                }
            }

            \App\Support\StorefrontCache::forgetProduct('', (int) $request->product_id); // Invalidate cache

            return back()->with('success', "Knowledge Signal FAQ added in English and automatically translated to French, Dutch, and German!");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function toggleApproval(int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot toggle approval.');
        }

        return SupabaseDb::run(function () use ($id) {
            $signal = ProductKnowledgeSignal::findOrFail($id);
            $signal->approved = !$signal->approved;
            $signal->save();

            if ($signal->product) {
                \App\Support\StorefrontCache::forgetProduct(
                    (string) $signal->product->handle,
                    (int) $signal->product_id
                );
            }

            return back()->with('success', "Signal approved status updated successfully!");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update signal.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $signal = ProductKnowledgeSignal::findOrFail($id);
            
            $validated = $request->validate([
                'question'     => 'required|string',
                'answer'       => 'required|string',
            ]);

            $signal->update($validated);

            if ($signal->product) {
                \App\Support\StorefrontCache::forgetProduct(
                    (string) $signal->product->handle,
                    (int) $signal->product_id
                );
            }

            return back()->with('success', "Signal #{$id} updated successfully.");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function jsonLdBuilder()
    {
        $filePath = storage_path('app/json_ld_schemas.json');
        $schemas = [];
        if (file_exists($filePath)) {
            $schemas = json_decode(file_get_contents($filePath), true) ?: [];
        }

        // If empty, provide one empty block as requested
        if (empty($schemas)) {
            $schemas = [
                [
                    'org_name' => 'Dainely',
                    'org_logo' => 'https://dainely.com/images/Dainelycut.png',
                    'name' => '',
                    'url' => '',
                    'description' => '',
                    'sku' => '',
                    'brand' => 'Dainely',
                    'image' => '',
                    'price' => '',
                    'currency' => 'USD',
                    'availability' => 'https://schema.org/InStock',
                    'condition' => 'https://schema.org/NewCondition',
                    'faqs' => [
                        ['question' => '', 'answer' => '']
                    ]
                ]
            ];
        }

        return view('admin.signals.jsonld', compact('schemas'));
    }

    public function jsonLdPublish(Request $request)
    {
        $schemas = $request->input('schemas', []);
        $filePath = storage_path('app/json_ld_schemas.json');
        
        file_put_contents($filePath, json_encode($schemas, JSON_PRETTY_PRINT));

        return back()->with('success', 'JSON-LD Schemas saved successfully! (Not yet implemented on frontend as requested)');
    }
}
