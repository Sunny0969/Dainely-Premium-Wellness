<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog\EducationPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEducationController extends AdminController
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = EducationPage::where('locale', 'en')
            ->select('id', 'title', 'slug', 'category', 'is_active', 'locale')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'ilike', '%' . $search . '%')
                  ->orWhere('hero_title', 'ilike', '%' . $search . '%')
                  ->orWhere('slug', 'ilike', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'published');
        }

        $pages = $query->paginate(20)->withQueryString();
        $categories = EducationPage::whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category');

        return view('admin.education.index', compact('pages', 'categories'));
    }

    public function create()
    {
        $page = new EducationPage();
        $products = \App\Models\Supabase\Product::where('status', 'active')->orWhere('status', 'ACTIVE')->get();
        return view('admin.education.edit', compact('page', 'products'));
    }

    public function store(Request $request)
    {
        set_time_limit(300);
        $validated = $this->validateEducation($request);
        $validated = $this->handleUploads($request, $validated);
        
                try {
            $page = EducationPage::create(array_merge($validated, ['locale' => $request->input('locale', 'en')]));
            
            if ($page->locale === 'en') {
                $this->syncTranslations($page);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23505) { // Unique violation
                return back()->withInput()->withErrors(['locale' => 'A page with this language and slug already exists.']);
            }
            throw $e;
        }

        return redirect('/dainely-admin-panel/education')->with('success', 'Education Page created successfully.');
    }

    public function edit(int $id)
    {
        $page = EducationPage::findOrFail($id);
        $products = \App\Models\Supabase\Product::where('status', 'active')->orWhere('status', 'ACTIVE')->get();
        return view('admin.education.edit', compact('page', 'products'));
    }

    public function update(Request $request, int $id)
    {
        set_time_limit(300);
        $page = EducationPage::findOrFail($id);
        $validated = $this->validateEducation($request);
        $validated = $this->handleUploads($request, $validated);
        
                $submittedLocale = $request->input('locale', 'en');
        if ($submittedLocale === 'en' && $page->locale !== 'en') {
            $actualEnPage = EducationPage::where('slug', $page->slug)->where('locale', 'en')->first();
            if ($actualEnPage) {
                $page = $actualEnPage;
            }
        }
        
        try {
            $page->update(array_merge($validated, ['locale' => $submittedLocale]));
            
            if ($page->locale === 'en') {
                $this->syncTranslations($page);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23505) { // Unique violation
                return back()->withInput()->withErrors(['locale' => 'A page with this language and slug already exists.']);
            }
            throw $e;
        }

        \App\Support\StorefrontCache::forgetEducation($page->id);
        return back()->with('success', 'Education Page updated successfully.');
    }

    public function destroy(int $id)
    {
        $page = EducationPage::findOrFail($id);
        $page->delete();
        return back()->with('success', 'Education Page deleted.');
    }

    private function handleUploads(Request $request, array $validated): array
    {
        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $filename = time() . '_hero_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $validated['hero_image'] = $filename;
        }
        unset($validated['hero_image_file']);
        
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['figures'] = $this->cleanRepeater($request->input('figures'));
        $validated['root_causes'] = $this->cleanRepeater($request->input('root_causes'));
        $validated['treatments'] = $this->cleanRepeater($request->input('treatments'), 'bullet');
        
        $contentBlocks = $this->cleanRepeater($request->input('content_blocks'));
        if ($request->hasFile('content_blocks')) {
            $files = $request->file('content_blocks');
            foreach ($files as $index => $blockFiles) {
                if (isset($blockFiles['image_file'])) {
                    $file = $blockFiles['image_file'];
                    $filename = time() . '_cb_' . $index . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images'), $filename);
                    if (isset($contentBlocks[$index])) {
                        $contentBlocks[$index]['image'] = $filename;
                    }
                }
            }
        }
        $validated['content_blocks'] = $contentBlocks;

        $layoutOrder = $request->input('layout_order');
        if (!empty($layoutOrder)) {
            $validated['layout_order'] = explode(',', $layoutOrder);
        } else {
            $validated['layout_order'] = ['figures', 'root_causes', 'treatments', 'content_blocks'];
        }
        
        return $validated;
    }

    public function syncTranslations(EducationPage $englishPage)
    {
        try {
            $translator = app(\App\Services\ContentTranslationService::class);
            $targets = ['fr', 'de'];
            
            $textFields = ['title', 'hero_title', 'hero_description', 'author_role', 'root_causes_title', 'treatments_title', 'treatments_description'];
            
            $fieldsToTranslate = [];

            // Add top-level fields
            foreach ($textFields as $field) {
                if (!empty($englishPage->{$field})) {
                    $fieldsToTranslate[$field] = $englishPage->{$field};
                }
            }

            // Add figures
            $figures = $englishPage->figures ?? [];
            foreach ($figures as $idx => $fig) {
                if (!empty($fig['label'])) $fieldsToTranslate["fig_{$idx}_label"] = $fig['label'];
            }

            // Add root causes
            $rootCauses = $englishPage->root_causes ?? [];
            foreach ($rootCauses as $idx => $rc) {
                if (!empty($rc['title'])) $fieldsToTranslate["rc_{$idx}_title"] = $rc['title'];
                if (!empty($rc['description'])) $fieldsToTranslate["rc_{$idx}_desc"] = $rc['description'];
            }

            // Add treatments
            $treatments = $englishPage->treatments ?? [];
            foreach ($treatments as $idx => $tr) {
                if (!is_array($tr)) {
                    $fieldsToTranslate["tr_{$idx}"] = $tr;
                } elseif (!empty($tr['text'])) {
                    $fieldsToTranslate["tr_{$idx}_text"] = $tr['text'];
                }
            }

            // Add content blocks
            $contentBlocks = $englishPage->content_blocks ?? [];
            foreach ($contentBlocks as $idx => $cb) {
                if (!empty($cb['title'])) $fieldsToTranslate["cb_{$idx}_title"] = $cb['title'];
                if (!empty($cb['content'])) $fieldsToTranslate["cb_{$idx}_content"] = $cb['content'];
            }

            if (empty($fieldsToTranslate)) {
                return;
            }

            $translatedPayloads = $translator->translateFields($fieldsToTranslate, $targets);

            foreach ($targets as $locale) {
                $translatedData = $englishPage->toArray();
                unset($translatedData['id'], $translatedData['created_at'], $translatedData['updated_at']);
                $translatedData['locale'] = $locale;
                
                $p = $translatedPayloads[$locale] ?? [];

                // Re-apply top-level fields
                foreach ($textFields as $field) {
                    if (!empty($p[$field])) $translatedData[$field] = $p[$field];
                }

                // Re-apply figures
                if (!empty($translatedData['figures'])) {
                    foreach ($translatedData['figures'] as $idx => &$fig) {
                        if (!empty($p["fig_{$idx}_label"])) $fig['label'] = $p["fig_{$idx}_label"];
                    }
                }

                // Re-apply root causes
                if (!empty($translatedData['root_causes'])) {
                    foreach ($translatedData['root_causes'] as $idx => &$rc) {
                        if (!empty($p["rc_{$idx}_title"])) $rc['title'] = $p["rc_{$idx}_title"];
                        if (!empty($p["rc_{$idx}_desc"])) $rc['description'] = $p["rc_{$idx}_desc"];
                    }
                }

                // Re-apply treatments
                if (!empty($translatedData['treatments'])) {
                    foreach ($translatedData['treatments'] as $idx => &$tr) {
                        if (!is_array($tr)) {
                            if (!empty($p["tr_{$idx}"])) $tr = $p["tr_{$idx}"];
                        } elseif (!empty($p["tr_{$idx}_text"])) {
                            $tr['text'] = $p["tr_{$idx}_text"];
                        }
                    }
                }

                // Re-apply content blocks
                if (!empty($translatedData['content_blocks'])) {
                    foreach ($translatedData['content_blocks'] as $idx => &$cb) {
                        if (!empty($p["cb_{$idx}_title"])) $cb['title'] = $p["cb_{$idx}_title"];
                        if (!empty($p["cb_{$idx}_content"])) $cb['content'] = $p["cb_{$idx}_content"];
                    }
                }

                $actualFrPage = EducationPage::where('slug', $englishPage->slug)->where('locale', $locale)->first();
                if ($actualFrPage) {
                    $actualFrPage->update($translatedData);
                } else {
                    EducationPage::create($translatedData);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("EducationPage syncTranslations failed: " . $e->getMessage());
        }
    }

    private function validateEducation(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_image' => 'nullable|string',
            'hero_image_file' => 'nullable|image|max:4096',
            'author_image' => 'nullable|string',
            'author_name' => 'nullable|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'read_time' => 'nullable|string|max:255',
            
            'root_causes_title' => 'nullable|string|max:255',
            'treatments_title' => 'nullable|string|max:255',
            'treatments_description' => 'nullable|string',
            
            'layout_order' => 'nullable|string',
            'related_products' => 'nullable|array',
            'related_products.*' => 'integer',
            
            'is_active' => 'boolean',
        ]);
    }

    private function cleanRepeater($items, $mode = 'array')
    {
        if (!is_array($items)) {
            return [];
        }
        
        $cleaned = [];
        foreach ($items as $item) {
            if ($mode === 'bullet') {
                if (!empty($item['text'])) {
                    $cleaned[] = $item['text'];
                }
            } else {
                if (!empty($item['title']) || !empty($item['value']) || !empty($item['label']) || !empty($item['description']) || !empty($item['content'])) {
                    $cleaned[] = $item;
                }
            }
        }
        
        return $cleaned;
    }
}

