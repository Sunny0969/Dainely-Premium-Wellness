<?php

namespace App\Http\Controllers\Admin;

use App\Models\Catalog\EducationPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEducationController extends AdminController
{
    public function index()
    {
        $pages = EducationPage::orderBy('id', 'asc')->get();
        return view('admin.education.index', compact('pages'));
    }

    public function create()
    {
        $page = new EducationPage();
        return view('admin.education.edit', compact('page'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEducation($request);
        
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

        EducationPage::create($validated);

        return redirect('/dainely-admin-panel/education')->with('success', 'Education Page created successfully.');
    }

    public function edit(int $id)
    {
        $page = EducationPage::findOrFail($id);
        return view('admin.education.edit', compact('page'));
    }

    public function update(Request $request, int $id)
    {
        $page = EducationPage::findOrFail($id);
        $validated = $this->validateEducation($request);
        
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

        $page->update($validated);

        \App\Support\StorefrontCache::forgetEducation($page->id);

        return redirect('/dainely-admin-panel/education')->with('success', 'Education Page updated successfully.');
    }

    public function destroy(int $id)
    {
        $page = EducationPage::findOrFail($id);
        $page->delete();
        return back()->with('success', 'Education Page deleted.');
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
