<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog\EducationPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEducationController extends AdminController
{
    public function index()
    {
        $pages = EducationPage::where('locale', 'en')->orderBy('id', 'desc')->get();
        return view('admin.education.index', compact('pages'));
    }

    public function create()
    {
        $page = new EducationPage();
        $products = \App\Models\Supabase\Product::where('status', 'active')->orWhere('status', 'ACTIVE')->get();
        return view('admin.education.edit', compact('page', 'products'));
    }

    public function store(Request $request)
    {
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
        return redirect('/dainely-admin-panel/education')->with('success', 'Education Page updated successfully.');
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
        $validated['content_blocks'] = $this->cleanRepeater($request->input('content_blocks'));

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
            
            foreach ($targets as $locale) {
                $translatedData = $englishPage->toArray();
                unset($translatedData['id'], $translatedData['created_at'], $translatedData['updated_at']);
                $translatedData['locale'] = $locale;
                
                // Translate top-level text fields
                foreach ($textFields as $field) {
                    if (!empty($translatedData[$field])) {
                        $translatedData[$field] = $translator->translateContent($translatedData[$field], 'en', $locale);
                    }
                }
                
                // Translate repeaters
                if (!empty($translatedData['figures'])) {
                    foreach ($translatedData['figures'] as &$fig) {
                        if (!empty($fig['label'])) $fig['label'] = $translator->translateContent($fig['label'], 'en', $locale);
                    }
                }
                if (!empty($translatedData['root_causes'])) {
                    foreach ($translatedData['root_causes'] as &$rc) {
                        if (!empty($rc['title'])) $rc['title'] = $translator->translateContent($rc['title'], 'en', $locale);
                        if (!empty($rc['description'])) $rc['description'] = $translator->translateContent($rc['description'], 'en', $locale);
                    }
                }
                if (!empty($translatedData['treatments'])) {
                    foreach ($translatedData['treatments'] as &$tr) {
                        if (!is_array($tr)) {
                            $tr = $translator->translateContent($tr, 'en', $locale);
                        } elseif (!empty($tr['text'])) {
                            $tr['text'] = $translator->translateContent($tr['text'], 'en', $locale);
                        }
                    }
                }
                if (!empty($translatedData['content_blocks'])) {
                    foreach ($translatedData['content_blocks'] as &$cb) {
                        if (!empty($cb['title'])) $cb['title'] = $translator->translateContent($cb['title'], 'en', $locale);
                        if (!empty($cb['content'])) $cb['content'] = $translator->translateContent($cb['content'], 'en', $locale);
                    }
                }
                
                EducationPage::updateOrCreate(
                    ['slug' => $englishPage->slug, 'locale' => $locale],
                    $translatedData
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Education translation failed: ' . $e->getMessage());
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
