@extends('layouts.admin')
@section('admin_title', 'Edit Education Page')

@section('admin_content')
<div class="p-6 max-w-5xl mx-auto" x-data="educationForm()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $page->exists ? 'Edit: ' . $page->title : 'Create New Education Page' }}</h1>
        <div class="flex items-center gap-4">
            <a href="{{ url('dainely-admin-panel/education') }}" class="text-gray-500 hover:text-gray-700">Back</a>
            <button type="button" onclick="document.getElementById('education-form').submit();" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2 rounded-lg text-sm shadow-sm">
                Save / Publish
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            <ul>
                @foreach($errors->all() as $err) <li>- {{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form id="education-form" action="{{ $page->exists ? url('dainely-admin-panel/education/'.$page->id) : url('dainely-admin-panel/education') }}" method="POST" enctype="multipart/form-data" class="space-y-8 bg-white p-6 shadow rounded">
        @csrf
        @if($page->exists)
            @method('PUT')
        @endif

        <div class="mb-4">
            <label class="block text-sm font-bold mb-2">Language (Locale) *</label>
            <select name="locale" class="w-full border rounded p-2" required>
                <option value="en" {{ old("locale", $page->locale ?? "en") == "en" ? "selected" : "" }}>English</option>
                <option value="fr" {{ old("locale", $page->locale) == "fr" ? "selected" : "" }}>French</option>
                <option value="de" {{ old("locale", $page->locale) == "de" ? "selected" : "" }}>German</option>
            </select>
            @error('locale')
                <p class="text-red-500 text-sm mt-1 font-bold">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Basic Info --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">1. Basic Info</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Title (Admin reference)</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block font-bold mb-1">URL Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-bold mb-1">Category</label>
                    <select name="category" class="w-full border p-2 rounded">
                        <option value="">-- Select Category --</option>
                        <option value="Movement & Mobility" {{ old('category', $page->category) == 'Movement & Mobility' ? 'selected' : '' }}>Movement & Mobility</option>
                        <option value="Back & Core Support" {{ old('category', $page->category) == 'Back & Core Support' ? 'selected' : '' }}>Back & Core Support</option>
                        <option value="Posture & Alignment" {{ old('category', $page->category) == 'Posture & Alignment' ? 'selected' : '' }}>Posture & Alignment</option>
                        <option value="Recovery & Relaxation" {{ old('category', $page->category) == 'Recovery & Relaxation' ? 'selected' : '' }}>Recovery & Relaxation</option>
                        <option value="Active 50+ Lifestyle" {{ old('category', $page->category) == 'Active 50+ Lifestyle' ? 'selected' : '' }}>Active 50+ Lifestyle</option>
                        <option value="Everyday Comfort" {{ old('category', $page->category) == 'Everyday Comfort' ? 'selected' : '' }}>Everyday Comfort</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-1">Status</label>
                    <select name="is_active" class="w-full border p-2 rounded">
                        <option value="1" {{ $page->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$page->is_active ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEO Info --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">SEO Info</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-bold mb-1">Canonical URL</label>
                    <input type="text" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" class="w-full border p-2 rounded">
                </div>
            </div>
            <div class="mt-4">
                <label class="block font-bold mb-1">Meta Description</label>
                <textarea name="meta_description" class="w-full border p-2 rounded" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
        </div>

        {{-- Layout Order --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">Layout Ordering</h2>
            <p class="text-sm text-gray-500 mb-4">Drag and drop the sections below to change their display order on the storefront.</p>
            <div id="layout-sortable" class="flex flex-col gap-2 max-w-sm">
                @php
                    $defaultOrder = ['figures', 'root_causes', 'treatments', 'content_blocks'];
                    $currentOrder = old('layout_order', is_array($page->layout_order) && count($page->layout_order) > 0 ? $page->layout_order : $defaultOrder);
                    
                    if (is_string($currentOrder)) {
                        $currentOrder = explode(',', $currentOrder);
                    }
                    
                    $labels = [
                        'figures' => 'Figures (e.g. 80%, #1)',
                        'root_causes' => 'Root Causes',
                        'treatments' => 'Treatments & Bullets',
                        'content_blocks' => 'Extra Text Blocks'
                    ];
                @endphp
                @foreach($currentOrder as $item)
                    @if(isset($labels[$item]))
                        <div class="bg-white border p-3 rounded cursor-move flex items-center justify-between shadow-sm" data-id="{{ $item }}">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">☰</span>
                                <span class="font-medium text-sm text-slate-700">{{ $labels[$item] }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <input type="hidden" name="layout_order" id="layout_order_input" value="{{ implode(',', $currentOrder) }}">
        </div>

        {{-- Hero --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">2. Hero Section</h2>
            <div class="space-y-4">
                <div>
                    <label class="block font-bold mb-1">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-bold mb-1">Hero Description</label>
                    <div class="bg-white">
                        <input type="hidden" name="hero_description" x-ref="hero_desc_input" value="{{ old('hero_description', $page->hero_description) }}">
                        <div x-init="initQuill($el, $refs.hero_desc_input)"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded border">
                    <div>
                        <label class="block font-bold mb-1">Hero Image URL</label>
                        <input type="text" name="hero_image" value="{{ old('hero_image', $page->hero_image) }}" class="w-full border p-2 rounded" placeholder="https://...">
                        <span class="text-xs text-gray-500">Provide an external URL...</span>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">OR Media Library ID</label>
                        <input type="number" name="hero_media_id" value="{{ old('hero_media_id', $page->hero_media_id) }}" class="w-full border p-2 rounded" placeholder="e.g. 15">
                        <span class="text-xs text-gray-500">Overrides URL</span>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">OR Upload Hero Image</label>
                        <input type="file" name="hero_image_file" class="w-full border p-1.5 bg-white rounded" accept="image/*">
                        <span class="text-xs text-gray-500">...or select a file from your computer (overrides URL)</span>
                        <p class="text-xs text-rose-500 mt-1 font-medium">⚠️ Max file size: 2MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold mb-1">Author Name (e.g. Dr. Reinhalt)</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $page->author_name) }}" class="w-full border p-2 rounded">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Author Role</label>
                        <input type="text" name="author_role" value="{{ old('author_role', $page->author_role) }}" class="w-full border p-2 rounded">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Read Time</label>
                        <input type="text" name="read_time" value="{{ old('read_time', $page->read_time) }}" class="w-full border p-2 rounded">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Author Image URL</label>
                        <input type="text" name="author_image" value="{{ old('author_image', $page->author_image) }}" class="w-full border p-2 rounded">
                    </div>
                </div>
            </div>
        </div>

        {{-- Figures --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">3. Figures (e.g. 80%, #1)</h2>
            <div class="space-y-2">
                <template x-for="(fig, index) in figures" :key="fig._id">
                    <div class="flex gap-2 items-center">
                        <input type="text" x-model="fig.value" :name="`figures[${index}][value]`" placeholder="Value (e.g. 80%)" class="border p-2 rounded w-1/3">
                        
                        <div class="bg-white flex-1 w-full relative min-h-[100px]">
                            <input type="hidden" x-model="fig.label" :name="`figures[${index}][label]`">
                            <div x-init="initQuill($el, $el.previousElementSibling, val => figures[index].label = val)"></div>
                        </div>

                        <div class="flex gap-2 ml-2 mt-2">
                            <button type="button" x-show="index > 0" @click="figures.splice(index - 1, 0, figures.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Up">↑</button>
                            <button type="button" x-show="index < figures.length - 1" @click="figures.splice(index + 1, 0, figures.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Down">↓</button>
                            <button type="button" @click="figures.splice(index, 1)" class="text-red-600 hover:text-red-800 text-xl font-bold">&times;</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="figures.push({value: '', label: '', _id: Math.random()})" class="text-sm text-blue-600 hover:underline">+ Add Figure</button>
            </div>
        </div>

        {{-- Root Causes --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">4. Root Causes</h2>
            <div class="mb-4">
                <label class="block font-bold mb-1">Section Title</label>
                <input type="text" name="root_causes_title" value="{{ old('root_causes_title', $page->root_causes_title) }}" class="w-full border p-2 rounded">
            </div>
            <div class="space-y-4">
                <template x-for="(cause, index) in rootCauses" :key="cause._id">
                    <div class="flex gap-3 items-start p-4 border rounded relative bg-gray-50 mb-4 group">
                        <div class="flex-1 w-full">
                        <input type="text" x-model="cause.title" :name="`root_causes[${index}][title]`" placeholder="Cause Title" class="w-full border p-2 rounded mb-2">
                        <div class="bg-white">
                            <input type="hidden" x-model="cause.description" :name="`root_causes[${index}][description]`">
                            <div x-init="initQuill($el, $el.previousElementSibling, val => rootCauses[index].description = val)"></div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 ml-2">
                        <button type="button" x-show="index > 0" @click="rootCauses.splice(index - 1, 0, rootCauses.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Up">↑</button>
                        <button type="button" x-show="index < rootCauses.length - 1" @click="rootCauses.splice(index + 1, 0, rootCauses.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Down">↓</button>
                        <button type="button" @click="rootCauses.splice(index, 1)" class="text-red-600 hover:text-red-800 text-xl font-bold">&times;</button>
                    </div>
                </div>
                </template>
                <button type="button" @click="rootCauses.push({title: '', description: '', _id: Math.random()})" class="text-sm text-blue-600 hover:underline">+ Add Root Cause</button>
            </div>
        </div>

        {{-- Treatments --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">5. Treatments (Bullet Points)</h2>
            <div class="mb-4">
                <label class="block font-bold mb-1">Section Title</label>
                <input type="text" name="treatments_title" value="{{ old('treatments_title', $page->treatments_title) }}" class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block font-bold mb-1">Section Description</label>
                <div class="bg-white">
                    <input type="hidden" name="treatments_description" x-ref="treatments_desc_input" value="{{ old('treatments_description', $page->treatments_description) }}">
                    <div x-init="initQuill($el, $refs.treatments_desc_input)"></div>
                </div>
            </div>
            <div class="space-y-2">
                <template x-for="(treatment, index) in treatments" :key="treatment._id">
                    <div class="flex gap-3 items-center">
                        <div class="bg-white w-full">
                            <input type="hidden" x-model="treatment.text" :name="`treatments[${index}][text]`">
                            <div x-init="initQuill($el, $el.previousElementSibling, val => treatments[index].text = val)"></div>
                        </div>
                        <div class="flex gap-2 ml-2">
                            <button type="button" x-show="index > 0" @click="treatments.splice(index - 1, 0, treatments.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Up">↑</button>
                            <button type="button" x-show="index < treatments.length - 1" @click="treatments.splice(index + 1, 0, treatments.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Down">↓</button>
                            <button type="button" @click="treatments.splice(index, 1)" class="text-red-600 hover:text-red-800 text-xl font-bold">&times;</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="treatments.push({text: '', _id: Math.random()})" class="text-sm text-blue-600 hover:underline">+ Add Bullet</button>
            </div>
        </div>

        {{-- Content Blocks --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">6. Extra Text Blocks</h2>
            <div class="space-y-4">
                <template x-for="(block, index) in contentBlocks" :key="block._id">
                    <div class="flex gap-3 items-start p-4 border rounded relative bg-gray-50 mb-4 group">
                        <div class="flex-1 w-full">
                        <input type="text" x-model="block.title" :name="`content_blocks[${index}][title]`" placeholder="Block Heading (e.g. What is Sciatica?)" class="w-full border p-2 rounded mb-2">
                        <div class="bg-white">
                            <input type="hidden" x-model="block.content" :name="`content_blocks[${index}][content]`">
                            <div x-init="initQuill($el, $el.previousElementSibling, val => contentBlocks[index].content = val)"></div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 ml-2">
                        <button type="button" x-show="index > 0" @click="contentBlocks.splice(index - 1, 0, contentBlocks.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Up">↑</button>
                        <button type="button" x-show="index < contentBlocks.length - 1" @click="contentBlocks.splice(index + 1, 0, contentBlocks.splice(index, 1)[0])" class="text-blue-600 hover:text-blue-800" title="Move Down">↓</button>
                        <button type="button" @click="contentBlocks.splice(index, 1)" class="text-red-600 hover:text-red-800 text-xl font-bold">&times;</button>
                    </div>
                </div>
                </template>
                <button type="button" @click="contentBlocks.push({title: '', content: '', _id: Math.random()})" class="text-sm text-blue-600 hover:underline">+ Add Text Block</button>
            </div>
        </div>

        {{-- Related Products --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">7. Related Products</h2>
            <div class="mb-4">
                <label class="block font-bold mb-1">Select Products to Display</label>
                <select name="related_products[]" multiple class="w-full border p-2 rounded" style="min-height: 120px;">
                    @php
                        $dbProducts = is_string($page->related_products) ? json_decode($page->related_products, true) : $page->related_products;
                        $selectedProducts = old('related_products', $dbProducts ?? []);
                        if (!is_array($selectedProducts)) $selectedProducts = [];
                    @endphp
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ in_array($product->id, $selectedProducts) ? 'selected' : '' }}>
                            {{ $product->title }}
                        </option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple products.</p>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold text-lg py-3 rounded">Save Education Page</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function selectLocalImage(quill) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    input.onchange = () => {
        const file = input.files[0];
        if (/^image\//.test(file.type)) {
            const fd = new FormData();
            fd.append('image', file);
            fd.append('folder_type', 'education');
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const token = csrfToken ? csrfToken.getAttribute('content') : '';
            
            fetch('/dainely-admin-panel/editor-upload', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(async r => {
                if (!r.ok) {
                    let err = await r.json().catch(() => ({}));
                    throw new Error(err.message || 'Server error: ' + r.status);
                }
                return r.json();
            })
            .then(result => {
                if (result.success) {
                    const range = quill.getSelection(true) || {index: quill.getLength()};
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else { 
                    alert('Upload failed: ' + (result.message || 'Unknown error')); 
                }
            })
            .catch(e => {
                console.error(e);
                alert('Upload failed: ' + e.message);
            });
        }
    };
}

function educationForm() {
    return {
        figures: (@json(old('figures', $page->figures ?? []))).map(i => ({...i, _id: Math.random()})),
        rootCauses: (@json(old('root_causes', $page->root_causes ?? []))).map(i => ({...i, _id: Math.random()})),
        treatments: (@json(old('treatments', collect($page->treatments ?? [])->map(fn($t) => ['text' => $t])->toArray()))).map(i => ({...i, _id: Math.random()})),
        contentBlocks: (@json(old('content_blocks', $page->content_blocks ?? []))).map(i => ({...i, _id: Math.random()})),
        initQuill(container, hiddenInputEl, updateCallback) {
            if (typeof Quill === 'undefined') {
                setTimeout(() => this.initQuill(container, hiddenInputEl, updateCallback), 100);
                return;
            }
            Quill.register('modules/htmlEditButton', htmlEditButton);
            let quill = new Quill(container, {
                theme: 'snow',
                modules: {
                    htmlEditButton: { msg: 'Edit HTML Code' },
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'clean', 'image']
                        ],
                        handlers: {
                            image: function() {
                                selectLocalImage(this.quill);
                            }
                        }
                    }
                }
            });
            
            let initialVal = hiddenInputEl ? hiddenInputEl.value : '';
            if (initialVal) {
                quill.clipboard.dangerouslyPasteHTML(initialVal);
            }
            
            quill.on('text-change', function() {
                let html = quill.root.innerHTML;
                if (html === '<p><br></p>') html = '';
                if (updateCallback) updateCallback(html);
                if (hiddenInputEl) {
                    hiddenInputEl.value = html;
                    hiddenInputEl.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var el = document.getElementById('layout-sortable');
    if (el) {
        Sortable.create(el, {
            animation: 150,
            onEnd: function (evt) {
                var order = [];
                el.querySelectorAll('[data-id]').forEach(function(item) {
                    order.push(item.getAttribute('data-id'));
                });
                document.getElementById('layout_order_input').value = order.join(',');
            }
        });
    }
});
</script>

@push('admin_head')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
  .ql-editor { min-height: 120px; font-family: inherit; font-size: inherit; }
  .ql-toolbar.ql-snow { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; background: #f8fafc; border-color: #e2e8f0; }
  .ql-container.ql-snow { border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem; border-color: #e2e8f0; }
</style>
@endpush

@push('admin_scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://unpkg.com/quill-html-edit-button@2.2.7/dist/quill.htmlEditButton.min.js"></script>
@endpush
@endsection