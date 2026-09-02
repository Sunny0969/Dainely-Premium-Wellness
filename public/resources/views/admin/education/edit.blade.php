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
                
                @foreach($currentOrder as $sectionKey)
                    @if(isset($labels[$sectionKey]))
                    <div class="bg-white border p-3 rounded shadow-sm flex items-center justify-between cursor-move" data-id="{{ $sectionKey }}">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">⋮⋮</span>
                            <span class="font-semibold">{{ $labels[$sectionKey] }}</span>
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
                    <textarea name="hero_description" class="w-full border p-2 rounded" rows="3">{{ old('hero_description', $page->hero_description) }}</textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded border">
                    <div>
                        <label class="block font-bold mb-1">Hero Image URL</label>
                        <input type="text" name="hero_image" value="{{ old('hero_image', $page->hero_image) }}" class="w-full border p-2 rounded" placeholder="https://...">
                        <span class="text-xs text-gray-500">Provide an external URL...</span>
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
                        <label class="block font-bold mb-1">Author Name (e.g. Dr. Reinholt)</label>
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
                <template x-for="(fig, index) in figures" :key="index">
                    <div class="flex gap-4 items-center">
                        <input type="text" x-model="fig.value" :name="`figures[${index}][value]`" placeholder="Value (e.g. 80%)" class="border p-2 rounded w-1/3">
                        <input type="text" x-model="fig.label" :name="`figures[${index}][label]`" placeholder="Label text" class="border p-2 rounded w-full">
                        <button type="button" @click="figures.splice(index, 1)" class="text-red-500 font-bold">X</button>
                    </div>
                </template>
                <button type="button" @click="figures.push({value: '', label: ''})" class="bg-gray-200 px-3 py-1 rounded text-sm">+ Add Figure</button>
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
                <template x-for="(cause, index) in rootCauses" :key="index">
                    <div class="border p-4 rounded bg-gray-50 relative">
                        <button type="button" @click="rootCauses.splice(index, 1)" class="absolute top-2 right-2 text-red-500 font-bold">Remove</button>
                        <input type="text" x-model="cause.title" :name="`root_causes[${index}][title]`" placeholder="Cause Title" class="w-full border p-2 rounded mb-2">
                        <textarea x-model="cause.description" :name="`root_causes[${index}][description]`" placeholder="Description" class="w-full border p-2 rounded" rows="2"></textarea>
                    </div>
                </template>
                <button type="button" @click="rootCauses.push({title: '', description: ''})" class="bg-gray-200 px-3 py-1 rounded text-sm">+ Add Root Cause</button>
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
                <textarea name="treatments_description" class="w-full border p-2 rounded" rows="2">{{ old('treatments_description', $page->treatments_description) }}</textarea>
            </div>
            <div class="space-y-2">
                <template x-for="(treatment, index) in treatments" :key="index">
                    <div class="flex gap-4 items-center">
                        <input type="text" x-model="treatment.text" :name="`treatments[${index}][text]`" placeholder="Treatment bullet point" class="border p-2 rounded w-full">
                        <button type="button" @click="treatments.splice(index, 1)" class="text-red-500 font-bold">X</button>
                    </div>
                </template>
                <button type="button" @click="treatments.push({text: ''})" class="bg-gray-200 px-3 py-1 rounded text-sm">+ Add Bullet</button>
            </div>
        </div>

        {{-- Extra Content Blocks --}}
        <div>
            <h2 class="text-xl font-bold mb-4 border-b pb-2">6. Extra Text Blocks</h2>
            <div class="space-y-4">
                <template x-for="(block, index) in contentBlocks" :key="index">
                    <div class="border p-4 rounded bg-gray-50 relative">
                        <button type="button" @click="contentBlocks.splice(index, 1)" class="absolute top-2 right-2 text-red-500 font-bold">Remove</button>
                        <input type="text" x-model="block.title" :name="`content_blocks[${index}][title]`" placeholder="Block Heading (e.g. What is Sciatica?)" class="w-full border p-2 rounded mb-2">
                        <textarea x-model="block.content" :name="`content_blocks[${index}][content]`" placeholder="Paragraph text..." class="w-full border p-2 rounded" rows="4"></textarea>
                    </div>
                </template>
                <button type="button" @click="contentBlocks.push({title: '', content: ''})" class="bg-gray-200 px-3 py-1 rounded text-sm">+ Add Text Block</button>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold text-lg py-3 rounded">Save Education Page</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function educationForm() {
    return {
        figures: @json(old('figures', $page->figures ?? [])),
        rootCauses: @json(old('root_causes', $page->root_causes ?? [])),
        treatments: @json(old('treatments', collect($page->treatments ?? [])->map(fn($t) => ['text' => $t])->toArray())),
        contentBlocks: @json(old('content_blocks', $page->content_blocks ?? []))
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
@endsection
