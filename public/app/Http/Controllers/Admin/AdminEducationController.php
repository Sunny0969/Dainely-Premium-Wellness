<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\EducationPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEducationController extends Controller
{
    public function index(Request $request)
    {
        $query = EducationPage::query()
            ->where(function ($q) {
                $q->where('locale', 'en')
                  ->orWhereNull('locale')
                  ->orWhere('locale', '');
            })
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
        $categories = EducationPage::where(function ($q) {
                $q->where('locale', 'en')
                  ->orWhereNull('locale')
                  ->orWhere('locale', '');
            })
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return view('admin.education.index', compact('pages', 'categories'));
    }

    public function syncAll()
    {
        $englishPages = EducationPage::where('is_active', true)
            ->where(function ($q) {
                $q->where('locale', 'en')
                  ->orWhereNull('locale')
                  ->orWhere('locale', '');
            })
            ->get();

        $count = 0;
        foreach ($englishPages as $page) {
            $this->syncTranslations($page);
            $count++;
        }

        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_en');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_fr');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_de');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_en');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_fr');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_de');

        return redirect('/dainely-admin-panel/education')->with('success', "All {$count} education pages synced & translated to FR and DE successfully!");
    }

    public function create()
    {
        $page = new EducationPage();
        $page->locale = 'en';
        $products = \App\Models\Supabase\Product::where('status', 'active')->orWhere('status', 'ACTIVE')->get();
        return view('admin.education.edit', compact('page', 'products'));
    }

    public function store(Request $request)
    {
        set_time_limit(300);
        $validated = $this->validateEducation($request);
        $validated = $this->handleUploads($request, $validated);
        
        $this->ensureSeoColumnsExist();
        $existingColumns = \Illuminate\Support\Facades\Schema::getColumnListing('education_pages');
        $safePayload = array_intersect_key($validated, array_flip($existingColumns));
        $safePayload['locale'] = 'en';

        try {
            $page = EducationPage::create($safePayload);
            
            if ($page->locale === 'en') {
                $this->syncTranslations($page);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23505) {
                return back()->withInput()->withErrors(['slug' => 'A page with this URL slug already exists.']);
            }
            throw $e;
        }

        return redirect('/dainely-admin-panel/education')->with('success', 'Education Page created successfully.');
    }

    public function edit(int $id)
    {
        $this->ensureSeoColumnsExist();
        $page = EducationPage::findOrFail($id);
        
        if ($page->locale !== 'en') {
            $actualEnPage = EducationPage::where('slug', $page->slug)->where('locale', 'en')->first();
            if ($actualEnPage) {
                $page = $actualEnPage;
            }
        }

        $products = \App\Models\Supabase\Product::where('status', 'active')->orWhere('status', 'ACTIVE')->get();
        return view('admin.education.edit', compact('page', 'products'));
    }

    public function update(Request $request, int $id)
    {
        set_time_limit(300);
        $page = EducationPage::findOrFail($id);
        $validated = $this->validateEducation($request);
        $validated = $this->handleUploads($request, $validated);
        
        if ($page->locale !== 'en') {
            $actualEnPage = EducationPage::where('slug', $page->slug)->where('locale', 'en')->first();
            if ($actualEnPage) {
                $page = $actualEnPage;
            }
        }
        
        $this->ensureSeoColumnsExist();
        $existingColumns = \Illuminate\Support\Facades\Schema::getColumnListing('education_pages');
        $safePayload = array_intersect_key($validated, array_flip($existingColumns));
        $safePayload['locale'] = 'en';

        try {
            $oldSlug = $page->slug;
            $page->update($safePayload);
            
            if (!empty($safePayload['slug']) && $safePayload['slug'] !== $oldSlug) {
                EducationPage::where('slug', $oldSlug)
                    ->where('id', '!=', $page->id)
                    ->update(['slug' => $safePayload['slug']]);
            }

            if ($page->locale === 'en') {
                $this->syncTranslations($page);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23505) {
                return back()->withInput()->withErrors(['slug' => 'A page with this URL slug already exists.']);
            }
            throw $e;
        }

        \App\Support\StorefrontCache::forgetEducation($page->id);
        return back()->with('success', 'Education Page updated successfully.');
    }

    public function destroy(int $id)
    {
        $page = EducationPage::findOrFail($id);
        $slug = $page->slug;

        if (!empty($slug)) {
            $allRows = EducationPage::where('slug', $slug)->get();
            foreach ($allRows as $item) {
                \App\Support\StorefrontCache::forgetEducation($item->id);
                $item->delete();
            }
        } else {
            \App\Support\StorefrontCache::forgetEducation($page->id);
            $page->delete();
        }

        return back()->with('success', 'Education Page deleted.');
    }

    private function handleUploads(Request $request, array $validated): array
    {
        if (!empty($validated['hero_description'])) {
            $validated['hero_description'] = \App\Services\HtmlImageProcessor::processBase64Images($validated['hero_description']);
        }
        if (!empty($validated['treatments_description'])) {
            $validated['treatments_description'] = \App\Services\HtmlImageProcessor::processBase64Images($validated['treatments_description']);
        }
        if (isset($validated['figures']) && is_array($validated['figures'])) {
            foreach ($validated['figures'] as &$item) {
                if (!empty($item['label'])) $item['label'] = \App\Services\HtmlImageProcessor::processBase64Images($item['label']);
            }
        }
        if (isset($validated['root_causes']) && is_array($validated['root_causes'])) {
            foreach ($validated['root_causes'] as &$item) {
                if (!empty($item['description'])) $item['description'] = \App\Services\HtmlImageProcessor::processBase64Images($item['description']);
            }
        }
        if (isset($validated['content_blocks']) && is_array($validated['content_blocks'])) {
            foreach ($validated['content_blocks'] as &$item) {
                if (!empty($item['content'])) $item['content'] = \App\Services\HtmlImageProcessor::processBase64Images($item['content']);
            }
        }
        
        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $filename = time() . '_hero_' . $file->getClientOriginalName();
            $path = $file->storePubliclyAs('education', $filename, 's3');
            $filename = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
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
                    $path = $file->storePubliclyAs('education', $filename, 's3');
                    $filename = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
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

            foreach ($textFields as $field) {
                if (!empty($englishPage->{$field})) {
                    $fieldsToTranslate[$field] = $englishPage->{$field};
                }
            }

            $figures = $englishPage->figures ?? [];
            foreach ($figures as $idx => $fig) {
                if (!empty($fig['value'])) $fieldsToTranslate["fig_{$idx}_val"] = $fig['value'];
                if (!empty($fig['label'])) $fieldsToTranslate["fig_{$idx}_label"] = $fig['label'];
            }

            $rootCauses = $englishPage->root_causes ?? [];
            foreach ($rootCauses as $idx => $rc) {
                if (!empty($rc['title'])) $fieldsToTranslate["rc_{$idx}_title"] = $rc['title'];
                if (!empty($rc['description'])) $fieldsToTranslate["rc_{$idx}_desc"] = $rc['description'];
            }

            $treatments = $englishPage->treatments ?? [];
            foreach ($treatments as $idx => $tr) {
                if (!is_array($tr)) {
                    $fieldsToTranslate["tr_{$idx}"] = $tr;
                } elseif (!empty($tr['text'])) {
                    $fieldsToTranslate["tr_{$idx}_text"] = $tr['text'];
                }
            }

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

                foreach ($textFields as $field) {
                    if (!empty($p[$field])) $translatedData[$field] = $p[$field];
                }

                if (!empty($translatedData['figures'])) {
                    foreach ($translatedData['figures'] as $idx => &$fig) {
                        if (!empty($p["fig_{$idx}_val"])) $fig['value'] = $p["fig_{$idx}_val"];
                        if (!empty($p["fig_{$idx}_label"])) $fig['label'] = $p["fig_{$idx}_label"];
                    }
                }

                if (!empty($translatedData['root_causes'])) {
                    foreach ($translatedData['root_causes'] as $idx => &$rc) {
                        if (!empty($p["rc_{$idx}_title"])) $rc['title'] = $p["rc_{$idx}_title"];
                        if (!empty($p["rc_{$idx}_desc"])) $rc['description'] = $p["rc_{$idx}_desc"];
                    }
                }

                if (!empty($translatedData['treatments'])) {
                    foreach ($translatedData['treatments'] as $idx => &$tr) {
                        if (!is_array($tr)) {
                            if (!empty($p["tr_{$idx}"])) $tr = $p["tr_{$idx}"];
                        } elseif (!empty($p["tr_{$idx}_text"])) {
                            $tr['text'] = $p["tr_{$idx}_text"];
                        }
                    }
                }

                if (!empty($translatedData['content_blocks'])) {
                    foreach ($translatedData['content_blocks'] as $idx => &$cb) {
                        if (!empty($p["cb_{$idx}_title"])) $cb['title'] = $p["cb_{$idx}_title"];
                        if (!empty($p["cb_{$idx}_content"])) $cb['content'] = $p["cb_{$idx}_content"];
                    }
                }

                $existingColumns = \Illuminate\Support\Facades\Schema::getColumnListing('education_pages');
                $safeTranslated = array_intersect_key($translatedData, array_flip($existingColumns));

                $actualFrPage = EducationPage::where('slug', $englishPage->slug)->where('locale', $locale)->first();
                if ($actualFrPage) {
                    $actualFrPage->update($safeTranslated);
                    \App\Support\StorefrontCache::forgetEducation($actualFrPage->id);
                } else {
                    $newPage = EducationPage::create($safeTranslated);
                    \App\Support\StorefrontCache::forgetEducation($newPage->id);
                }
                \Illuminate\Support\Facades\Cache::forget("education_index_pages_v1_{$locale}");
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
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'canonical_url' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_image' => 'nullable|string',
            'hero_image_file' => 'nullable|image|max:4096',
            'hero_media_id' => 'nullable|integer',
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

    private function ensureSeoColumnsExist(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('education_pages', 'meta_title')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE education_pages ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255)');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE education_pages ADD COLUMN IF NOT EXISTS meta_description TEXT');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE education_pages ADD COLUMN IF NOT EXISTS canonical_url VARCHAR(255)');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE education_pages ADD COLUMN IF NOT EXISTS hero_media_id BIGINT');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not alter education_pages table: ' . $e->getMessage());
        }
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