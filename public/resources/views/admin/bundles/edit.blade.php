@extends('layouts.admin')

@section('admin_title', 'Edit Bundle Components: ' . $bundle->title)

@section('admin_content')
<div class="space-y-6">
    {{-- Back Button --}}
    <div>
        <a href="/{{ $adminBase ?? 'dainely-admin-panel' }}/bundles" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-navy-600 transition-colors bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm hover:shadow">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Bundles
        </a>
    </div>

    {{-- Bundle Info --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Bundle Settings</h2>
        <form id="bundle-edit-form" action="/{{ $adminBase }}/bundles/{{ $bundle->id }}/update" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                <input type="hidden" name="bundle_shopify_product_id" value="{{ $bundle->bundle_shopify_product_id }}">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Title</label>
                    <input type="text" name="title" value="{{ $bundle->title }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Price ($)</label>
                    <input type="number" name="price" value="{{ $bundle->price }}" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Image</label>
                    <div class="flex items-center gap-4">
                        <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-navy-50 file:text-navy-700 hover:file:bg-navy-100">
                        @php
                            $imgUrl = null;
                            if (is_array($bundle->images) && isset($bundle->images['url'])) {
                                $imgUrl = $bundle->images['url'];
                            } elseif (is_string($bundle->images)) {
                                $decoded = json_decode($bundle->images, true);
                                if (is_array($decoded) && isset($decoded['url'])) $imgUrl = $decoded['url'];
                            }
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="Bundle Image" class="h-10 w-10 object-cover rounded shadow-sm border border-slate-200">
                        @endif
                    </div>
                </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <input type="hidden" name="description" id="hidden-description" value="{{ $bundle->description }}">
                <div id="editor-description" class="bg-white rounded-b-lg border-x border-b border-slate-300" style="min-height: 200px;">
                    {!! $bundle->description !!}
                </div>
            </div>

            <div class="md:col-span-2 mt-6">
                <label class="block text-lg font-bold text-slate-800 mb-4 border-b pb-2">Bundle Components</label>
                
                <div id="components-container" class="space-y-4">
                    @forelse($bundle->items as $index => $item)
                        <div class="component-row space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-lg mt-4 relative">
                            <button type="button" class="remove-row-btn absolute top-2 right-2 px-2 py-1 bg-rose-100 text-rose-600 rounded text-xs hover:bg-rose-200">Remove</button>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                                <select name="components[{{ $index }}][product_id]" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                    <option value="">Select Component Product</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ $item->product_id == $prod->id ? 'selected' : '' }}>{{ $prod->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                                <input type="number" name="components[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1"  required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Qty">
                            </div>
                        </div>
                    @empty
                        <div class="component-row space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                                <select name="components[0][product_id]" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                    <option value="">Select Component Product</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                                <input type="number" name="components[0][quantity]" value="1" min="1"  required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Qty">
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-4 flex justify-center">
                    <button type="button" id="add-component-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-bold py-2 px-4 rounded shadow-sm text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Another Product
                    </button>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-between items-center mt-6 pt-6 border-t border-slate-200">
                <button type="button" onclick="if(confirm('Are you sure you want to permanently delete this bundle? This action cannot be undone.')) { document.getElementById('delete-bundle-form').submit(); }" class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-6 py-3 rounded-lg shadow-sm transition">
                    Delete Bundle
                </button>
                <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white font-bold px-8 py-3 rounded-lg shadow-sm transition">
                    Save All Changes
                </button>
            </div>
        </form>
        <form id="delete-bundle-form" action="/{{ $adminBase }}/bundles/{{ $bundle->id }}/delete" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</div>

@push('admin_scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorElement = document.getElementById('editor-description');
        if (editorElement && typeof Quill !== 'undefined') {
            const quill = new Quill('#editor-description', {
                theme: 'snow',
                modules: {
                    table: true,
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'video'],
                        ['table'],
                        ['clean']
                    ]
                }
            });

            const form = document.getElementById('bundle-edit-form');
            if (form) {
                form.addEventListener('submit', function () {
                    const hiddenDesc = document.getElementById('hidden-description');
                    if (hiddenDesc) {
                        hiddenDesc.value = quill.root.innerHTML;
                    }
                });
            }
        }
        
        // Dynamic components script
        const addBtn = document.getElementById('add-component-btn');
        const container = document.getElementById('components-container');
        if(addBtn && container) {
            let rowCount = {{ max(count($bundle->items), 1) }};
            addBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'component-row space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-lg mt-4 relative';
                row.innerHTML = `
                    <button type="button" class="remove-row-btn absolute top-2 right-2 px-2 py-1 bg-rose-100 text-rose-600 rounded text-xs hover:bg-rose-200">Remove</button>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                        <select name="components[${rowCount}][product_id]" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Component Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ str_replace("'", "\\'", $product->title) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                        <input type="number" name="components[${rowCount}][quantity]" value="1" min="1"  required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Qty">
                    </div>
                `;
                container.appendChild(row);
                rowCount++;
            });

            container.addEventListener('click', function(e) {
                if(e.target.classList.contains('remove-row-btn')) {
                    e.target.closest('.component-row').remove();
                }
            });
        }
    });
</script>
@endpush
@endsection
